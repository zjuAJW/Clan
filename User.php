<?php
require_once 'MysqlConnect.php';
class User{
	protected $username;
	protected $con;
	
	public function __construct($username){
		if(!self::isUserExist($username)){
			throw new Exception("No such user!");
		}else{
			$this->username = $username;
			$this->con = MysqlConnect::getInstance();
		}
	}
	
	
	public function getUserInfo($info){
		$con = MysqlConnect::getInstance();
		$sql = "select * from user where username = '$this->username'";
		$result = $con->query($sql);
		return $result[0][$info];
	}
	
	public function changeDiamond($num){
		$diamond = $this->getUserInfo("diamond");
		if($diamond + $num < 0){
			throw new Exception("宝石不足");
		}else{
			$sql = "update user set diamond = diamond + '$num' where username = '$this->username'";
			$this->con->query($sql);
		}
	}
	
	public function changeLevel($diff){
		$sql = "update user set level = level + '$diff' where username = '$this->username'";
		$this->con->query($sql);
	}
	
	public function getClanQuitRecord(){
		$sql = "select * from clan_quit_record where username = '$this->username'";
		$quit_result = $this->con->query($sql);
		return $quit_result;
	}
	
	public function deleteClanQuitRecord($clan_name){
		$sql = "delete from clan_quit_record where username = '$this->username' and clan_name = '$clan_name'";
		$result = $this->con->query($sql);
		return $result;
	}
	
	public function addClanJoinRecord($clan_name){
		$date = date('Y-m-d H:i:s',time());
		$sql = "select * from clan_join_in_request where username = '$this->username' and clan_name = '$clan_name'";
		$join_result = $this->con->query($sql);
		if(!count($join_result)){
			$sql = "insert into clan_join_in_request (username,clan_name,request_time) values ('$this->username','$clan_name','$date')";
			$result = $this->con->query($sql);
		}
	}
	
	public function deleteClanJoinRecord($clan_name){
		$sql = "delete from clan_join_in_request where username = '$this->username' and clan_name = '$clan_name'";
		$result = $this->con->query($sql);
		return $result;
	}
	
	public function getClanJoinRecord($clan_name){
		$sql = "select * from clan_join_in_request where username = '$this->username' and clan_name = '$clan_name'";
		$result = $this->con->query($sql);
		return $result;
	}
	
	public static function isUserExist($username){
		$con = MysqlConnect::getInstance();
		$result = $con->query("select * from user where username = '$username'");
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
		$con->query("insert into user_clan(username) values ('$username')");
		return new User($username);
	}
	
	
	public static function getInstance($username){
		if(!self::isUserExist($username)){
			throw new Exception("User not exists");
		}
		$con = MysqlConnect::getInstance();
		$sql = "select * from user_clan where username = '$username'";
		$result = $con->query($sql);
		switch ($result[0]['clan_job']){
			case null:
				return new User($username);
				break;
			case CLAN_LEADER:
				return new Leader($username);
				break;
			case CLAN_ELDER:
				return new Elder($username);
				break;
			case CLAN_MEMBER:
				return new Member($username);
				break;
			default:
				throw new Exception("User class getInstance error: wrong clan job");
		}
	}
	
}
?>