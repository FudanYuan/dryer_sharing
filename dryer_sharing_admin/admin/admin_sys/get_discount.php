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

$info = [];

// 如果选择了商家，则直接返回该信息
if($merchant != 'all'){
    // 查询时价优惠信息
    $sql = "select mer_name, fee_1, fee_2, fee_add, len_1, len_2, len_add, fee_rate_1*100 as fee_rate_1,  fee_rate_2*100 as fee_rate_2, fee_rate_add*100 as fee_rate_add
                from price_len_rate where mer_name='{$merchant}'";
    $info_tmp = $adminDB->ExecSQL($sql, $conn);
    if ($info_tmp != null) {
        for($t = 0; $t<count($info_tmp); $t++){
            array_push($info, $info_tmp[$t]);
        }
    }
}
else{
    if ($pro == '不限' && $city == '不限') {
        $sql = "select DISTINCT mer_id from mer_dev";
    } else {
        $sql = "select city_id from city where city_name = '{$city}'";
        $city_id = $adminDB->ExecSQL($sql, $conn);
        $city_id = $city_id[0]['city_id'];
        if($dist = ''){
            $sql = "select mer_id from merchant_info where mer_name != '讯鑫科技' and city_id = {$city_id}";
        }else{
            $sql = "select mer_id from merchant_info where mer_name != '讯鑫科技' and city_id = {$city_id} and addr_detail like '%{$dist}%'";
        }
    }

    // 查询商户编号
    $mer_id = $adminDB->ExecSQL($sql, $conn);
    for ($i = 0; $i < count($mer_id); $i++) {
        $mer_id_temp = $mer_id[$i]['mer_id'];
        // 查询商户名称
        $sql = "select DISTINCT(concat(mer_name, '_' ,mer_sub_id)) as merchant_name from merchant_info where mer_id={$mer_id_temp}";
        $mer_name_temp = $adminDB->ExecSQL($sql, $conn);
        $mer_name_temp = $mer_name_temp[0]['merchant_name'];

        // 查询时价优惠信息
        $sql = "select mer_name, fee_1, fee_2, fee_add, len_1, len_2, len_add, fee_rate_1*100 as fee_rate_1,  fee_rate_2*100 as fee_rate_2, fee_rate_add*100 as fee_rate_add 
                from price_len_rate where mer_name='{$mer_name_temp}'";
        $info_tmp = $adminDB->ExecSQL($sql, $conn);
        if ($info_tmp != null) {
            for($t = 0; $t<count($info_tmp); $t++){
                array_push($info, $info_tmp[$t]);
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