<?php
require_once 'User.php';
require_once 'paraCheck.php';
class UserService{
	public static function register($parameter){
		if(isset($parameter['username'])){
			$username = $parameter['username'];
		}else{
			throw new Exception("Request Syndax Error:Register with no username");
		}
		if(isset($parameter['password'])){
			$password = md5($parameter['password']);
		}else{
			throw new Exception("Request Syndax Error:Register with no password");
		}
		if(isset($parameter['nickname'])){
			$nickname = $parameter['nickname'];
		}else{
			throw new Exception("Request Syndax Error:Register with no nickname");
		}
		$new_user = User::register($username, $password, $nickname);
		if($new_user instanceof User){
			return "Register Successfully";
		}else{
			throw new Exception("Register failed");
		}
	}
	
	public static function changeLevel($parameter){
		if(ParaCheck::check($parameter, ['username','diff'])){
			$user = new User($parameter['username']);
			$diff = $parameter['diff'];
		}
		$user->changeLevel($diff);
		return "Level changed";
	}
}
?>