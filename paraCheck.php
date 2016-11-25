<?php
class ParaCheck{
	public static function check($parameter,$template){
		foreach($template as $p){
			if(!isset($parameter[$p])){
				throw new Exception("Syndax Error: Parameter ".$p." is missed");
			}
		}
		return true;
	}
}
?>