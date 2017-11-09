<?php
/**
 * Created by PhpStorm.
 * User: Jeremy
 * Date: 2017/3/30
 * Time: 14:48
 */
// 设置时区
ini_set('date.timezone', 'Asia/Shanghai');

// 设置响应数据的内容格式，和字符集
header("Content-Type:application/json;charset=utf-8");

// 引入数据库操作函数
require_once '../../common/database/adminDB.php';

// 会话
session_start();
if (!isset($_SESSION['system'])) {
    header('location: ../login.html');
}

// 获取设备所在省
$pro = $_POST['pro'];
// 获取设备所在市
$city = $_POST['city'];
// 获取设备所在具体地址
$dist = $_POST['dist'];
// 获取输入的商户名称
$merchant = $_POST['merchant'];
// 起止时间
$beginTime = $_POST['beginTime'];

$endTime = $_POST['endTime'];

// 如果开始时间和结束时间为空，则代表为今天（默认值）
if ($beginTime == '' && $endTime == '') {
    $beginTime = date('Y-m-d', time()); // 当前日期
    $endTime = date('Y-m-d', time()); // 当前日期
}

// 计算时间间隔
$d1 = strtotime($beginTime);
$d2 = strtotime($endTime);
$days = round(($d2 - $d1) / 3600 / 24);

if ($pro == '' || $city == '') {
    $pro = '不限';
    $city = '不限';
}

$info = [];
if ($merchant != 'all') {
    // 获取商户名称，商户编号
    $merchant_info = explode('_', $merchant);
    $merchant_name = $merchant_info[0];
    $merchant_id = $merchant_info[1];

    // 查询商户编号
    $sql = "select mer_id from merchant_info where mer_name = '{$merchant_name}' and mer_sub_id = {$merchant_id}";
    $mer_id = $adminDB->ExecSQL($sql, $conn);
    $mer_id = $mer_id[0]['mer_id'];
    for ($d = 0; $d <= $days; $d++) {
        $datetime = date('Y-m-d', strtotime($beginTime . '+' . $d . 'days'));
        // 查询统计结果
        $sql = "select concat(mer_name, '_' ,mer_sub_id) as mer_name, dev_id, pro_name, city_name, consume_t, cost, time_len from statistic_view where mer_id = {$mer_id} and consume_t like '{$datetime}%'";
        $info_tmp = $adminDB->ExecSQL($sql, $conn);
        if ($info_tmp != null) {
            for($t = 0; $t<count($info_tmp); $t++){
                array_push($info, $info_tmp[$t]);
            }
        }
    }
} else {
    if ($pro == '不限' && $city == '不限') {
        $sql = "select DISTINCT mer_id from mer_dev";
    } else {
        $sql = "select city_id from city where city_name = '{$city}'";
        $city_id = $adminDB->ExecSQL($sql, $conn);
        $city_id = $city_id[0]['city_id'];
        if ($dist = '') {
            $sql = "select mer_id from merchant_info where mer_name != '讯鑫科技' and city_id = {$city_id}";
        } else {
            $sql = "select mer_id from merchant_info where mer_name != '讯鑫科技' and city_id = {$city_id} and addr_detail like '%{$dist}%'";
        }
    }
    // 查询商户编号
    $mer_id = $adminDB->ExecSQL($sql, $conn);
    for ($i = 0; $i < count($mer_id); $i++) {
        for ($d = 0; $d <= $days; $d++) {
            $datetime = date('Y-m-d', strtotime($beginTime . '+' . $d . 'days'));
            // 查询统计结果
            $sql = "select concat(mer_name, '_' ,mer_sub_id) as mer_name, dev_id, pro_name, city_name, consume_t, cost, time_len from statistic_view 
                    where consume_t like '{$datetime}%:%:%' and mer_id = {$mer_id[$i]['mer_id']}";
            $info_tmp = $adminDB->ExecSQL($sql, $conn);
            //echo $sql."<br/>";
            if ($info_tmp != null) {
                for($t = 0; $t<count($info_tmp); $t++){
                    array_push($info, $info_tmp[$t]);
                }
            }
        }
    }
}

//分页时需要获取记录总数，键值为 total
$result["total"] = count($info);
//根据传递过来的分页偏移量和分页量截取模拟分页 rows 可以根据前端的 dataField 来设置
$result["rows"] = array_slice($info, $_POST['offset'], $_POST['limit']);

// 返回时价信息
echo json_encode($result);