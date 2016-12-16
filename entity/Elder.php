<?php
require_once dirname(__FILE__).'/Member.php';
class Elder extends Member{
	public function startClanInstance($primary_id){
		$clan_id = $this->getUserClanInfo("clan_id");
		$date = date("Y-m-d H:i:s",time());
		$sql = "insert into clan_instance_in_process (clan_id,primary_id,time_start)
				values ($clan_id, $primary_id, '$date')";
		$this->con->query($sql);
		return "Start clan instance successfully";
	}
}
?>