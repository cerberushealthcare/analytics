<?php
require_once 'php/data/rec/sql/_SqlRec.php';
/**
 * Data Record Base Classes
 * @author Warren Hornsby 
 */
/**
 * Data Record
 */
abstract class DataRec extends SqlRec {
  /**
   * @see SqlRec::__clone()
   */
  public function __clone() {
    parent::__clone();
    $this->sessionId = null;
    $this->active = null;
    $this->dateUpdated = null;
  }
  /**
   * Override with natural key for associating facesheet recs
   * @return string 'agent' 
   */
  public function getKey() {
    return null;
  }
  /**
   * @return string 'value'
   */
  public function getKeyValue() {
    $key = $this->getKey();
    if ($key) 
      return $this->$key;
  }
  /**
   * @param DataRec $rec
   * @return bool
   */
  public function isNewerThan($rec) {
    return (compareDates($this->dateUpdated, $rec->dateUpdated) > 0);
  }
  //
  /**
   * Copy non-null values from one rec to another 
   * e.g. refresh face rec with values from a newer session rec  
   */
  protected static function _copyNonNullValues($to, $from) {
    foreach ($from as $fid => $value) 
      if ($value !== null) 
        $to->$fid = $value;
  }
  /**
   * Reset active flag and save
   */
  protected static function _deactivate($face) {
    $face->active = false;
    $face->save();
  }
  /**
   * Build a new active face rec from a session rec
   */
  protected static function _faceFromSession($sess, $class) {
    $face = new $class();
    $face->date = nowNoQuotes();
    $face->active = true;
    $face->setFromSession($sess);
    return $face;
  }
  /**
   * Build a 'face audit record' (SID=0)
   */
  protected static function _auditFromFace($face, $class) {
    $rec = new $class($face);
    $rec->setPkValue(null);
    $rec->sessionId = '0';
    $rec->active = false;
    $rec->date = nowShortNoQuotes();
    return $rec;
  }
  /**
	 * Fetch existing face rec
   */
  protected static function _fetchFace($id, $class) {
    $c = self::_asFaceCriteria(null, $class);
    $c->setPkValue($id);
    return self::fetchOneBy($c); 
  }
  /**
   * Fetch active and inactive face records for client
   * @return array(key=>DataRec,..) @see getKey()  
   */
  protected static function _fetchFaces($cid, $class) {
    $c = self::_asFaceCriteria($cid, $class);
    return self::fetchMapBy($c, $c->getKey());
  }
  /**
   * Fetch active face records for client
   */
  protected static function _fetchActiveFaces($cid, $class) {
    $c = self::_asFaceCriteria($cid, $class);
    $c->active = true;
    return self::fetchAllBy($c);
  }
  /**
   * Fetch existing active face rec with key matching that of supplied rec
   * @return DataRec (if one exists; may be the same as that supplied)
   */
  protected static function _fetchActiveKeyMatch($rec) {
    $key = $rec->getKey();
    $keyValue = $rec->$key;
    $c = self::_asFaceCriteria($rec->clientId, get_class($rec));
    $c->active = true;
    $c->$key = $keyValue;
    return end(self::fetchAllBy($c));
  }
  /**
   * Fetch all session records for client
   */
  protected static function _fetchAllSess($cid, $class) {
    $c = self::_asSessCriteria($cid, $class);
    return self::fetchAllBy($c);
  }
	/**
   * Mark unbuilt sessions as built 
   * @param DataRec $rec last session record (e.g. highest PK) @see fetchUnbuiltFor()
   */
  protected static function _markAsBuilt($rec) {
    $cid = $rec->clientId;
    $id = $rec->getPkValue();
    $pk = $rec->getPkField();
    $table = $rec->getSqlTable();
    query("UPDATE $table SET active=0 WHERE client_id=$cid AND $pk<=$id AND session_id>0 AND active IS NULL");
  }
  /**
   * Fetch unprocessed session records (active=null) for building facesheet recs 
   */
  protected static function _fetchAllUnbuilt($cid, $class) {
    $c = new $class();
    $c->clientId = $cid;
    $c->sessionId = CriteriaValue::greaterThan('0');
    $c->active = CriteriaValue::isNull();
    return self::fetchAllBy($c);
  }
  //
  protected static function _asFaceCriteria($cid, $class) {
    $c = new $class();
    $c->clientId = $cid;
    $c->sessionId = CriteriaValue::isNull();
    return $c;
  }
  protected static function _asSessCriteria($cid, $class) {
    $c = new $class();
    $c->clientId = $cid;
    $c->sessionId = CriteriaValue::isNotNull();
    return $c;
  }
}
