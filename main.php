<?php
require 'gateWayFactory.php';
$handler = gateWayFactory::createGateWay();
$handler->processRequest();
$handler->output();
?>