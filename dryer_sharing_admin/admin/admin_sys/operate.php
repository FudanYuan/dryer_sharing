<?php
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

$operate = $_REQUEST['operate'];
if(isset($_REQUEST["merchants"])){
    $merchant = $_REQUEST['merchants'];
}
if(isset($_REQUEST["dev_ids"])){
    $dev_ids = $_REQUEST['dev_ids'];
}

if($operate == 'QR_code'){
    $info = [];
    for ($j = 0; $j < count($dev_ids); $j++) {
        $sql= "select dev_id, mac_addr, QR_code from device_info where dev_id = {$dev_ids[$j]}";

        $info_tmp = $adminDB->ExecSQL($sql, $conn);
        if ($info_tmp[0] != null) {
            array_push($info, $info_tmp[0]);
        }
    }
    // 分页时需要获取记录总数，键值为 total
    $result["total"] = count($info);
    // 根据传递过来的分页偏移量和分页量截取模拟分页 rows 可以根据前端的 dataField 来设置
    $result["rows"] = array_slice($info, $_POST['offset'], $_POST['limit']);

    // 返回时价信息
    // file_put_contents('url.txt',json_encode($result));

    echo json_encode($result);
}

else{
    for ($i = 0; $i < count($merchant); $i++) {
        // 获取商户名称，商户编号
        $merchant_info = explode('_', $merchant[$i]);
        $merchant_name = $merchant_info[0];
        $merchant_id = $merchant_info[1];

        // 查询商户编号
        $sql = "select mer_id from merchant_info where mer_name = '{$merchant_name}' and mer_sub_id = {$merchant_id}";
        $mer_id = $adminDB->ExecSQL($sql, $conn);
        $mer_id = $mer_id[0]['mer_id'];

        switch ($operate) {
            case 'pass': {
                // 更新状态为审核通过
                $sql = "update admin set audit = 3 where mer_id = {$mer_id}";
                $adminDB->ExecSQL($sql, $conn);
                break;
            }
            case 'reject': {
                // 更新状态为审核未通过
                $sql = "update admin set audit = 4 where mer_id = {$mer_id}";
                $adminDB->ExecSQL($sql, $conn);
                break;
            }
            case 'allocate':{
                for ($j = 0; $j < count($dev_ids); $j++) {
                    // 分配设备
                    $sql = array();
                    $sql[0] = "update device_info set allo_status = 1 where dev_id = {$dev_ids[$j]}";
                    $sql[1] = "insert into mer_dev values ({$mer_id}, {$dev_ids[$j]})";
                    $adminDB->Transcation($sql, $conn);
                }
                break;
            }
        }
    }
}
