<?php
require_once 'MysqlConnect.php';
require 'Clan.php';
//require 'Member.php';

$clan = new Clan("company");
echo $clan->getClanInfo("member_num");

?>