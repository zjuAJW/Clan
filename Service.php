<?php
require_once 'CONSTANT.php';
date_default_timezone_set('prc');
require_once 'MysqlConnect.php';
class Service{
	protected static function login($parameter){
		//echo "login";
		if(isset($parameter['username'])){
			$username = $parameter['username'];
			//echo $username;
		}
		else{
			//echo "Request Syndax Error:No username";
			throw new Exception("Request Syndax Error:Login with no username");
			//exit();
		}
		if(isset($parameter['password'])){
			$password = $parameter['password'];
			//echo $password;
		}else{
			//echo "Request Syndax Error:No password";
			throw new Exception("Request Syndax Error:Login with no password");
			//exit();
		}
		$con = MysqlConnect::getInstance();
		if(!$con){
			throw new Exception("Connect Error");
		}else{
			$result = $con->query("select password from user where username = \"$username\"");
			if(!count($result)){
				throw new Exception("Wrong username or password");
			}elseif($result[0]["password"] != md5($password)){
				throw new Exception("Wrong username or password");
			}else{
				$date = date('Y-m-d H:i:s',time());
				$flag = $con->query("update user set time_last_log_in = '$date' where username = '$username'");
				if($flag){
					return "login success";
				}
				else{
					throw new Exception("Sql error");
				}	
			}
		}
	}
	
	protected static function register($parameter){
		if(isset($parameter['username'])){
			$username = $parameter['username'];
			//echo $username;
		}
		else{
			//echo "Request Syndax Error:No username";
			throw new Exception("Request Syndax Error:Register with no username");
			//exit();
		}
		if(isset($parameter['password'])){
			$password = md5($parameter['password']);
			//echo $password;
		}else{
			//echo "Request Syndax Error:No password";
			throw new Exception("Request Syndax Error:Register with no password");
		}
		if(isset($parameter['nickname'])){
			$nickname = $parameter['nickname'];
			//echo $password;
		}else{
			//echo "Request Syndax Error:No nickname";
			throw new Exception("Request Syndax Error:Register with no nickname");
		}
		$con = MysqlConnect::getInstance();
		if(!$con){
			//echo "服务器未连接，请稍后再试";
			throw new Exception("Connect Error");
		}else{
			$result = $con->query("select * from user where username = \"$username\"");
			if(count($result)){
				//echo "用户名已存在";
				throw new Exception("Existing username");
			}
			$result = $con->query("select * from user where nickname = \"$nickname\"");
			if(count($result)){
				//echo "昵称已存在";
				throw new Exception("Existing nickname");
			}
			$date = date('Y-m-d H:i:s',time());
			$flag = $con->query("insert into user(username,password,nickname,time_last_log_in,time_register) values (\"$username\",\"$password\",\"$nickname\",'$date','$date')");
			$flag2 = $con->query("insert into user_clan(username) values ('$username')");
			if($flag && $flag2){
				//echo "注册成功";
				throw new Exception("Register successfully");
			}
			else{
				//echo "注册失败，数据库连接错误";
				throw new Exception("sql Error");
			}
			
		}
	}
	
	
	
	//这个没写完！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！
	protected static function loadClanInfo($parameter){
		if(isset($parameter['username'])){
			$username = $parameter['username'];
		}else{
			throw new Exception("Request Syndax Error:Load clan information with no username");
		}
		$con = MysqlConnect::getInstance();
		$sql = "select * from user_clan where username = '$username'";
		$userClanResult =$con->query($sql);
		if($userClanResult[0]['clan_name'])
		$job = $userClanResult[0]['clan_job'];
	}
	
	
	protected static function createClan($parameter){
		if(isset($parameter['username'])){
			$username = $parameter['username'];
			//echo $username;
		}
		else{
			//echo "Request Syndax Error:No username";
			throw new Exception("Request Syndax Error:Create clan with no username");
			//exit();
		}
		if(isset($parameter['clan_name'])){
			$clan_name = $parameter['clan_name'];
			//echo $password;
		}else{
			//echo "Request Syndax Error:No password";
			throw new Exception("Request Syndax Error:Create clan with no clan name");
		}
		if(isset($parameter['icon_id'])){
			$icon_id = $parameter['icon_id'];
			//echo $password;
		}else{
			//echo "Request Syndax Error:No nickname";
			throw new Exception("Request Syndax Error:Create clan with no icon id");
		}
		$con = MysqlConnect::getInstance();
		$sql = "select * from user where username = '$username'";
		$result = $con->query($sql);
		//var_dump($result);
		if($result[0]['clan_name']!=null){
			throw new Exception("Request denied: You have to quit your clan first");
		}
		if($result[0]['level']<LEVEL_TO_CREATE_CLAN){
			echo $result[0]['level'];
			throw new Exception("Request denied: You have to reach Lv".LEVEL_TO_CREATE_CLAN." to create a clan");
			//exit();
		}
		if($result[0]['diamond']<DIAMOND_TO_CREATE_CLAN){
			throw new Exception("Request denied: Creating a clan needs at least ".DIAMOND_TO_CREATE_CLAN." diamond");
			//exit();
		}
		$sql = "select * from clan where clan_name = '$clan_name'";
		$result = $con->query($sql);
		if(count($result)){
			throw new Exception("Request denied:Clan already exits, please change the clan name");
		}
		$sql = "Insert into clan(clan_name,member_num,leader,icon_id) values('$clan_name',1,'$username','$icon_id')";
		$flag1 = $con->query($sql);
		$sql = "update user set clan_name = '$clan_name' ,diamond = diamond-1000 where username = '$username'";
		$flag2 = $con->query($sql);
		$date = date('Y-m-d H:i:s',time());
		$sql = "update user_clan set clan_name = '$clan_name',clan_job = ".CLAN_LEADER.",join_time = '$date',contribution = 0 where username = '$username'";
		$flag3 = $con->query($sql);
		if($flag1 && $flag2 && $flag3){
			return "Create clan successfully";
		}
		else{
			throw new Exception("Sql Error");
		}
	}
	
// 	protected static function joinInRequest($parameter){
// 		if(isset($parameter['username'])){
// 			$username = $parameter['username'];
// 		}
// 		else{
// 			throw new Exception("Request Sydax Error: Join in a clan without username");
// 		}
// 		if(isset($parameter['clan_name'])){
// 			$clan_name = $parameter['clan_name'];
// 		}
// 		else{
// 			throw new Exception("Request Sydax Error: Join in a clan without clan_name");
// 		}
		
// 	}
	
