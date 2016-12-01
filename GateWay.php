<?php
require_once 'JsonHandler.php';
class gateWay{
	protected $acceptEncoding,$contentType,$rawInputdata,$contentEncoding;
	protected $rawOutputData;
	public function __construct($acceptEncoding,$contentType,$rawInputData,$contentEncoding){
		$this->acceptEncoding = $acceptEncoding;
		$this->contentType = $contentType;
		$this->rawInputdata = $rawInputData;
		$this->contentEncoding = $contentEncoding;
	}
	
	protected function predeserilizeRequest(){
		//echo $this->contentType;
		$contentType = explode(";", $this->contentType);
    	$trueContentType = $contentType[0];
    	//echo $trueContentType;
        if(strcmp($trueContentType, "application/json")!=0){
        	throw new Exception("ContentType Should Be application/json");
        }
        if(!strcmp($this->contentEncoding, "gzip")){
            $tempData=gzinflate(substr($this->rawInputData, 10, -8)); //这里是按照网上写的，但是。。。。为什么？
            if(!$tempData){
                throw new Exception('Failed To Decode GZip Data');
            }else{
                $this->rawInputData=$tempData;
            }
        }
	}
	
	public function processRequest(){
		try{
			$this->predeserilizeRequest();
			//echo $this->rawInputdata;
			$data = JsonHandler::deserializeRequest($this->rawInputdata);
			$this->rawOutputData = JsonHandler::handleRequest($data);
			//echo $this->rawOutputData;
			$this->rawOutputData = JsonHandler::serializeData($this->rawOutputData);
		}catch(Exception $e){
			$this->rawOutputData = JsonHandler::handleException($e);
		}
	}
	
	
	public function output(){
		//ob_clean();
		echo $this->rawOutputData;
	}
}
?>