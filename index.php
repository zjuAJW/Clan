<?php
require dirname(__FILE__)."/gateway/gateWayFactory.php";
$handler = gateWayFactory::createGateWay();
$handler->processRequest();
$handler->output();
?>