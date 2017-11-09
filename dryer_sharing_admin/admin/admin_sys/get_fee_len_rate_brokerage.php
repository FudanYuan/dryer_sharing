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

// 获取输入的商户名称
$merchant = $_POST['merchant'];

$info = [];

// 查询价格、时长信息
$sql = "select mer_name, fee_1, fee_2, fee_add, len_1, len_2, len_add, 
rate_1, rate_2, rate_add, brokerage
from mer_fee_len_rate_broke_view where role_id = 1";

$info_tmp = $adminDB->ExecSQL($sql, $conn);
if ($info_tmp != null) {
    for($t = 0; $t<count($info_tmp); $t++){
        array_push($info, $info_tmp[$t]);
    }
}

//分页时需要获取记录总数，键值为 total
$result["total"] = count($info);
//根据传递过来的分页偏移量和分页量截取模拟分页 rows 可以根据前端的 dataField 来设置
$result["rows"] = array_slice($info, $_POST['offset'], $_POST['limit']);

// 返回时价信息
echo json_encode($result);