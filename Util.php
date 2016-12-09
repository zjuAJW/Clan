<?php
class Util{
	public static function soldierPrice($CE){
		$price = ceil(100 * sqrt($CE));
		return $price;
	}
	
	public static function checkParameter($parameter,$template){
		foreach($template as $p){
			if(!isset($parameter[$p])){
				throw new Exception("Syndax Error: Parameter ".$p." is missed");
			}
		}
		return true;
	}
}
?>