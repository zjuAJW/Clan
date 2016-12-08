<?php
require_once 'MysqlConnect.php';
require_once 'User.php';
class Member extends User{
	//protected $con = MysqlConnect::getInstance();
	//构造函数
	
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
			return $result[0];
		}
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
				$sql = "update user_clan set admire_num = admire_num + 1 where uid = '$this->uid'";
				$this->con->query($sql);
				$this->changeEnergy(15);
				break;
			case GOLD_ADMIRE:
				$this->changeGold(-30000);
				$sql = "update user_clan set admire_num = admire_num + 1 where uid = '$this->uid'";
				$this->con->query($sql);
				$this->changeEnergy(30);
				break;
			case DIAMOND_ADMIRE:
				$this->changeDiamond(-150);
				$sql = "update user_clan set admire_num = admire_num + 1 where uid = '$this->uid'";
				$this->con->query($sql);
				$this->changeEnergy(100);
				break;
		}
	}
	
	public function beAdmired($type){
		switch($type){
			case FREE_ADMIRE:
				$sql = "update user_clan set admire_reward_gold = admire_reward_gold + 1000 where uid = '$this->uid'"; //TODO:这里如果一直累加不领的话会不会超上限啊？
				$this->con->query($sql);
				break;
			case GOLD_ADMIRE:
				$sql = "update user_clan set admire_reward_gold = admire_reward_gold + 5000 where uid = '$this->uid'";
				$this->con->query($sql);
				break;
			case DIAMOND_ADMIRE:
				$sql = "update user_clan set admire_reward_gold = admire_reward_gold + 10000 where uid = '$this->uid'";
				$this->con->query($sql);
				break;
		}
	}
	
	public function getAdmireReward(){
		$reward = $this->getUserClanInfo("admire_reward_gold");
		$sql = "update user_clan set admire_reward_gold = 0 where uid = '$this->uid'";
		$this->con->query($sql);
		$this->changeGold($reward);
	}
	
	public function sendOutSoldier($soldier_id){
		$soldier = $this->getUserSoldierInfo($soldier_id);
		if(isset($soldier)){
			$clan_id = $this->getUserInfo("clan_id");
			$level = $soldier['level'];
			$CE = 100;
			$date = date("Y:m:d H:i:s",time());
			$sql = "insert into soldier_sent_out (uid,clan_id,time_send_out,level,price) 
					values ('$this->uid','$clan_id','$date','$level','$price')";
			
		}
	}
}
?>