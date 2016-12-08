<?php
require_once 'User.php';
require_once 'paraCheck.php';
class UserService{
	public static function register($parameter){
		if(ParaCheck::check($parameter, ["username","password","nickname"])){
			$username = $parameter['username'];
			$password = md5($parameter['password']);
			$nickname = $parameter['nickname'];
		}
		$new_user = User::register($username, $password, $nickname);
		if($new_user instanceof User){
			return "Register Successfully";
		}else{
			throw new Exception("Register failed");
		}
	}
	
	public static function changeLevel($parameter){
		if(ParaCheck::check($parameter, ['uid','diff'])){
			$user = new User($parameter['uid']);
			$diff = $parameter['diff'];
		}
		$user->changeLevel($diff);
		return "Level changed";
	}
}
?>