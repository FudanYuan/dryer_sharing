<?php
/**
 * Created by PhpStorm.
 * User: Jeremy
 * Date: 2017/6/11
 * Time: 23:57
 */

/* 设置响应数据的内容格式，和字符集*/
header("Content-Type:application/json;charset=utf-8");

// 引入数据库操作函数
require_once '../../common/database/adminDB.php';

// 会话
session_start();
if (!isset($_SESSION['system'])) {
    header('location: ../login.html');
}

// 得到省市
$pro = $_REQUEST['pro'];
$city = $_REQUEST['city'];
$dist = $_REQUEST['dist'];
$type = $_REQUEST['type'];

$info = [];

$sql = "";

// 得到所在址的商户
if ($type == 'wait_audit') {
    if ($dist == "") {
        $sql = "select DISTINCT(concat(mer_name, '_' ,mer_sub_id)) as mer_name from admin_merchant_view 
            where role_id = 2 
            and audit = 2 
            and city_name = '{$city}'";
    } else {
        $sql = "select DISTINCT(concat(mer_name, '_' ,mer_sub_id)) as mer_name from admin_merchant_view
            where role_id = 2 
            and audit = 2 
            and city_name = '{$city}' 
            and addr_detail = '{$dist}'";
    }
} else if ($type == 'audited') {
    if ($dist == "") {
        $sql = "select DISTINCT(concat(mer_name, '_' ,mer_sub_id)) as mer_name from admin_merchant_view 
            where role_id = 2
            and audit = 3 
            and city_name = '{$city}'";
    } else {
        $sql = "select DISTINCT(concat(mer_name, '_' ,mer_sub_id)) as mer_name from admin_merchant_view
            where role_id = 2
            and audit = 3 
            and city_name = '{$city}' 
            and addr_detail like '%{$dist}%'";
    }
} else if ($type == 'audited') {
    if ($dist == "") {
        $sql = "select DISTINCT(concat(mer_name, '_' ,mer_sub_id)) as mer_name from admin_merchant_view 
            where role_id = 2
            and audit = 3 
            and city_name = '{$city}'";
    } else {
        $sql = "select DISTINCT(concat(mer_name, '_' ,mer_sub_id)) as mer_name from admin_merchant_view
            where role_id = 2
            and audit = 3 
            and city_name = '{$city}' 
            and addr_detail like '%{$dist}%'";
    }
} else if ($type == 'allocated') {
    $sql = "select DISTINCT(concat(mer_name, '_' ,mer_sub_id)) as mer_name from merchant_info, admin 
where city_id = (select city_id from city where city_name = '{$city}') and mer_name != '讯鑫科技' 
and audit = 3 and merchant_info.mer_id = admin.mer_id and merchant_info.mer_id in (select mer_id from mer_dev)";
}

$info_tmp = $adminDB->ExecSQL($sql, $conn);

if ($info_tmp != null) {
    for ($t = 0; $t < count($info_tmp); $t++) {
        array_push($info, $info_tmp[$t]['mer_name']);
    }
}

echo json_encode($info);
