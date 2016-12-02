<?php
require_once 'User.php';
require_once 'Member.php';
require_once 'Clan.php';
require_once 'CONSTANT.php';
require_once 'ParaCheck.php';
class ClanService{
	public static function createClan($parameter){
		if(ParaCheck::check($parameter, ["username","clan_name","icon_id"])){
			$user = new User($parameter['username']);
			$clan_name = $parameter['clan_name'];
			$icon_id = $parameter['icon_id'];
		}
		if($user->getUserInfo('clan_name')!=null){
			throw new Exception("Request denied: You have to quit your clan first");
		}
		if($user->getUserInfo('level')<LEVEL_TO_CREATE_CLAN){
			throw new Exception("Request denied: You have to reach Lv".LEVEL_TO_CREATE_CLAN." to create a clan");
		}
		if($user->getUserInfo('diamond')<DIAMOND_TO_CREATE_CLAN){
			throw new Exception("Request denied: Creating a clan needs at least ".DIAMOND_TO_CREATE_CLAN." diamond");
		}
		if(Clan::isClanExist($clan_name)){
			throw new Exception("Request denied:Clan already exits, please change the clan name");
		}
		Clan::createClan($clan_name, $user, $icon_id);
		return "Create clan successfully";
	}
	
	public static function quitClan($parameter){
		if(ParaCheck::check($parameter, ["username"])){
			$user = User::getInstance($parameter['username']);
		}
		if($user instanceof User){
			throw new Exception("User is not in any clan");
		}
		if($user instanceof Leader){
			throw new Exception("Leader of clan cannot quit");
		}
		$clan = new Clan($user->getUserInfo('clan_name'));
		$user->addClanQuitRecord(0);
		$clan->deleteMember($user);
		
	}
	
	public static function joinClan($parameter){
		if(ParaCheck::check($parameter, ['username','clan_name'])){
			$user = User::getInstance($parameter['username']);
			$clan_name = $parameter['clan_name'];
		}
		if($user->getUserInfo("clan_name")!=null){
			throw new Exception("不能同时加入两个工会，请先退出现在的工会");
		}
		if(!Clan::isClanExist($clan_name)){
			throw new Exception("工会不存在");
		}else{
			$clan = new Clan($clan_name);
		}
		if($clan->getClanInfo('member_num')>=MAX_CLAN_MEMBER_NUM){
			throw new Exception("工会人数已满");
		}
		if($clan->getClanInfo('level_required') > $user->getUserInfo('level')){
			throw new Exception("你的战队等级低于加入该公会所需等级");
		}
		$quit_result = $user->getClanQuitRecord();
		if(count($quit_result)!=0){
			for($i = 0;$i<count($quit_result);$i++){
				$quit_time = strtotime($quit_result[$i]['quit_time']);
				$timeDiff = (time()-$quit_time);
				//同一公会
				if($quit_result[$i]['clan_name'] == $clan_name){
					if($timeDiff/3600 < 48){
						$timeRequire = 48*3600-$timeDiff;
						$d = floor($timeRequire/86400);
						$h = floor($timeRequire%86400/3600);
						$m = floor($timeRequire%86400%3600/60);
						$s = floor($timeRequire%86400%3600%60);
						throw new Exception("无法在退出公会48小时内加入同个公会，再过".$d."天".$h."小时" .$m ."分钟".$s."秒后才可加入该公会");
					}else{
						$user->deletClanQuitRecord($clan_name);
					}
				}else if($quit_result[$i]['kickout'] == 0){
					if($timeDiff/3600 < 1){
						$timeRequire = 3600-$timeDiff;
						//$d = floor($timeRequire/86400);
						//$h = floor($timeRequire%86400/3600);
						$m = floor($timeRequire/60);
						$s = floor($timeRequire%60);
						throw new Exception("无法在退出公会1小时内加入任何公会，再过".$m ."分钟".$s."秒后才可选择加入公会");
					}
				}else if($timeDiff/3600 >= 48){
					$clan_name_i = $quit_result[$i]['clan_name'];
					$user->deleteClanQuitRecord($clan_name_i);
				}
			}
		}
		$user->addClanJoinRecord($clan_name);
		return("Application is successful, awaiting approval.");
	}
	
	public static function searchForClan($parameter){
		if(ParaCheck::check($parameter, ["username","clan_name"])){
			$user = User::getInstance($parameter["username"]);
			$clan = new Clan($parameter["clan_name"]);
		}
		if($clan){
			$result = $clan->getClanInfo('all');
			$info = "";
			foreach($result as $k=>$value){
				$info = $info.$k.":".$value." ";
			}
			return $info;
		}else{
			throw new Exception("你查找的公会不存在");
		}
	}
	
	public static function acceptMember($parameter){
		if(ParaCheck::check($parameter, ['username','member_name','accept'])){
				$user = User::getInstance($parameter['username']);
				$member = User::getInstance($parameter['member_name']);
				$accept = $parameter['accept'];
		}
		if($user->getUserInfo('clan_name') == null){
			throw new Exception("Request denied: user not in any clan");
		}else{
			$clan_job = $user->getUserClanInfo('clan_job');
		}
		if(!($clan_job == CLAN_LEADER || $clan_job == CLAN_ELDER)){
			throw new Exception("Request denied: Only leader or elder can accept member");
		}
		$clan_name = $user->getUserClanInfo("clan_name");
		if(!($member->getClanJoinRecord($clan_name))){
			throw new Exception("No such request");
		}
		$clan = new Clan($clan_name);
		if($accept){
			if($member == null){
				throw new Exception('No such user');
			}
			if($member->getUserInfo("clan_name")){
				throw new Exception("Request denied: The player has already been in a clan");
			}
			if($clan->getClanInfo("member_num")>=MAX_CLAN_MEMBER_NUM){
				throw new Exception("Request denied: The number of clan member is max");
			}
			$clan->addMember($member);
			return ("Accept successfully!");
		}else{
			$member->deleteClanJoinRecord($clan_name);
			return("Refuse");
		}
	}
	
