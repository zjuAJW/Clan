<?php
require_once __DIR__ . "/lib/RedisConnect.php";
require_once __DIR__ . "/config/redis_config.php";
$redis = getRedisConnection();
$test = $redis->get("test");
echo $test;


?>