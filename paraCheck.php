<?php
class ParaCheck{
	public static function check($para,$template){
		foreach($template as $p){
			if(!isset($para[$p])){
				throw new Exception("Syndax Error: Parameter ".$p."is missed");
			}
		}
		return true;
	}
}
?>