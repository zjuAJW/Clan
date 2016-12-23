<?php
require_once __DIR__ . "/lib/Redis/RedisConnect.php";
require_once __DIR__ . "/config/redis_config.php";
$redis = getRedisConnection();
$test = $redis->ttl("test");
$test2 = $redis->ttl("wja");
var_dump($test);
var_dump($test2);


?>