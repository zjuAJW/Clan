<?php
require_once dirname(dirname(__FILE__))."/constant/MysqlException.php";
class MysqlConnect{
	private $con;
	private static $instance;
	private function __construct(){
		require_once dirname(dirname(__FILE__	)).'/config/db_info.php';
		$this->con=mysqli_connect($DB_INFO["host"],$DB_INFO["username"],$DB_INFO["password"],$DB_INFO['database']);
		if(!$this->con){
			throw new MysqlException("Database connection Failed: " . $this->con->connect_error);
		}
		else{
			mysqli_query($this->con,"set names 'utf8' ");
			//echo "connect_established!<br>";
		}
	} 
	
	public static function getInstance(){
		if(!(self::$instance instanceof self)){
			self::$instance = new MysqlConnect();
		} 
		return self::$instance;
	}
	
	public function __clone(){
		echo "clone of MysqlConnect is not permitted";
	}
	
	public function query($sql){
		$result = mysqli_query($this->con, $sql);
		if($result === true || $result === false){
			//echo "true or false";
			return $result;
		}
		else{
			//echo "return rows";
			$row = array();
			$rows = array();
			while($row = $result->fetch_assoc()){
				$rows[] = $row;
			}
			return $rows;
		}
	}
}
?>