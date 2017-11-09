<?php
session_start();
if(!isset($_SESSION['merchant']))
{
    header('location: ../login.html');
}
$id = $_SESSION['merchant'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <script src="../../common/bootstrap/js/jquery.js"></script>
    <script src='../../common/bootstrap/js/dialog.js'></script>
    <link rel="stylesheet" href="../../common/bootstrap/css/weui.css"/>
    <title>讯鑫科技-修改密码</title>
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

        #form {
            position: relative;
            top: 0;
            left: 0;
            width: 90%;
            padding: 5%;
            height: 300px;
            vertical-align: middle;
        }

        #form input {
            width: 100%;
            height: 35px;
            margin-bottom: 1.5%;
        }

        .submit {
            height: 35px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<!--iframe内容-->
<div class="iframe_content">
    <div id="form">
        <label>
            <input type="password" name="pwdOld" id="pwdOld" placeholder="请输入原密码"/>
            <br/>
            <input type="password" name="pwdNew" id="pwdNew" placeholder="请输入新密码"/>
        </label>
        <div class="submit">
            <button class="weui-btn weui-btn_primary" id="submit" onclick="changePwd()">确认修改</button>
        </div>
        <label>
            <input type="text" name="id" id="id" value="<?php echo $id; ?>" style="display: none;"/>
            <br/>
        </label>
    </div>
</div>

<!--数据加载-->
<div id="loadingToast" style="display:none;">
    <div class="weui-mask_transparent"></div>
    <div class="weui-toast">
        <i class="weui-loading weui-icon_toast"></i>
        <p class="weui-toast__content">用户验证中</p>
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
    function check() {
        var pwdOld = document.getElementById("pwdOld");
        var pwdNew = document.getElementById("pwdNew");

        if (trim(pwdOld.value) == null || trim(pwdOld.value) == "") {
            dialog('警告', '请输入原密码', '确认', '', function () {
                pwdOld.focus();
            });
            return false;
        }

        if (trim(pwdNew.value) == null || trim(pwdNew.value) == "") {
            dialog('警告', '请输入新密码', '确认', '', function () {
                pwdNew.focus();
            });
            return false;
        }
        return true;
    }

    function trim(str) { //删除左右两端的空格
        return str.replace(/(^\s*)|(\s*$)/g, "");
    }

    // 异步验证用户名和密码
    var xmlHttp;
    function changePwd() {
        if (check()) {
            var pwdOld = document.getElementById("pwdOld");
            var id = "<?php echo $id;?>";
            var pwdNew = document.getElementById("pwdNew");
            xmlHttp = GetXmlHttpObject();
            if (xmlHttp == null) {
                alert("Browser does not support HTTP Request");
                return;
            }
            var url = "changePwd.php";
            var data = "id=" + id + "&pwdOld=" + pwdOld.value + "&pwdNew=" + pwdNew.value;
            xmlHttp.onreadystatechange = getResult;
            xmlHttp.open("POST", url, true);
            xmlHttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xmlHttp.send(data);
            // loading
            var $loadingToast = $('#loadingToast');
            $loadingToast.fadeIn(100);
            t = setTimeout(function () {
                $loadingToast.fadeOut(100);
            }, 2000);
        }
    }

    // 获取服务器返回
    function getResult() {
        if (xmlHttp.readyState == 4 || xmlHttp.readyState == "OK") {
            // 结束加载
            var $loadingToast = $('#loadingToast');
            clearTimeout(t);
            $loadingToast.fadeOut();

            // 解析支付参数
            var result = xmlHttp.responseText;

            if (result == 'SUCCESS') {
                var pwdOld = $('#pwdOld');
                var pwdNew = $('#pwdNew');
                pwdOld.val('');
                pwdOld.focus();
                pwdNew.val('');
                toast('修改成功');
            }

            else {
                dialog('验证失败', '原密码错误', '确认', '', function () {
                    var pwdOld = $('#pwdOld');
                    var pwdNew = $('#pwdNew');
                    pwdOld.val('');
                    pwdNew.val('');
                    pwdOld.focus();
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