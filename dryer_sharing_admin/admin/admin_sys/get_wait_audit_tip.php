<?php
if(!$_GET['timed']) exit();

// 开始会话
session_start();
if (!isset($_SESSION['system'])) {
    header('location: ../login.html');
}
$id = $_SESSION['system'];
// 设置时区
ini_set('date.timezone', 'Asia/Shanghai');

// 设置响应数据的内容格式，和字符集
header("Content-Type:application/json;charset=utf-8");

// 引入数据库操作函数
require_once '../../common/database/adminDB.php';

date_default_timezone_set("PRC");
set_time_limit(0);//无限请求超时时间
$timed = $_GET['timed'];
while (true) {
    // 查询数据库中未审核的个数
    $sql = "select count(*) as num from admin where audit = 2";
    $wait_audit_num = $adminDB->ExecSQL($sql, $conn);
    $wait_audit_num = $wait_audit_num[0]['num'];
    if ($wait_audit_num > 0) { // 如果随机数在20-56之间就视为有效数据，模拟数据发生变化
        // 返回数据信息
        echo $wait_audit_num;
        exit();
    } else { // 模拟没有数据变化，将休眠 hold住连接
        // 返回数据信息
        echo $wait_audit_num;
        exit();
    }
}