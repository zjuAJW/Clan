<?php
require_once 'MysqlConnect.php';
require_once 'CONSTANT.php';
require_once 'Member.php';
require_once 'Leader.php';
require_once 'Elder.php';
class Clan{
	protected $clan_id;
	protected $con;
	
	public function __construct($clan_id){
		if(!self::isClanExist($clan_id)){
			//echo($clan_name);
			throw new Exception("No such Clan");
		}else{
			$this->clan_id = $clan_id;
			$this->con = MysqlConnect::getInstance();
		}
	}
	
	public function getClanInfo($info){
		$sql = "select * from clan where clan_id = '$this->clan_id'";
		$result = $this->con->query($sql);
		if($info != "all"){
			return $result[0][$info];
		}else{
			return $result[0];
		}
	}
	
	
	public function addMember($member){
		$date = date('Y-m-d H:i:s',time());
		$uid = $member->getUserInfo("uid");
		$sql = "update user_clan set clan_id = '$this->clan_id',clan_job = 3,join_time = '$date' where uid = '$uid'";
		$this->con->query($sql);
		$sql = "update clan set member_num = member_num + 1 where id = '$this->clan_id'";
		$this->con->query($sql);
		$sql = "delete from clan_join_in_request where uid = '$uid'";
		$this->con->query($sql);
	}
	
	public function deleteMember($member){
		$uid = $member->getUserInfo("uid");
		$this->con->query("update user set clan_id = null where uid = '$uid'");
		$this->con->query("update user_clan set clan_id = null,clan_job = null,join_time = null,contribution = 0,instance_num = 0 where uid = '$uid'");
		$this->con->query("update clan set member_num = member_num - 1 where id = '$this->clan_id'");
	}
	
	public static function createClan($clan_name,$leader,$icon_id){
		$leader_id = $leader->getUserInfo('uid');
		$con = MysqlConnect::getInstance();
		$date = date('Y-m-d H:i:s',time());
		$leader->changeDiamond(-1000);
		$sql = "Insert into clan(clan_name,member_num,leader,icon_id,create_time) values('$clan_name',1,'$leader_id','$icon_id','$date')";
		$con->query($sql);
		$clan_id = $con->query("select id from clan where clan_name = '$clan_name'")[0]["id"];
		$sql = "update user_clan set clan_id = '$clan_id',clan_job = ".CLAN_LEADER.",join_time = '$date',contribution = 0 where uid = '$leader_id'";
		$con->query($sql);
	}
	
	public function setJob($uid,$job){
		$sql = "update user_clan set clan_job = '$job' where uid = '$uid'";
		$result = $this->con->query($sql);
		return $result;
	}
	
	public static function isClanExist($clan_id){
		$con = MysqlConnect::getInstance();
		$sql = "select * from clan where id = '$clan_id'";
		$result = $con->query($sql);
		if(count($result) == 0){
			return false;
		}else{
			return true;
		}
	}
	
	public function changeClanName($new_name){
		$sql = "update clan set clan_name = '$new_name' where id = '$this->clan_id'";
		$result = $this->con->query($sql);
		return $result;
	}
	
	public function changeClanIcon($new_icon){
		$sql = "update clan set icon_id = '$new_icon' where id = '$this->clan_id'";
		$result = $this->con->query($sql);
		return $result;
	}
	
	public function changeClanType($new_type){
		$sql = "update clan set type = '$new_type' where id = '$this->clan_id'";
		$result = $this->con->query($sql);
		return $result;
	}
	
	public function changeLevelRequired($level){
		$sql = "update clan set level_required = '$level' where id = '$this->clan_id'";
		$result = $this->con->query($sql);
		return $result;
	}
	
	public function changeNotice($notice){
		$sql = "update clan set notice = '$notice' where id = '$this->clan_id'";
		$result = $this->con->query($sql);
		return $result;
	}
	
	public function getElderNum(){
		$sql = "select * from user_clan where clan_id = '$this->clan_id' and clan_job =".CLAN_ELDER;
		$result = $this->con->query($sql);
		return count($result);
	}
	
	public function dissolve(){
		$sql = "update user set clan_id = null where clan_id = '$this->clan_id'";
		$this->con->query($sql);
		$sql = "update user_clan set clan_id = null,clan_job = null,join_time = null,contribution = 0,instance_num = 0 where clan_id = '$this->clan_id'";
		$this->con->query($sql);
		$sql = "delete from clan where id = '$this->clan_id'";
		$this->con->query($sql);
	}
	
	public function getFinishedInstance(){
		$sql = "select * from clan_instance_finished where clan_id = $this->clan_id";
		$result = $this->con->query($sql);
		return $result;
	}
	
	public function getInstanceInProcess(){
		$sql = "select * from clan_instance_in_process where clan_id = $this->clan_id";
		$result = $this->con->query($sql);
		return $result;
	}
	
}
?>