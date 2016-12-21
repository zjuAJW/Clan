<?php
require_once dirname(dirname(__FILE__))."/entity/User.php";
require_once dirname(dirname(__FILE__))."/entity/Member.php";
require_once dirname(dirname(__FILE__))."/entity/Clan.php";
require_once dirname(dirname(__FILE__))."/constant/CONSTANT.php";
require_once dirname(dirname(__FILE__))."/constant/ClanException.php";
require_once dirname(dirname(__FILE__))."/util/Util.php";
require_once dirname(dirname(__FILE__))."/entity/DispatchedSoldier.php";


class ClanService{
	public function createClan($parameter){
		if(Util::checkParameter($parameter, ["uid","clan_name","icon_id"])){
			$user = new User($parameter['uid']);
			$clan_name = $parameter['clan_name'];
			$icon_id = $parameter['icon_id'];
		}
		if($user->getUserClanInfo('clan_id')!=null){
			throw new ClanException("Request denied: You have to quit your clan first",ClanException::ILLEGAL_OPERATION);
		}
		if($user->level < LEVEL_TO_CREATE_CLAN){
			throw new ClanException("Request denied: You have to reach Lv".LEVEL_TO_CREATE_CLAN." to create a clan",
																						ClanException::ILLEGAL_OPERATION);
		}
		if(Clan::isClanExist($clan_name)){
			throw new CLanException("Request denied:Clan already exits, please change the clan name",ClanException::ILLEGAL_OPERATION);
		}
		Clan::createClan($clan_name, $user, $icon_id);
		return "Create clan successfully";
	}
	
	public function quitClan($parameter){
		if(Util::checkParameter($parameter, ["uid"])){
			$user = User::getInstance($parameter['uid']);
		}
		if($user->getUserClanInfo("clan_id") == null){
			throw new ClanException("User is not in any clan",ClanException::ILLEGAL_OPERATION);
		}
		if($user instanceof Leader){
			throw new ClanException("Leader of clan cannot quit",ClanException::ILLEGAL_OPERATION);
		}
		$clan_id = $user->getUserClanInfo('clan_id');
		if(Clan::isClanExist($clan_id)){
			$clan = new Clan($user->getUserClanInfo('clan_id'));
		}else{
			throw new ClanException("Clan do not exist", ClanException::CLAN_DO_NOT_EXIST);
		}
		$user->addClanQuitRecord(0);
		$clan->deleteMember($user);
		return "Quit Clan successfully";
	}
	
	public function joinClan($parameter){
		if(Util::checkParameter($parameter, ['uid','clan_id'])){
			$user = User::getInstance($parameter['uid']);
			$clan_id = $parameter['clan_id'];
		}
		if($user->getUserClanInfo("clan_id")!=null){
			throw new ClanException("不能同时加入两个工会，请先退出现在的工会",ClanException::ILLEGAL_OPERATION);
		}
		if(!Clan::isClanExist($clan_id)){
			throw new CLanException("工会不存在",ClanException::CLAN_DO_NOT_EXIST);
		}else{
			$clan = new Clan($clan_id);
		}
		if($clan->getClanInfo('member_num')>=MAX_CLAN_MEMBER_NUM){
			throw new ClanException("工会人数已满",ClanException::CLAN_IS_FULL);
		}
		if($clan->getClanInfo('level_required') > $user->level){
			throw new ClanException("你的战队等级低于加入该公会所需等级",ClanException::ILLEGAL_OPERATION);
		}
		$quit_result = $user->getClanQuitRecord();
		if(count($quit_result)!=0){
			for($i = 0;$i<count($quit_result);$i++){
				$quit_time = strtotime($quit_result[$i]['quit_time']);
				$timeDiff = (time()-$quit_time);
				//同一公会
				if($quit_result[$i]['clan_id'] == $clan_id){
					if($timeDiff/3600 < 48){
						$timeRequire = 48*3600-$timeDiff;
						$d = floor($timeRequire/86400);
						$h = floor($timeRequire%86400/3600);
						$m = floor($timeRequire%86400%3600/60);
						$s = floor($timeRequire%86400%3600%60);
						throw new ClanException("无法在退出公会48小时内加入同个公会，再过".$d."天".$h."小时" .$m ."分钟".$s."秒后才可加入该公会",
																								ClanException::ILLEGAL_OPERATION);
					}else{
						$user->deletClanQuitRecord($clan_id);
					}
				}else if($quit_result[$i]['kickout'] == 0){
					if($timeDiff/3600 < 1){
						$timeRequire = 3600-$timeDiff;
						$m = floor($timeRequire/60);
						$s = floor($timeRequire%60);
						throw new ClanException("无法在退出公会1小时内加入任何公会，再过".$m ."分钟".$s."秒后才可选择加入公会",ClanException::ILLEGAL_OPERATION);
					}
				}else if($timeDiff/3600 >= 48){
					$clan_id_i = $quit_result[$i]['clan_id'];
					$user->deleteClanQuitRecord($clan_id_i);
				}
			}
		}
		$user->addClanJoinRecord($clan_id);
		return("Application is successful, awaiting approval.");
	}
	
