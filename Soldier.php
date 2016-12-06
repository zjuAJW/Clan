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
	
	public function getCE(){
		$id = "id".$this->id;
		$sql = "select '$id' from user_soldier where level = '$level'";
		$result = $this->con->query($sql);
		return $result[0][$id];
	}
	
	public function getSoldierInfo($info){
		$sql = "select * from user_soldier where username = '$this->username' and soldier_id = '$this->soldier_id";
		$result = $this->con->query($sql);
		return $result[0][$info];
	}
}
?>