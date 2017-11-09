<?php
/**
 * Created by PhpStorm.
 * User: Jeremy
 * Date: 2017/6/5
 * Time: 15:23
 */
session_start();
if (!isset($_SESSION['system'])) {
    header('location: ../login.html');
}
$id = $_SESSION['system'];

/* 设置响应数据的内容格式，和字符集*/
header('Content-type:text/html;charset=utf-8');
// 引入数据库操作函数
require_once '../../common/database/adminDB.php';
// 读入审核结果
$sql = "select mer_name, addr_detail, pro_name, city_name, name, phone, email 
from merchant_info, admin, province, city 
where merchant_info.mer_id = (select admin.mer_id from admin where username = '{$id}') 
and admin.username = '{$id}'
and city.city_id = merchant_info.city_id 
and city.pro_id = province.pro_id";
$mer_info = $adminDB->ExecSQL($sql, $conn);
$mer_name = $mer_info[0]['mer_name'];
$prov = $mer_info[0]['pro_name'];
$city = $mer_info[0]['city_name'];
$dist = $mer_info[0]['addr_detail'];
$name = $mer_info[0]['name'];
$phone = $mer_info[0]['phone'];
$email = $mer_info[0]['email'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <link type="text/css" href="../../common/bootstrap/css/iF.step.css" rel="stylesheet">
    <link rel="stylesheet" href="../../common/bootstrap/css/weui.css"/>
    <script src="../../common/bootstrap/js/jquery.js"></script>
    <script type="text/javascript" src="../../common/bootstrap/js/jquery.cityselect.js"></script>
    <script src='../../common/bootstrap/js/dialog.js'></script>
    <title>商户注册</title>
    <style>
        .iframe_content {
            position: relative;
            top: 0;
            left: 5%;
            width: 90%;
            height: 300px;
            font-size: 1em;
            vertical-align: middle;
        }

        .mer_info {
            position: relative;
            top: 0;
            left: 0;
            width: 90%;
            padding: 5%;
            vertical-align: middle;
            /*background-color: #f1f1f1;*/
        }

        .mer_info .label {
            position: relative;
            top: 0;
            left: 0;
            width: 30%;
            height: 30px;
            margin-bottom: 3.5%;
        }

        .mer_info input {
            position: relative;
            top: 0;
            left: 1.5%;
            width: 70%;
            height: 30px;
            font-size: 0.8em;
            margin-bottom: 3.5%;
        }

        .mer_info .flag {
            position: relative;
            top: 0;
            left: 1.5%;
            height: 30px;
            font-size: 0.5em;
            margin-bottom: 3.5%;
            vertical-align: middle;
            color: red;
        }

        .img_flag {
            display: none;
            width: 4%;
            height: 4%;
            margin-left: 2%;
            vertical-align: middle;
        }

        #prov, #city, #dist {
            position: relative;
            top: 0;
            left: 1.5%;
            width: 15%;
            height: 30px;
            font-size: 0.8em;
            margin-bottom: 3.5%;
        }

        .img_mer_charter {
            position: relative;
            top: 0;
            left: 0;
            width: 100%;
            font-size: 0.8em;
            margin-bottom: 3.5%;
        }

        .showInfo {
            padding: 5px;
        }
    </style>
</head>
<body onload="showMerInfo()">
<!--网页主体-->
<div class="iframe_content">
    <!--设置商户信息-->
    <div class='mer_info' style="display: block;">
        <span class="label">公司名称</span>
        <label>
            <input type="text" name="mer_name" id="mer_name" disabled="disabled"/>
        </label>
        <br/>

        <span class="label">公司地址</span>
        <label id="mer_address">
            <select class="prov" name="prov" id="prov" disabled="disabled"></select>
            <select class="city" name="city" id="city" disabled="disabled"></select>
        </label>
        <input class="dist" style="width: 39%;" type="text" name="dist" id="dist"
               disabled="disabled" onblur="check(6, this.value)"/>
        <span class="flag" id="mer_address_check">*</span>
        <img class="img_flag" src="../../logo/ok.png" id="mer_address_ok">
        <br/>

        <span class="label">&nbsp;&nbsp;&nbsp;&nbsp;负责人</span>
        <label>
            <input type="text" name="name" id="name"
                   disabled="disabled" onblur="check(8, this.value)"/>
            <span class="flag" id="name_check">*</span>
            <img class="img_flag" src="../../logo/ok.png" id="name_ok">
        </label>
        <br/>

        <span class="label">联系方式</span>
        <label>
            <input type="tel" name="phone" id="phone"
                   disabled="disabled" onblur="check(9, this.value)"/>
            <span class="flag" id="phone_check">*</span>
            <img class="img_flag" src="../../logo/ok.png" id="phone_ok">
        </label>
        <br/>

        <span class="label">常用邮箱</span>
        <label>
            <input type="email" name="email" id="email"
                   disabled="disabled" onblur="check(10, this.value)"/>
            <span class="flag" id="email_check">*</span>
            <img class="img_flag" src="../../logo/ok.png" id="email_ok">
        </label>
        <br/>
        <div>
            <button class="weui-btn weui-btn_primary" id="modify"
                    style="width: 30%;" onclick="modify()">
                <span id="btn_name">修改</span>
            </button>
        </div>
    </div>