	//TODO:还没有根据公会名查找的功能
	public function searchForClan($parameter){
		if(Util::checkParameter($parameter, ["uid","clan_id"])){
			$user = User::getInstance($parameter["uid"]);
			$clan_id = $parameter["clan_id"];
			if(Clan::isClanExist($clan_id)){
				$clan = new Clan($parameter["clan_id"]);
			}else{
				throw new ClanException("Clan do not exist",ClanException::CLAN_DO_NOT_EXIST);
			}
		}
		if($clan){
			$result = $clan->getClanInfo('all');
			$info = "";
			foreach($result as $k=>$value){
				$info = $info.$k.":".$value." ";
			}
			return $info;
		}else{
			throw new ClanException("你查找的公会不存在",ClanException::CLAN_DO_NOT_EXIST);
		}
	}
	
	public static function acceptMember($parameter){
		if(Util::checkParameter($parameter, ['uid','member_id','accept'])){
				$user = User::getInstance($parameter['uid']);
				$member = User::getInstance($parameter['member_id']);
				$accept = $parameter['accept'];
		}
		if($user->getUserClanInfo('clan_id') == null){
			throw new Exception("Request denied: user not in any clan");
		}else{
			$clan_job = $user->getUserClanInfo('clan_job');
		}
		if(!($clan_job == CLAN_LEADER || $clan_job == CLAN_ELDER)){
			throw new ClanException("Request denied: Only leader or elder can accept member",ClanException::PERMISSION_DENIED);
		}
		$clan_id = $user->getUserClanInfo("clan_id");
		if(!($member->getClanJoinRecord($clan_id))){
			throw new Exception("No such request");
		}
		if(Clan::isClanExist($clan_id)){
			$clan = new Clan($clan_id);
		}else{
			throw new ClanException("Clan do not exist", ClanException::CLAN_DO_NOT_EXIST);
		}
		if($accept){
			if($member == null){
				throw new Exception('No such user');
			}
			if($member->getUserClanInfo("clan_id")){
				throw new Exception("Request denied: The player has already been in a clan");
			}
			if($clan->getClanInfo("member_num")>=MAX_CLAN_MEMBER_NUM){
				throw new ClanException("Request denied: Clan is full",ClanException::CLAN_IS_FULL);
			}
			$clan->addMember($member);
			return ("Accept successfully!");
		}else{
			$member->deleteClanJoinRecord($clan_id);
			return("Refuse");
		}
	}
	
	
	//TODO：感觉这个函数写的有问题。。。。。。（每个拆开？工会名字好像应该判断一下到底有没有变过）
	public function clanSettings($parameter){
		if(Util::checkParameter($parameter, ['uid','clan_name','icon_id','type','level_required'])){
			$user = User::getInstance($parameter['uid']);
			$new_name = $parameter['clan_name'];
			$icon_id = $parameter['icon_id'];
			$type = $parameter['type'];
			$level_required = $parameter['level_required'];
		}
		if(!$user instanceof Leader){
			throw new ClanException("Request denied: Only the leader can change settings",ClanException::PERMISSION_DENIED);
		}else{
			$clan_id = $user->getUserClanInfo("clan_id");
			if(Clan::isClanExist($clan_id)){
				$clan = new Clan($clan_id);
			}else{
				throw new ClanException("Clan do not exist",ClanException::CLAN_DO_NOT_EXIST);
			}
			if(!empty($new_name)){
				$user->changeDiamond(-500);
				$clan->changeClanName($new_name);
			}
			if(!empty($icon_id)){
				$clan->changeClanIcon($icon_id);
			}
			if(!empty($type)){
				$clan->changeClanType($type);
			}
			if(!empty($level_required)){
				$clan->changeLevelRequired($level_required);
			}
			return "Modified done";
		}
	}
	