	protected static function quitClan($parameter){
		if(isset($parameter['username'])){
			$username = $parameter['username'];
		}else{
			throw new Exception("Request Sydax Error: Quit a clan without username");
		}
		$con = MysqlConnect::getInstance();
		$sql = "select * from user_clan where username = '$username'";
		$result = $con->query($sql);
		$clan_name = $result[0]['clan_name'];
		if($clan_name == null){
			throw new Exception("User is not in any clan");
		}else if($result[0]['clan_job'] == CLAN_LEADER){
			throw new Exception("Leader of clan cannot quit");
		}else{
			$date = date('Y-m-d H:i:s',time());
			$flag1 = $con->query("insert into clan_quit_record (username,clan_name,quit_time) values ('$username','$clan_name','$date')");
			$flag2 = $con->query("update user set clan_name = null where username = '$username'");
			$flag3 = $con->query("update user_clan set clan_name = null,clan_job = null,join_time = null,contribution = 0 where username = '$username'");
			$flag4 = $con->query("update clan set member_num = member_num - 1 where clan_name = '$clan_name'");
			if($flag1 && $flag2 && $flag3 && $flag4){
				return "Quit clan successfully";
			}
			else{
				throw new Exception("Sql error");
			}
		}
	}
	
	protected static function joinClan($parameter){
		if(isset($parameter['username'])){
			$username = $parameter['username'];
		}else{
			throw new Exception("Request Sydax Error: Join a clan without username");
		}
		if(isset($parameter['clan_name'])){
			$clan_name = $parameter['clan_name'];
		}else{
			throw new Exception("Request Sydax Error: Join a clan without clan name");
		}
		$con = MysqlConnect::getInstance();
		$sql = "select * from user where username = '$username'";
		$user_result = $con->query($sql);
		$sql = "select * from clan where clan_name = '$clan_name'";
		$clan_result = $con->query($sql);
		if($user_result[0]['clan_name'] != null){
			throw new Exception("Request denied: You must quit your clan to join a new one"); //不能同时加入两个公会
		}
		if(count($clan_result)==0){
			throw new Exception("Require denied: No such clan");
		}
		if($clan_result[0]['member_num'] >= MAX_CLAN_MEMBER_NUM){
			throw new Exception("该公会人数已满");
		}
		if($user_result[0]['level']<$clan_result[0]['level_required']){
			throw new Exception("你的战队等级低于加入该公会所需等级");
		}
		//if($user_result)
		$sql = "select * from clan_quit_record where username = '$username'";
		$quit_result = $con->query($sql);
		//判断时间限制条件
		if(count($quit_result)!=0){
			for($i = 0;$i<count($quit_result);$i++){
				//同一公会
				if($quit_result[$i]['clan_name'] == $clan_name){
					$quit_time = strtotime($quit_result[$i]['quit_time']);
					$timeDiff = (time()-$quit_time);
					if($timeDiff/3600 < 48){
						$timeRequire = 48*3600-$timeDiff;
						$d = floor($timeRequire/86400);
						$h = floor($timeRequire%86400/3600);
						$m = floor($timeRequire%86400%3600/60);
						$s = floor($timeRequire%86400%3600%60);
						throw new Exception("无法在退出公会48小时内加入同个公会，再过".$d."天".$h."小时" .$m ."分钟".$s."秒后才可加入该公会");
					}else{
						$sql = "delete from clan_quit_record where username = '$username' and clan_name = '$clan_name'";
						$result = $con->query($sql);
					}
				}else{
					$quit_time = strtotime($quit_result[$i]['quit_time']);
					$timeDiff = (time()-$quit_time);
					if($timeDiff/3600 < 1){
						$timeRequire = 3600-$timeDiff;
						//$d = floor($timeRequire/86400);
						//$h = floor($timeRequire%86400/3600);
						$m = floor($timeRequire/60);
						$s = floor($timeRequire%60);
						throw new Exception("无法在退出公会1小时内加入任何公会，再过".$m ."分钟".$s."秒后才可选择加入公会");
					}else if($timeDiff/3600 >= 48){
						$clan_name_i = $quit_result[$i]['clan_name'];
						$sql = "delete from clan_quit_record where username = '$username' and clan_name = '$clan_name_i'";
						$result = $con->query($sql);
					}
				}
			}
		}
		$date = date('Y-m-d H:i:s',time());
		$sql = "select * from clan_join_in_request where username = '$username' and clan_name = '$clan_name'";
		$join_result = $con->query($sql);
		if(!count($join_result)){
			$sql = "insert into clan_join_in_request (username,clan_name,request_time) values ('$username','$clan_name','$date')";
			$result = $con->query($sql);
		}
		return("Application is successful, awaiting approval.");
		
		
	}
	
	
	//接收成员,这个函数的逻辑感觉可以写的更好一点，但是结果现在好像还是正确的
	protected static function acceptMember($parameter){
		if(isset($parameter['username'])){
			$username = $parameter['username'];
		}else{
			throw new Exception("Request Sydax Error: accept member without username");
		}
		if(isset($parameter['member_name'])){
			$member_name = $parameter['member_name'];
		}else{
			throw new Exception('Request Sydax Error: accept member without member name');
		}
		if(isset($parameter['accept'])){
			$accept = $parameter['accept'];
		}else{
			throw new Exception("Request Sydax Error: accept member without accept or refuse");
		}
		
		$con = MysqlConnect::getInstance();
		$sql = "select * from user_clan where username = '$username'";
		$user_result = $con->query($sql);
		$clan_name = $user_result[0]['clan_name'];
		$sql = "select * from clan where clan = 'clan_name'";
		$clan_result = $con->query($sql);
		$sql = "select * from user_clan where username = '$member_name'";
		$member_result = $con->query($sql);
		//var_dump($member_result);
		if($user_result[0]['clan_job']!=CLAN_ELDER && $user_result[0]['clan_job']!=CLAN_LEADER){
			throw new Exception("Request denied: Only leader or elder can accept member");
		}
		if($accept){
			if(count($member_result) == 0){
				throw new Exception('No such user');
			}
			if($member_result[0]['clan_name']!=null){
				throw new Exception("Request denied: The player has already been in a clan");
			}
			if($clan_result[0]['member_num'] >= MAX_CLAN_MEMBER_NUM){
				throw new Exception("Request denied: The number of clan member is max");
			}
			$date = date('Y-m-d H:i:s',time());
			$sql = "delete from clan_join_in_request where username = '$member_name'";
			$con->query($sql);
			$sql = "update user set clan_name = '$clan_name' where username = '$member_name'";
			$con->query($sql);
			$sql = "update user_clan set clan_name = '$clan_name' ,clan_job = ".CLAN_MEMBER.",join_time='$date' where username = '$member_name'";
			$con->query($sql);
			$sql = "update clan set member_num = member_num + 1 where clan_name = '$clan_name'";
			$con->query($sql);
			return ("Accept successfully!");
		}else{
			$sql = "delete from clan_join_in_request where username = '$member_name' and clan_name = '$clan_name'";
			$con->query($sql);
			return("Refuse");
		}
	}
	
