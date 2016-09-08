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
    Logger::debug($sql);
    $conn = static::open($db);
	
	if (MyEnv::$IS_ORACLE) {
		//Oracle does not like backticks, but SQL does. Substitute them for a single quote before doing the query.
		$sql = str_replace('`', '"', $sql);
		$stid = oci_parse($conn, $sql);
		$res = oci_execute($stid);
	}
	else {
		$res = mysql_query($sql); 
	}
    
    if (! $res) {
		if (MyEnv::$IS_ORACLE) {
			$err = oci_error();
			throw static::buildException(htmlentities($err['message'], ENT_QUOTES), E_USER_ERROR);
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
    static::query($sql);
    return mysql_insert_id();
  }
  /**
   * @param string $sql UPDATE/DELETE/INSERT ON DUPLICATE
   * @return int number of affected rows (for INSERT/DELETE)
   * @return int 1=was inserted, 2=was updated (for INSERT ON DUPLICATE)
   */
  static function update($sql) {
    static::query($sql);
    return mysql_affected_rows();
  }
  /**
   * Fetch single row
   * @param string $sql SELECT
   * @return array('col_name'=>value,..)
   */
  static function fetchRow($sql, $db = null) {
    $res = static::query($sql, $db);
    return mysql_fetch_array($res, MYSQL_ASSOC);
  }
  /**
   * Fetch multiple rows
   * @param string $sql SELECT
   * @param string $db (optional) @see open()
   * @return array(array('col_name'=>value,..),..) 
   */
  static function fetchRows($sql, $db = null) {
    $res = static::query($sql, $db);
    $rows = array();
    while ($row = mysql_fetch_array($res, MYSQL_ASSOC))
      $rows[] = $row;  
    return $rows;  
  }
  /**
   * Fetch column value of single row
   * @param string $sql SELECT
   * @param string $col 'col_name' (optional, defaults to first column)
   * @return value 
   */
  static function fetchValue($sql, $col = 0) {
    $res = static::query($sql);
    $row = mysql_fetch_array($res, MYSQL_BOTH);
    return $row[$col];
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
    while ($row = mysql_fetch_array($res, MYSQL_BOTH))
      $values[] = $row[$col];
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