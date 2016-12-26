<?php
require_once __DIR__."/../Predis/Autoloader.php";
require_once __DIR__."/../../config/redis_config.php";

Predis\Autoloader::register();

function getRedisConnection($db = REDIS_DB_DEFAULT){ 
	$redis = new Predis\Client(array("host"=>REDIS_HOST_DEFAULT,"port"=>REDIS_PORT_DEFAULT,"database" => $db));
	return $redis;
}
?>