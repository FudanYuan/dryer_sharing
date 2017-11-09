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

// 初始化会话
session_start();
if(!isset($_SESSION['merchant']))
{
    header('location: ../login.html');
}

// 管理员账号
$id = $_POST['id'];
$pwdOld = $_POST['pwdOld'];
$pwdNew = $_POST['pwdNew'];

$sql = "select pwd from admin where username = '{$id}'";
$password = $adminDB->ExecSQL($sql, $conn);
$password = $password[0]['pwd'];

if ($password == sha1(md5($pwdOld))) {
    $pwdNew = sha1(md5($pwdNew));
    $sql = "update admin set pwd= '{$pwdNew}' where username = '{$id}'";
    if($adminDB->ExecSQL($sql, $conn)){
        echo 'SUCCESS';
    }
    else{
        echo 'FAIL';
    }
} else {
    echo 'WRONG';
}

