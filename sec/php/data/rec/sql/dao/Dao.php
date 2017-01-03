<?php
require_once 'config/MyEnv.php';
require_once 'php/data/rec/sql/dao/Logger.php';
require_once 'php/dao/_util.php';

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
  protected $globalConnection;
  
  static function query($sql, $db = null, $table = null) {
    if ($table == 'proc_results') Logger::debug('| We entered Dao::query with the query ' . $sql . '|  trace is ' . getStackTrace());
    
	//Logger::debug('Dao::query: Database has been opened. Running query ' . $sql);
	//Logger::debug('Dao::query: Users table detected Backtrace is ' . print_r(debug_backtrace(), true));
	
	if (MyEnv::$IS_ORACLE) {
		
		/*
		 * 
		 * If you want to use a persistent connection and not have to use a new oci_connect or mysql_connect call every
		 * time you want to do a query, define $GLOBALS['dbConn'] as the value you get from static::open.
		 * This will set an oci_connect or mysql_connect result to $GLOBALS['dbConn'] and you can use it for as long as 
		 * your script runs.
		 * 
		 * The catch: Make sure you define AND CLOSE the connection in your PHP script.
		 */
		
		//Logger::debug('Dao::query: Our DB connection is ' . gettype($GLOBALS['dbConn']) . ' ' . $GLOBALS['dbConn']);
		if (isset($GLOBALS['dbConn'])) {
			Logger::debug('We already have a connection!');
			$conn = $GLOBALS['dbConn'];
			
		}
		else {
			Logger::debug('No connection, make a new one!!!');
			$conn = static::open($db);
			//$GLOBALS['dbConn'] = $conn;
			//Logger::debug('We set the DB conn variable to ' . gettype($GLOBALS['dbConn']) . ' ' . $GLOBALS['dbConn']);
		}
		
		/*Logger::debug('Checking out the global connection....');
		
		//Logger::debug('GLOBALS:');
		//Logger::debug(print_r($GLOBALS, true));
			
		
		
		Logger::debug('Dao::query: The connection we will use is ' . gettype($conn));
		//Oracle does not like backticks, but SQL does. Substitute them for a single quote before doing the query.
		/*for($i = 0; $i < strlen($sql); $i++) {
			//Logger::debug('Is ' . $sql[$i] . ' a backtick?');
			if ($sql[$i] == '`') {
				//Logger::debug('YES, replace the backtick');
				$sql[$i] = '"';
			}
		}*/
		
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
		try {
			$res = oci_parse($conn, $sql);
			
			if (!$res) {
				throw new RuntimeException('Invalid oci_parse result.');
			}
		}
		catch (Exception $e) {
			
			ob_start();
			debug_print_backtrace();
			$trace = ob_get_contents();
			ob_end_clean();
	
			Logger::debug('ERROR in Dao::query oci_PARSE call: ' . $err['message'] . '. Query is ' . $sql . '. Trace = ' . $trace);
			if ($_POST['IS_BATCH'] == '1') echo 'ERROR in Dao::query oci_PARSE call: ' . $err['message'] . '. Query is ' . $sql; //When we echo something out in batch, we want to echo the error so it will be more noticeable. Sometimes the logs get WAY too big with debug code and it's good to have this query error immediately visible. This was mainly put here to help with debugging the patient batch import process.
		}
		
		/*if ($table == 'logins') {
			oci_bind_by_name($res, ':returnVal', $returnValue, 8, OCI_B_INT);
			//echo 'Dao::query: ReturnValue is ' . $returnValue;
			//return $returnValue;
			Logger::debug('Dao::query: Got return val as ' . $returnValue);
		}*/
		
		
		
		logit_r($sql, 'PARSED SQL');
		
		$returnValue = null;
		
		
		/*if ($table == 'clients') {
			Logger::debug('Dao::query: Clients table! Trace is ' . print_r(debug_backtrace(), true));
		}*/
		
		//print_r($res);
		Logger::debug('Dao.php: Res is ' . gettype($res) . ' ' . print_r($res, true));
		
		
		//echo '<hr>';
		//$ex = oci_execute($res); //If things break we may have to uncomment this....what we really want is to return the result of oci_parse and NOT actually run oci_execute. The reason for this is we may want to bind return variables with oci_bind_by_name and we have to do that BEFORE we run oci_execute.
		//logit_r($ex, 'Ex');
		//logit_r('executed');
		/*
		$rows = oci_fetch_all($res, $res2);
		logit_r('rows='.$rows);
		logit_r($res2, 'res2');
		*/
		
		try {
			$err = oci_error($res);
		}
		catch (Exception $e) {
			Logger::debug('ERROR in Dao::query oci_error call: ' . $err['message'] . '. Query is ' . $sql);
			echo 'ERROR in Dao::query oci_error call: ' . $err['message'] . '. Query is ' . $sql; //When we echo something out in batch it will be more noticeable. Sometimes the logs get WAY too big with debug code and it's good to have this query error immediately visible. This was mainly put here to help with debugging the patient batch import process.
		}
		
		if (!empty($err)) {
			Logger::debug('ERROR in Dao::query: ' . $err['message']);
			throw static::buildException(htmlentities('Dao::query: Error in Oracle query: ' . $err['message'] . '. Query is ' . $sql, ENT_QUOTES), E_USER_ERROR);
		}
		
		//if ($table == 'logins') return $returnValue;
	}
	else {
		Logger::debug('NOT ORACLE.');
		$conn = static::open($db);
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
	 //$closed = static::close($conn);
	 Logger::debug('Dao: Returning res which is a ' . gettype($res) . '.');
	 //echo 'Dao::query: Returning res  . '<br>';
     return $res;
  }
  
  
  static function close($conn) {
  	return close($conn);
  }
  /**
   * @param string $sql INSERT
   * @return int ID generated
   */
  static function insert($sql, $table = null) {
	if (MyEnv::$IS_ORACLE) {
		
		$stmt = static::query($sql, null, $table); //We want to return the inserted row ID after this gets returned.
		Logger::debug('Dao::query: Oracle stmt is ' . gettype($stmt));
		$insertId = -1; //We need to accept the fact that $insertId may be null - there are insert commands that purposefully insert null into an ID column for some reason.
		oci_bind_by_name($stmt, ':returnVal', $insertId, 8, OCI_B_INT);
		
		try {
			$result = oci_execute($stmt);
			if (!$result) {
				$err = oci_error($stmt);
				throw new RuntimeException('Could not insert a row. Query is ' . $sql . '. Error is ' . $err['message']);
			}
			
			if ($insertId == -1) {
				$err = oci_error($stmt);
				throw new RuntimeException('Could not insert a row - INSERTID lower than 1 [' . gettype($insertId) . ' ' . $insertId.  ']. Query is ' . $sql . '. OCI Error is ' . $err['message']);
			}
		}
		catch (Exception $e) {
			throw new RuntimeException('Error in Dao::insert: ' . $e->getMessage());
		}
	    //return integer ID
		
		//$rows = oci_fetch_all($stmt, $arr);
		
		//$id = $arr[0]['insert_id'];
		Logger::debug('Dao::query: Returning insert ID as ' . gettype($insertId) . ' ' . $insertId);
		return $insertId;
		//echo 'Dao::insert: stmt is a ' . gettype($stmt) . '<br>';
		
		//We may not need the below oci_fetch_array? This is an insert statement, we shouldn't need an array returned.....
		/*if ($table == 'logins') {
			//echo 'Dao::insert: The sql table is logins. The stmt is ' . gettype($stmt) .  ' ' . $stmt;
		}
		else {
			$array = oci_fetch_array($stmt, OCI_BOTH);
			//echo 'Dao::insert: Got array :<pre>' . print_r($array, true) . '</pre><br><br>---------<br><br>';
		}*/
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
  
  
  static function fetchRowFromResource($res) {
	if (MyEnv::$IS_ORACLE) {
		$row = oci_fetch_assoc($res);
	}
	else {
		$row = mysqli_fetch_array($res, MYSQLI_ASSOC);
	}
	
	return $row;
  }
  
  /**
   * Fetch single row
   * @param string $sql SELECT
   * @return array('col_name'=>value,..)
   */
  static function fetchRow($sql, $db = null) {
    $res = static::query($sql, $db);
	return fetchRowFromResource($res);
  }
  /**
   * Fetch multiple rows
   * @param string $sql SELECT
   * @param string $db (optional) @see open()
   * @return array(array('col_name'=>value,..),..) 
   */
  static function fetchRows($sql, $db = null) {
    $res = static::query($sql, $db);
	
	
	Logger::debug('Dao::fetchRows');
	
	if (MyEnv::$IS_ORACLE) {
		/*if ($table == 'logins') {
			oci_bind_by_name($res, ':returnVal', $returnValue, 8, OCI_B_INT);
			//echo 'Dao::query: ReturnValue is ' . $returnValue;
			//return $returnValue;
			Logger::debug('Dao::query: Got return val as ' . $returnValue);
		}*/
		
		//oci_bind_by_name($res, ':returnVal', $returnValue, 8, OCI_B_INT);
		try {
			$result = oci_execute($res);
			
			if (!$result) {
				$err = oci_error($res);
				throw new RuntimeException($err['message'] . '. Query is ' . $sql);
			}
		}
		catch(Exception $e) {
			Logger::debug('ERROR in Dao::fetchRows: ' . $e->getMessage());
			return null;
		}
		$rows = array();
		//logit_r('looping thru orc rows');
		while (($row = oci_fetch_array($res, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
			//logit_r($row, 'orc row');
			//array_push($rows, row);
			$rows[] = $row;
		}
		
		//Logger::debug('Dao::fetchRows: Got row array ' . print_r($rows, true));
		
	}
    else {
		$rows = array();
		while ($row = mysqli_fetch_array($res, MYSQL_ASSOC)) {
			$rows[] = $row; 
		}
    }
    //logit_r($rows, 'rows');
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
    $res = static::query($sql); //Should return whatever oci_parse returns.
	if (MyEnv::$IS_ORACLE) {
	    try {
			$result = oci_execute($res);
			
			if (!$result) {
				$err = oci_error($res);
				throw new RuntimeException($err['message'] . '. Query is ' . $sql);
			}
		}
		catch(Exception $e) {
			Logger::debug('ERROR in Dao::fetchValue: ' . $e->getMessage());
			return null;
		}
		$row = oci_fetch_array($res, OCI_BOTH);
		//print_r($row);
		//Logger::debug('Dao::fetchValue: Got row ' . print_r($row, true) . '. Col is ' . $col);
		$val = $row[$col];
	}
	else {
		$row = mysql_fetch_array($res, MYSQL_BOTH);
		$val = $row[$col];
	}
	//echo 'fetchValue: Returning ' . $val;
	return $val;
    /*oci_execute();
	while (($row = oci_fetch_array($res, OCI_BOTH)) != false) {
		//echo 'Looping. Got ' . $row[$col] . '. print_rred:';
		//print_r($row);
	}
	return;*/
  //}
   /* $res = static::query($sql); //Should return whatever oci_execute returns.
	echo 'res is a ' . gettype($res);
	$rows = oci_fetch_all($res, $r);
	echo 'in our res we have ' . $rows . ' rows.';
	*/
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