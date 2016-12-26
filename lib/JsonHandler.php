<?php
require_once dirname(dirname(__FILE__))."/service/Service.php";
class JsonHandler{
	public static function deserializeRequest($rawInputData){
		//echo $rawInputData;
		$data = json_decode($rawInputData,true);
		//echo $data;
		if(empty($data)){
			throw new Exception("Can't not decode the json rawInputData");
		}
		return $data;
	}
	
	 
	
	public static function handleRequest($data){
		$responses = array();
		if(empty($data['serviceName'])){
			throw new Exception("Request Syndax Error: No service name");
		}
		if(empty($data['methodName'])){
			throw new Exception("Request Syndax Error: No method name");
		}
		if(empty($data['parameter'])){
			throw new Exception("Request Syndax Error: No parameter");
		}
		$serviceName = $data['serviceName'];
		$parameter = $data['parameter'];
		$methodName = $data['methodName'];
		$response = Service::executeService($serviceName,$methodName,$parameter);
		$responses["CODE"] = 0;
		$responses["DATA"] = $response;
		return $responses;
	}
	
	public static function serializeData($data){
		foreach($data as $key=>$value){
			$data[$key] = urlencode($value);
		}
		return urldecode(json_encode($data));
	}
	
	public static function handleException($e){
		$responses = array();
		$responses["CODE"] = $e->getCode();
		$responses["MESSAGE"] = $e->getMessage();
		return $responses;
	}
	
}
?>