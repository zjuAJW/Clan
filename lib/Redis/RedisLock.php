<?php
require_once __DIR__."/RedisConnect.php";
class RedisLock{
	const DISPATCHED_SOLDIER_EXPIRE_TIME = 600;
	
	
	protected function getConnection(){
		return getRedisConnection(REDIS_DB_DEFAULT);
	}
	
	//TODO: 加锁的时候直接用EXPIRE行么？
	public static function acquireLock($lock){
		$con = self::getConnection();
		if($con->setnx($lock, time() + self::DISPATCHED_SOLDIER_EXPIRE_TIME)){
			return true;
		}
		$lockValue = $con->get($lock);
		if($lockValue != null && strcmp($lockValue,time())<0){
			if(strcmp($lockValue,$con->getset($lock,time()))){
				return true;
			}
		}
		return false;
	}
	
	public static function releaseLock($lock){
		$con = self::getConnection();
		$lockValue = $con->get($lock);
		
		//TODO:这里逻辑有点混乱
		if($lockValue != null && strcmp($lockValue,time()) > 0){
			
		}
	}
}
?>