</div>

<!--弹窗-->
<div id="toast" style="display: none;">
    <div class="weui-mask_transparent"></div>
    <div class="weui-toast">
        <i class="weui-icon-success-no-circle weui-icon_toast"></i>
        <p class="weui-toast__content"></p>
    </div>
</div>
</body>
</html>

<script>
    // 显示企业信息
    function showMerInfo() {
        $("#mer_address").citySelect({
            url: "../../common/bootstrap/js/city.min.js",
            prov: "<?php echo $prov;?>",
            city: "<?php echo $city;?>"
        });

        var mer_name = "<?php echo $mer_name;?>";
        var dist = "<?php echo $dist;?>";
        var name = "<?php echo $name;?>";
        var phone = "<?php echo $phone;?>";
        var email = "<?php echo $email;?>";
        $('#mer_name').val(mer_name);
        $('#dist').val(dist);
        $('#name').val(name);
        $('#phone').val(phone);
        $('#email').val(email);
    }

    // 检查信息
    function check(type, data) {
        var url = 'register_verify.php';
        /*1: 检查账号；2: 检查密码；3: 检查确认密码*/
        if (type == 1) {
            if (data == "") {
                $('#id_ok').hide();
                $('#id_check').text('*账号不能为空');
                $('#id_check').show();
                $('#pwd').attr("disabled", true);
                $('#re_pwd').attr("disabled", true);
                $('#next').attr("disabled", true);
                console.log($('#id_check').text());
            }
            else {
                $.post(url, {'type': type, 'id': data}, function (res) {//注意jquery的$.post的第2个参数必须是键值对形式
                    if (res == 'OK') {
                        $('#id_ok').show();
                        $('#id_check').hide();
                        $('#id_check').text('*账号OK');
                        $('#pwd').attr("disabled", false);
                        $('#re_pwd').attr("disabled", false);
                        console.log($('#id_check').text());
                    }
                    if (res == 'EXITS') {
                        $('#id_ok').hide();
                        $('#id_check').text('*该账号已被注册');
                        $('#id_check').show();
                        $('#pwd').attr("disabled", true);
                        $('#re_pwd').attr("disabled", true);
                        $('#next').attr("disabled", true);
                        console.log($('#id_check').text());
                    }
                });
            }

        }

        else if (type == 2) {
            if (data == "") {
                $('#pwd_ok').hide();
                $('#pwd_check').text('*密码不能为空');
                $('#pwd_check').show();
            }
            else {
                $('#pwd_check').hide();
                $('#pwd_ok').show();
                $('#re_pwd').val('');
                $('#re_pwd_ok').hide();
                $('#pwd_check').hide();
                $('#pwd_check').text('*密码OK');
                $('#re_pwd_check').text('*');
                $('#re_pwd_check').show();
            }
        }

        else if (type == 3) {
            var pwd = $("#pwd").val();
            if (data == "") {
                $('#re_pwd_ok').hide();
                $('#re_pwd_check').text('*密码不能为空');
                $('#re_pwd_check').show();
            }

            else if (data != pwd) {
                $('#re_pwd_ok').hide();
                $('#re_pwd_check').text('*密码输入不一致');
                $('#re_pwd_check').show();
            }

            else if (data == pwd) {
                console.log($('#id_check').text());
                $('#re_pwd_check').hide();
                $('#re_pwd_check').text('*确认密码OK');
                $('#re_pwd_ok').show();
            }
        }

        /*5: 检查公司；2: 检查公司地点；3: 检查营业执照*/
        else if (type == 5) {
            if (data == "") {
                $('#mer_name_ok').hide();
                $('#mer_name_check').text('*名称不能为空');
                $('#mer_name_check').show();
                $('#prov').attr("disabled", true);
                $('#city').attr("disabled", true);
                $('#dist').attr("disabled", true);
                $('#mer_charter').attr("disabled", true);
                $('#mer_name').focus();
            }
            else {
                $('#mer_name_ok').show();
                $('#mer_name_check').hide();
                $('#mer_name_check').text('*公司名称OK');
                $('#prov').attr("disabled", false);
                $('#city').attr("disabled", false);
                $('#dist').attr("disabled", false);
                $('#mer_charter').attr("disabled", false);
            }
        }

        else if (type == 6) {
            if (data == "") {
                $('#mer_address_ok').hide();
                $('#mer_address_check').text('*具体地址不能为空');
                $('#mer_address_check').show();
            }
            else {
                $('#mer_address_ok').show();
                $('#mer_address_check').hide();
                $('#mer_address_check').text('*具体地址OK');
            }
        }

        else if (type == 7) {
            var status = changeToop('mer_charter', 'img_mer_charter');
            if (status == '*营业执照OK') {
                $('#mer_charter_ok').show();
                $('#mer_charter_check').hide();
                $('#mer_charter_check').text(status);
                $('#img_mer_charter').show();
            }
            else {
                $('#mer_charter_ok').hide();
                $('#mer_charter_check').text(status);
                $('#mer_charter_check').show();
            }
        }

        else if (type == 8) {
            if (data == "") {
                $('#name_ok').hide();
                $('#name_check').text('*姓名不能为空');
                $('#name_check').show();
            }
            else {
                $('#name_ok').show();
                $('#name_check').hide();
                $('#name_check').text('*负责人姓名OK');
            }
        }

        else if (type == 9) {
            if (data == "") {
                $('#phone_ok').hide();
                $('#phone_check').text('*联系方式不能为空');
                $('#phone_check').show();
            }
            else {
                var phone = $('#phone').val();
                var verify_phone = /(\(\d{3,4}\)|\d{3,4}-|\s)?\d{7,14}/;
                if (verify_phone.test(phone)) {
                    $('#phone_ok').show();
                    $('#phone_check').hide();
                    $('#phone_check').text('*联系方式OK');
                }
                else {
                    $('#phone_ok').hide();
                    $('#phone_check').text('*联系方式无效');
                    $('#phone_check').show();
                }
            }
        }

        else if (type == 10) {
            if (data == "") {
                $('#email_ok').hide();
                $('#email_check').text('*常用邮箱不能为空');
                $('#email_check').show();
            }
            else {
                var email = $('#email').val();
                var vertify_email = /\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/;
                if (vertify_email.test(email)) {
                    $('#email_ok').show();
                    $('#email_check').hide();
                    $('#email_check').text('*常用邮箱OK');
                }
                else {
                    $('#email_ok').hide();
                    $('#email_check').text('*常用邮箱无效');
                    $('#email_check').show();
                }
            }
        }

        // next是否可按
        var id_check_end = $('#id_check').text();
        var pwd_end = $('#pwd_check').text();
        var re_pwd_end = $('#re_pwd_check').text();
        if (id_check_end == '*账号OK'
            && pwd_end == '*密码OK'
            && re_pwd_end == '*确认密码OK') {
            $('#next').attr("disabled", false);
        }
        else {
            $('#next').attr("disabled", true);
        }

        // next2是否可按
        var mer_name_check_end = $('#mer_name_check').text();
        var mer_address_check_end = $('#mer_address_check').text();
        var mer_charter_check_end = $('#mer_charter_check').text();
        var name_check_end = $('#name_check').text();
        var phone_check_end = $('#phone_check').text();
        var email_check_end = $('#email_check').text();
        if (mer_name_check_end == '*公司名称OK'
            && mer_address_check_end == '*具体地址OK'
            && mer_charter_check_end == '*营业执照OK'
            && name_check_end == '*负责人姓名OK'
            && phone_check_end == '*联系方式OK'
            && email_check_end == '*常用邮箱OK') {
            $('#next2').attr("disabled", false);
        }
        else {
            $('#next2').attr("disabled", true);
        }
    }

    // 修改按钮
    function modify() {
        var btn_name = $('#btn_name').text();
        if (btn_name == '修改') {
            $('#btn_name').text('确认修改');
            $("#mer_address").citySelect({
                url: "../../common/bootstrap/js/city.min.js",
                prov: "<?php echo $prov;?>",
                city: "<?php echo $city;?>"
            });
            $('#prov').attr('disabled', false);
            $('#city').attr('disabled', false);
            $('#dist').attr('disabled', false);
            $('#name').attr('disabled', false);
            $('#phone').attr('disabled', false);
            $('#email').attr('disabled', false);
        }
        else {
            $('#btn_name').text('修改');
            $('input').attr('disabled', true);
            $('#prov').attr('disabled', true);
            $('#city').attr('disabled', true);

            // 更新数据
            var id = "<?php echo $id;?>";
            var mer_name = $('#mer_name').val();
            var prov = $('#prov').val();
            var city = $('#city').val();
            var dist = $('#dist').val();
            var name = $('#name').val();
            var phone = $('#phone').val();
            var email = $('#email').val();
            var url = 'modify.php';
            $.post(url, {
                'id': id, 'mer_name': mer_name,
                'prov': prov, 'city': city, 'dist': dist, 'name': name,
                'phone': phone, 'email': email
            }, function (res) {
                console.log(res);
                if (res == 'SUCCESS') {
                    toast('修改完成');
                }
            });
        }
    }
</script>