	public function changeNotice($parameter){
		if(Util::checkParameter($parameter, ["uid","notice"])){
			$user = User::getInstance($parameter["uid"]);
			$notice = $parameter["notice"];
		}
		if(!($user instanceof Leader || $user instanceof Elder)){
			throw new ClanException("Request denied: Only Leader or Elder can modify the motice",ClanException::PERMISSION_DENIED);
		}else{
			$clan_id = $user->getUserClanInfo("clan_id");
			if(Clan::isClanExist($clan_id)){
				$clan = new Clan($clan_id);
			}else{
				throw new ClanException("Clan do not exist",ClanException::CLAN_DO_NOT_EXIST);
			}
			$result = $clan->changeNotice($notice);
			if($result){
				return "Notice Changed";
			}
		}
	}
	
	public function checkMemberInfo($parameter){
		if(Util::checkParameter($parameter, ["uid","member_id","info"])){
			$user = User::getInstance($parameter["uid"]);
			$member = User::getInstance($parameter["member_id"]);
			$info = $parameter["info"];
			$result = [];
		}
		if(!$user instanceof Leader){
			throw new ClanException("Request denied: Only the leader can check member info",ClanException::PERMISSION_DENIED);
		}else{
			$info_array = explode(',',$info);
			if(in_array("time_last_log_in", $info_array)){
				$result["time_last_log_in"] = $member->time_last_log_in;
			}
			if(in_array("contribution",$info_array)){
				$result["contribution"] = $member->getUserClanInfo("contribution");
			}
			if(in_array("instance_num",$info_array)){
				$result["instance_num"] = $member->getUserClanInfo("instance_num");
			}
			return $result;
		}
	}
	
	public function setJob($parameter){
		if(Util::checkParameter($parameter, ["uid","member_id","job"])){
			$user = User::getInstance($parameter["uid"]);
			$member = User::getInstance($parameter["member_id"]);
			$job = $parameter["job"];
		}
		if(!$user instanceof Leader){
			throw new ClanException("Request denied: Only Leader can change the job",ClanException::PERMISSION_DENIED);
		}else if($parameter["uid"] == $parameter["member_id"]){
			throw new Exception("You can't change your own job");
		}else{
			$clan_id = $user->getUserClanInfo("clan_id");
			if(Clan::isClanExist($clan_id)){
				$clan = new Clan($clan_id);
			}else{
				throw new ClanException("Clan do not exist",ClanException::CLAN_DO_NOT_EXIST);
			}
			if(!($member->getUserClanInfo("clan_id") == $clan_id)){
				throw new Exception("Request denied: member not in clan");
			}else{
				switch($job){
					case CLAN_LEADER:
						$member_job = $member->getUserClanInfo('clan_job');
						$clan->setJob($parameter['member_id'], CLAN_LEADER);
						$clan->setJob($parameter['uid'],$member_job);
						return "Set Leader successfully";
					case CLAN_ELDER:
						if($clan->getElderNum() >= MAX_CLAN_ELDER_NUM){
							throw new Exception("Request denied: 长老人数超限");
						}else{
							$result = $clan->setJob($parameter['member_id'], CLAN_ELDER);
							return "Set Elder successfully";
						}
					case CLAN_MEMBER:
						$clan->setJob($parameter['member_id'], CLAN_MEMBER);
						return "Set Member successfully";
				}
			}
		}
	}
	
