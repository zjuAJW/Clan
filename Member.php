<?php
require_once 'MysqlConnect.php';
require_once 'User.php';
class Member extends User{
	//protected $con = MysqlConnect::getInstance();
	//构造函数
	public function __construct($username){
		if(!self::isUserExist($username)){
			throw new Exception("No such user!");
		}else{
			$this->username = $username;
			$this->con = MysqlConnect::getInstance();
		}
	}
	
	public function getUserClanInfo($info){
		$sql = "select * from user_clan where username = '$this->username'";
		$result = $this->con->query($sql);
		return $result[0][$info];
	}
	
	
	public function addClanQuitRecord($kickout){
		$date = date('Y-m-d H:i:s',time());
		$clan_name = $this->getUserClanInfo("clan_name");
		$sql = "insert into clan_quit_record (username,clan_name,quit_time,kickout) values ('$this->username','$clan_name','$date','$kickout')";
		$result = $this->con->query($sql);
		return $result;
	}
	
	public function admire($type){
		switch($type){
			case FREE_ADMIRE:
				$sql = "update user_clan set admire_num = admire_num + 1 where username = '$this->username'";
				$this->con->query($sql);
				$this->changeEnergy(15);
				break;
			case GOLD_ADMIRE:
				$this->changeGold(-30000);
				$sql = "update user_clan set admire_num = admire_num + 1 where username = '$this->username'";
				$this->con->query($sql);
				$this->changeEnergy(30);
				break;
			case DIAMOND_ADMIRE:
				$this->changeDiamond(-150);
				$sql = "update user_clan set admire_num = admire_num + 1 where username = '$this->username'";
				$this->con->query($sql);
				$this->changeEnergy(100);
				break;
		}
	}
	
	public function beAdmired($type){
		switch($type){
			case FREE_ADMIRE:
				$sql = "update user_clan set admire_reward_gold = admire_reward_gold + 1000 where username = '$this->username'"; //TODO:这里如果一直累加不领的话会不会超上限啊？
				$this->con->query($sql);
				break;
			case GOLD_ADMIRE:
				$sql = "update user_clan set admire_reward_gold = admire_reward_gold + 5000 where username = '$this->username'";
				$this->con->query($sql);
				break;
			case DIAMOND_ADMIRE:
				$sql = "update user_clan set admire_reward_gold = admire_reward_gold + 10000 where username = '$this->username'";
				$this->con->query($sql);
				break;
		}
	}
	
	public function getAdmireReward(){
		$reward = $this->getUserClanInfo("admire_reward_gold");
		$sql = "update user_clan set admire_reward_gold = 0 where username = '$this->username'";
		$this->con->query($sql);
		$this->changeGold($reward);
	}
}
?>