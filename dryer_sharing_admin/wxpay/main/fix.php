<?php
/**
 * Created by PhpStorm.
 * User: Jeremy
 * Date: 2017/9/8
 * Time: 15:38
 */
include_once "../../common/database/adminDB.php";
$id = $_REQUEST['id'];
$sql = "update device_info set status = 1 where mac_addr = '{$id}'";
if($adminDB->ExecSQL($sql, $conn)){
    echo 'SUCCESS';
}
else{
    echo 'FAILED';
}