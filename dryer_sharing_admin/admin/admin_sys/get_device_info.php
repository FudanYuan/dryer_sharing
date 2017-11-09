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

// 获取设备状态信息
$allocation_status = $_POST['allocation_status'];
// 获取设备所在省
$pro = $_POST['pro'];
// 获取设备所在市
$city = $_POST['city'];
// 获取设备所在具体地址
$dist = $_POST['dist'];
// 获取设备状态
$status = $_POST['status'];

if($pro == '' || $city == ''){
    $pro = '不限';
    $city = '不限';
}

$info = [];

// 查询价格、时长信息
if ($allocation_status == '已分配') {
    // 获取商家
    $merchant = $_POST['merchant'];
    // 如果选择了商家，则直接返回该信息
    if($merchant != 'all'){
        $sql = "select * from dev_info_view where merchant_name = '{$merchant}'";
    }
    else{
        // 已分配的设备信息
        if ($pro == '不限') {
            $sql = "select * from dev_info_view";
        } else {
            if ($city == '不限') {
                $sql = "select * from dev_info_view where pro_name = '{$pro}'";
            } else {
                if ($dist == '') {
                    $sql = "select * from dev_info_view where pro_name = '{$pro}' and city_name = '{$city}'";
                } else {
                    $sql = "select * from dev_info_view where pro_name = '{$pro}' and city_name = '{$city}' and addr_detail like '%{$dist}%'";
                }
            }
        }
    }

    $info_tmp = $adminDB->ExecSQL($sql, $conn);
    if ($info_tmp != null) {
        for ($i = 0; $i < count($info_tmp); $i++) {
            if ($status == '不限') {
                array_push($info, $info_tmp[$i]);
            } else if ($status == '待机') {
                if ($info_tmp[$i]['status'] == 0) {
                    array_push($info, $info_tmp[$i]);
                }
            } else if ($status == '运行') {
                if ($info_tmp[$i]['status'] == 1) {
                    array_push($info, $info_tmp[$i]);
                }
            } else if ($status == '故障') {
                if ($info_tmp[$i]['status'] == 2) {
                    array_push($info, $info_tmp[$i]);
                }
            }
        }
    }
} else if ($allocation_status == '未分配') {
    // 未分配的设备信息
    $sql = "select dev_id, mac_addr, QR_code from device_info
            where allo_status = 0";

    $info_tmp = $adminDB->ExecSQL($sql, $conn);
    if ($info_tmp != null) {
        for ($i = 0; $i < count($info_tmp); $i++) {
            array_push($info, $info_tmp[$i]);
        }
    }
}

//分页时需要获取记录总数，键值为 total
$result["total"] = count($info);
//根据传递过来的分页偏移量和分页量截取模拟分页 rows 可以根据前端的 dataField 来设置
$result["rows"] = array_slice($info, $_POST['offset'], $_POST['limit']);

// 返回
echo json_encode($result);