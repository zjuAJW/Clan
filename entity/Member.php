<?php
require_once dirname(dirname(__FILE__))."/lib/MysqlConnect.php";
require_once dirname(dirname(__FILE__))."/util/Util.php";
require_once dirname(__FILE__).'/User.php';
require_once dirname(__FILE__).'/Soldier.php';
class Member extends User{
	//protected $con = MysqlConnect::getInstance();
	//构造函数
	
	public function getSoldierDispatched(){
		$sql = "select * from soldier_dispatched where uid = '$this->uid'";
		$result = $this->con->query($sql);
		if($result){
			$soldiers = [];
			foreach($result as $s){
				$soldier = new DispatchedSoldier($s['soldier_id'],$s['uid']);
				$soldiers[] = $soldier;
			}
			return $soldiers;
		}else{
			return null;
		}
	}
	
	
	public function addClanQuitRecord($kickout){
		$date = date('Y-m-d H:i:s',time());
		$clan_id = $this->getUserClanInfo("clan_id");
		$sql = "insert into clan_quit_record (uid,clan_id,quit_time,kickout) values ('$this->uid','$clan_id','$date','$kickout')";
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
	
	public function getEmployedSoldier(){
		$sql = "select * from soldier_employed where uid = $this->uid";
		$result = $this->con->query($sql);
		return $result;
	}
	
	public function dispatchSoldier($soldier_id){
		$soldier = new Soldier($soldier_id,$this->uid);
		$soldier_dispatched = $this->getSoldierDispatched();
		if($soldier_dispatched){
			foreach($soldier_dispatched as $k){
				if($k->id == $soldier_id){
					throw new Exception("Soldier has already been dispatched");
				}
			}
		}
		$clan_id = $this->getUserClanInfo("clan_id");
		$CE = $soldier->CE;
		$level = $soldier->level;
		$date = date("Y-m-d H:i:s",time());
		$price = Util::soldierPrice($CE);
		$sql = "insert into soldier_dispatched (uid,clan_id,soldier_id,time_dispatched,CE,price,level) 
				values ($this->uid,$clan_id,$soldier_id,'$date',$CE,$price,$level)";
		$result = $this->con->query($sql);
	}
	
	public function employSoldier($soldier,$owner){
		$date = date("Y-m-d H:i:s",time());
		$this->changeGold(-$soldier->price);
		$sql = "insert into soldier_employed (uid,soldier_id,owner,time_employed) 
										values ($this->uid,$soldier->id,$owner->uid,'$date')";
		$this->con->query($sql);
		$soldier->employed_times = $soldier->employed_times + 1;
		if($soldier->employed_income < 200000){
			$soldier->employed_income = $soldier->employed_income + ceil($soldier->price * 0.7);
		}
	}
	
	public function recallSoldier($soldier){
		$income = Util::soldierIncome($soldier->time_dispatched, $soldier->employed_times, $soldier->price, $soldier->CE);
		$this->changeGold($income);
		$sql = "delete from soldier_dispatched where uid = $this->uid and soldier_id = $soldier->id";
		$this->con->query($sql);
	}
}
?>