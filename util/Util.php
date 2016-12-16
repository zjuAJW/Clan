<?php
class Util{
	public static function soldierPrice($CE){
		$price = ceil(100 * sqrt($CE));
		return $price;
	}
	
	public static function soldierIncome($time_dispatched,$employed_times,$price,$CE){
		$employed_income = $employed_times * $price * 0.7;
		$timeDiff = time() - strtotime($time_dispatched);
		$guard_income = 0.2 * sqrt($CE) * $timeDiff/60;
		return round($employed_income + $guard_income);
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