	public function kickOutMember($parameter){
		if(Util::checkParameter($parameter, ["uid","member_id"])){
			$user = User::getInstance($parameter["uid"]);
			$member = User::getInstance($parameter["member_id"]);
		}
		if(!($user instanceof Leader)){
			throw new ClanException("Request denied: Only Leader can kick out member",ClanException::PERMISSION_DENIED);
		}
		$clan_id = $user->getUserClanInfo("clan_id");
		if(Clan::isClanExist($clan_id)){
			$clan = new Clan($clan_id);
		}else{
			throw new ClanException("Clan do not exist",ClanException::CLAN_DO_NOT_EXIST);
		}
		if(!($member->getUserClanInfo("clan_id") == $clan_id)){
			throw new Exception("Request denied: member not in clan");
		}else{
			$member->addClanQuitRecord(1);
			$clan->deleteMember($member);
			return "Kick out successfully";
		}
	}
	
	public function dissolveClan($parameter){
		if(Util::checkParameter($parameter, ["uid"])){
			$user = User::getInstance($parameter["uid"]);
		}
		if(!($user instanceof Leader)){
			throw new ClanException("Request denied: Only Leader can dissolve clan",ClanException::PERMISSION_DENIED);
		}else{
			$clan_id = $user->getUserClanInfo("clan_id");
			if(Clan::isClanExist($clan_id)){
				$clan = new Clan($clan_id);
			}else{
				throw new ClanException("Clan do not exist",ClanException::CLAN_DO_NOT_EXIST);
			}
			$clan->dissolve();
			return "Clan has been dissoved";
		}
	}
	
	public function admire($parameter){
		if(Util::checkParameter($parameter, ["uid","member_id","type"])){
			$user = User::getInstance($parameter["uid"]);
			$member = User::getInstance($parameter["member_id"]);
			$type = $parameter["type"];
		}
		if($parameter['uid'] == $parameter['member_id']){
			throw new Exception("不能自己膜拜自己......");
		}
		if($user->getUserClanInfo("clan_id") != $member->getUserClanInfo("clan_id")){
			throw new ClanException("Request denied: Two users are not in the same clan",ClanException::ILLEGAL_OPERATION);
		}
		if($user->level >= $member->level){
			throw new Exception("Request denied: 只能膜拜比你等级高的玩家");
		}
		$vip_level = $user->vip_level;
		$admire_num = $user->getUserClanInfo("admire_num");
		if(($vip_level < 8 && $admire_num >= 1) || (8 <= $vip_level && $vip_level < 12 && $admire_num >= 2) || $admire_num >= 3){
			throw new Exception("膜拜次数已用完");
		}
		if($type == DIAMOND_ADMIRE && $vip_level < 9){
			throw new Exception("VIP9才能开启钻石膜拜");
		}
		$user->admire($type);
		$member->beAdmired($type);
		return "Admire successfully";
	}
	
	public function getAdmireReward($parameter){
		if(Util::checkParameter($parameter, ["uid"])){
			$user = User::getInstance($parameter["uid"]);
		}
		if($user->getUserClanInfo("admire_reward_gold") <= 0){
			throw new Exception("没有可领取的奖励");
		}else{
			$user->getAdmireReward();
			return "Get reward successfully";		
		}
	}
	
