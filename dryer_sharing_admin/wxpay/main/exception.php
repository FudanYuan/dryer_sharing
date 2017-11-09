<?php
/**
 * Created by PhpStorm.
 * User: Jeremy
 * Date: 2017/3/31
 * Time: 21:37
 */
include_once "../../common/database/adminDB.php";
require_once "../lib/jssdk.php";
require_once "../lib/WxPay.Api.php";

$jssdk = new JSSDK(WxPayConfig::APPID, WxPayConfig::APPSECRET);
$signPackage = $jssdk->getSignPackage();
// 获取设备状态
$status = $_REQUEST['status'];
if(isset($_REQUEST['openId']) && isset($_REQUEST['id'])){
    $openId = $_REQUEST['openId'];
    $id = $_REQUEST['id'];
}
else{
    $openId = 'NULL';
    $id = 'NULL';
}
$msg = '';
if ($status == '') {
    $msg = '该设备不存在！';
}
else {
    switch($status) {
        case '1':
            $msg = '设备正在运行，无法进行后续操作，请尝试其他干衣机';
            break;
        case '2':
            // 首先，判断该设备是否通过微信支付使用过，即判断该设备是否有用户消费记录
            $sql = "select device_info.dev_id, mac_addr, balance, status, user_id, consume_t from device_info, consume_rec 
            where mac_addr = '{$id}' and
            device_info.dev_id = consume_rec.dev_id
            ORDER BY consume_t desc LIMIT 1";
            $result = $adminDB->ExecSQL($sql, $conn);
            $user = $result[0]['user_id'];
            $result[0]['consume_t'] = strtotime($result[0]['consume_t']);
            if ($user == $openId) { // 如果用户一致，则比较时间戳相差是否大于24
                $now = time();
                $effect = ($now - $result[0]['consume_t'] >= 86400) ? 0 : 1;
                if (!$effect) {// 付款失效，设备余额归零，并重新付款
                    $sql = "update device_info set balance = 0, status = 0 
                      where mac_addr = '{$id}'";
                    $adminDB->ExecSQL($sql, $conn);
                    $url = 'http://' . $_SERVER['HTTP_HOST'] . '/wxpay/main/pay.php?id=' . $id;
                    header("Location: $url");
                } else {// 代表付款未失效，此时要等待客户端将status=1，即用户确认（修复）
                    $msg = '您有一笔未结束的交易，确认继续';
                }
            } else { // 如果用户不一致，则直接返回
                $sql = "update device_info set balance = 0, status = 0
                      where mac_addr = '{$id}'";
                $adminDB->ExecSQL($sql, $conn);
                $url = 'http://' . $_SERVER['HTTP_HOST'] . '/wxpay/main/pay.php?id=' . $id;
                header("Location: $url");
            }
            break;
        case '3':
            $msg = '设备未分配，不可使用';
            break;
    }
}
?>

<html>
<head>
    <title>警告</title>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <meta name="viewport" content="initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no">
    <script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
    <script src='../js/dialog.js'></script>
    <script src="../js/jquery.js"></script>
    <script src="../js/scan.js"></script>
    <link rel='stylesheet' href='../weui/dist/style/weui.css'/>
    <style>
        body {
            height: 100%;
            width: 100%;
            font-size: larger;
        }
    </style>
</head>
<body ontouchstart>
<!--数据加载-->
<div id="loadingToast" style="display:none;">
    <div class="weui-mask_transparent"></div>
    <div class="weui-toast">
        <i class="weui-loading weui-icon_toast"></i>
        <p class="weui-toast__content">努力加载中</p>
    </div>
</div>

<!--弹窗-->
<div id="toast" style="display: none;">
    <div class="weui-mask_transparent"></div>
    <div class="weui-toast">
        <i class="weui-icon-success-no-circle weui-icon_toast"></i>
        <p class="weui-toast__content">操作成功</p>
    </div>
</div>
</body>
<script>
    var appId = '<?php echo $signPackage["appId"];?>';
    var timestamp =  <?php echo $signPackage["timestamp"];?>;
    var nonceStr =  '<?php echo $signPackage["nonceStr"];?>';
    var signature =  '<?php echo $signPackage["signature"];?>';

    var type = '警告';
    var status = '<?php echo $status;?>';
    var msg = '<?php echo $msg; ?>';

    var xmlHttp;
    if(status == '2'){
        dialog(type, msg, '确认', '取消',
            function () {

                var id = '<?php echo $id;?>';
                fix(id);
            },
            function () {
                WeixinJSBridge.call('closeWindow');
            });
    }
    else{
        dialog(type, msg, '扫一扫', '取消',
            function () {
                scanner(appId, timestamp, nonceStr, signature);
            },
            function () {
                WeixinJSBridge.call('closeWindow');
            });
    }

    // 异步获取js参数
    function fix(id) {
        if (id) {
            // 异步通信
            xmlHttp = GetXmlHttpObject();
            if (xmlHttp == null) {
                alert("Browser does not support HTTP Request");
                return;
            }
            var url = "fix.php";
            url += "?id=" + id + "&uid=" + Math.random();
            xmlHttp.onreadystatechange = getJsApiParameters;
            xmlHttp.open("GET", url, true);
            xmlHttp.send(null);
            // loading
            var $loadingToast = $('#loadingToast');
            $loadingToast.fadeIn(100);
            t = setTimeout(function () {
                $loadingToast.fadeOut(100);
            }, 2000);
        }
    }

    // 获取服务器返回
    function getJsApiParameters() {
        if (xmlHttp.readyState == 4 || xmlHttp.readyState == "OK") {
            // 结束加载
            var $loadingToast = $('#loadingToast');
            clearTimeout(t);
            $loadingToast.fadeOut(100);

            var ret = xmlHttp.responseText;
            if(ret == 'SUCCESS'){
                var $toast = $('#toast');
                if ($toast.css('display') != 'none') return;
                $toast.fadeIn(100);
                setTimeout(function () {
                    $toast.fadeOut(100);
                }, 2000);
                WeixinJSBridge.call('closeWindow');
            }else{
                dialog('警告', '操作失败', '扫一扫', '取消',
                    function () {
                        scanner(appId, timestamp, nonceStr, signature);
                    },
                    function () {
                        WeixinJSBridge.call('closeWindow');
                    });
            }
        }
    }

    // 得到服务器返回值json格式
    function GetXmlHttpObject() {
        var xmlHttp = null;
        try {
            // Firefox, Opera 8.0+, Safari
            xmlHttp = new XMLHttpRequest();
        }
        catch (e) {
            //Internet Explorer
            try {
                xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
            }
            catch (e) {
                xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
            }
        }
        return xmlHttp;
    }

</script>
</html>
