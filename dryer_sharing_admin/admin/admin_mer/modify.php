<?php
/**
 * Created by PhpStorm.
 * User: Jeremy
 * Date: 2017/6/3
 * Time: 17:17
 */
/* 设置响应数据的内容格式，和字符集*/
header('Content-type:text/html;charset=utf-8');

require_once '../../common/database/adminDB.php';

$id =  $_REQUEST['id'];

// 修改账号信息
$mer_name = $_REQUEST['mer_name'];
$prov = $_REQUEST['prov'];
$city = $_REQUEST['city'];
$dist = $_REQUEST['dist'];
$name = $_REQUEST['name'];
$phone = $_REQUEST['phone'];
$email = $_REQUEST['email'];

// 得到城市编号
$sql = "select pro_id from province where pro_name = '{$prov}'";
$pro_id = $adminDB->ExecSQL($sql, $conn);
$pro_id = $pro_id[0]['pro_id'];
$sql = "select city_id from city where city_name = '{$city}' and pro_id = {$pro_id}";
$city_id = $adminDB->ExecSQL($sql, $conn);
$city_id = $city_id[0]['city_id'];

// 得到商户编号
$sql = "select mer_id from admin where username = '{$id}'";
$mer_id = $adminDB->ExecSQL($sql, $conn);
$mer_id = $mer_id[0]['mer_id'];

// 更新数据
$sql = array();
$sql[0] = "update merchant_info set city_id = {$city_id}, addr_detail = '{$dist}'
 where mer_id = (select mer_id from admin where username = '{$id}')";
$sql[1] = "update admin set name = '{$name}', phone = '{$phone}', email = '{$email}'
 where mer_id = {$mer_id}";
if($adminDB->Transcation($sql, $conn)) {
    echo 'SUCCESS';
}
else {
    echo 'FAIL';
}