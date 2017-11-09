<?php
/**
 * Created by PhpStorm.
 * User: Jeremy
 * Date: 2017/4/21
 * Time: 16:12
 */
session_start();
if (!isset($_SESSION['merchant'])) {
    header('location: ../login.html');
}
$id = $_SESSION['merchant'];

/* 设置响应数据的内容格式，和字符集*/
header('Content-type:text/html;charset=utf-8');
// 引入数据库操作函数
require_once '../../common/database/adminDB.php';
// 读入审核结果
$sql = "select audit from admin where username = '{$id}'";
$audit_flag = $adminDB->ExecSQL($sql, $conn);
$audit_flag = $audit_flag[0]['audit'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <script src="../../common/bootstrap/js/jquery.js"></script>
    <link rel="stylesheet" href="../../common/bootstrap/css/style.css"/>
    <title>讯鑫干衣科技后台管理系统</title>
    <link rel="shortcut icon" href="../../logo/logo.ico"/>
    <link rel="bookmark" href="../../logo/logo.ico"/>

</head>
<body onload="audit()">

<!--标题-->
<div class="logo">
    <img src="../../logo/IMG_2748.JPG" style="height: 50px; width: 100px;"><span>讯鑫干衣科技</span>
    <span id="welcome">您好，<?php echo $id;?></span>
    <a id="logout" href="../login.html">注销登录</a>
    <hr/>
</div>

<!--网页主体-->
<div class="content">
    <!--菜单栏-->
    <div id="menu">
        <ul class="mainMenus" id="mer_info_main">
            <li>
                <a class="audit" id="account_info" href="javascript:void(0)" target="showHere">账户信息</a>
                <ul class="subMenus" id="mer_info_sub">
                    <li>
                        <a class="_audit" id="audit_info" href="func_audit_info.php" target="showHere">审核信息</a>
                    </li>
                    <li>
                        <a class="audit" id="mer_info" href="func_mer_info.php" target="showHere">企业信息</a>
                    </li>
                    <li>
                        <a class="audit" id="change_pwd" href="func_chgpwd.php" target="showHere">修改密码</a>
                    </li>
                </ul>
            </li>
        </ul>

        <ul>
            <li><a class="audit" id="statistic" href="func_get_statistic.php" target="showHere">数据统计</a></li>
        </ul>
    </div>

    <div class="iframe">
        <iframe name="showHere">
        </iframe>
    </div>
</div>
</body>
</html>

<script>
    // 如果审核未通过，则只允许查看审核信息
    function audit() {
        var flag = <?php echo $audit_flag;?>;
        if (flag != 3) {
            $('.audit').attr('href', 'func_audit_info.php');
        }else{
            $('#audit_info').hide();
        }
    }

    // 账户信息下拉菜单
    $('#account_info').click(function () {
        $("a").addClass('notActive');
        $(".subMenus").slideUp();
        if ($('#mer_info_sub').is(':hidden')) {
            $("#mer_info_sub").slideDown();
        }
        $("#account_info").removeClass('notActive').addClass('active');
    });

    $('#audit_info').click(function () {
        $("a").addClass('notActive');
        $("#account_info").removeClass('notActive').addClass('active');
        $("#audit_info").removeClass('notActive').addClass('subActive');
    });

    $('#mer_info').click(function () {
        $("a").addClass('notActive');
        $("#account_info").removeClass('notActive').addClass('active');
        $("#mer_info").removeClass('notActive').addClass('subActive');
    });

    $('#change_pwd').click(function () {
        $("a").addClass('notActive');
        $("#account_info").removeClass('notActive').addClass('active');
        $("#change_pwd").removeClass('notActive').addClass('subActive');
    });

    // 统计信息
    $('#statistic').click(function () {
        $("a").addClass('notActive');
        $(".subMenus").slideUp();
        $("#statistic").removeClass('notActive').addClass('active');
    });
</script>
