<?php
/**
 * Created by PhpStorm.
 * User: Jeremy
 * Date: 2017/4/25
 * Time: 18:39
 */
// 设置时区
ini_set('date.timezone', 'Asia/Shanghai');

// 设置响应数据的内容格式，和字符集
header("Content-Type:application/json;charset=utf-8");

// 引入数据库操作函数
require_once '../../common/database/adminDB.php';

// 会话
session_start();
if(!isset($_SESSION['merchant']))
{
    header('location: ../login.html');
}

// 获得参数
$merchant = $_POST['merchant'];
$beginTime = $_POST['beginTime'];
$endTime = $_POST['endTime'];

// 获取商户标识
$merchant_info = explode('_',$merchant);
$merchant_name = $merchant_info[0];
$merchant_id = $merchant_info[1];

$sql = "select mer_id from merchant_info where mer_name = '{$merchant_name}'";
$mer_id = $adminDB->ExecSQL($sql, $conn);
$mer_id = $mer_id[0]['mer_id'];

// 如果开始时间和结束时间为空，则代表为今天（默认值）
if ($beginTime == '' && $endTime == '') {
    $beginTime = date('Y-m-d', time()); // 当前日期
    $endTime = date('Y-m-d', time()); // 当前日期
}

$d1 = strtotime($beginTime);
$d2 = strtotime($endTime);
$days = round(($d2 - $d1) / 3600 / 24);

// 返回值
$return['time'] = [];
$return['money'] = [];
$return['xAxis'] = [];
// 如果开始时间和结束时间相同，则为一天（今天、昨天或自定义某一天）
if ($days == 0) {
    for ($i = 0; $i <= 23; $i++) {
        $hour = $i;
        if ($i <= 9)
            $hour = '0' . $i;
        $datetime_hour = $beginTime . ' ' . $hour;

        // get time
        $sqlTime = "select sum(time_len) as time from statistic_view where mer_id='{$mer_id}' and consume_t like '{$datetime_hour}%'";
        $time = $adminDB->ExecSQL($sqlTime, $conn);
        array_push($return['time'], $time[0]['time'] == null ? 0 : $time[0]['time']);

        // get money
        // $sqlMoney = "select sum(cost) as money from statistic_view where mer_id='{$mer_id}' and consume_t like '{$datetime_hour}%'";
        // $money = $adminDB->ExecSQL($sqlMoney, $conn);
        // array_push($return['money'], $money[0]['money'] == null ? 0 : $money[0]['money']);

        // set xAxis
        array_push($return['xAxis'], ($i <= 9 ? ('0' . $i) : $i) . ':00');
    }

} else if ($days <= 31) {
    for ($i = 0; $i <= $days; $i++) {
        $datetime = date('Y-m-d', strtotime($beginTime . '+' . $i . 'days'));

        // get time
        $sqlTime = "select sum(time_len) as time from statistic_view where mer_id='{$mer_id}' and consume_t like '{$datetime}%'";
        $time = $adminDB->ExecSQL($sqlTime, $conn);
        array_push($return['time'], $time[0]['time'] == null ? 0 : $time[0]['time']);

        // get money
        // $sqlMoney = "select sum(cost) as money from statistic_view where mer_id='{$mer_id}' and consume_t like '{$datetime}%'";
        // $money = $adminDB->ExecSQL($sqlMoney, $conn);
        // array_push($return['money'], $money[0]['money'] == null ? 0 : $money[0]['money']);

        // set xAxis
        array_push($return['xAxis'], $datetime);
    }
} else {
    $datetimeBegin = date('m', strtotime($beginTime));
    $datetimeEnd = date('m', strtotime($endTime));
    $months = $datetimeEnd - $datetimeBegin;
    $year = date('Y', strtotime($beginTime));

    for ($i = 0; $i <= $months; $i++) {
        $datetime = $year . '-' . (($datetimeBegin + $i) <= 9 ? '0' . ($datetimeBegin + $i) : $datetimeBegin + $i);
        // get time
        $sqlTime = "select sum(time_len) as time from statistic_view where mer_id='{$mer_id}' and consume_t like '{$datetime}%'";
        $time = $adminDB->ExecSQL($sqlTime, $conn);
        array_push($return['time'], $time[0]['time'] == null ? 0 : $time[0]['time']);

        // get money
        // $sqlMoney = "select sum(cost) as money from statistic_view where mer_id='{$mer_id}' and consume_t like '{$datetime}%'";
        // $money = $adminDB->ExecSQL($sqlMoney, $conn);
        // array_push($return['money'], $money[0]['money'] == null ? 0 : $money[0]['money']);
        array_push($return['xAxis'], $datetime);
    }
}
$sql = "select brokerage from mer_fee_len_rate_broke_view where role_id = 1";
$brokerage = $adminDB->ExecSQL($sql, $conn);
$brokerage = $brokerage[0]['brokerage'];

// set yAxis Max
$return['timeMax'] = max($return['time']);
//$return['moneyMax'] = max($return['money']);
$return['timeSum'] = array_sum($return['time']);
//$return['moneySum'] = array_sum($return['money']);
$return['brokeSum'] = $return['timeSum'] * $brokerage;

echo json_encode($return);