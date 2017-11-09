<?php
include_once("system.class.inc.php");
include_once('dbInfo.php');
$dbInfo = new dbInfo();
$connDB = new ConnDB($dbInfo::DBSTYLE, $dbInfo::HOST,
    $dbInfo::USER, $dbInfo::PWD, $dbInfo::DBNAME);
$conn = $connDB->GetConnld();
$adminDB = new AdminDB();

