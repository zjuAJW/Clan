<?php
require 'gateWayFactory.php';
$handler = gateWayFactory::creatGateWay();
$handler->processRequest();
$handler->output();
?>