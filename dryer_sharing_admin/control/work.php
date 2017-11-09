<?php
/**
 * Created by PhpStorm.
 * User: Jeremy
 * Date: 2017/2/8
 * Time: 11:12
 */
include_once("../common/database/adminDB.php");
require_once('phpqrcode/phpqrcode.php');

file_put_contents('url.txt', $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
$mac_addr = $_GET['mac_addr'];
$consume_id = $_GET['consume_id'];
$balance = $_GET['balance'];
$normal = $_GET['normal'];

$sql = "select dev_id, mac_addr from device_info where mac_addr = '{$mac_addr}'";
$result = $adminDB->ExecSQL($sql, $conn);

$ret = [];

// 如果该机器不存在，则添加至数据库
if ($mac_addr != null && $result == null) {
    // 生成二维码
    $url0 = $_SERVER['HTTP_HOST'] . '/wxpay/main/pay.php?id=' . $mac_addr;
    $url = urldecode($url0);
    ob_start();
    QRcode::png($url);
    $pngData = ob_get_clean();
    file_put_contents('./img/' . $mac_addr . '.png', $pngData);
    $QR_url = $_SERVER['HTTP_HOST'] . '/control/img/' . $mac_addr . '.png';
    // 添加信息
    $sql = "insert into device_info(mac_addr, balance, QR_code, status, allo_status) values('{$mac_addr}', 0, '{$QR_url}', 0, 0)";
    $adminDB->ExecSQL($sql, $conn);

    // 返回信息
    $sql = "select mac_addr, balance, status from device_info where mac_addr = '{$mac_addr}'";
    $result = $adminDB->ExecSQL($sql, $conn);
    $ret[0]['mac_addr'] = $result[0]['mac_addr'];
    $ret[0]['balance'] = $result[0]['balance'];
    $ret[0]['consume_id'] = "0";
    $ret[0]['flag'] = 1; // 代表添加成功
} else { // 机器已存在于数据库，则判断该设备是否处于正常请求状态
    $sql = "select mac_addr, balance, status, rec_id, consume_t from device_info, consume_rec 
            where mac_addr = '{$mac_addr}' and
            device_info.dev_id = consume_rec.dev_id
            ORDER BY consume_t desc LIMIT 1";
    $result = $adminDB->ExecSQL($sql, $conn);

    if ($normal == 1) { // 设备正常结束
        // 首先，判断该设备是否通过微信支付使用过，即判断该设备是否有用户消费记录
        if ($result != null) { // 存在消费记录，直接返回余额
            $ret[0]['mac_addr'] = $result[0]['mac_addr'];
            if ($balance == 0) {// 正常启动
                $ret[0]['balance'] = $result[0]['balance'];
                $ret[0]['flag'] = 2; // 代表存在消费记录，直接工作
                if ($consume_id == $result[0]['rec_id']) {
                    // 正常工作结束之后，发来的参数内consume_id（不为0）用来重置状态
                    $sql = "update device_info set status = 0 where mac_addr = '{$mac_addr}'";
                    $adminDB->ExecSQL($sql, $conn);
                    $ret[0]['consume_id'] = "0";
                } else {
                    // 正常查询余额时，发来的参数内consume_id（为0）用来获取时间余额
                    $sql = "update device_info set balance = 0 where mac_addr = '{$mac_addr}'";
                    $adminDB->ExecSQL($sql, $conn);
                    if ($ret[0]['balance'] == 0) {
                        $ret[0]['consume_id'] = "0";
                    } else {
                        $ret[0]['consume_id'] = $result[0]['rec_id'];
                    }
                }
            } else {// 开门狗启动
                $ret[0]['consume_id'] = $result[0]['rec_id'];
                $ret[0]['balance'] = $balance;
                $ret[0]['flag'] = 3; // 代表开门狗启动，无需更改数据库
            }

        } else { // 不存在消费记录，返回构造的json数据
            $ret[0]['mac_addr'] = $mac_addr;
            $ret[0]['balance'] = "0";
            $ret[0]['consume_id'] = "0";
            $ret[0]['flag'] = 2;
        }
    } else { // 设备中有余额
        // 首先，判断该付款信息是否失效（时间戳相隔时间大于24h， 即视为失效）
        if ($result != null) {
            $result[0]['consume_t'] = strtotime($result[0]['consume_t']);
            $rec_id = $result[0]['rec_id'];
            if ($rec_id == $consume_id) { // 如果用户一致，则比较时间戳相差是否大于24
                $now = time();
                $effect = ($now - $result[0]['consume_t'] >= 86400) ? 0 : 1;
                if (!$effect) {// 付款失效，设备余额归零
                    $sql = "update device_info set balance = 0, status = 0 
                      where mac_addr = '{$mac_addr}'";
                    $adminDB->ExecSQL($sql, $conn);
                    $ret[0]['mac_addr'] = $result[0]['mac_addr'];
                    $ret[0]['balance'] = "0";
                    $ret[0]['consume_id'] = "0";
                    $ret[0]['flag'] = 4;
                } else {// 代表付款未失效，此时要等待客户端将status=1，即用户确认（修复）
                    $ret[0]['mac_addr'] = $result[0]['mac_addr'];
                    $ret[0]['consume_id'] = $result[0]['rec_id'];
                    if ($result[0]['balance'] == 0 && $result[0]['status'] == 1) {
                        $sql = "update device_info set balance = {$balance}, status = 2 
                      where mac_addr = '{$mac_addr}'";
                        $adminDB->ExecSQL($sql, $conn);
                        $ret[0]['flag'] = 5;
                    } else if ($result[0]['balance'] != 0 && $result[0]['status'] == 2) {
                        $ret[0]['flag'] = 5;
                    } else if ($result[0]['balance'] != 0 && $result[0]['status'] == 1) {
                        $sql = "update device_info set balance = 0
                      where mac_addr = '{$mac_addr}'";
                        $adminDB->ExecSQL($sql, $conn);
                        $ret[0]['flag'] = 2;
                    }else{
                        $ret[0]['consume_id'] = "0";
                        $balance = "0";
                        $ret[0]['flag'] = 2;
                    }
                    $ret[0]['balance'] = $balance;
                }
            } else { // 如果订单不一致，则直接返回
                $sql = "update device_info set balance = 0 where mac_addr = '{$mac_addr}'";
                $adminDB->ExecSQL($sql, $conn);
                $ret[0]['mac_addr'] = $result[0]['mac_addr'];
                $ret[0]['balance'] = $result[0]['balance'];
                if($ret[0]['balance'] == 0){
                    $ret[0]['consume_id'] = "0";
                }else{
                    $ret[0]['consume_id'] = $result[0]['rec_id'];
                }
                $ret[0]['flag'] = 6;
            }
        }
    }
}

echo json_encode($ret);



