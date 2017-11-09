<?php
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
    <script src="../../common/bootstrap/js/jquery.js"></script>
    <script src='../../common/bootstrap/js/dialog.js'></script>
    <script type="text/javascript" src="../../common/bootstrap/js/jquery.cityselect.js"></script>
    <script src="../../common/bootstrap/js/showImg.js"></script>
    <link type="text/css" href="../../common/bootstrap/css/iF.step.css" rel="stylesheet">
    <link rel="stylesheet" href="../../common/bootstrap/css/weui.css"/>
    <title>审核信息</title>
    <style>
        .iframe_content {
            position: relative;
            top: 5px;
            left: 5%;
            width: 90%;
            height: 300px;
            font-size: 1em;
            vertical-align: middle;
        }

        .progress {
            position: relative;
            top: 0;
            left: 0;
            width: 90%;
            padding: 5%;
            text-align: center;
            vertical-align: middle;
            background-color: transparent;
            border: 0;
        }

        .form {
            position: relative;
            top: 0;
            left: 15%;
            width: 60%;
            padding: 5%;
            font-size: 1.1em;
            vertical-align: middle;
            background-color: #f1f1f1;
        }

        .form .label {
            position: relative;
            top: 0;
            left: 0;
            width: 30%;
            height: 30px;
            font-size: 0.9em;
        }

        .form input {
            position: relative;
            top: 0;
            left: 1.5%;
            width: 70%;
            height: 30px;
            font-size: 0.8em;
            margin-bottom: 3.5%;
        }

        .form .flag {
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
        }

        #next2 {
            position: relative;
            top: 0;
            left: 0;
            width: 50%;
            font-size: 0.9em;
            cursor: pointer;
        }

        #next2:disabled {
            background-color: rgba(107, 202, 106, 0.5);
        }

        .form p{
            text-align: center;
        }
    </style>
</head>
<body onload="getStauts()">

<!--iframe内容-->
<div class="iframe_content">
    <!--进度条-->
    <div class="progress">
        <ol class="ui-step ui-step-4">
            <li class="step-start step-done" id="step-start step-done">
                <div class="ui-step-line"></div>
                <div class="ui-step-cont">
                    <span class="ui-step-cont-number">1</span>
                    <span class="ui-step-cont-text">设置账户密码</span>
                </div>
            </li>
            <li class="step-next-1" id="step-next-1">
                <div class="ui-step-line"></div>
                <div class="ui-step-cont">
                    <span class="ui-step-cont-number">2</span>
                    <span class="ui-step-cont-text">填写公司信息</span>
                </div>
            </li>
            <li class="step-next-2" id="step-next-2">
                <div class="ui-step-line"></div>
                <div class="ui-step-cont">
                    <span class="ui-step-cont-number">3</span>
                    <span class="ui-step-cont-text">审核填写信息</span>
                </div>
            </li>

            <li class="step-end" id="step-end">
                <div class="ui-step-line"></div>
                <div class="ui-step-cont">
                    <span class="ui-step-cont-number">4</span>
                    <span class="ui-step-cont-text">审核结果</span>
                </div>
            </li>
        </ol>
    </div>

    <!--设置商户信息-->
    <div class='form' id="step2" style="display: none;">
        <span class="label">公司名称</span>
        <label>
            <input type="text" name="mer_name" id="mer_name" placeholder="请输入公司名称" onblur="check(5, this.value)"/>
            <span class="flag" id="mer_name_check">*</span>
            <img class="img_flag" src="../../logo/ok.png" id="mer_name_ok">
        </label>
        <br/>

        <span class="label">公司地址</span>
        <label id="mer_address">
            <select class="prov" name="prov" id="prov"></select>
            <select class="city" name="city" id="city"></select>
        </label>
        <input class="dist" style="width: 38.5%;" type="text" name="dist" id="dist" placeholder="请输入具体地址"
               onblur="check(6, this.value)"/>
        <span class="flag" id="mer_address_check">*</span>
        <img class="img_flag" src="../../logo/ok.png" id="mer_address_ok">
        <br/>

        <span class="label">公司执照</span>
        <label>
            <input type="file" accept=".jpg" style="width: 30%; height: 30px;"
                   name="mer_charter" id="mer_charter" onblur="check(7, this.value)"/>
            <span class="flag" id="mer_charter_check">大小不超过300kB</span>
            <img class="img_flag" src="../../logo/ok.png" id="mer_charter_ok">
        </label>

        <div class="img_mer_charter">
            <img id="img_mer_charter" style="width: 100%; height: 100%; display: none;" src="">
        </div>

        <span class="label">&nbsp;&nbsp;&nbsp;&nbsp;负责人</span>
        <label>
            <input type="text" name="name" id="name" placeholder="请输入负责人姓名" onblur="check(8, this.value)"/>
            <span class="flag" id="name_check">*</span>
            <img class="img_flag" src="../../logo/ok.png" id="name_ok">
        </label>
        <br/>

        <span class="label">联系方式</span>
        <label>
            <input type="tel" name="phone" id="phone" placeholder="请输入负责人联系方式" onblur="check(9, this.value)"/>
            <span class="flag" id="phone_check">*</span>
            <img class="img_flag" src="../../logo/ok.png" id="phone_ok">
        </label>
        <br/>

        <span class="label">常用邮箱</span>
        <label>
            <input type="email" name="email" id="email" placeholder="请输入负责人常用邮箱" onblur="check(10, this.value)"/>
            <span class="flag" id="email_check">*</span>
            <img class="img_flag" src="../../logo/ok.png" id="email_ok">
        </label>
        <br/>

        <div>
            <button class="weui-btn weui-btn_primary" id="next2" disabled="disabled" onclick="account(11)">下一步</button>
        </div>
    </div>

    <!--审核填写信息-->
    <div class='form' id="step3" style="display: none;">
        <p>您的申请已提交，审核结果将于5个工作日内发送至负责人常用邮箱，请您耐心等待！</p>
    </div>

    <!--审核结果-->
    <div class='form' id="step4" style="display: none;">
        <p>恭喜您，您的申请已审核通过！<a id="refresh" href="javascript:void(0)"
                            onclick="refresh()"
                            title="如果界面不能正常显示，请刷新页面！">刷新</a></p>
    </div>
