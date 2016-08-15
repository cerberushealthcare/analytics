<?php
require_once 'php/data/rec/sql/_SqlRec.php';
//
/**
 * @author Warren Hornsby
 */
abstract class Hdata extends SqlRec implements CompositePK, NoAudit {
  //
  public $source;
  public $data;
  //
  static $TABLE;
  static $TYPE;
  static $JOIN_SQL_ID;
  //
  const TABLE_CLIENT = 1;
  const TABLE_PROC = 2;
  const TABLE_VITALS = 3;
  const TABLE_MED = 4;
  const TABLE_DIAGNOSIS = 5;
  const TABLE_IMMUN = 6;
  const TABLE_SESSION = 7;
  const TABLE_SCHED = 8;
  //
  public function getSqlTable() {
    return 'hdata';
  }
  public function getPkFieldCount() {
    return 1;
  }
  public function toJsonObject(&$o) {
    $o = null;
  }
  public function setSource($ugid, $id) {
    $this->source = static::hash($ugid . static::$TABLE . static::$TYPE . $id);
    return $this;
  }
  public function setData($data) {
    $this->data = $data;
    return $this;
  }
  public function setValue($value) { /*set data by value*/
    return $this->setData(static::toInt($value));
  }
  public function setCriteriaValue($cv) {
    if (! empty($cv)) {
      if ($cv instanceof CriteriaValue)
        static::setCriteriaValueToInt($cv);
      else if ($cv instanceof CriteriaValues)
        foreach ($cv->values as $c)
          static::setCriteriaValueToInt($c);
      else if (is_scalar($cv))
        $cv = static::toInt($cv);
      $this->setData($cv);
    }
    return $this;
  }
  public function asJoin($ugid = null) {
    $jv = static::joinValue($ugid);
    return CriteriaJoin::requires($this, 'source')->on($jv);
  }
  public function asMultiJoin($ugids) {
    $jvs = array();
    foreach ($ugids as $ugid)
      $jvs[] = static::joinValue($ugid);
    $jv = "(" . implode(',', $jvs) . ")";
    return CriteriaJoin::requires($this, 'source')->on($jv, 'IN');
  }
  //
  static function create($ugid = null, $id = null, $value = null) {
    $me = new static();
    if ($ugid && $id)
      $me->setSource($ugid, $id);
    if ($value)
      $me->setValue($value);
    return $me;
  }
  static function join(/*CriteriaValue*/$cv = null) {
    return static::create()->setCriteriaValue($cv)->asJoin();
  }
  static function toInt($value) {
    return $value;
  }
  //
  protected static function hash($value) {
    return MyCrypt_Auto::hash($value);
  }
  protected static function dateToInt($value) {
    $int = strtotime($value);
    if ($int === false) 
      return $value; /*can't be converted; leave as-is, e.g. when a SQL clause is assigned*/
    else
      return $int;
  }
  protected static function joinValue($ugid = null) {
    if ($ugid == null) 
      $ugid = static::getUgid();
    $table = static::$TABLE;
    $type = static::$TYPE;
    $fid = static::$JOIN_SQL_ID;
    $hk = MyCrypt_Auto::getHashKey();
    return "SHA1(CONCAT('$ugid','$table','$type',{PARENT_ALIAS}.$fid,'$hk'))";
  }
  protected static function setCriteriaValueToInt($cv) {
    $cv->value = static::toInt($cv->value);
  }
  protected static function getUgid() {
    global $login;
    return $login->userGroupId;
  }
}
/** Clients */
class Hdata_Client extends Hdata {
  //
  static $TABLE = self::TABLE_CLIENT;
  static $JOIN_SQL_ID = 'client_id';
  //
  const TYPE_DOB = 1;
  const TYPE_NAME = 2;
}
class Hdata_ClientDob extends Hdata_Client {
  //
  static $TYPE = self::TYPE_DOB;
  //
  static function from(/*Patient*/$rec) {
    return static::create($rec->userGroupId, $rec->clientId, $rec->birth);
  }
  static function toInt($dob) {
    return static::dateToInt($dob);
  }
}
class Hdata_ClientName extends Hdata_Client {
  //
  static $TABLE = self::TABLE_CLIENT;
  static $TYPE = self::TYPE_NAME;
  //
  static function from(/*Patient*/$rec) {
    return static::create($rec->userGroupId, $rec->clientId, $rec->lastName);
  }
  static function toInt($name) {
    return static::nameToInt($name);
  }
  protected static function nameToInt($name) {
    $int = 0;
    if (! empty($name)) {
      $n1 = ord(strtoupper(substr($name, 0, 1)));
      $n2 = (strlen($name) > 1) ? ord(strtoupper(substr($name, 1, 1))) : 0;
      $int = $n1 * 128 + $n2;
    }
    return $int;
  }
}
/** Procedures */
class Hdata_Proc extends Hdata {
  //
  static $TABLE = self::TABLE_PROC;
  static $JOIN_SQL_ID = 'proc_id';
  //
  const TYPE_DATE = 1;
}
class Hdata_ProcDate extends Hdata_Proc {
  //
  static $TYPE = self::TYPE_DATE;
  //
  static function from(/*Proc*/$rec) {
    return static::create($rec->userGroupId, $rec->procId, $rec->date);
  }
  static function toInt($date) {
    return static::dateToInt($date);
  }
}
/** Other dates */
abstract class Hdata_Date extends Hdata {
  //
  static $TABLE;
  static $TYPE = self::TYPE_DATE;
  static $JOIN_SQL_ID;
  static $DATE_FID = 'date';
  //
  const TYPE_DATE = 1;
  //
  static function from($rec) {
    $fid = static::$DATE_FID;
    return static::create($rec->userGroupId, $rec->getPkValue(), $rec->$fid);
  }
  static function toInt($date) {
    return static::dateToInt($date);
  }
}
class Hdata_VitalsDate extends Hdata_Date {
  //
  static $TABLE = self::TABLE_VITALS;
  static $JOIN_SQL_ID = 'data_vitals_id';
}
class Hdata_MedDate extends Hdata_Date {
  //
  static $TABLE = self::TABLE_MED;
  static $JOIN_SQL_ID = 'data_med_id';
}
class Hdata_DiagnosisDate extends Hdata_Date {
  //
  static $TABLE = self::TABLE_DIAGNOSIS;
  static $JOIN_SQL_ID = 'data_diagnoses_id';
}
class Hdata_DiagnosisDateClosed extends Hdata_DiagnosisDate {
  //
  static $TYPE = self::TYPE_DATE_CLOSED;
  static $DATE_FID = 'dateClosed';
  //
  const TYPE_DATE_CLOSED = 2;
}
class Hdata_ImmunDate extends Hdata_Date {
  //
  static $TABLE = self::TABLE_IMMUN;
  static $JOIN_SQL_ID = 'data_immun_id';
  static $DATE_FID = 'dateGiven';
}
class Hdata_SessionDos extends Hdata_Date {
  //
  static $TABLE = self::TABLE_SESSION;
  static $JOIN_SQL_ID = 'session_id';
  static $DATE_FID = 'dateService';
}
class Hdata_SchedDate extends Hdata_Date {
  //
  static $TABLE = self::TABLE_SCHED;
  static $JOIN_SQL_ID = 'sched_id';
  static $DATE_FID = 'date';
}
