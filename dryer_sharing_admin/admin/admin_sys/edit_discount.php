<?php
/**
 * Created by PhpStorm.
 * User: Jeremy
 * Date: 2017/5/11
 * Time: 19:51
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
// 获得传来的数据
$row = $_POST['data'];
$merchant = $row['mer_name'];
// 获取商户名称，商户编号
$merchant_info = explode('_', $merchant);
$mer_name = $merchant_info[0];
$mer_sub_id = $merchant_info[1];
$fee_1 = $row['fee_1'];
$fee_2 = $row['fee_2'];
$fee_add = $row['fee_add'];
$len_1 = $row['len_1'];
$len_2 = $row['len_2'];
$len_add = $row['len_add'];
$fee_rate_1 = $row['fee_rate_1']/100;
$fee_rate_2 = $row['fee_rate_2']/100;
$fee_rate_add = $row['fee_rate_add']/100;

// 获取商户编号
$sql = "select mer_id from merchant_info where mer_name = '{$mer_name}' 
      and mer_sub_id = '{$mer_sub_id}'";
$mer_id = $adminDB->ExecSQL($sql, $conn);
$mer_id = $mer_id[0]['mer_id'];

// 更新惠率
$sql = "update merchant_info set fee_rate_1 = {$fee_rate_1}, 
      fee_rate_2 = {$fee_rate_2}, fee_rate_add = {$fee_rate_add} where mer_id={$mer_id}";
$adminDB->ExecSQL($sql, $conn);

// 更新各商户价格时长
$sql = "select max(f_id) from fee_bracket";
$f_id = $adminDB->ExecSQL($sql, $conn);
$sql = "update merchant_info set f_id = (select max(f_id) from fee_bracket), 
      l_id = (select max(l_id) from len_bracket)";
$adminDB->ExecSQL($sql, $conn);
echo 'success';