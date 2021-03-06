<?php
	//WHERE CLAUSE FIX version of the file.
require_once 'php/data/rec/_Rec.php';
require_once 'php/data/rec/sql/dao/Dao.php';
require_once 'php/dao/LoginDao.php';
require_once 'php/data/rec/cryptastic.php';
require_once 'php/data/rec/sql/dao/Logger.php';
//
/**
 * Marker Interfaces  
 */
interface ReadOnly {}        // Record is not persistable
interface CompositePk {}     // Record does not use single auto-inc PK
interface NoAudit {}         // Record should not self-audit updates
interface AdminOnly {}       // Only admins may update
interface NoAuthenticate {}  // No authentication done (use caution; to allow access prior to session establishment, for example)
// 
/**
 * Persistable Data Record (SQL table row) 
 * Formatting of class properties (fids):
 * - First n fids for column values: 'colName'
 * - Fids for joined rows: 'SqlRecName' or 'SqlRecName_FkFid' ('_FkFid' suffix not required if fk is same as SqlRec's pk)  
 * - Helper fids: '_helper' 
 * @author Warren Hornsby
 */
abstract class SqlRec extends Rec {
  //
  /**
   * @return string 'table_name'
   */
  public function getSqlTable() {
    return static::$SQL_TABLE;
  }
  /**
   * Assigns fields based upon args supplied:
   *   (value1,value2,..)      multiple args: each assigned in field definition order
   *   ([value,..])            single arg, array: values assigned in field definition order 
   *   ([sql_field=>value,..]) single arg, SQL array: assigned in field definition order 
   *   ({fid:value,..})        single arg, decoded JSON object: values mapped to fields
   */
  public function __construct() {
    $args = func_get_args();
    if (count($args) == 1) {
      if (is_assoc($args[0])) {  
        $this->__constructFromSqlArray($args[0]);
        $args = null;
      } else if (is_array($args[0])) {  
        parent::__constructFromArray($args[0]);
        $args = null;
      } else if (is_object($args[0])) { 
        parent::__constructFromObject($args[0]);
        $args = null;
      }
    }
    if ($args)  
      parent::__constructFromArray($args);
    if ($this instanceof NoAuthenticate)
      $this->_authenticated = true;
    if ($this instanceof AutoEncrypt)
      $this->decryptSqlValues();
  }
  public function __constructFromSqlArray($arr) {
    //logit_r($arr,'arr');
    $fids = $this->getFids();
    foreach ($arr as $rfid => $value) {
      if (is_array($value)) {
        //logit_r("set $rfid,$value", 'rfid,value');
        $this->set($rfid, $value);
      } else {
        $fid = current($fids);
        $this->set($fid, $value);
        next($fids);
      }
    }
  }
  public function _toJsonObject() {
    $obj = parent::_toJsonObject();
    unset($obj->_authenticated);
    return $obj;   
  }
  /**
   * Clone record (shallow) excluding PK 
   */
  public function __clone() {
    $fid = $this->getPkFid();
    if ($fid) 
      $this->$fid = null;
  }
  
