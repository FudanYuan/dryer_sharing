<?php
/**
 * Created by PhpStorm.
 * User: Jeremy
 * Date: 2017/6/3
 * Time: 17:17
 */
/* 设置响应数据的内容格式，和字符集*/
header('Content-type:text/html;charset=utf-8');

require_once '../common/database/adminDB.php';

$type =  $_REQUEST['type'];
$id =  $_REQUEST['id'];
// 检查账号是否已存在
if($type == 1){
    $sql = "select * from admin where username = '{$id}'";
    if($adminDB->ExecSQL($sql, $conn)){
        echo 'EXITS';
    }
    else{
        echo 'OK';
    }
}

// 添加账号信息
if($type == 4){
    $pwd = $_REQUEST['pwd'];
    $pwd = sha1(md5($pwd));
    $sql = "insert into admin(username, role_id, pwd, audit) values('{$id}', 2, '{$pwd}', 1)";
    if($adminDB->ExecSQL($sql, $conn)){
        echo 'SUCCESS';
    }
    else{
        echo 'FAIL';
    }
}

// 添加公司信息
if($type == 11){
    $mer_name = $_REQUEST['mer_name'];
    $prov = $_REQUEST['prov'];
    $city = $_REQUEST['city'];
    $dist = $_REQUEST['dist'];
    $name = $_REQUEST['name'];
    $phone = $_REQUEST['phone'];
    $email = $_REQUEST['email'];

    // 获得城市编号
    $sql = "select pro_id from province where pro_name = '{$prov}'";
    $pro_id = $adminDB->ExecSQL($sql, $conn);
    $pro_id = $pro_id[0]['pro_id'];
    $sql = "select city_id from city where city_name = '{$city}' and pro_id = {$pro_id}";
    $city_id = $adminDB->ExecSQL($sql, $conn);
    $city_id = $city_id[0]['city_id'];

    // 得到子编号
    $sql = "select max(mer_sub_id) as mer_sub_id from merchant_info where mer_name = '{$mer_name}'";
    if($adminDB->ExecSQL($sql, $conn)){
        $mer_sub_id = $adminDB->ExecSQL($sql, $conn);
        $mer_sub_id = $mer_sub_id[0]['mer_sub_id'] + 1;
    }
    else{
        $mer_sub_id = 1;
    }

    // 获得价格、时长、惠率、佣金信息
    $sql = "select max(f_id) as f_id from fee_bracket";
    $f_id = $adminDB->ExecSQL($sql, $conn);
    $f_id = $f_id[0]['f_id'];
    $sql = "select max(l_id) as l_id from len_bracket";
    $l_id = $adminDB->ExecSQL($sql, $conn);
    $l_id = $l_id[0]['l_id'];
    $sql = "select max(r_id) as r_id from rate_bracket";
    $r_id = $adminDB->ExecSQL($sql, $conn);
    $r_id = $r_id[0]['r_id'];
    $sql = "select max(b_id) as b_id from brokerage";
    $b_id = $adminDB->ExecSQL($sql, $conn);
    $b_id = $b_id[0]['b_id'];

    // 添加商户信息
    $sql = "insert into merchant_info(mer_name, mer_sub_id, city_id, addr_detail, 
f_id, l_id, r_id, b_id) values('{$mer_name}', {$mer_sub_id}, {$city_id}, '{$dist}', 
{$f_id}, {$l_id}, {$r_id}, {$b_id})";
    if($adminDB->ExecSQL($sql, $conn)){
        // 获得该商户的键值序号
        $sql = "select mer_id from merchant_info where mer_name = '{$mer_name}' and mer_sub_id = {$mer_sub_id}";
        $mer_id = $adminDB->ExecSQL($sql, $conn);
        $mer_id = $mer_id[0]['mer_id'];
        // 添加管理员信息
        $sql = "update admin set mer_id = {$mer_id}, name='{$name}', phone = '{$phone}', email = '{$email}', audit = 2 where username = '{$id}'";
        if($adminDB->ExecSQL($sql, $conn)){
            echo 'SUCCESS';
        }
        else{
            echo 'FAIL';
        }
    }
}