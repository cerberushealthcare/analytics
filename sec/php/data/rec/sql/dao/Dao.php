<?php
require_once 'config/MyEnv.php';
require_once 'php/data/rec/sql/dao/Logger.php';

//
/**
 * MySql Data Access Object
 * @author Warren Hornsby
 */
class Dao {
  //
  /**
   * Execute query 
   * @param string $sql
   * @param string $db (optional) @see open()
   * @return MySqlResource
   */
  static function query($sql, $db = null, $table = null) {
    //Logger::debug('| We entered Dao::query with the query ' . $sql . '|');
    $conn = static::open($db);
	Logger::debug('Dao::query: Database has been opened. Running query ' . $sql);
	if (MyEnv::$IS_ORACLE) {
		
		//Oracle does not like backticks, but SQL does. Substitute them for a single quote before doing the query.
		/*for($i = 0; $i < strlen($sql); $i++) {
			//Logger::debug('Is ' . $sql[$i] . ' a backtick?');
			if ($sql[$i] == '`') {
				//Logger::debug('YES, replace the backtick');
				$sql[$i] = '"';
			}
		}*/
			
		//Logger::debug('Non-backticked query: ' . $sql);
		
		//Take out all the quotes after the from clause. In Oracle, quotes are okay in the select area (aliases) but Oracle doesn't want any quotes
		//anywhere else.
		
		$fromPos = strpos($sql, 'FROM ');
		//echo 'Doing from loop for query <b>' . $sql . '</b>. fromPos is ' . gettype($fromPos) . ' ' . $fromPos;
		//var_dump(debug_backtrace());
		if ($fromPos < strlen($sql) && is_numeric($fromPos)) {
			for ($i = $fromPos; $i < strlen($sql); $i++) {
				//Logger::debug('Is ' . $sql[$i] . ' a quote?');
				if ($sql[$i] == '"') {
					//Logger::debug('YES, remove the quote');
					$sql[$i] = ' ';
				}
			}
		}
		else {
			//var_dump(debug_backtrace());
		}
		
		//echo 'Dao::query: Done with fromPos business.<br>';
		$sql = str_replace('. ', '.', $sql);
		
		//
		
		//echo 'Dao: Getting resource. SQL is ' . $sql;
		
		//echo 'Dao::query:';
		$res = oci_parse($conn, $sql);
		$returnValue = null;
		
		if ($table == 'logins') {
			oci_bind_by_name($res, ':returnVal', $returnValue, 8, OCI_B_INT);
			//echo 'Dao::query: ReturnValue is ' . $returnValue;
			//return $returnValue;
			Logger::debug('Dao::query: Got return val as ' . $returnValue);
		}
		
		if ($table == 'scan_files') {
			Logger::debug('Scan files! trace is ' . print_r(debug_backtrace(), true));
		}
		
		//print_r($res);
		//echo 'Res is ' . gettype($res) . ' ' . $res;
		//Logger::debug('Dao::query: Backtrace is ' . print_r(debug_backtrace(), true));
		/*echo '<pre>';
		var_dump(debug_backtrace());
		echo '</pre>';*/
		//echo '<hr>';
		oci_execute($res);
		//echo '<br>Got resource.';
		$err = oci_error($res);
		if (!empty($err)) {
			Logger::debug('ERROR in Dao::query: ' . $err['message']);
			throw static::buildException(htmlentities('Dao::query: Error in Oracle query: ' . $err['message'] . '. Query is ' . $sql . '. Stack trace is ' . print_r(debug_backtrace(), true), ENT_QUOTES), E_USER_ERROR);
		}
		
		if ($table == 'logins') return $returnValue;
	}
	else {
		Logger::debug('NOT ORACLE.');
		$res = mysqli_query($conn, $sql); 
	}
	
	Logger::debug('res is ' . gettype($res));
    
    if (!$res) {
		if (MyEnv::$IS_ORACLE) {
			$err = oci_error();
			print_r($err);
			throw static::buildException(htmlentities('Dao::query: No resource! Got message ' . $err['message'], ENT_QUOTES), E_USER_ERROR);
		}
		else {
			throw static::buildException(mysql_error(), mysql_errno($conn));
		}
	 }
	Logger::debug('Dao: Returning res which is a ' . gettype($res));
	//echo 'Dao::query: Returning res  . '<br>';
    return $res;
	
  }
  /**
   * @param string $sql INSERT
   * @return int ID generated
   */
  static function insert($sql, $table = null) {
	if (MyEnv::$IS_ORACLE) {
		//echo 'Dao::insert: Got query ' . $sql . '<br>';
		
		$stmt = static::query($sql, null, $table);
		
		//echo 'Dao::insert: stmt is a ' . gettype($stmt) . '<br>';
		
		if ($table == 'logins') {
			//echo 'Dao::insert: The sql table is logins. The stmt is ' . gettype($stmt) .  ' ' . $stmt;
		}
		else {
			$array = oci_fetch_array($stmt, OCI_BOTH);
			//echo 'Dao::insert: Got array :<pre>' . print_r($array, true) . '</pre><br><br>---------<br><br>';
		}
	}
	else {
		static::query($sql);
		return mysqli_insert_id();
	}
  }
  /**
   * @param string $sql UPDATE/DELETE/INSERT ON DUPLICATE
   * @return int number of affected rows (for INSERT/DELETE)
   * @return int 1=was inserted, 2=was updated (for INSERT ON DUPLICATE)
   */
  static function update($sql) {
	if (MyEnv::$IS_ORACLE) {
		$res = static::query($sql);
		return oci_num_rows($res);
	}
	else {
		$res = static::query($sql);
		return mysqli_affected_rows($res);
	}
  }
  /**
   * Fetch single row
   * @param string $sql SELECT
   * @return array('col_name'=>value,..)
   */
  static function fetchRow($sql, $db = null) {
    $res = static::query($sql, $db);
	
	if (MyEnv::$IS_ORACLE) {
		$row = oci_fetch_assoc($res);
	}
	else {
		$row = mysqli_fetch_array($res, MYSQLI_ASSOC);
	}
    return $row;
  }
  /**
   * Fetch multiple rows
   * @param string $sql SELECT
   * @param string $db (optional) @see open()
   * @return array(array('col_name'=>value,..),..) 
   */
  static function fetchRows($sql, $db = null) {
    $res = static::query($sql, $db);
	Logger::debug('Dao::fetchRows: Got SQL ' . $sql);
	
	if (MyEnv::$IS_ORACLE) {
		$rows = array();
		while (($row = oci_fetch_array($res, OCI_BOTH)) != false) {
			$rows [] = $row;
		}
	}
    else {
		$rows = array();
		while ($row = mysqli_fetch_array($res, MYSQL_ASSOC)) {
			$rows[] = $row; 
		}
    }
    return $rows;  
  }
  /**
   * Fetch column value of single row
   * @param string $sql SELECT
   * @param string $col 'col_name' (optional, defaults to first column)
   * @return value 
   */
  static function fetchValue($sql, $col = 0) {
	//echo 'fetchValue with col ' . $col;
   $res = static::query($sql); //Should return whatever oci_execute returns.
	while (($row = oci_fetch_array($res, OCI_BOTH)) != false) {
		//echo 'Looping. Got ' . $row[$col] . '. print_rred:';
		//print_r($row);
	}
	return;
  //}
   /* $res = static::query($sql); //Should return whatever oci_execute returns.
	echo 'res is a ' . gettype($res);
	$rows = oci_fetch_all($res, $r);
	echo 'in our res we have ' . $rows . ' rows.';
	if (MyEnv::$IS_ORACLE) {
		echo 'fetchValue: row is this: ';
		$row = oci_fetch_array($res, OCI_BOTH);
		print_r($row);
		$val = $row[$col];
	}
	else {
		$row = mysql_fetch_array($res, MYSQL_BOTH);
		$val = $row[$col];
	}
	echo 'fetchValue: Returning ' . $val;
	return $val;*/
  }
  /** 
   * Fetch column value across multiple rows 
   * @param string $sql SELECT
   * @param string $col 'col_name' (optional, defaults to first column)
   * @return array(value,..)
   */
  static function fetchValues($sql, $col = 0) {
    $res = static::query($sql);
	$values = array();
	
	if (MyEnv::$IS_ORACLE) {
		while (($row = oci_fetch_array($res, OCI_BOTH)) != false) {
			$values[] = $row[strtoupper($col)];
		}
	}
	else {
		while ($row = mysqli_fetch_array($res, MYSQL_BOTH))
		  $values[] = $row[$col];
	}
	
    return $values;  
  } 
  /**
   * Open connection
   * @param string $db (optional, supply for batch if needed)
   * @return MySqlConnection
   */
  static function open($db = null) {
    if ($db == null)
      $db = MyEnv::$DB_NAME;
  
	if (MyEnv::$IS_ORACLE) {
		Logger::debug('Opening oracle....');
		$conn = openOracle();
	}
	else {
		try {
			$conn = mysqli_connect(MyEnv::$DB_SERVER, MyEnv::$DB_USER, MyEnv::$DB_PW, $db);
		}
		catch (Exception $e) {
			throw new RuntimeException('Could not open the MySQLi database: ' . $e->getMessage());
		}
	}
	//echo 'Dao open: Made a connection.';
    return $conn;
  }
  
