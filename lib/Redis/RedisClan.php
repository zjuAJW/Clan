<?php
require_once __DIR__ . "/RedisConnect.php";
require_once __DIR__. "/../../config/redis_config.php";
require_once __DIR__. "/../../constant/CONSTANT.php";

class RedisClan{
	const ONE_HOUR = 3600;
	const TWO_DAYS = 172800;
	
	const QUIT_CLAN = "quitclan";
	const JOIN_CLAN_REQUEST = "request to join";
	
	protected static function getConnection(){
		return getRedisConnection(REDIS_DB_CLAN);
	}
	public static function setQuitClanTime($uid,$clan_id,$time){
		$con = self::getConnection();
		$key = $uid . ":" . self::QUIT_CLAN . ":" . $clan_id;
		$con->set($key,$time);
		$con->expire($key, self::TWO_DAYS);
		$key = $uid . ":" . self::QUIT_CLAN;
		$con->set($key,$time);
		$con->expire($key,self::ONE_HOUR);
	}
	
	public static function getQuitClanTime($uid,$clan_id){
		$con = self::getConnection();
		$key = $uid . ":". self::QUIT_CLAN . ":" . $clan_id;
		$clan_quit = $con->ttl($key);
		$key = $uid . ":" . self::QUIT_CLAN;
		$quit = $con->ttl($key);
		return array($clan_quit,$quit);
	}
	
	public static function setKickOutMemberTime($uid,$clan_id,$date){
		$con = self::getConnection();
		$key = $uid . ":" . self::QUIT_CLAN . ":" . $clan_id;
		$con->set($key,$time);
		$con->expire($key, self::TWO_DAYS);
	}
	
	public static function addJoinClanRequest($uid, $clan_id,$date){
		$con = self::getConnection();
		$key = $uid . ":" . self::JOIN_CLAN_REQUEST . ":" . $clan_id;
		$con->set($key,$date);
	}
	
	public static function getJoinClanRequest($uid, $clan_id){
		$con = self::getConnection();
		$key = $uid . ":" . self::JOIN_CLAN_REQUEST . ":" . $clan_id;
		return $con->get($key);
	}
	
	public static function deleteJoinClanRequest($uid, $clan_id){
		$con = self::getConnection();
		if($clan_id == ClanConstants::ALL_CLAN_ID){
			$keys = $con->keys($uid.":".self::JOIN_CLAN_REQUEST ."*");
			foreach($keys as $key){
				$con->del($key);
			}
		}else{
			$key = $uid . ":" . self::JOIN_CLAN_REQUEST . ":" . $clan_id;
			$con->del($key);
		}
	}
}
?>