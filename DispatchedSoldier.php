<?php
class DispatchedSoldier extends Soldier{
	public function __construct($id,$owner){
		if(self::isDispatchedSoldierExist($id, $owner)){
			$this->id = $id;
			$this->owner = $owner;
			$this->con = MysqlConnect::getInstance();
		}else{
			throw new Exception("No such soldier");
		}
	}
	
	public function __get($property){
		switch($property){
			case "id":
				return $this->id;
				break;
			case "owner":
				return $this->owner;
				break;
			default:
				$sql = "select * from soldier_dispatched where uid = $this->owner and soldier_id = $this->id";
				$result = $this->con->query($sql);
				return $result[0][$property];
		}
	}
	
	public function __set($property,$value){
			$sql = "update soldier_dispatched set $property = $value 
						where uid = $this->owner and soldier_id = $this->id";
			$this->con->query($sql);
	}
	
	public static function isDispatchedSoldierExist($soldier_id,$uid){
		$con = MysqlConnect::getInstance();
		$sql = "select * from soldier_dispatched where uid = $uid && soldier_id = $soldier_id";
		$result = $con->query($sql);
		if(isset($result)){
			return true;
		}else{
			return false;
		}
	}
}
?>