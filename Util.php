<?php
class Util{
	public static function soldierPrice($CE){
		$price = ceil(100 * sqrt($CE));
		return $price;
	}
}
?>