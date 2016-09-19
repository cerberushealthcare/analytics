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
  static function query($sql, $db = null) {
	//echo $sql;
    Logger::debug($sql);
    $conn = static::open($db);
	if (MyEnv::$IS_ORACLE) {
		//Oracle does not like backticks, but SQL does. Substitute them for a single quote before doing the query.
		$sql = str_replace('`', '"', $sql);
		
		//echo 'Dao::query:';
		$res = oci_parse($conn, $sql);
		//print_r($res);
		//echo 'Res is ' . gettype($res) . ' ' . $res;
		oci_execute($res);
		
		$err = oci_error($res);
		if (!empty($err)) {
			throw static::buildException(htmlentities('Dao::query: Error in Oracle query: ' . $err['message'], ENT_QUOTES), E_USER_ERROR);
		}
	}
	else {
		$res = mysql_query($sql); 
	}
    
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
    return $res;
	
  }
  /**
   * @param string $sql INSERT
   * @return int ID generated
   */
  static function insert($sql) {
	if (MyEnv::$IS_ORACLE) {
		$stmt = static::query($sql);
		OCIBindByName($stmt, ":ID", $id, 32);
		return $id;
	}
	else {
		static::query($sql);
		return mysql_insert_id();
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
		static::query($sql);
		return mysql_affected_rows();
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
		$row = mysql_fetch_array($res, MYSQL_ASSOC);
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
	
	if (MyEnv::$IS_ORACLE) {
		$rows = array();
		while (($row = oci_fetch_array($res, OCI_BOTH)) != false) {
			$rows [] = $row;
		}
	}
    else {
		$rows = array();
		while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
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
	echo 'fetchValue with col ' . $col;
   $res = static::query($sql); //Should return whatever oci_execute returns.
	while (($row = oci_fetch_array($res, OCI_BOTH)) != false) {
		echo 'Looping. Got ' . $row[$col] . '. print_rred:';
		print_r($row);
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
		while ($row = mysql_fetch_array($res, MYSQL_BOTH))
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
		$conn = openOracle();
	}
	else {
		$conn = mysql_connect(MyEnv::$DB_SERVER, MyEnv::$DB_USER, MyEnv::$DB_PW)
		  or die(mysql_error());
		mysql_select_db($db) 
		  or die(mysql_error());
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