</div>
</body>
</html>

<script>
    function getStauts() {
        var flag = <?php echo $audit_flag;?>;
        if (flag == 1) {
            var obj1 = document.getElementById("step-next-1");
            obj1.setAttribute("class", "step-active");
            $("#mer_address").citySelect({
                url: "../../common/bootstrap/js/city.min.js",
                prov: "湖南", //省份
                city: "长沙" //城市
            });
            $("#step2").show();
        }

        if (flag == 2) {
            var obj1 = document.getElementById("step-next-1");
            obj1.setAttribute("class", "step-done");
            var obj1 = document.getElementById("step-next-2");
            obj1.setAttribute("class", "step-active");
            $("#step2").hide();
            $("#step3").show();
        }

        if (flag == 3) {
            $('#refresh').show();
            var obj1 = document.getElementById("step-next-1");
            obj1.setAttribute("class", "step-done");
            var obj1 = document.getElementById("step-next-2");
            obj1.setAttribute("class", "step-done");
            var obj2 = document.getElementById("step-end");
            obj2.setAttribute("class", "step-end step-active");
            $("#step3").hide();
            $("#step4").show();
        }
    }

    // 检查信息
    function check(type, data) {
        var url = '../register_verify.php';
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

    // 设置账户信息
    function account(type) {
        if (type == 11) {
            var id = '<?php echo $id;?>';
            var mer_name = $("#mer_name").val();
            var prov = $("#prov option:selected").val();
            var city = $("#city option:selected").val();
            var dist = $("#dist").val();
            var name = $("#name").val();
            var phone = $("#phone").val();
            var email = $("#email").val();

            var url = '../register_verify.php';
            $.post(url, {
                'type': type, 'id': id, 'mer_name': mer_name,
                'prov': prov, 'city': city, 'dist': dist, 'name': name,
                'phone': phone, 'email': email
            }, function (res) {//注意jquery的$.post的第2个参数必须是键值对形式
                if (res == 'SUCCESS') {
                    console.log('<?php echo $id;?>');
                    setCookie("id", $("#id").val(), 24, "/");
                    setCookie("pwd", $("#pwd").val(), 24, "/");
                    $("#step2").hide();
                    $("#step3").show();
                    var obj1 = document.getElementById("step-next-1");
                    obj1.setAttribute("class", "step-done");
                    var obj2 = document.getElementById("step-next-2");
                    obj2.setAttribute("class", "step-active");
                }
            });
        }
    }

    // 刷新页面
    function refresh() {
        parent.location.reload();
    }
</script>
