<?php
/**
 * Created by PhpStorm.
 * User: Jeremy
 * Date: 2017/3/30
 * Time: 14:48
 */
/* 设置响应数据的内容格式，和字符集*/
header('Content-type:text/html;charset=utf-8');

// 引入数据库操作函数
require_once '../common/database/adminDB.php';

// 初始化会话
session_start();

// 获取前端输入账户和密码
$id = $_POST['id'];
$pwd = sha1(md5($_POST['pwd']));
$sql = "select user_id, role_id from admin where username = '{$id}' limit 1";
$result = $adminDB->ExecSQL($sql, $conn);

if ($result) {
    // 获取账号id及角色id
    $user_id = $result[0]['user_id'];
    $role_id = $result[0]['role_id'];
    // 验证密码
    $sql = "select pwd from admin where user_id = '{$user_id}'";
    $password = $adminDB->ExecSQL($sql, $conn);
    $password = $password[0]['pwd'];

    if ($role_id == 1) {
        if ($password == $pwd) {
            $_SESSION['system'] = $id;
            echo 'SYS_SUCCESS';
        } else {
            echo 'SYS_FAIL';
        }
    }
    else {
        if ($password == $pwd) {
            $_SESSION['merchant'] = $id;
            echo 'MER_SUCCESS';
        } else {
            echo 'MER_FAIL';
        }
    }
}
else {
    echo 'NOTEXITS';
}