	protected static function searchForClan($parameter){
		if(isset($parameter['username'])){
			$username = $parameter['username'];
		}else{
			throw new Exception("Syndax error: Search for clan without username");
		}
		if(isset($parameter['clan_name'])){
			$clan_name = $parameter['clan_name'];			
		}else{
			throw new Exception("Syndax error: Search for clan without clan_name");
		}
		$con = MysqlConnect::getInstance();
		$sql = "select * from clan where clan_name = '$clan_name'";
		$result = $con->query($sql);
		if(count($result) == 0){
			throw new Exception("你查找的公会不存在");
		}else{
			$info = "";
			foreach($result[0] as $k=>$value){
				$info = $info.$k.":".$value." ";
			}
			return $info;
		}
	}
	
// 	public static function executeService($serviceName,$parameter){
// 		switch ($serviceName){
// 			case "login":
// 				$response = self::login($parameter);
// 				//echo $response;
// 				break;
// 			case "register":
// 				$response = self::register($parameter);
// 				break;
// 			case "createClan":
// 				$response = self::createClan($parameter);
// 				break;
// 			case "quitClan":
// 				$response = self::quitClan($parameter);
// 				break;
// 			case "joinClan":
// 				$response = self::joinClan($parameter);
// 				break;
// 			case "acceptMember":
// 				$response = self::acceptMember($parameter);
// 				break;
// 			case "searchForClan":
// 				$response = self::searchForClan($parameter);
// 				break;
// 			default:
// 				throw new Exception("Service name error");
// 		}
// 		return $response;
// 	}
	
	public static function executeService($serviceName,$methodName,$parameter){
		require_once $serviceName.".php";
		$service = new $serviceName();
		$response = $service->$methodName($parameter);
		return $response;
	}
}