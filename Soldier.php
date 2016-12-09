<?php
require_once 'MysqlConnect.php';
class Soldier{
	protected $id;
	protected $owner;
	protected $con;
	public function __construct($id,$owner){
		if(self::isSoldierExist($id, $owner)){
			$this->id = $id;
			$this->owner = $owner;
			$this->con = MysqlConnect::getInstance();
		}else{
			throw new Exception("No such soldier");
		}
	}

	public function getSoldierInfo($info){
		$sql = "select * from user_soldier where uid = '$this->owner' and soldier_id = '$this->id'";
		$result = $this->con->query($sql);
		return $result[0][$info];
	}
	
	public function __get($property){
		switch($property){
			case "id":
				return $this->id;
			case "uid":
				return $this->owner;
			default:
				return $this->getSoldierInfo($property);
		}
	}
	
	public static function isSoldierExist($soldier_id,$uid){
		$con = MysqlConnect::getInstance();
		$sql = "select * from user_soldier where uid = $uid && soldier_id = $soldier_id";
		$result = $con->query($sql);
		if(!empty($result)){
			return true;
		}else{
			return false;
		}
	}
}
?>