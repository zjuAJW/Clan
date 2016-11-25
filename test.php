<?php
require_once 'MysqlConnect.php';
require 'Clan.php';
require 'Member.php';

$member = new Member('lalala');
$username = $member->getUserInfo('username');
var_dump($username);

?>