  /*
	In our SQL queries, there are times where we'll have a column or table name that is valid in SQL but reserved in Oracle.
	This function will add a _ to the end of the word if we are dealing with Oracle so that the query can be built correctly.
	
	You should only need this function if the environment is Oracle.
	
	Words that are reserved in both SQL and Oracle (e.g., ADD, ALL, SELECT) are not included - they wouldn't be there anyway because SQL forbids them too.
	
	@param string $word
	
	returns string $word
  */
  public function convertReservedOracleWords($word) {
	$reservedWords = array('ACCESS', 'AUDIT', 'CHAR', 'CLUSTER', 'COMMENT', 'COMPRESS', 'CONNECT', 'DATE', 'DECIMAL', 'EXCLUSIVE', 'FLOAT', 'IDENTIFIED', 'IMMEDIATE', 'INCREMENT', 'INITIAL', 'INTEGER',  'LEVEL', 'LOCK', 'LONG', 'MAXEXTENTS', 'MINUS', 'MLSLABEL', 'MODE', 'MODIFY', 'NOAUDIT', 'NOCOMPRESS', 'NOWAIT', 'NUMBER', 'OFFLINE', 'ONLINE', 'PCTFREE', 'PRIOR', 'PRIVILEGES', 'RAW', 'RENAME', 'RESOURCE', 'ROW', 'ROWID', 'ROWNUM', 'ROWS', 'SESSION', 'SHARE', 'SIZE', 'SMALLINT', 'START', 'SUCCESSFUL', 'SYNONYM', 'SYSDATE', 'TRIGGER', 'UID', 'VALIDATE', 'VARCHAR', 'VARCHAR2', 'WHENEVER');
	
	$upper = strtoupper($word);
	
	foreach ($reservedWords as &$reservedWord) {
		if ($upper === $reservedWord) {
			$word = $word . '_';
			break;
		}
	}
	
	return $word;
  }
  /**
   * Assign field value according to type
   * @param string $fid
   * @param string $value: simple field assignment
   *        array $value: child Rec assignment from SQL array [sql_field=>value,..]
   *        object $value: child Rec assignment from decoded JSON object {fid:value,..}  
   */
  public function set($fid, $value) {
    //logit_r("$fid=$value", 'set');
    if ($value !== null) {
      if (is_array($value))
        $this->setSqlArray($fid, $value);
      else
        parent::set($fid, $value);
    }
    return $this;
  }
  public function setSqlArray($fid, $value) {
    //logit_r($value, 'setsqlarray');
    if (substr(key($value), 0, 1) == '@') {  /*indicates came from join (see unflatten)*/
      $field = key(current($value));
      if (strpos($field, '.') == false) {
        parent::set($fid, $value);
      } else {
        $class = self::getClassFromSqlField($field);
        $recs = array();
        foreach ($value as $v) 
          $recs[] = new $class($v);
        $this->$fid = $recs;
      }
    } else {
      if (current($value) != null) {
        $class = self::getClassFromSqlField(key($value));
        $this->$fid = new $class($value);
      }
    }
  }
  protected function getAuthenticator() {
    // override if not SqlAuthenticator  
  }
  /**
   * Authenticate record as fetch criteria
   * @throws SecurityException
   */
  public function authenticateAsCriteria() {
    if (isset($this->_authenticated))
      return;
    if (! $this->authenticatePk(true)) {
      $cid = get($this, 'clientId');
      if (is_scalar($cid)) 
        $this->authenticateClientId($cid, true);
      else 
        if ($this->hasUserGroupId()) 
          $this->authenticateUserGroupId(get($this, 'userGroupId'), true);
    }
    $this->_authenticated = true;
  }
  /**
   * Authenticate record on saves
   * Override if necessary to extend checks 
   * @param SaveMode $mode
   * @throws SecurityException
   */
  public function authenticate($mode = null) {
    if (isset($this->_authenticated))
      return;
    if ($mode != SaveModes::INSERT)
      $this->authenticatePk();
    if ($this->hasUserGroupId()) 
      $this->authenticateUserGroupId(get($this, 'userGroupId'));
    if ($this->hasClientId()) {
      // logit_r($this, 'auth clientId');
      $cid = get($this, 'clientId');
      if (is_scalar($cid))
        $this->authenticateClientId($cid);
    }
  }
  /**
   * Authenticate record PK value for fetch
   * Override necessary if no user group on table
   * @param bool $forReadOnly (optional)
   * @return true if PK set and authenticated 
   * @throws SecurityException
   */
  public function authenticatePk($forReadOnly = false) {
    if (isset($this->_authenticated))
      return;
    if (is_scalar($this->getPkValue())) {
      if ($this->hasUserGroupId()) {
        $this->authenticateUserGroupIdWithin($this->getSqlTable(), $this->getPkField(), $this->getPkValue(), $forReadOnly);
        return true;
      }        
    }
  }
  protected function authenticateUserGroupIdWithin($table, $pkCol, $pkVal, $forReadOnly = false) {
    $ugid = static::fetchUgidWithin($table, $pkCol, $pkVal);
    if ($ugid)
      $this->authenticateUserGroupId($ugid, $forReadOnly);    
  }
  protected function fetchUgidWithin($table, $col, $id) {
    require_once 'php/data/LoginSession.php';
    $key = "ugidv[$table,$id]";
    return SessionCache::getset($key, function() use ($table, $col, $id) {
      return SqlRec::_fetchUgidWithin($table, $col, $id);
    });
  }
  static function _fetchUgidWithin($table, $col, $id) {
    return Dao::fetchValue("SELECT user_group_id FROM $table WHERE $col='$id'");
  }
  protected function authenticateUserGroupId($ugid, $forReadOnly = false) {
    if ($ugid == null) 
      throw new SecurityException('User group ID required in ' . $this->getMyName());
    $class = $this->getAuthenticator();
    if ($class == null)
      SqlAuthenticator::authenticateUserGroupId($ugid);
    else 
      $class::authenticateUserGroupId($ugid);
  }
  protected function authenticateClientId($cid, $forReadOnly = false) {
    if ($cid == null) 
      throw new SecurityException('Client ID required in ' . $this->getMyName());
    $class = $this->getAuthenticator();
    if ($class == null) 
      SqlAuthenticator::authenticateClientId($cid);
    else 
      $class::authenticateClientId($cid);
  }
  protected function authenticateUserId($id, $forReadOnly = false) {
    $class = $this->getAuthenticator();
    if ($class == null)
      SqlAuthenticator::authenticateUserId($id);
    else 
      $class::authenticateUserId($id);
  }
  //
  public function validateThrow() {
    $rv = RecValidator::from($this);
    $this->validate($rv);
    $rv->validate();
  }
  /**
   * Override to provide validation
   * @param RecordValidator $rv 
   * @throws RecValidatorException
   */
  protected function validate(&$rv) {
    // e.g. $rv->requires('name');
  }
  /**
   * Persist record to database
   * @param int $ugid (optional)
   * @param int $mode SaveModes::X (optional, omit to let method determine insert/update based on PK)
   * @return SqlRec this record fetched after update (if auditing) 
   * @throws ReadOnlySaveException, SecurityException, RecValidatorException
   */
  public function save($ugid = null, $mode = null, $auditAction = null) {
    if ($this instanceof ReadOnly) 
      throw new ReadOnlySaveException($this);
    if ($this instanceof AdminOnly) { 
      global $login;
      if (! $login->admin)
        throw new SqlRecException($rec, 'Invalid operation');  
    }
    if ($ugid) 
      $this->userGroupId = $ugid;
    if ($mode != SaveModes::UPDATE_NO_VALIDATE)
      $this->validateThrow();
    if (isset($this->dateUpdated))
      $this->dateUpdated = nowNoQuotes();
    if ($mode == null) 
      if ($this instanceof CompositePk) 
        $mode = SaveModes::INSERT_ON_DUPE_UPDATE;
      else  
        $mode = ($this->getPkValue() == null) ? SaveModes::INSERT : SaveModes::UPDATE;
    $this->authenticate($mode);
    switch ($mode) {
      case SaveModes::INSERT:  
        $sql = $this->getSqlInsert();
        $id = Dao::insert($sql);
        //logit_r($id, 'Dao::insert');
        if ($this->getPkValue() == null && $this->getPkFieldCount() == 1) 
          $this->setPkValue($id);
        break;
      case SaveModes::UPDATE:
      case SaveModes::UPDATE_NO_VALIDATE:
        if ($this->shouldAudit()) 
          $before = AuditImage::from($this);
        $sql = $this->getSqlUpdate();
        Dao::query($sql);
        break;
      case SaveModes::INSERT_ON_DUPE_UPDATE:
        if ($this->shouldAudit())  
          $before = AuditImage::from($this);
        $sql = $this->getSqlInsertOnDupeUpdate();
        Dao::update($sql);
        break;
    }
    if ($this->shouldAudit()) {
      //logit_r('should audit');
      switch ($mode) {
        case SaveModes::INSERT:
          $rec = Auditing::logCreateRec($this);
          break;
        case SaveModes::UPDATE:
        case SaveModes::UPDATE_NO_VALIDATE:
          $rec = Auditing::logUpdateRec($this, $before, $auditAction);
          //logit_r($rec, 'after rec');
          break;
        case SaveModes::INSERT_ON_DUPE_UPDATE:
          $rec = Auditing::logDupeUpdateRec($this, $before);
          break;
      }
      return $rec;
    }
  }
  public function saveAsInsert($ugid = null) {
    $this->save($ugid, SaveModes::INSERT);
  }
  public function saveAsUpdate($ugid = null) {
    $this->save($ugid, SaveModes::UPDATE);
  }
  public function saveAsInsertOnDupeUpdate($ugid = null) {
    $this->save($ugid, SaveModes::INSERT_ON_DUPE_UPDATE);
  } 
  public function saveAsUpdateNoValidate($ugid = null) {
    $this->save($ugid, SaveModes::UPDATE_NO_VALIDATE);
  }
  public function saveAsAuditAction($action) {
    $this->save(null, null, $action);
  }
  protected function shouldAudit() {
    return ! ($this instanceof NoAudit || $this instanceof NoAuthenticate || $this instanceof AdminOnly || isset($this->_noAudit));
  }
  /**
   * @return string value of record PK for auditing
   * Must be overridden for CompositePk recs,  e.g. return "$this->clientId,$this->seq"; 
   */
  public function getAuditRecId() {
    if ($this instanceof CompositePk)
      return implode(',', $this->getPkValues()); 
    else
      return $this->getPkValue();
  }
  /**
   * @return string name of record for Auditing
   */
  public function getAuditRecName() {
    return $this->getMyName();
  }
  /**
   * @return string value of client ID for auditing, can be overridden 
   */
  public function getAuditClientId() {
    return get($this, 'clientId');
  }
  /**
   * @return string for saving audit label (if overridden)
   */
  public function getAuditLabel() {
    return null;
  }
  /**
   * @return SqlRec for auditing before/after image
   */
  public function fetchForAudit() {
    if ($this instanceof CompositePk)
      return self::fetch($this->getPkValues());
    else 
      return self::fetch($this->getPkValue());
  }
  /**
   * @return array(fid=>value,..) excluding any fields to omit from incremental change, e.g. dateUpdated
   */
  public function getAuditFields() {
    $fields = $this->getSqlFidValues();
    return $fields;
  } 
  /**
   * @return int
   * Should be overridden for CompositePk recs
   */
  public function getPkFieldCount() {
    return 1;
  }
  /**
   * @param mixed $value
   */
  public function setPkValue($value) {
    if (! $this instanceOf CompositePk) {
      $fid = $this->getPkFid();
      $this->$fid = $value;
    }
  }
  /**
   * @return string
   */
  public function getPkValue() {
    if ($this instanceof CompositePk) 
      return null;
    else 
      return $this->getFirstValue();
  }
  protected function getFirstValue() {
    reset($this);
    $value = current($this);
    if (is_scalar($value))
      return $value;
    if (is_object($value) && get_class($value) == 'CriteriaValue')
      if ($value->comparator == CriteriaValue::EQ || $value->comparator == CriteriaValue::EQN) 
        return $value->value;
  }
  /**
   * @return array(string,..)
   * Only applies to CompositePk recs
   */
  public function getPkValues() {
    reset($this);
    $values = array(current($this));
    for ($i = 1; $i < $this->getPkFieldCount(); $i++) 
      $values[] = next($this);
    return $values;
  }
  //
  protected function setParentage($children, $fid = null) {
    if ($children) {
      $fid = $fid ? $fid : $this->getMyName();
      foreach ($children as $child)
        $child->$fid = $this;
    }
  }
  /**
   * @return string SQL
   */
  public function getSqlInsert() {
    $table = $this->getSqlTable();
    $fields = implode(',', $this->getSqlFields());
    $values = implode(',', $this->getSqlValues());
    $sql = "INSERT INTO $table ($fields) VALUES($values)";
    return $sql;
  }
  /**
   * @return string SQL
   */
  public function getSqlUpdate() {
    $table = $this->getSqlTable();
    $fields = $this->getSqlFields();
    $values = $this->getSqlValues();  
    $pkField = array_shift($fields);
    $pkValue = array_shift($values);
    if ($pkValue == null) 
      throw new SqlRecException($this, 'Cannot update record without PK');
    $values = implode_with_keys(',', array_combine($fields, $values));
    $sql = "UPDATE $table SET $values WHERE $pkField=$pkValue"; 
    return $sql;
  }
  /**
   * @return string SQL
   */
  public function getSqlInsertOnDupeUpdate() {
    $table = $this->getSqlTable();
    $fields = $this->getSqlFields();
    $values = $this->getSqlValues();  
    $ifields = implode(',', $fields);
    $ivalues = implode(',', $values);
    $uvalues = array();
    foreach ($fields as $field) 
      $uvalues[$field] = "VALUES($field)";
    $values = implode_with_keys(',', array_combine($fields, $uvalues));
    $sql = "INSERT INTO $table ($ifields) VALUES($ivalues) ON DUPLICATE KEY UPDATE $values";
    return $sql;
  }
  /**
   * Build SQL DELETE
   * @return string SQL
   */
  public function getSqlDelete() {
    $table = $this->getSqlTable();
    $where = $this->getSqlWherePk();
    return "DELETE FROM $table WHERE $where"; 
  }
  protected function getSqlWherePk() {
    $fields = $this->getSqlFields();
    $values = $this->getPkValues();
    $wheres = array();
    foreach ($values as $value) {
      if ($value == null) 
        throw new SqlRecException($this, 'Null found in PK');
      $field = current($fields);
	  
	  
	  
	  
      $wheres[] = "$field='$value'";
      next($fields);
    }
    return implode(' AND ', $wheres);
  }
  /**
   * Build SQL SELECT clause using this record as criteria
   * @return string SQL
   * @throws InvalidCriteriaException
   */
  public function getSqlSelect($recs = null, $infos = null, $asCount = false, $sortBy = null, $groupBy = null) {
    //logit_r($infos, 'infos');
    if ($recs == null) {
      $ci = $this->getRecsFromCriteria();
      $recs = $ci['recs'];
      $infos = self::buildSqlSelectInfos($ci);
    } 
    if ($infos['where'][0] == null) {
      if (! $this instanceof NoAuthenticate && $this->hasUserGroupId())
        throw new InvalidCriteriaException($this, 'No user group set as criteria');
      else
        $infos['where'][0] = '1=1';
    }
    $pk = $infos['alias'][0] . '.' . $infos['pk'][0];
    $sorts = array();
    if (! empty($infos['pk'][0]))
      $sorts[] = $pk; 
    $cts = array();
    $parentAlias = $infos['alias'][0];
    $infos['table'][0] .= " $parentAlias";
    $groupByPk = null;
    //p_r($recs);
    $joins = array();
    for ($i = 1; $i < count($recs); $i++) { /*add joins*/
      $fid = $infos['fid'][$i];
      $join = $recs[$fid];
      if ($join) {
        $pfid = $infos['parent'][$i];
        $pix = $infos['pix'][$i];
        if ($pfid == null) {
          $parent = $this;
        } else {
          $parent = $recs[$pfid]->rec;
        }
        $parentAlias = $infos['alias'][$pix];
        $parentPkFid = $parent->getPkFid();
        $parentPk = $infos['pk'][$pix];
        $parentFkFid = $fid;
        $table = $infos['table'][$i];
        $alias = $infos['alias'][$i];
        $childPk = $infos['pk'][$i];
        $where = $infos['where'][$i];
		Logger::debug('_SqlRec: call calcSQL!');
        $join->calcSql($parent, $parentAlias, $parentPk, $parentPkFid, $parentFkFid, $table, $alias, $childPk, $where, $cts);
        if (! empty($cts) && $groupByPk == null)
          $groupByPk = "$parentAlias.$parentPk";
        $sql = array("S$i" => $join->sql, "E$i" => $join->on);
        if ($pix == 0) 
          $joins = $joins + $sql;
        else 
          $joins = array_insert($joins, $sql, key_offset("E$pix", $joins));
        //$infos['table'][$i] = $join->sql . $join->on;
        $infos['table'][$i] = null;
        $infos['where'][$i] = $join->where;
        if (! empty($childPk))
          $sorts[] = "$alias.$childPk";
      }
    }
    //p_r($infos);
    //p_r($sqljoins);
    $fields = ($asCount) ? 'COUNT(*)' : implode(', ', array_filter($infos['fields']));
    $table = self::implodeTables($infos);
    $where = self::combineWheres($infos);
    if (! empty($joins))
      $table .= ' ' . implode('', $joins);
    $sql = "SELECT $fields FROM $table WHERE $where";
    if (! empty($cts)) 
      $sql .= " GROUP BY $groupByPk HAVING " . implode(' AND ', $cts);
    else if ($groupBy !== null)
      $sql .= " GROUP BY $groupBy";
    if ($sortBy !== null)
      $sorts = array($sortBy);
    if (count($recs) >= 1 && ! $asCount && ! empty($sorts)) 
      $sql .= " ORDER BY " . implode(', ', $sorts);
    return $sql;
  }
  private function combineWheres($infos) { //By the time we hit this function, the actual "column.alias = value" is already established. $where will be something like Alias.col = val.
    //Logger::debug('combineWheres entered. setting wheres to ' . $infos['where']);
    $wheres = $infos['where'];
    $ands = array();
    foreach ($wheres as $where) {
	  Logger::debug('Looping through wheres. We got ' . $where . ' which is a ' . gettype($where));
      if ($where) {
        if (is_array($where)) {  // e.g. array('1'=>'where')
          $key = key($where);
		  //Logger::debug('Key is a ' . gettype($key));
		  //$key = $this->convertReservedOracleWords($key);
		  //Logger::debug('Converted the key name ' . $key);
          $where = current($where);
          $this->pushWhere($ands, $key, $where);
        } else {
          $ands[] = $where; 
        }
      }
    }
    foreach ($ands as &$sql) {
      if (is_array($sql))
        $sql = $this->joinWheres($sql);
    }
    return implode(' AND ', $ands);  
  }
  private function pushWhere(&$arr, $key, &$e) {
    if (is_array($e)) {
      if (! isset($arr[$key])) 
        $arr[$key] = array();
      push($arr[$key], key($e), current($e));
    } else {
      push($arr, $key, $e);
    }
  }
  private function joinWheres($arr) {
    foreach ($arr as &$where) {
      if (is_array($where))
        $where = '(' . implode(' AND ', $where) . ')';
    }
    $sql = '(' . implode(' OR ', $arr) . ')';
    return $sql;
  }
  private function implodeTables($infos) {  
    $tables = array_filter($infos['table']);
    $sql = array_shift($tables);
    //sort($tables); used to sort left joins to top
    return $sql . implode('', $tables);
  }
  /**
   * Build SQL WHERE clause using this record as criteria
   * @param(opt) string $tableAlias 
   * @return string 'field_name=field_value AND..'
   */
  protected function getSqlWhere($tableAlias = null) {
    $fields = $this->getSqlFields();
    if ($tableAlias == null) 
      $tableAlias = $this->getSqlTable();
    $values = array();
    $lfid = $this->getLastFid();
    foreach ($this as $fid => &$value) {
      if ($value !== null) {
        if (is_scalar($value)) 
          $value = CriteriaValue::equals($value);
	  
	    //Logger::debug('getSqlWhere: Got column name ' . current($fields));
		//current($fields) = $this->convertReservedOracleWords(current($fields));
        $field = $tableAlias . '.' . $this->convertReservedOracleWords(current($fields));
		Logger::debug('After converting we got field ' . gettype($field) . '. ' . $field . '. current fields is ' . current($fields));
        $values[$field] = $value;
      }
      if ($fid == $lfid)
        break;
      next($fields);        
    }
    $values = count($values) ? CriteriaValue::_toSql($values) : null;
    return $values;
  }
  /**
   * @return array(fid=>sql_field,..)
   */
  protected function getSqlFields() {
    static $fields;
    if ($fields === null) {  
      $fields = array();
      $fids = $this->getSqlFids();
      foreach ($fids as $fid) {
		/*if (MyEnv::$IS_ORACLE) {
			$fid = $this->convertReservedOracleWords($fid);
		}*/
		
        $fields[$fid] = self::camelToSql($fid);
		
		
	  }
    }
    return $fields;
  }
  /**
   * @return array(fid,..) 
   */
  protected function getSqlFids() {
    static $sfids;
    if ($sfids === null) {
      $sfids = array();
      $fids = $this->getFids();
      $lfid = $this->getLastFid();
      foreach ($fids as $fid) {
        $sfids[] = $fid;
        if ($fid == $lfid)
          break;
      }
    }
    return $sfids;
  }
  /**
   * @return array(fid=>value,..) 
   */
  protected function getSqlFidValues() {
    $fids = $this->getSqlFids();
    $fvs = array();
    foreach ($fids as $fid) 
      $fvs[$fid] = $this->$fid;
    return $fvs;
  }
  /**
   * @return string of last column fid
   */
  protected function getLastFid() {
    static $lfid;
    if ($lfid === null) {
      foreach ($this->getFids() as $fid) {  
        if (! self::isTableFid($fid))
          return $lfid;
        $lfid = $fid;
      }
    }
    return $lfid;
  }
  public function hasUserGroupId() {
    static $hasUgid;
    if ($hasUgid === null) 
      $hasUgid = array_key_exists('userGroupId', $this);
    return $hasUgid;
  } 
  protected function hasClientId() {  // has clientId as a FK
    if ($this->getPkFid() == 'clientId')
      return false;
    static $hasCid;
    if ($hasCid === null) 
      $hasCid = array_key_exists('clientId', $this); 
    return $hasCid;
  } 
  /**
   * @return int
   */
  protected function getSqlFieldCt() {
    static $ct;
    if ($ct === null) 
      $ct = count($this->getSqlFields());
    return $ct;
  }
  /**
   * @return string 'sql_field'
   */
  protected function getPkField() {
    static $field;
    if ($field === null) {
      if ($this instanceOf CompositePk) { 
        $field = null;
      } else {
        reset($this);
        $fid = key($this);
        $field = self::camelToSql($fid);
      }
    }
    return $field;
  }
  public function getJoinPkField() {
    $fid = $this->getJoinPkFid();
    if ($fid) 
      return self::camelToSql($fid);
  }
  /**
   * @return string 'sqlField'
   */
  protected function getPkFid() {
    static $fid;
    if ($fid === null) {
      if ($this instanceOf CompositePk) { 
        $fid = null;
      } else {
        reset($this);
        $fid = key($this);
      }
    }
    return $fid;
  }
  protected function getJoinPkFid() {
    return $this->getPkFid();
  }
  protected function getSqlValue($fid, $efids) {
    $value = $this->$fid;
    if (is_bool($value)) { 
      return ($value) ? 1 : 0;
    } else {
      if ($efids && in_array($fid, $efids))
        $value = MyCrypt_Auto::encrypt($value);
      return quote($value, true);
    }
  }
  protected function getSqlValues() {
    $efids = $this instanceof AutoEncrypt ? $this->getEncryptedFids() : null;
    $values = array();
    $lfid = $this->getLastFid();
    foreach ($this as $fid => &$value) {
      $values[] = $this->getSqlValue($fid, $efids);
      if ($fid == $lfid)
        break;
    }
    return $values;
  }
  protected function buildSqlSelectFields($recFid, $tableAlias) {  // $recFid passed for record 'child' fids of criteria, e.g. 'ScanFiles'
    $fields = $this->getSqlFields();
    $class = $this->getMyName();
    $class .= ".$tableAlias";
    //if ($recFid) 
      //$class .= ".$recFid";
    $i = 0;
    foreach ($this as $fid => &$value) {
      $field = geta($fields, $fid);
      if ($field) {
		if (MyEnv::$IS_ORACLE) {
			$field = $this->convertReservedOracleWords($field); //Look at a list of reserved words that Oracle has for field names and if this word is reserved, add a _ to the end.
		}
		
		//$as = "$class.$fid";
        $as = "$class.$i";
		
		//Oracle wants aliases to have quotes around them if the alias contains a period
		if (MyEnv::$IS_ORACLE) {
			$fields[$fid] = "$tableAlias.$field AS \"$as\"";
		}
		else {
			$fields[$fid] = "$tableAlias.$field AS `$as`";
		}
        $i++;
      } 
    }
    $fields = implode(', ', $fields);
    return $fields;
  }
  /**
   * @return array(  
   *   'recs'=>array(fid=>Rec,fid=>CriteriaJoin,..) // first element is self, rest are joins
   *   'ct'=>#)
   */
  public function getRecsFromCriteria($parentFid = null, &$recs = null, $parentIx = 0) {
    $arrays = array();
    if ($recs == null) 
      $recs = array($this->getMyName() => $this);
    foreach ($this as $fid => $value) {
      if (is_array($value))
        $value = new CriteriaJoin(current($value), CriteriaJoin::JOIN_TBD, CriteriaJoin::AS_ARRAY);
      else if ($value instanceof SqlRec)
        $value = new CriteriaJoin($value, CriteriaJoin::JOIN_TBD);
      if ($value instanceof CriteriaJoin) {
        $value->joinsIx = $parentIx;
        $value->joinsTo = $parentFid;
        $f = ($parentFid) ? $parentFid . '.' . $fid : $fid;
        $recs[$f] = $value;
        $value->rec->getRecsFromCriteria($f, $recs, count($recs) - 1);
      }
    }
    //p_r($recs, 'RESULT');
    return array(
      'recs' => $recs,
      'ct' => count($recs));
  }
  /**
   * Fetch by primary key
   * @param int|int[] PK value(s)
   * @return SqlRec  
   */
  static function fetch() {
    $values = array_flatten(func_get_args());
    $rec = new static($values);
    if (empty($values))
      throw new SqlRecException($rec, 'No fetch args supplied');
    return static::fetchOneBy($rec->setFetchCriteria(), 1);
  }
  /**
   * May be overridden to add necessary joins prior to fetch() 
   * @return SqlRec
   */
  public function setFetchCriteria() {
    return $this;
  }
  /**
   * Fetch records using supplied record as criteria
   * @param SqlRec $criteria
   * @param(opt) RecSort $order
   * @param(opt) int $limit 0=no limit
   * @param(opt) string $keyFid to return array(keyValue=>Rec,..)  
   * @param(opt) string $sortBy 'T1.date DESC, T0.client_id'  
   * @param(opt) int $page to set start record of limited set (e.g. page 2 of limit 30 = record 31); returns limit + 1 recs to indicate whether a subsequent page exists 
   * @return SqlRec[]
   */
  static function fetchAllBy($criteria, $order = null, $limit = 500, $keyFid = null, $sortBy = null, $page = null, $groupBy = null) {
    $a = static::fetchAllAndFlatten($criteria, $order, $limit, $keyFid, $sortBy, $page, $groupBy);
    return $a[0];
  }
  /**
   * @return array(SqlRec[], row[], limit) -- row array is raw output of SQL query (which flatten into SqlRec[])  
   */
  static function fetchAllAndFlatten($criteria, $order = null, $limit = 500, $keyFid = null, $sortBy = null, $page = null, $groupBy = null) {
    $criteria->authenticateAsCriteria();
    $class = $criteria->getMyName();
    $ci = $criteria->getRecsFromCriteria();
    $infos = self::buildSqlSelectInfos($ci);
    $sql = $criteria->getSqlSelect($ci['recs'], $infos, null, $sortBy, $groupBy);
    if ($limit > 0 && !MyEnv::$IS_ORACLE) {
      if ($page >= 1)
        $sql .= " LIMIT " . ($limit * ($page - 1)) . "," . ($limit + 1);
      else
        $sql .= " LIMIT $limit";
    }
    $rows = static::fetchRows($sql);
    $frows = ($ci['ct'] == 1) ? $rows : self::unflattenRows($rows, $infos, $ci['ct'], $criteria);
    $recs = $class::fromRows($frows, $keyFid);
    if ($order)
      Rec::sort($recs, $order, ($keyFid != null));
    return array($recs, $rows, $limit);
  }
  protected static function fetchRows($sql) {
    return Dao::fetchRows($sql);
  }
  protected static function fromSql($sql) {
    return static::fromRows(Dao::fetchRows($sql));
  }
  protected static function fromRows($rows, $keyFid = null) {
    $recs = array();
    foreach ($rows as &$row) {
      $rec = new static($row);
      if ($keyFid)
        $recs[$rec->$keyFid] = $rec;
      else
        $recs[] = $rec;
    }
    return $recs;
  }
  /**
   * Fetch supplied criteria(s) and merge result 
   */
  static function fetchMerge() {
    $crits = func_get_args();
    $recs = array();
    foreach ($crits as $crit) 
      $recs = array_merge($recs, static::fetchAllBy($crit));
    return $recs;
  }
  /**
   * Fetch and associate by key
   * @param SqlRec $criteria
   * @param string $keyFid;
   * @return array(keyValue=>Rec,..)
   */
  static function fetchMapBy($criteria, $keyFid = null, $limit = 0) {
    if ($keyFid == null)
      $keyFid = $criteria->getPkFid();
    return self::fetchAllBy($criteria, null, $limit, $keyFid);
  }
  /**
   * Return first result of self::fetchAllBy($rec)  
   * @param SqlRec $criteria
   * @param int $limit use 1 only if query does not contain any joins, e.g. fetch()
   * @return Rec
   */
  static function fetchOneBy($criteria, $limit = 500) {
    $recs = self::fetchAllBy($criteria, null, $limit);
    if (! empty($recs))
      return current($recs);
  }
  /**
   * Fetch count(*) using supplied record as criteria
   * @param SqlRec $criteria
   */
  static function count($criteria) {
    $criteria->authenticateAsCriteria();
    $ci = $criteria->getRecsFromCriteria();
    $infos = self::buildSqlSelectInfos($ci);
    $sql = $criteria->getSqlSelect($ci['recs'], $infos, true/*=asCount*/);
    return Dao::fetchValue($sql);
  }
  /**
   * Delete record from database
   * @param SqlRec $rec 
   * @throws ReadOnlyDeleteException, SecurityException
   */
  static function delete(&$rec) {
    if ($rec instanceof ReadOnly) 
      throw new ReadOnlyDeleteException($rec);
    $rec->authenticate(SaveModes::DELETE);
    if ($rec->shouldAudit())
      Auditing::logDeleteRec($rec, AuditImage::from($rec));
    Dao::query($rec->getSqlDelete());
    $rec = null;
  }
  static function saveAll($recs) {
    if ($recs)
      foreach ($recs as $rec)
        $rec->save();
  }
  static function insertAll($recs, $batch = 20) {  // for multi-inserts of same table
    $sqls = static::getSqlInserts($recs, $batch);
    foreach ($sqls as $sql)
      Dao::query($sql);
  }
  static function getSqlInserts($recs, $batch = 20) {
    if (empty($recs))
      return;
    $rec = current($recs);
    $table = $rec->getSqlTable();
    $fields = implode(',', $rec->getSqlFields());
    $values = array();
    foreach ($recs as $rec)
      $values[] = implode(',', $rec->getSqlValues());
    $values = array_chunk($values, $batch);
    $sqls = array();
    foreach ($values as $value)
      $sqls[] = "INSERT INTO $table ($fields) VALUES(" . implode('),(', $value) .');';
    return $sqls;
  }
  static function getSqlInsertOnDupeUpdates($recs, $batch = 20) {
    if (empty($recs))
      return;
    $rec = current($recs);
    $table = $rec->getSqlTable();
    $f = $rec->getSqlFields();
    $fields = implode(',', $f);
    $values = array();
    foreach ($recs as $rec)
      $values[] = implode(',', $rec->getSqlValues());
    $values = array_chunk($values, $batch);
    $uvalues = array();
    foreach ($f as $field) 
      $uvalues[$field] = "VALUES($field)";
    $uvalues = implode_with_keys(',', array_combine($f, $uvalues));
    $sqls = array();
    foreach ($values as $value)
      $sqls[] = "INSERT INTO $table ($fields) VALUES(" . implode('),(', $value) .')' . " ON DUPLICATE KEY UPDATE $uvalues;";
    return $sqls;
  }
  static function deleteAll($recs) {
    if ($recs)
      foreach ($recs as $rec)
        static::delete($rec);
  }
  static function asRequiredJoin($fid = null) {
    $c = static::asJoinCriteria();
    return CriteriaJoin::requires($c, $fid);
  }
  static function asOptionalJoin($fid = null) {
    $c = static::asJoinCriteria();
    return CriteriaJoin::optional($c, $fid);
  }
  protected static function asJoinCriteria() {
    return new static();
  }
  //
  protected static function buildSqlSelectInfos($ci) {
    $recs = $ci['recs'];
    $infos = array();
    $fix = 0;
    $ix = 0;
    foreach ($recs as $fid => $rec) {
      $info = self::buildSqlSelectInfo($ix, $fid, $rec);
      push($infos, 'alias', $info['alias']);
      push($infos, 'fields', $info['fields']);
      push($infos, 'fct', $info['fct']);
      push($infos, 'table', $info['table']);
      push($infos, 'pk', $info['pk']);
      push($infos, 'pkct', $info['pkct']);
      push($infos, 'where', $info['where']);
      push($infos, 'fix', $fix);
      push($infos, 'array', $info['array']);
      push($infos, 'fid', $fid);
      push($infos, 'parent', $info['parent']);
      push($infos, 'pix', $info['pix']);
      if ($info['array'])
        $infos['anyArray'] = true;
      $fix += $info['fct'];
      $ix++;
    }
    return $infos;
  }
  protected static function buildSqlSelectInfo($ix, $fid, $rec) {
    if ($rec == null) 
      return array(
        'fields' => null,
        'fieldas' => null,
        'fct' => 0,
        'table' => null,
        'alias' => null,
        'pk' => null,
        'pkct' => null,
        'where' => null,
        'array' => null,
        'parent' => null,
        'pix' => null);
    $tableAlias = "T$ix";
    if ($ix == 0) {
      $recFid = null;
      $isArray = false;
      $where = $rec->getSqlWhere($tableAlias);
      $parent = null;
      $pix = null;
    } else {
      $recFid = $fid;
      $join = $rec;
      $parent = $join->joinsTo;
      $pix = $join->joinsIx;
      $rec = $join->rec;
      $isArray = $join->as;
      if ($join->recs) 
        $where = self::getSqlWheres($tableAlias, $join, $rec);  // construct ORs from multi-record condition
      else  
        $where = $rec->getSqlWhere($tableAlias);  // one record condition
    }
    return array(
      'fields' => $rec->buildSqlSelectFields($fid, $tableAlias),
      'fct' => $rec->getSqlFieldCt(),
      'table' => $rec->getSqlTable(),
      'alias' => $tableAlias,
      'pk' => $rec->getPkField(),
      'pkct' => $rec->getPkFieldCount(),
    	'where' => $where,
      'array' => $isArray,
      'parent' => $parent,
      'pix' => $pix);
  }
  protected static function getSqlWheres($tableAlias, $join, $rec0) {
    $a = array();
    foreach ($join->recs as $rec) {
      if (! $rec instanceof SqlRec) 
        throw new SqlRecException($rec0, 'Non-SqlRec used inside CriteriaJoin array');
      $a[] = $rec->getSqlWhere($tableAlias);
    } 
    return '((' . implode(') OR (', $a) . '))';
  } 
  protected static function unflattenRows($rows, $infos, $recCt, $rec) {
    //logit_r($rows, 'rows');
    //logit_r($infos, 'infos');
    //logit_r($recCt, 'recCt');
    $urows = array();
    $urow = null;
    $lastPk = null;
    $anyArray = isset($infos['anyArray']);
    $rowct = 1;
    foreach ($rows as &$row) {
      $pk = $rec->getRowPk($row);
      //logit_r($pk, 'pk for unflatten');
      //logit_r("--- ROW " . $rowct++);
      if ($pk == $lastPk) {
        if ($anyArray) 
          $overlay = true;
        //else
          //throw new SqlRecException($rec, 'Multiple records returned from join, pk=' . $lastPk); 
      } else {
        if ($urow) 
          $urows[] = $urow;
        $overlay = false;
        $urow = array_slice($row, 0, $infos['fct'][0], true);
      }
      //logit_r($urow, 'urow, overlay=' . $overlay);
      for ($i = 1; $i < $recCt; $i++) {
        $key = $infos['fid'][$i];
        // logit_r($key, 'key');
        $reca = self::nullIfEmpty(array_slice($row, $infos['fix'][$i], $infos['fct'][$i]));
        // logit_r($reca, 'reca');
        if ($reca) {  /*assign pka=concat pk of joined row*/
          if ($infos['pkct'][$i] == 1)  
            $pka = "@" . current($reca); 
          else
            $pka = "@" . implode('|', array_slice($reca, 0, $infos['pkct'][$i]));
        } else {
          $pka = null;
        }
        // logit_r($pka, 'pka');
        if (strpos($key, '.') !== false) {  // e.g. 'Join0.Ipc'
          $jfids = explode('.', $key);
          // logit_r($jfids, 'jfids');
          $uu =& $urow;
          // logit_r($uu, 'uu0');
          for ($j = 0, $k = count($jfids) - 1; $j < $k; $j++) {
            $jfid = $jfids[$j];
            $uu =& $uu[$jfid];
            if ($uu !== null) {
              $lk = array_pop(array_keys($uu));  
              if (substr($lk, 0, 1) == '@')  // if an array, step into last element 
                $uu =& $uu[$lk];
            }
          }
          $key = $jfids[$j];
          // logit_r($uu, 'uu1');
          // logit_r($reca, 'reca for ' . $key);
          if (! empty($reca)) 
            if ($infos['array'][$i] && $pka)  
              $uu[$key][$pka] = $reca;
            else  
              $uu[$key] = $reca;
          // logit_r($uu, 'uu2');
        } else if ($overlay) {  // use last row to append this child array instance
          if ($infos['array'][$i] && $pka) {
            if (! isset($urow[$key][$pka]))  
              $urow[$key][$pka] = $reca;
          }
        } else {
          if ($infos['array'][$i] && $pka) {  
            if (! isset($urow[$key][$pka]))  
              $urow[$key][$pka] = $reca;
          } else {  
            $urow[$key] = $reca;
          }
        }
        // logit_r($urow, 'urow for iteration ' . $i);
      }
      // logit_r($urow, 'done with loop, urow');
      $lastPk = $pk;
    }
    if ($urow)
      $urows[] = $urow;
    // logit_r('--- END');
    // logit_r($urows, 'urows');
    return $urows;     
  }
  protected function getRowPk($row) {
    $ct = $this->getPkFieldCount();
    if ($ct == 1) 
      return current($row);
    else
      return join('|', array_slice($row, 0, $ct));
  }
  protected static function nullIfEmpty($a) {
    return (current($a) == null) ? null : $a;  // assumes first column of child row should always be populated
  }
  public static function camelToSql($str) {
    $str[0] = strtolower($str[0]);
    $func = create_function('$c', 'return "_" . strtolower($c[1]);');
    $field = preg_replace_callback('/([A-Z])/', $func, $str);
	
	if (MyEnv::$IS_ORACLE) {
		return "$field";
	}
    return "`$field`";
  }
  public static function sqlToCamel($str, $capitalizeFirstChar = false) {
    if (substr($str, 0, 1) == '`') 
      $str = substr($str, 1, -1);
    if ($capitalizeFirstChar) 
      $str[0] = strtoupper($str[0]);
    $func = create_function('$c', 'return strtoupper($c[1]);');
    return preg_replace_callback('/_([a-z])/', $func, $str);
  }
  protected static function getClassFromSqlField($field) {
    $a = explode('.', $field);
    return $a[0];
  }
  protected static function isTableFid($fid) {
    return parent::isScalarFid($fid);
  }
  protected function getEncryptedFids() {
    // e.g. return array('name','addr1','addr2','city','zip','phone','number'); 
  }
  protected function encryptSqlValues() {
    foreach ($this->getEncryptedFids() as $fid) 
      MyCrypt_Sql::encrypt($this, $fid);
  }
  protected function decryptSqlValues() {
    foreach ($this->getEncryptedFids() as $fid) 
      MyCrypt_Sql::decrypt($this, $fid);
  }
}
class SaveModes {
  const INSERT = 1;
  const UPDATE = 2;
  const INSERT_ON_DUPE_UPDATE = 3;
  const UPDATE_NO_VALIDATE = 4;
  const DELETE = 5; 
}
require_once 'php/data/rec/sql/_SqlRec_Criteria.php';
require_once 'php/data/rec/sql/_SqlRec_Join.php';
