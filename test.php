<?php
require_once dirname(__FILE__).'/lib/MysqlConnect.php';
require_once dirname(__FILE__).'/entity/Clan.php';
//require 'Member.php';

$con = MysqlConnect::getInstance();
$sql = "select * from user where uid = 1";
$result = $con->query($sql);
var_dump($result);
?>