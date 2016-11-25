<?php
require_once 'MysqlConnect.php';
class Member extends User{
	//protected $con = MysqlConnect::getInstance();
	//构造函数
	public function __construct($username){
		$sql = "select * from user where username = '$username'";
		$result = $this->con->query($sql);
		if(count($result) == 0){
			throw new Exception("No such user!");
		}else{
			$this->username = $username;
		}
	}
	
	public function getUserClanInfo($info){
		$sql = "select * form user_clan where username = '$this->username'";
		$result = $this->con->query($sql);
		return $result[0][$info];
	}
	
	
	public function addClanQuitRecord(){
		$date = date('Y-m-d H:i:s',time());
		$this->con->query("insert into clan_quit_record (username,clan_name,quit_time) values ('$username',".$this->getUserInfo('clan_name').",'$date')");
	}
	
}
?>