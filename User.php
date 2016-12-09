<?php
require_once 'MysqlConnect.php';
class User{
	protected $uid;
	protected $con;
	
	public function __construct($uid){
		if(!self::isUserExist($uid)){
			throw new Exception("No such user!");
		}else{
			$this->uid = $uid;
			$this->con = MysqlConnect::getInstance();
		}
	}
	
	public function __get($property){
		switch($property){
			case "uid":
				return $this->uid;
		}
	}
	
	public function getUserInfo($info){
		$con = MysqlConnect::getInstance();
		$sql = "select * from user where uid = '$this->uid'";
		$result = $con->query($sql);
		return $result[0][$info];
	}
	
	public function getUserClanInfo($info){
		$sql = "select * from user_clan where uid = '$this->uid'";
		$result = $this->con->query($sql);
		return $result[0][$info];
	}
	
	public function getUserSoldierInfo($soldier_id){
		if($soldier_id == 0){
			$sql = "select * from user_soldier where uid = '$this->uid'";
			$result = $this->con->query($sql);
			return $result;
		}else{
			$sql = "select * from user_soldier where uid = '$this->uid' and soldier_id = '$soldier_id'";
			$result = $this->con->query($sql);
			if(isset($result[0])){
				return $result[0];
			}else{
				return null;
			}
		}
	}
	
	public function changeDiamond($num){
		$diamond = $this->getUserInfo("diamond");
		if($diamond + $num < 0){
			throw new Exception("宝石不足");
		}else{
			$sql = "update user set diamond = diamond + '$num' where uid = '$this->uid'";
			$this->con->query($sql);
		}
	}
	
	public function changeGold($num){
		$gold = $this->getUserInfo("gold");
		if($gold + $num < 0){
			throw new Exception("金币不足");
		}else{
			$sql = "update user set gold = gold + '$num' where uid = '$this->uid'";
			$this->con->query($sql);
		}
	}
	
	public function changeLevel($diff){
		$sql = "update user set level = level + '$diff' where uid = '$this->uid'";
		$this->con->query($sql);
	}
	
	public function changeEnergy($diff){
		$energy = $this->getUserInfo("energy");
		if($energy + $diff < 0){
			throw new Exception("体力不足");
		}else{
			$sql = "update user set energy = energy + '$diff' where uid = '$this->uid'";
			$this->con->query($sql);
		}
	}
	
	public function getClanQuitRecord(){
		$sql = "select * from clan_quit_record where uid = '$this->uid'";
		$quit_result = $this->con->query($sql);
		return $quit_result;
	}
	
	public function deleteClanQuitRecord($clan_id){
		$sql = "delete from clan_quit_record where uid = '$this->uid' and clan_id = '$clan_id'";
		$result = $this->con->query($sql);
		return $result;
	}
	
	public function addClanJoinRecord($clan_id){
		$date = date('Y-m-d H:i:s',time());
		$sql = "select * from clan_join_in_request where uid = '$this->uid' and clan_id = '$clan_id'";
		$join_result = $this->con->query($sql);
		if(!count($join_result)){
			$sql = "insert into clan_join_in_request (uid,clan_id,request_time) values ('$this->uid','$clan_id','$date')";
			$result = $this->con->query($sql);
		}
	}
	
	public function deleteClanJoinRecord($clan_id){
		$sql = "delete from clan_join_in_request where uid = '$this->uid' and clan_id = '$clan_id'";
		$result = $this->con->query($sql);
		return $result;
	}
	
	public function getClanJoinRecord($clan_id){
		$sql = "select * from clan_join_in_request where uid = '$this->uid' and clan_id = '$clan_id'";
		$result = $this->con->query($sql);
		return $result;
	}
	
	public static function isUserExist($uid){
		$con = MysqlConnect::getInstance();
		$result = $con->query("select * from user where uid = '$uid'");
		if(count($result)!=0){
			return true;
		}else{
			return false;
		}
	}
	
	public static function register($username,$password,$nickname){
		$con = MysqlConnect::getInstance();
		$result = $con->query("select * from user where username = '$username'");
		if(count($result)){
			//var_dump($result);
			throw new Exception("Existing username");
		}
		$result = $con->query("select * from user where nickname = '$nickname'");
		if(count($result)){
			throw new Exception("Existing nickname");
		}
		$date = date('Y-m-d H:i:s',time());
		$con->query("insert into user(username,password,nickname,time_last_log_in,time_register) values ('$username','$password','$nickname','$date','$date')");
		$uid = $con->query("select uid from user where username = '$username'")[0]['uid'];
		$con->query("insert into user_clan(uid) values ('$uid')");
		return new User($uid);
	}
	
	public static function getInstance($uid){
		if(!self::isUserExist($uid)){
			throw new Exception("User not exists");
		}
		$con = MysqlConnect::getInstance();
		$sql = "select * from user_clan where uid = '$uid'";
		$result = $con->query($sql);
		switch ($result[0]['clan_job']){
			case null:
				return new User($uid);
				break;
			case CLAN_LEADER:
				return new Leader($uid);
				break;
			case CLAN_ELDER:
				return new Elder($uid);
				break;
			case CLAN_MEMBER:
				return new Member($uid);
				break;
			default:
				throw new Exception("User class getInstance error: wrong clan job");
		}
	}
	
}
?>