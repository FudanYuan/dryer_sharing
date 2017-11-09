<?php
/**
 * Created by PhpStorm.
 * User: Jeremy
 * Date: 2017/4/1
 * Time: 14:28
 */
ini_set('date.timezone', 'Asia/Shanghai');
require_once "../lib/WxPay.Api.php";
require_once "WxPay.JsApiPay.php";
require_once '../lib/log.php';
require_once '../../common/database/adminDB.php';

//初始化日志
$logHandler = new CLogFileHandler("../logs/" . date('Y-m-d') . '.log');
$log = Log::Init($logHandler, 15);

// 获取参数
$openId = $_REQUEST['open_id'];
$product_id = $_REQUEST['product_id'];
$time = $_REQUEST["time"];
//$money = $_REQUEST["money"]*100;
$money = 1;

// 统一下单
$input = new WxPayUnifiedOrder();
$input->SetBody("讯鑫科技");
$input->SetAttach($product_id.'_'.$time);
$input->SetOut_trade_no(WxPayConfig::MCHID . date("YmdHis"));
$input->SetTotal_fee($money);
$input->SetTime_start(date("YmdHis"));
$input->SetTime_expire(date("YmdHis", time() + 600));
$input->SetGoods_tag('感谢使用讯鑫科技干衣机('.$product_id.')');
// 迁移时改这里的url值
$input->SetNotify_url('http://' . $_SERVER['HTTP_HOST'] . '/wxpay/main/notify.php');
$input->SetTrade_type("JSAPI");
$input->SetOpenid($openId);
$order = WxPayApi::unifiedOrder($input);
//file_put_contents('../log/order.txt', json_encode($order));

// 获取支付参数
$tools = new JsApiPay();
$jsApiParameters = $tools->GetJsApiParameters($order);

$return['jsApiParameters'] = $jsApiParameters;
$return['money'] = $money;
//file_put_contents('return.txt', json_encode($return));

echo json_encode($return);