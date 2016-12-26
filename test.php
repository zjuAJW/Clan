<?php
require_once __DIR__ . "/lib/Redis/RedisConnect.php";
require_once __DIR__ . "/config/redis_config.php";
$redis = getRedisConnection();
var_dump($redis);
$test = $redis->keys("wja*");
var_dump($test);

?>