	public static function clanSettings($parameter){
		if(ParaCheck::check($parameter, ['username','clan_name','icon_id','type','level_required'])){
			$user = User::getInstance($parameter['username']);
			$new_name = $parameter['clan_name'];
			$icon_id = $parameter['icon_id'];
			$type = $parameter['type'];
			$level_required = $parameter['level_required'];
		}
		if(!$user instanceof Leader){
			throw new Exception("Request denied: Only the leader can change settings");
		}else{
			$clan_name = $user->getUserClanInfo("clan_name");
			$clan = new Clan($clan_name);
			if(!empty($clan_name)){
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
			return "修改成功";
		}
	}
	
	public static function changeNotice($parameter){
		if(ParaCheck::check($parameter, ["username","notice"])){
			$user = User::getInstance($parameter["username"]);
			$notice = $parameter["notice"];
		}
		if(!($user instanceof Leader || $user instanceof Elder)){
			throw new Exception("Request denied: Only Leader or Elder can modify the motice");
		}else{
			$clan_name = $user->getUserClanInfo("clan_name");
			$clan = new Clan($clan_name);
			$result = $clan->changeNotice($notice);
			if($result){
				return "Notice Changed";
			}
		}
	}
	
	public static function checkMemberInfo($parameter){
		if(ParaCheck::check($parameter, ["username","member_name","info"])){
			$user = User::getInstance($parameter["username"]);
			$member = User::getInstance($parameter["member_name"]);
			$info = $parameter["info"];
			$result = [];
		}
		if(!$user instanceof Leader){
			throw new Exception("Request denied: Only the leader can check member info");
		}else{
			$info_array = explode(',',$info);
			if(in_array("time_last_log_in", $info_array)){
				$result["time_last_log_in"] = $member->getUserInfo("time_last_log_in");
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
	
	public static function setJob($parameter){
		if(ParaCheck::check($parameter, ["username","member_name","job"])){
			$user = User::getInstance($parameter["username"]);
			$member = User::getInstance($parameter["member_name"]);
			$job = $parameter["job"];
		}
		if(!$user instanceof Leader){
			throw new Exception("Request denied: Only Leader can change the job");
		}else if($parameter["username"] == $parameter["member_name"]){
			throw new Exception("You can't change your own job");
		}else{
			$clan_name = $user->getUserClanInfo("clan_name");
			$clan = new Clan($clan_name);
			if(!($member->getUserInfo("clan_name") == $clan_name)){
				throw new Exception("Request denied: member not in clan");
			}else{
				switch($job){
					case CLAN_LEADER:
						$member_job = $member->getUserClanInfo('clan_job');
						$clan->setJob($parameter['member_name'], CLAN_LEADER);
						$clan->setJob($parameter['username'],$member_job);
						return "Set Leader successfully";
					case CLAN_ELDER:
						if($clan->getElderNum() >= MAX_CLAN_ELDER_NUM){
							throw new Exception("Request denied: 长老人数超限");
						}else{
							$result = $clan->setJob($parameter['member_name'], CLAN_ELDER);
							return "Set Elder successfully";
						}
					case CLAN_MEMBER:
						$clan->setJob($parameter['member_name'], CLAN_MEMBER);
						return "Set Member successfully";
				}
			}
		}
	}
	
	public static function kickOutMember($parameter){
		if(ParaCheck::check($parameter, ["username","member_name"])){
			$user = User::getInstance($parameter["username"]);
			$member = User::getInstance($parameter["member_name"]);
		}
		if(!($user instanceof Leader)){
			throw new Exception("Request denied: Only Leader can kick out member");
		}
		$clan_name = $user->getUserClanInfo("clan_name");
		$clan = new Clan($clan_name);
		if(!($member->getUserInfo("clan_name") == $clan_name)){
			throw new Exception("Request denied: member not in clan");
		}else{
			$member->addClanQuitRecord(1);
			$clan->deleteMember($member);
			return "Kick out successfully";
		}
	}
	
	public static function dissolveClan($parameter){
		if(ParaCheck::check($parameter, ["username"])){
			$user = User::getInstance($parameter["username"]);
		}
		if(!($user instanceof Leader)){
			throw new Exception("Request denied: Only Leader can dissolve clan");
		}else{
			$clan_name = $user->getUserClanInfo("clan_name");
			$clan = new Clan($clan_name);
			$clan->dissolve();
			return "Clan has been dissoved";
		}
	}
	
	public static function admire($parameter){
		if(ParaCheck::check($parameter, ["username","member_name","type"])){
			$user = User::getInstance($parameter["username"]);
			$member = User::getInstance($parameter["member_name"]);
			$type = $parameter["type"];
		}
		if($parameter['username'] == $parameter['member_name']){
			throw new Exception("不能自己膜拜自己......");
		}
		if($user->getUserInfo("clan_name") != $member->getUserInfo("clan_name")){
			throw new Exception("Request denied: Two users are not in the same clan");
		}
		if($user->getUserInfo("level") >= $member->getUserInfo("level")){
			throw new Exception("Request denied: 只能膜拜比你等级高的玩家");
		}
		$vip_level = $user->getUserInfo("vip_level");
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
	
}
?>