	public function dispatchSoldier($parameter){
		if(Util::checkParameter($parameter, ["uid","soldier_id"])){
			$user = User::getInstance($parameter["uid"]);
			$soldier_id = $parameter["soldier_id"];
		}
		$clan_id = $user->getUserClanInfo("clan_id");
		if($clan_id == null){
			throw new Exception("Request denied: Join a clan to send out soldiers");
		}
		$vip = $user->vip_level;
		$soldier_dispatched = $user->getSoldierDispatched();
		$dispatched_num = count($soldier_dispatched);
		if(($vip<12 && $dispatched_num>=2) || ($vip>12 && $vip < 14 && $dispatched_num >= 3) || ($vip > 14 && $dispatched_num >= 4)){
			throw new Exception("派出英雄已达上限");
		}
		$user->dispatchSoldier($soldier_id);
		return "Soldier has been dispatched";
	}
	
	public function employSoldier($parameter){
		
		if(Util::checkParameter($parameter, ["uid","soldier_id","owner"])){
			$owner = User::getInstance($parameter["owner"]);
			$user = User::getInstance($parameter['uid']);
			$soldier = new DispatchedSoldier($parameter["soldier_id"], $parameter["owner"]);
		}
		if($user->uid == $owner->uid){
			throw new Exception("Request denied: 不能雇佣自己的英雄");
		}
		if(!($user->getUserClanInfo("clan_id") == $owner->getUserClanInfo("clan_id"))){
			throw new Exception("Request denied: You can only employ soldier from your own clan");
		}
		if($user->level < $soldier->level){
			throw new Exception("Request denied: You can only employ soldier whose level is lower than you");
		}
		$employed_soldier = $user->getEmployedSoldier();
		if($employed_soldier){
			foreach($employed_soldier as $s){
				if($s['owner'] == $owner->uid){
					throw new Exception("每天只能从同一支战队雇佣一位佣兵");
				}
			}
		}
		$user->employSoldier($soldier,$owner);
		return "Employ successfully";
	}
	
	public function recallSoldier($parameter){
		if(Util::checkParameter($parameter, ["uid","soldier_id"])){
			$user = User::getInstance($parameter["uid"]);
			$soldier = new DispatchedSoldier($parameter["soldier_id"],$parameter["uid"]);
		}
		$time_dispatched = strtotime($soldier->time_dispatched);
		$timeDiff = (time()-$time_dispatched);
		if($timeDiff/3600 < 0.5){
			$timeRequire = 0.5*3600-$timeDiff;
			$m = floor($timeRequire/60);
			$s = floor($timeRequire%60);
			throw new Exception("该英雄还有".$m ."分钟".$s."秒才能归队");
		}else{
			$user->recallSoldier($soldier);
		}
		return "Recall successfully";
	}
	
	public function startClanInstance($parameter){
		if(Util::checkParameter($parameter, ["uid","primary_id"])){
			$user = User::getInstance($parameter["uid"]);
			$primary_id = $parameter["primary_id"];
		}
		if(!($user instanceof Leader || $user instanceof Elder)){
			throw new Exception("Request denied: Only leader of elder can satrt instance");
		}
		$clan_id = $user->getUserClanInfo("clan_id");
		if(Clan::isClanExist($clan_id)){
			$clan = new Clan($clan_id);
		}else{
			throw new ClanException("Clan do not exist",ClanException::CLAN_DO_NOT_EXIST);
		}
		$finished_instance = $clan->getFinishedInstance();
		$instance_in_process = $clan->getInstanceInProcess();
		if((empty($finished_instance) && $primary_id != 1) || (!empty($finished_instance) && $finished_instance[0]["primary_id"] < $primary_id - 1)){
			throw new Exception("通关前面的关卡来解锁当前关卡");
		}
		if(!empty($instance_in_process) && $instance_in_process[0]["primary_id"] == $primary_id){
			throw new Exception("关卡已开通");
		}
		$result = $user->startClanInstance($primary_id);
		return $result;
	}
	
	public function applicateForTrophy($parameter){
		if(Util::checkParameter($parameter, ["uid","trophy_id"])){
			$user = User::getInstance($parameter["uid"]);
			$trophy_id = $parameter["trophy_id"];
		}
		$clan_id = $user->getUserInfo("clan_id");
		if(!isset($clan_id)){
			throw new Exception("Request denied:User not in any clan");
		}
	}
}
?>