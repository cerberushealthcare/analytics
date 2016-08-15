<?php
require_once 'php/data/rec/sql/_SqlRec.php';
//
/**
 * Contextless data
 * @author Warren Hornsby
 */
interface HdDate {}

abstract class Hdata extends SqlRec implements CompositePK, NoAudit {
  //
  public $t/*type*/;
  public $i/*id*/;
  public $d/*data*/;
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
  const TABLE_TRACK = 9;
  //
  public function getSqlTable() {
    return 'hdata' . static::$TABLE;
  }
  public function getPkFieldCount() {
    return 2;
  }
  public function toJsonObject(&$o) {
    $o = null;
  }
  public function setType($ugid) {
    $this->t = $this->getType($ugid);
    return $this;
  }
  public function setSource($ugid, $id) {
    $this->setType($ugid);
    $this->i = $id;
    return $this;
  }
  public function setData($data) {
    $this->d = $data;
    return $this;
  }
  public function setValue($value) { /*set data by value*/
    return $this->setData(static::toInt($value));
  }
  public function setCriteriaValue($cv) {
    if (! empty($cv)) {
      if ($cv instanceof CriteriaValue) {
        if ($this instanceof HdDate && $cv->comparator == 'sw') {
          $value = static::toInt($cv->value);
          $cv = CriteriaValues::_and(CriteriaValue::greaterThanOrEqualsNumeric($value), CriteriaValue::lessThan($value + 86400));
        } else {
          static::setCriteriaValueToInt($cv);
        }
      } else if ($cv instanceof CriteriaValues) {
        foreach ($cv->values as $c)
          static::setCriteriaValueToInt($c);
      } else if (is_scalar($cv)) {
        $cv = static::toInt($cv);
      }
      $this->setData($cv);
    }
    return $this;
  }
  public function asJoin($ugid = null) {
    if ($ugid == null) 
      $ugid = static::getUgid();
    $this->setType($ugid);
    return CriteriaJoin::requires($this, 'i');
  }
  public function asMultiJoin($ugids) {
    $this->setType(CriteriaValue::in($this->getTypes($ugids)));
    return CriteriaJoin::requires($this, 'i');
  }
  protected function getType($ugid) {
    return static::hash($ugid . static::$TABLE . static::$TYPE);
  }
  protected function getTypes($ugids) {
    $a = array();
    foreach ($ugids as $ugid)
      $a[] = $this->getType($ugid);
    return $a;
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
class Hdata_ClientDob extends Hdata_Client implements HdDate {
  //
  static $TYPE = self::TYPE_DOB;
  //
  static function from(/*Patient*/$rec) {
    return static::create($rec->userGroupId, $rec->clientId, $rec->birth);
  }
  static function toInt($value) {
    return static::dateToInt($value);
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
class Hdata_ProcDate extends Hdata_Proc implements HdDate {
  //
  static $TYPE = self::TYPE_DATE;
  //
  static function from(/*Proc*/$rec) {
    return static::create($rec->userGroupId, $rec->procId, $rec->date);
  }
  static function toInt($value) {
    return static::dateToInt($value);
  }
}
/** Other dates */
abstract class Hdata_Date extends Hdata implements HdDate {
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
  static function toInt($value) {
    return static::dateToInt($value);
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
class Hdata_TrackDueDate extends Hdata_Date {
  //
  static $TABLE = self::TABLE_TRACK;
  static $JOIN_SQL_ID = 'track_item_id';
  static $DATE_FID = 'dueDate';
}