  /**
   * Begin transaction
   */
  static function begin() {
    $tx = static::getTransaction();
    $tx->begin();
  }
  /**
   * Commit transaction
   */
  static function commit() {
    $tx = static::getTransaction();
    $tx->commit();
  }
  /**
   * Rollback transaction
   */
  static function rollback() {
    $tx = static::getTransaction();
    $tx->rollback();
  }
  //
  protected static function buildException($msg, $code) {
    switch ($code) {
      case 1062:
        return new DupeInsertException($msg, $code);
      default:
        return new SqlException($msg, $code);
    }
  }
  protected static function getTransaction() {
    static $tx;
    if ($tx == null) 
      $tx = new DaoTransaction();
    return $tx;
  }
}
//
class DaoTransaction {
  //
  protected $level;
  protected $rollingBack;
  //
  public function begin() {
    logit_r($this->level, 'level @ begin');
    if ($this->level == 0)
      $this->sql_begin();
    $this->level++;
  }
  public function rollback() {
    logit_r($this->level, 'level @ rollback');
    if ($this->level == 1) 
      $this->sql_rollback();
    else   
      $this->rollingBack = true;
    $this->level--;
  }
  public function commit() {
    logit_r($this->level, 'level @ commit');
    if ($this->level == 1) { 
      if ($this->rollingBack)
        $this->sql_rollback();
      else
        $this->sql_commit();
    }
    $this->level--;
  }
  //
  protected function sql_begin() {
    Dao::query('BEGIN');
  }
  protected function sql_commit() {
    Dao::query('COMMIT');
  }
  protected function sql_rollback() {
    Dao::query('ROLLBACK');
    $this->rollingBack = false;
  }
}
/* Exceptions */
class DupeInsertException extends Exception implements UserFriendly {}