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
$fee_1 = $row['fee_1'];
$fee_2 = $row['fee_2'];
$fee_add = $row['fee_add'];
$len_1 = $row['len_1'];
$len_2 = $row['len_2'];
$len_add = $row['len_add'];
$rate_1 = $row['rate_1'] /100;
$rate_2 = $row['rate_2'] /100;
$rate_add = $row['rate_add'] /100;
$brokerage = $row['brokerage'] /100;

$merchant_info = explode('_', $merchant);
$merchant_name = $merchant_info[0];
$merchant_id = $merchant_info[1];

// 获取商户编号
$sql = "select mer_id from merchant_info where mer_name = '{$merchant_name}' 
and mer_sub_id = {$merchant_info[1]}";
$mer_id = $adminDB->ExecSQL($sql, $conn);
$mer_id = $mer_id[0]['mer_id'];

// 更新各商户价格-时长-惠率-佣金比例
$sql = "select max(f_id) as f_id from fee_bracket";
$f_id = $adminDB->ExecSQL($sql, $conn);
$f_id = $f_id[0]['f_id'];
//echo $f_id;
$sql = "select max(l_id) as l_id from len_bracket";
$l_id = $adminDB->ExecSQL($sql, $conn);
$l_id = $l_id[0]['l_id'];
//echo $l_id;
$sql = "select max(r_id) as r_id from rate_bracket";
$r_id = $adminDB->ExecSQL($sql, $conn);
$r_id = $r_id[0]['r_id'];
//echo $r_id;
$sql = "select max(b_id) as b_id from brokerage";
$b_id = $adminDB->ExecSQL($sql, $conn);
$b_id = $b_id[0]['b_id'];
//echo $b_id;

// 更新价格-时长-惠率-佣金比例
$sql = [];
$now = date("Y-m-d H:i:s");
$sql[0] = "update fee_bracket set effect_e_date = '{$now}' where f_id = {$f_id}";
$sql[1] = "insert into fee_bracket(fee_1, fee_2, fee_add, effect_s_date) values ({$fee_1}, {$fee_2}, {$fee_add}, '{$now}')";
$sql[2] = "update len_bracket set effect_e_date = '{$now}' where l_id = {$l_id}";
$sql[3] = "insert into len_bracket(len_1, len_2, len_add, effect_s_date) values ({$len_1}, {$len_2}, {$len_add}, '{$now}')";
$sql[4] = "update rate_bracket set effect_e_date = '{$now}' where r_id = {$r_id}";
$sql[5] = "insert into rate_bracket(rate_1, rate_2, rate_add, effect_s_date) values ({$rate_1}, {$rate_2}, {$rate_add}, '{$now}')";
$sql[6] = "update brokerage set effect_e_date = '{$now}' where b_id = {$b_id}";
$sql[7] = "insert into brokerage(brokerage, effect_s_date) values ({$brokerage}, '{$now}')";
$f_id += 1;
$l_id += 1;
$r_id += 1;
$b_id += 1;
$sql[8] = "update merchant_info set f_id = {$f_id}, l_id = {$l_id}, r_id = {$r_id}, b_id = {$b_id}";
echo $sql[0].'<br/>';
echo $sql[1].'<br/>';
echo $sql[2].'<br/>';
echo $sql[3].'<br/>';
echo $sql[4].'<br/>';
echo $sql[5].'<br/>';
echo $sql[6].'<br/>';
echo $sql[7].'<br/>';
echo $sql[8].'<br/>';
if($adminDB->Transcation($sql, $conn)){
    echo 'success';
}


