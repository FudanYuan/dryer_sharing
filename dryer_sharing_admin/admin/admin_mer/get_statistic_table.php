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

// 获取输入的商户名称、时间段
$merchant = $_POST['merchant'];
$beginTime = $_POST['beginTime'];
$endTime = $_POST['endTime'];

// 测试
//$merchant = '速8连锁酒店_1';
//$merchant = '7天连锁酒店_1';
//$merchant = '全部';
//$beginTime = '2017-05-04';
//$endTime = '2017-05-10';

// 如果开始时间和结束时间为空，则代表为今天（默认值）
if ($beginTime == '' && $endTime == '') {
    $beginTime = date('Y-m-d', time()); // 当前日期
    $endTime = date('Y-m-d', time()); // 当前日期
}

// 计算时间间隔
$d1 = strtotime($beginTime);
$d2 = strtotime($endTime);
$days = round(($d2 - $d1) / 3600 / 24);

$info = [];
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
    $sql = "select DISTINCT(concat(mer_name, '_' ,mer_sub_id)) as mer_name, dev_id, pro_name, city_name, consume_t, cost, time_len from statistic_view where mer_id = {$mer_id} and consume_t like '{$datetime}%'";
    $res = $adminDB->ExecSQL($sql, $conn);
    for ($i = 0; $i < count($res); $i++) {
        $mer_id_temp = $res[$i];
        if($mer_id_temp != null){
            array_push($info, $mer_id_temp);
        }
    }
}

//分页时需要获取记录总数，键值为 total
$result["total"] = count($info);
//根据传递过来的分页偏移量和分页量截取模拟分页 rows 可以根据前端的 dataField 来设置
$result["rows"] = array_slice($info, $_POST['offset'], $_POST['limit']);

// 返回时价信息
echo json_encode($result);