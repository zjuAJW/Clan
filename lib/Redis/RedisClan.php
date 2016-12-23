<?php
require_once __DIR__ . "/RedisConnect.php";
require_once __DIR__. "/../../config/redis_config.php";
class RedisClan{
	const ONE_HOUR = 3600;
	const TWO_DAYS = 172800;
	
	const QUIT_CLAN = "quitclan";
	
	protected static function getConnection(){
		return getRedisConnection(REDIS_DB_CLAN);
	}
	public static function redisSetQuitClanTime($uid,$clan_id,$time){
		$con = self::getConnection();
		$key = $uid . self::QUIT_CLAN . $clan_id;
		$con->set($key,$time);
		$con->expire($key, self::TWO_DAYS);
		$key = $uid.self::QUIT_CLAN;
		$con->set($key,$time);
		$con->expire($key,self::ONE_HOUR);
	}
	
	public static function redisGetQuitClanTime($uid,$clan_id){
		$con = self::getConnection();
		$key = $uid . self::QUIT_CLAN . $clan_id;
		$clan_quit = $con->ttl($key);
		$key = $uid . self::QUIT_CLAN;
		$quit = $con->ttl($key);
		return array($clan_quit,$quit);
	}
}
?>