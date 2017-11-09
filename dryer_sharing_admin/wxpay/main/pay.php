<?php
/**
 * Created by PhpStorm.
 * User: Jeremy
 * Date: 2017/3/30
 * Time: 14:48
 */
ini_set('date.timezone', 'Asia/Shanghai');
//error_reporting(E_ERROR);
require_once "../lib/WxPay.Api.php";
require_once "WxPay.JsApiPay.php";
require_once '../lib/log.php';
require_once '../../common/database/adminDB.php';

//初始化日志
$logHandler = new CLogFileHandler("../logs/" . date('Y-m-d') . '.log');
$log = Log::Init($logHandler, 15);

// 获取设备编号
$productId = $_REQUEST['id'];

// 获取设备状态
/*status: 干衣机状态。状态说明：0->待机；1->运行；
2->故障（如果为同一用户，在24h内则需要用户修复，即点击客户端的确认按钮；
        若不为同一用户，直接可付款）；*/
$sql = "select status from device_info where mac_addr = '{$productId}'";
$status = $adminDB->ExecSQL($sql, $conn);
$status = $status[0]['status'];
// 获得时价信息
$sql = "select fee_1, fee_2, fee_add, len_1, len_2, len_add 
from mer_fee_len_rate_broke_view, dev_info_view 
where dev_info_view.mer_id = mer_fee_len_rate_broke_view.mer_id
and dev_info_view.mac_addr = '{$productId}'";
$fee_len = $adminDB->ExecSQL($sql, $conn);

if ($status != 0 || $fee_len == null) {
    if ($fee_len == null) {
        $status = 3;
    }
    if($status == 2){
        // 获取用户openid
        $tools = new JsApiPay();
        $openId = $tools->GetOpenid();
        $status .= '&openId=' . $openId . '&id=' . $productId;
    }
    $url = 'http://' . $_SERVER['HTTP_HOST'] . '/wxpay/main/exception.php?status=' . $status;
    header("Location: $url");
} else {
    // 获取用户openid
    $tools = new JsApiPay();
    $openId = $tools->GetOpenid();
    $fee_1 = $fee_len[0]['fee_1'];
    $fee_2 = $fee_len[0]['fee_2'];
    $fee_add = $fee_len[0]['fee_add'];
    $len_1 = $fee_len[0]['len_1'];
    $len_2 = $fee_len[0]['len_2'];
    $len_add = $fee_len[0]['len_add'];
}
?>

<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <meta name="viewport" content="initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no">
    <script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
    <script src="../js/jquery.js"></script>
    <script src='../js/dialog.js'></script>
    <script src='../js/getJsApiParameters.js'></script>
    <link rel='stylesheet' href='../weui/weui.css'/>
    <title>讯鑫干衣科技</title>
    <style>
        .payForm {
            position: relative;
            top: 5%;
            left: 5%;
            width: 90%;
            align-content: center;
        }

        .payForm span {
            width: 40%;
            height: 35px;
            font-size: larger;
            margin: 2% 0;
        }

        .payForm input, .payForm select {
            font-size: larger;
            width: 70%;
            height: 35px;
            margin: 2% 0;
        }

        .callpay input {
            position: relative;
            width: 100%;
            height: 45px;
            font-size: larger;
        }

        .payCircle {
            position: relative;
            align-content: center;
            width: 80%;
            height: auto;
            left: 10%;
            font-size: larger;
            background-color: transparent;
            border: 0;
        }

        .payCircle img {
            position: relative;
            align-content: center;
            width: 80%;
            height: auto;
            margin: 10%;
            font-size: larger;
            border: 0;
        }

        .payCircle #pay_disabled{
            position: absolute;
            top: 0;
            display: none;
            z-index: 1;
         }

        .payCircle #pay_active{
            position: relative;
            cursor: pointer;
            z-index: 0;
        }

        .payCircle span {
            position: relative;
            align-content: center;
            width: 80%;
            left: 30%;
            height: auto;
            font-size: larger;
        }

        .tip p {
            font-size: small;
        }

        .highLight{
            font-size: x-small;
            color: #FF6600;
        }
    </style>
</head>
<body ontouchstart>

<!--获取openid-->
<div class="info">
    <p hidden="hidden" id="productId"><?php echo $productId; ?></p>
    <p hidden="hidden" id="openId"><?php echo $openId; ?></p>
</div>

<div class="payForm">
    <div class="payCircle">
        <img id="pay_disabled" src="../../logo/pay_bg2.png">
        <img id="pay_active" src="../../logo/pay_bg.png" onclick="GetJsApiParameters(<?php echo $len_1; ?>, <?php echo $fee_1; ?>)">
    </div>

    <button class="weui-btn weui-btn_primary" id="bracket_1" style="display: none;"
            onclick="GetJsApiParameters(<?php echo $len_1; ?>, <?php echo $fee_1; ?>)">
        <span style="display: none">(<?php echo $len_1; ?>分钟)</span>
    </button>
    <button class="weui-btn weui-btn_primary" id="bracket_2" style="display: none;"
            onclick="GetJsApiParameters(<?php echo $len_2; ?>, <?php echo $fee_2; ?>)">
        <span>
            双人套餐
            (<?php echo $len_2; ?>分钟)
        </span>
    </button>

    <button class="weui-btn weui-btn_primary" id="bracket_add" style="display: none;"
            onclick="GetJsApiParameters(<?php echo $len_add; ?>, <?php echo $fee_add; ?>)">
        <span>
            加时套餐
            (<?php echo $len_add; ?>分钟)
        </span>
    </button>
    <!--弹窗-->
    <div id="toast" style="display: none;">
        <div class="weui-mask_transparent"></div>
        <div class="weui-toast">
            <i class="weui-icon-success-no-circle weui-icon_toast"></i>
            <p class="weui-toast__content">微信支付成功</p>
        </div>
    </div>

    <!--数据加载-->
    <div id="loadingToast" style="display:none;">
        <div class="weui-mask_transparent"></div>
        <div class="weui-toast">
            <i class="weui-loading weui-icon_toast"></i>
            <p class="weui-toast__content">数据加载中</p>
        </div>
    </div>

    <!--温馨提示-->
    <div class="weui-cells__tips" style="position: relative; top: 60%;">
        <article class="weui_article">
            <section class="tip">
                <p class="title">温馨提示</p>
                <section>
                    <p>1、支付一次即可使用干衣机<span class="highLight"><?php echo $len_1; ?>分钟</span>，这<span class="highLight"><?php echo $len_1; ?>分钟</span>让您告别"衣"身麻烦。</p>
                </section>
                <section>
                    <p>2、请确保您即将支付的干衣机是否发生故障或正在运行，如机器处于故障或运行状态，你的付款将失败。</p>
                </section>
            </section>
        </article>
    </div>
</div>
</body>
</html>
<script>
    $(function() {
        dialog('温馨提示', '请确保放入干衣机的衣物已甩干', '确认', '',
            function () {});
    })
</script>