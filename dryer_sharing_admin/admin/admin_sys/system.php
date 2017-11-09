<?php
/**
 * Created by PhpStorm.
 * User: Jeremy
 * Date: 2017/4/21
 * Time: 16:12
 */

session_start();
if (!isset($_SESSION['system'])) {
    header('location: ../login.html');
}
$id = $_SESSION['system'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <script src="../../common/bootstrap/js/jquery.js"></script>
    <link rel="stylesheet" href="../../common/bootstrap/css/style.css"/>
    <link rel="shortcut icon" href="../../logo/logo.ico"/>
    <link rel="bookmark" href="../../logo/logo.ico"/>
    <title>讯鑫干衣科技后台管理系统</title>
</head>
<body>

<!--标题-->
<div class="logo">
    <img src="../../logo/logo.JPG" style="height: 50px; width: 100px;"><span>讯鑫干衣科技</span>
    <span id="welcome">您好，<?php echo $id;?></span>
    <a id="logout" href="../login.html">注销登录</a>
    <hr/>
</div>

<!--网页主体-->
<div class="content">
    <!--菜单栏-->
    <div id="menu">
        <ul class="mainMenus" id="sys_info_main">
            <li>
                <a class="link" id="mer_info" href="javascript:void(0)" target="showHere">账户信息</a>
                <ul class="subMenus" id="mer_info_sub">
                    <li>
                        <a class="link" id="sys_info" href="func_sys_info.php" target="showHere">企业信息</a>
                    </li>
                    <li>
                        <a class="link" id="change_pwd" href="func_chgpwd.php" target="showHere">修改密码</a>
                    </li>
                </ul>
            </li>
        </ul>
        <ul class="mainMenus" id="audit_info_main">
            <li>
                <a class="link" id="audit_info" href="javascript:void(0)" target="showHere">商户信息
                    <div id="main_wait_num"
                         style="position: absolute; top: 1px; margin-left: 79%; width: 20px; height: 20px; background-color:#F00; border-radius: 10px;">
                        <span class="wait_num"
                              style="height: 10px; line-height: 20px; display:block; color:#FFF; text-align:center; font-size: small"></span>
                    </div>
                </a>
                <ul class="subMenus" id="audit_info_sub">
                    <li>
                        <a class="link" id="wait" href="func_wait_audit.php" target="showHere">待审核
                            <div id="sub_wait_num"
                                 style="position: absolute; top: 1px; margin-left: 69%; width: 16px; height: 16px; background-color:#F00; border-radius: 8px;">
                                <span class="wait_num"
                                      style="height: 8px; line-height: 16px; display:block; color:#FFF; text-align:center; font-size: small"></span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a class="link" id="audited" href="func_audited.php" target="showHere">已通过</a>
                    </li>
                    <li>
                        <a class="link" id="rejected" href="func_rejected.php" target="showHere">已拒绝</a>
                    </li>
                </ul>
            </li>
        </ul>
        <ul class="mainMenus" id="device_info_main">
            <li>
                <a class="link" id="device_info" href="javascript:void(0)" target="showHere">设备信息</a>
                <ul class="subMenus" id="device_info_sub">
                    <li>
                        <a class="link" id="to_allocation" href="func_dev_wait_allocation.php" target="showHere">待分配</a>
                    </li>
                    <li>
                        <a class="link" id="allocatd" href="func_dev_allocated.php" target="showHere">已分配</a>
                    </li>
                </ul>
            </li>
        </ul>
        <ul class="singleMenus">
            <li><a class="link" id="param_set" href="func_fee_len_rate_brokerage.php" target="showHere">参数设置</a></li>
        </ul>
        <ul class="singleMenus">
            <li><a class="link" id="statistic" href="func_get_statistic.php" target="showHere">数据统计</a></li>
        </ul>
    </div>

    <!--frame-->
    <div class="iframe">
        <iframe name="showHere"></iframe>
    </div>
</div>
</body>
</html>

<script>
    // comet实现
    $(function () {
        (function longPolling() {
            $.ajax({
                url: "get_wait_audit_tip.php",
                data: {"timed": Date.parse(new Date()) / 1000},
                dataType: "text",
                timeout: 5000, //5秒超时，可自定义设置
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    $(".wait_num").text('');
                    if (textStatus == "timeout") { // 请求超时
                        longPolling(); // 递归调用
                    } else { // 其他错误，如网络错误等
                        longPolling();
                    }
                },
                success: function (data, textStatus) {
                    if (data != 0) {
                        $(".wait_num").text(data);
                        $('#main_wait_num').show();
                        $('#sub_wait_num').show();
                    }
                    else {
                        $('#main_wait_num').hide();
                        $('#sub_wait_num').hide();
                    }
                    if (textStatus == "success") { // 请求成功
                        longPolling();
                    }
                }
            });

        })();
    });

    // 账户信息下拉菜单
    $('#mer_info').click(function () {
        $(".link").addClass('notActive');
        $(".subMenus").slideUp();
        if ($('#mer_info_sub').is(':hidden')) {
            $("#mer_info_sub").slideDown();
        }
        $("#mer_info").removeClass('notActive').addClass('active');
    });

    $('#sys_info').click(function () {
        $(".link").addClass('notActive');
        $("#mer_info").removeClass('notActive').addClass('active');
        $("#sys_info").removeClass('notActive').addClass('subActive');
    });

    $('#change_pwd').click(function () {
        $(".link").addClass('notActive');
        $("#mer_info").removeClass('notActive').addClass('active');
        $("#change_pwd").removeClass('notActive').addClass('subActive');
    });

    // 商户信息下拉菜单
    $('#audit_info').click(function () {
        $(".link").addClass('notActive');
        $(".subMenus").slideUp();
        if ($('#audit_info_sub').is(':hidden')) {
            $("#audit_info_sub").slideDown();
        }
        $("#audit_info").removeClass('notActive').addClass('active');
    });

    $('#wait').click(function () {
        $(".link").addClass('notActive');
        $("#audit_info").removeClass('notActive').addClass('active');
        $("#wait").removeClass('notActive').addClass('subActive');
    });

    $('#audited').click(function () {
        $(".link").addClass('notActive');
        $("#audit_info").removeClass('notActive').addClass('active');
        $("#audited").removeClass('notActive').addClass('subActive');
    });

    $('#rejected').click(function () {
        $(".link").addClass('notActive');
        $("#audit_info").removeClass('notActive').addClass('active');
        $("#rejected").removeClass('notActive').addClass('subActive');
    });

    //设备信息
    $('#device_info').click(function () {
        $(".link").addClass('notActive');
        $(".subMenus").slideUp();
        if ($('#device_info_sub').is(':hidden')) {
            $("#device_info_sub").slideDown();
        }
        $("#device_info").removeClass('notActive').addClass('active');
    });

    $('#to_allocation').click(function () {
        $(".link").addClass('notActive');
        $("#device_info").removeClass('notActive').addClass('active');
        $("#to_allocation").removeClass('notActive').addClass('subActive');
    });

    $('#allocatd').click(function () {
        $(".link").addClass('notActive');
        $("#device_info").removeClass('notActive').addClass('active');
        $("#allocatd").removeClass('notActive').addClass('subActive');
    });

    // 参数设置
    $('#param_set').click(function () {
        $(".link").addClass('notActive');
        $(".subMenus").slideUp();
        $("#param_set").removeClass('notActive').addClass('active');
    });

    // 统计信息
    $('#statistic').click(function () {
        $(".link").addClass('notActive');
        $(".subMenus").slideUp();
        $("#statistic").removeClass('notActive').addClass('active');
    });
</script>