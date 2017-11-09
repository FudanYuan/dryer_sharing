/**
 * Created by Jeremy on 2017/4/1.
 */
var xmlHttp;
var openId;
var productId;
var jsApiParameters;
var fee;
var t;
//调用微信JS api 支付
function jsApiCall() {
    WeixinJSBridge.invoke(
        'getBrandWCPayRequest',
        jsApiParameters,
        function (res) {
            WeixinJSBridge.log(res.err_msg);
            if (res.err_msg == "get_brand_wcpay_request:ok") {
                var $toast = $('#toast');
                if ($toast.css('display') != 'none') return;
                $toast.fadeIn(100);
                setTimeout(function () {
                    $toast.fadeOut(100);
                }, 2000);
            }
            else if(res.err_msg == "get_brand_wcpay_request:cancel"){
                dialog('取消支付', '您确定要取消', '支付', '取消',
                    function () {
                        jsApiCall();
                        // 支付按钮可按
                        WeixinJSBridge.call('closeWindow');
                        $('#pay_disabled').hide();
                        document.getElementById("bracket_1").disabled = false;
                        document.getElementById("bracket_2").disabled = false;
                        document.getElementById("bracket_add").disabled = false;
                    },
                    function () {
                        WeixinJSBridge.call('closeWindow');
                    });
            }
            else{
                dialog('支付失败', '抱歉，您的支付未成功', '确认', '',
                    function () {
                        WeixinJSBridge.call('closeWindow');
                    });
            }
        }
    );
}

// 提交支付
function callpay() {
    if (typeof WeixinJSBridge == "undefined") {
        if (document.addEventListener) {
            document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
        } else if (document.attachEvent) {
            document.attachEvent('WeixinJSBridgeReady', jsApiCall);
            document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
        }
    } else {
        var msg = '充值金额为' + fee + '元';
        dialog('预支付信息', msg, '支付', '取消',
            function () {
                jsApiCall();
                // 支付按钮不可按
                $('#pay_disabled').show();
                document.getElementById("bracket_1").disabled = true;
                document.getElementById("bracket_2").disabled = true;
                document.getElementById("bracket_add").disabled = true;
            }
        );
    }
}

// 异步获取js参数
function GetJsApiParameters(time, money) {
    fee = money;
    // 支付按钮不可按
    $('#pay_disabled').show();
    document.getElementById("bracket_1").disabled = true;
    document.getElementById("bracket_2").disabled = true;
    document.getElementById("bracket_add").disabled = true;
    openId = $('#openId').text();
    productId = $('#productId').text();
    if (openId && productId && time && money) {
        // 异步通信
        xmlHttp = GetXmlHttpObject();
        if (xmlHttp == null) {
            alert("Browser does not support HTTP Request");
            return;
        }
        var url = "getJsApiParameters.php";
        url += "?open_id=" + openId + "&product_id=" + productId + "&time=" + time + "&money=" + money + "&uid=" + Math.random();
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
        // 解析支付参数
        var data = JSON.parse(xmlHttp.responseText);
        jsApiParameters = JSON.parse(data['jsApiParameters']);

        // 支付按钮可按
        $('#pay_disabled').hide();
        document.getElementById("bracket_1").disabled = false;
        document.getElementById("bracket_2").disabled = false;
        document.getElementById("bracket_add").disabled = false;

        // 结束加载
        var $loadingToast = $('#loadingToast');
        clearTimeout(t);
        $loadingToast.fadeOut(100);

        /*支付*/
        callpay();
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
