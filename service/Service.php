<?php
date_default_timezone_set('prc');
class Service{
	public static function executeService($serviceName,$methodName,$parameter){
		require_once $serviceName.".php";
		$service = new $serviceName();
		$response = $service->$methodName($parameter);
		return $response;
	}
}