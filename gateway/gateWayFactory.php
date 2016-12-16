<?php
require_once 'GateWay.php';
class gateWayFactory{
	protected static function getRawInputData(){
		return file_get_contents("php://input");
	}
	
	public static function createGateWay(){
		$contentType = null;
		$contentEncoding = null;
		$acceptEncoding = null;
		if(isset($_SERVER['HTTP_ACCEPT_ENCODING'])){
			$acceptEncoding = $_SERVER['HTTP_ACCEPT_ENCODING'];
		}
		if(isset($_SERVER['CONTENT_TYPE'])){
			$contentType = $_SERVER['CONTENT_TYPE'];
		}
		if(isset($_SERVER['HTTP_CONENT_ENCODING'])){
			$contentEncoding = $_SERVER['HTTP_CONENT_ENCODING'];
		}
		$rawInputData = self::getRawInputData();
		return new gateWay($acceptEncoding, $contentType, $rawInputData,$contentEncoding);
	} 
}
?>