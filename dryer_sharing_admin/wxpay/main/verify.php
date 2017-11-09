<?php
/**
 * Created by PhpStorm.
 * User: Jeremy
 * Date: 2017/3/29
 * Time: 10:30
 */

/**验证token
 */
define("TOKEN","123");
function checkSignature()
{
    //从GET参数中读取三个字段的值
    $signature = $_REQUEST["signature"];
    $timestamp = $_REQUEST["timestamp"];
    $nonce = $_REQUEST["nonce"];
    //读取预定义的TOKEN
    $token = TOKEN;
    //对数组进行排序
    $tmpArr = array($token, $timestamp, $nonce);
    sort($tmpArr, SORT_STRING);
    //对三个字段进行sha1运算
    $tmpStr = implode( $tmpArr );
    $tmpStr = sha1( $tmpStr );
    //判断我方计算的结果是否和微信端计算的结果相符
    //这样利用只有微信端和我方了解的token作对比,验证访问是否来自微信官方.
    if( $tmpStr == $signature ){
        return true;
    }else{
        return false;
    }
}
//验证请求是否来自微信后台
if(checkSignature() == false){
    exit(0);
}
$echostr = $_REQUEST["echostr"];
if($echostr){
    echo $echostr;
    exit(0);
}

//获取post数据
$postData = $GLOBALS['HTTP_RAW_POST_DATA'];//file_get_contents("php://input");
file_put_contents('postData.txt', $postData);
//判断post数据是否为空
if(!$postData){
    file_put_contents('postDataWrong.txt', 'postDataWrong');
    echo "wrong input";
    exit(0);
}

//解析XML字符串
$xml=simplexml_load_string($postData, 'SimpleXMLElement', LIBXML_NOCDATA);
if(!$xml) {
    file_put_contents('xmlWrong.txt', 'xmlWrong');
    echo "wrong input";
    exit(0);
}

//获取FromUserName
$fromUserName = $xml->FromUserName;

//获取ToUserName
$toUserName = $xml->ToUserName;

//获取MsgType
$msgType = $xml->MsgType;

if('text' != $msgType){
    $retMsg = '只支持文本消息';
}
else{
    //获取用户输入文本
    $content = $xml->Content;
    //输出用户输入文本
    $retMsg = $content;
}

//输出xml描述的信息
$ret = "<xml><ToUserName><![CDATA[{$fromUserName}]]></ToUserName>
<FromUserName><![CDATA[{$toUserName}]]></FromUserName>
<CreateTime>time()</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[{$retMsg}]]></Content>
</xml>";
echo $ret;
