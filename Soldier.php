<?php
require_once 'MysqlConnect.php';
class Soldier{
	protected $id;
	protected $owner;
	protected $con;
	public function __construct($id,$owner){
		$this->id = $id;
		$this->owner = $owner;
		$this->con = MysqlConnect::getInstance();
	}

	
	public function getSoldierInfo($info){
		$sql = "select * from user_soldier where uid = '$this->uid' and soldier_id = '$this->soldier_id";
		$result = $this->con->query($sql);
		return $result[0][$info];
	}
}
?>