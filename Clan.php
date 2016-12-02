<?php
require_once 'MysqlConnect.php';
require_once 'CONSTANT.php';
require_once 'Member.php';
require_once 'Leader.php';
require_once 'Elder.php';
class Clan{
	protected $clan_name;
	protected $con;
	
	public function __construct($clan_name){
		if(!self::isClanExist($clan_name)){
			echo($clan_name);
			throw new Exception("No such Clan");
		}else{
			$this->clan_name = $clan_name;
			$this->con = MysqlConnect::getInstance();
		}
	}
	
	public function getClanInfo($info){
		$sql = "select * from clan where clan_name = '$this->clan_name'";
		$result = $this->con->query($sql);
		if($info != "all"){
			return $result[0][$info];
		}else{
			return $result[0];
		}
	}
	
	
	public function addMember($member){
		$date = date('Y-m-d H:i:s',time());
		$username = $member->getUserInfo("username");
		$sql = "update user set clan_name = '$this->clan_name' where username = '$username'";
		$this->con->query($sql);
		$sql = "update user_clan set clan_name = '$this->clan_name',clan_job = 3,join_time = '$date' where username = '$username'";
		$this->con->query($sql);
		$sql = "update clan set member_num = member_num + 1 where clan_name = '$this->clan_name'";
		$this->con->query($sql);
		$sql = "delete from clan_join_in_request where username = '$username'";
		$this->con->query($sql);
	}
	
	public function deleteMember($member){
		$username = $member->getUserInfo("username");
		$this->con->query("update user set clan_name = null where username = '$username'");
		$this->con->query("update user_clan set clan_name = null,clan_job = null,join_time = null,contribution = 0,instance_num = 0 where username = '$username'");
		$this->con->query("update clan set member_num = member_num - 1 where clan_name = '$this->clan_name'");
	}
	
	public static function createClan($clan_name,$leader,$icon_id){
		$leader_name = $leader->getUserInfo('username');
		$con = MysqlConnect::getInstance();
		$date = date('Y-m-d H:i:s',time());
		$sql = "Insert into clan(clan_name,member_num,leader,icon_id,create_time) values('$clan_name',1,'$leader_name','$icon_id','$date')";
		$con->query($sql);
		$sql = "update user_clan set clan_name = '$clan_name',clan_job = ".CLAN_LEADER.",join_time = '$date',contribution = 0 where username = '$leader_name'";
		$con->query($sql);
		$sql = "update user set clan_name = '$clan_name' where username = '$leader_name'";
		$con->query($sql);
		$leader->changeDiamond(-1000);
		return new Clan($clan_name);
	}
	
	public function setJob($member_name,$job){
		$sql = "update user_clan set clan_job = '$job' where username = '$member_name'";
		$result = $this->con->query($sql);
		return $result;
	}
	
	public static function isClanExist($clan_name){
		$con = MysqlConnect::getInstance();
		$sql = "select * from clan where clan_name = '$clan_name'";
		$result = $con->query($sql);
		if(count($result) == 0){
			return false;
		}else{
			return true;
		}
	}
	
	public function changeClanName($new_name){
		$sql = "update clan set clan_name = '$new_name' where clan_name = '$this->clan_name'";
		$result = $this->con->query($sql);
		$sql = "update user set clan_name = '$new_name' where clan_name = '$this->clan_name'";
		$result = $result && $this->con->query($sql);
		$sql = "update user_clan set clan_name = '$new_name' where clan_name = '$this->clan_name'";
		$result = $result && $this->con->query($sql);
		return $result;
	}
	
	public function changeClanIcon($new_icon){
		$sql = "update clan set icon_id = '$new_icon' where clan_name = '$this->clan_name'";
		$result = $this->con->query;
		return $result;
	}
	
	public function changeClanType($new_type){
		$sql = "update clan set type = '$new_type' where clan_name = '$this->clan_name'";
		$result = $this->con->query($sql);
		return $result;
	}
	
	public function changeLevelRequired($level){
		$sql = "update clan set level_required = '$level' where clan_name = '$this->clan_name'";
		$result = $this->con->query($sql);
		return $result;
	}
	
	public function changeNotice($notice){
		$sql = "update clan set notice = '$notice' where clan_name = '$this->clan_name'";
		$result = $this->con->query($sql);
		return $result;
	}
	
	public function getElderNum(){
		$sql = "select * from user_clan where clan_name = '$this->clan_name' and clan_job =".CLAN_ELDER;
		$result = $this->con->query($sql);
		return count($result);
	}
	
	public function dissolve(){
		$sql = "update user set clan_name = Null where clan_name = '$this->clan_name'";
		$this->con->query($sql);
		$sql = "update user_clan set clan_name = null,clan_job = null,join_time = null,contribution = 0,instance_num = 0 where clan_name = '$this->clan_name'";
		$this->con->query($sql);
		$sql = "delete from clan where clan_name = '$this->clan_name'";
		$this->con->query($sql);
	}
}
?>