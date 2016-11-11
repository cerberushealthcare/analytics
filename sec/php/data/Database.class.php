<?php
//require_once($_SERVER['DOCUMENT_ROOT'] . "/cert/sec/config/config.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/cert/sec/config/MyEnv.php");
//include(dirname(__FILE__) . "/dir/script_name.php");
//ini_set('include_path', '/' . get_include_path() . 'config');
//require_once('config/MyEnv.php');
//include_once('MyEnv.php');

class Database {
	public $host;
	public $dbName;
	private $user;
	private $password;
	
	private $conn;
	
	public function getHost() {
		return $this->host;
	}
	
	public function setHost($val) {
		$this->host = $val;
	}
	
	public function getDBName() {
		return $this->dbName;
	}
	
	public function setDBName($val) {
		$this->dbName = $val;
	}
	
	public function getUser() {
		return $this->user;
	}
	
	public function setUser($val) {
		$this->user = $val;
	}
	
	public function getPassword() {
		return $this->password;
	}
	
	public function setPassword($val) {
		$this->password = $val;
	}
	
	public function __construct() {
		//echo 'Construct: DBName is ' . MyEnv::$DB_NAME . '<br>';
		$this->host = MyEnv::$DB_SERVER; //$CONFIG["$env"]['host'];
		$this->dbName = MyEnv::$DB_NAME; //$CONFIG["$env"]['dbName'];
		$this->user = MyEnv::$DB_USER; //$CONFIG["$env"]['user'];
		$this->password = MyEnv::$DB_PW; //$CONFIG["$env"]['password'];
	}
	
	public function getObject($dbType) {
		if ($dbType == 'oracle') {
			$obj = oci_connect($this->user, $this->password, $this->host . '/' . 'pdborcl'); //we should make the process name here dynamic later.
			if($obj)
				echo "Connection succeeded";
			else
			{
				echo "Connection failed";
				//$err = oci_error();
				trigger_error(htmlentities($err['message'], ENT_QUOTES), E_USER_ERROR);	
			}
		}
		else {
			$connStr = strtolower($dbType) . ':host=' . $this->host . ';dbname=' . $this->dbName;
			echo 'connStr is ' . $connStr . '<br>';
			
			try {
				$obj = new PDO($connStr, $this->user, $this->password);
				echo 'getObj: Created object!';
			}
			catch (PDOException $e) {
				echo 'Could not connect to the database and return a PDO object: ' . $e->getMessage();
				return false;
			}
		}
		
		return $obj;
	}
}
?>