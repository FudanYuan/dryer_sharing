<?php
/**
 * Created by PhpStorm.
 * User: Jeremy
 * Date: 2017/6/8
 * Time: 20:18
 */
session_start();
if (!isset($_SESSION['system'])) {
    header('location: ../login.html');
}
$id = $_SESSION['system'];

/* 设置响应数据的内容格式，和字符集*/
header("Content-Type:application/json;charset=utf-8");

// 引入数据库操作函数
require_once '../../common/database/adminDB.php';

// 设置时区
ini_set('date.timezone', 'Asia/Shanghai');

// 设置响应数据的内容格式，和字符集
header("Content-Type:application/json;charset=utf-8");

// 获取商家
$merchant = $_POST['merchant'];
// 获取商家所在省
$pro = $_POST['pro'];
// 获取商家所在市
$city = $_POST['city'];
// 获取商家所在具体地址
$dist = $_POST['dist'];
// 获得审核状态
$status =  $_POST['status'];

if ($pro == '' || $city == '') {
    $pro = '不限';
    $city = '不限';
}

$info = [];
$audit = 0;
if($status == 'wait_audit'){
    $audit = 2;
}else if($status == 'audited'){
    $audit = 3;
}else if($status == 'rejected'){
    $audit = 4;
}

$sql = "";
// 如果选择了商家，则直接返回该信息
if ($merchant != 'all') {
    $merchant_info = explode('_', $merchant);
    $merchant_name = $merchant_info[0];
    $merchant_id = $merchant_info[1];
    $sql = "select DISTINCT(concat(mer_name, '_' ,mer_sub_id)) as mer_name,
            name, phone, email, pro_name, city_name, addr_detail, audit
            from admin_merchant_view 
            where role_id = 2 
            and audit = {$audit}
            and mer_name = '{$merchant_name}'
            and mer_sub_id = {$merchant_id}";

} else {
    // 为选择商家
    if ($pro == '不限') {
        $sql = "select DISTINCT(concat(mer_name, '_' ,mer_sub_id)) as mer_name,
            name, phone, email, pro_name, city_name, addr_detail, audit
            from admin_merchant_view 
            where role_id = 2 
            and audit = {$audit}";
    } else {
        if ($city == '不限') {
            $sql = "select DISTINCT(concat(mer_name, '_' ,mer_sub_id)) as mer_name,
            name, phone, email, pro_name, city_name, addr_detail, audit
            from admin_merchant_view 
            where role_id = 2 
            and audit = {$audit}
            and pro_name = '{$pro}'";
        } else {
            if ($dist == '') {
                $sql = "select DISTINCT(concat(mer_name, '_' ,mer_sub_id)) as mer_name,
            name, phone, email, pro_name, city_name, addr_detail, audit
            from admin_merchant_view 
            where role_id = 2 
            and audit = {$audit}
            and pro_name = '{$pro}' 
            and city_name = '{$city}'";
            } else {
                $sql = "select DISTINCT(concat(mer_name, '_' ,mer_sub_id)) as mer_name,
            name, phone, email, pro_name, city_name, addr_detail, audit
            from admin_merchant_view 
            where role_id = 2 
            and audit = {$audit}
            and pro_name = '{$pro}' 
            and city_name = '{$city}' 
            and addr_detail like '%{$dist}%'";
            }
        }
    }
}

$info_tmp = $adminDB->ExecSQL($sql, $conn);
if ($info_tmp != null) {
    for ($t = 0; $t < count($info_tmp); $t++) {
        array_push($info, $info_tmp[$t]);
    }
}

// 分页时需要获取记录总数，键值为 total
$result["total"] = count($info);
// 根据传递过来的分页偏移量和分页量截取模拟分页 rows 可以根据前端的 dataField 来设置
$result["rows"] = array_slice($info, $_POST['offset'], $_POST['limit']);

// 返回时价信息
echo json_encode($result);