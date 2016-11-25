<?php
require_once 'MysqlConnect.php';
require_once 'CONSTANT.php';
require_once 'Member.php';
class Clan{
	protected $clan_name;
	public function __construct($clan_name){
		$con = MysqlConnect::getInstance();
		$sql = "select * from clan where clan_name = '$clan_name'";
		$result = $con->query($sql);
		if(count($result) == 0){
			throw new Exception("No such Exception");
		}else{
			$this->clan_name = $clan_name;
		}
	}
	
	public function getClanInfo($info){
		$con = MysqlConnect::getInstance();
		$sql = "select * from clan where clan_name = '$this->clan_name'";
		$result = $con->query($sql);
		return $result[0][$info];
	}
	
	public function addMember($member){
		$con = MysqlConnect::getInstance();
		$date = date('Y-m-d H:i:s',time());
		$username = $member->username;
		$sql = "update user set clan_name = '$this->clan_name' where username = '$username'";
		$con->query($sql);
		$sql = "update user_clan set clan_name = '$this->clan_name',clan_job = 3,join_time = '$date' where username = '$username'";
		$con->query($sql);
		$sql = "update clan set member_num = member + 1 where clan_name = '$this->clan_name'";
		$con->query($sql);
		$sql = "delete from clan_join_in_request where username = '$username'";
		$con->query($sql);
	}
	
	public function deleteMember($member){
		$username = $member->username;
		$con->query("update user set clan_name = null where username = '$username'");
		$con->query("update user_clan set clan_name = null,clan_job = null,join_time = null,contribution = 0 where username = '$username'");
		$con->query("update clan set member_num = member_num - 1 where clan_name = '$clan_name'");
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
		return new Clan($clan_name);
	}
	
	public function setJob($member_name,$job){
		$con = MysqlConnect::getInstance();
		$sql = "update user_clan set clan_job = '$job' where username = 'member_name'";
		$con->query($sql);
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
}
?>