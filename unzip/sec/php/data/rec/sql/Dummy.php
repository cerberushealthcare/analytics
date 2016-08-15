<?php
require_once "php/data/rec/sql/AllergiesLegacy.php";
class Allergy_DataOut extends Allergy {
  //
  /**
   * @param object $out {'records':{'ugid|cid|ipc':{'fields':{fid:value,..}}}}
   * @return self 
   */
  static function save_from($out) { 
    $rec = static::fromOut($out);
    $rec->save();
    return $rec;
  }
  static function fromOut($out) {
    foreach ($out->records as $pk => $record) {
      $pk = Pk_Allergy::from($pk);
      $me = static::from($record->fields, $pk);
      return $me;
    }
  }
  static function from($o, $pk) {
    $me = new static();
    foreach ($o as $fid => $value) {
      $cfid = static::sqlToCamel($fid);
      $me->$cfid = $value;
    }
    $me->userGroupId = $pk->ugid;
    $me->clientId = $pk->cid;
    $me->sessionId = $pk->sid;
    $me->date = $pk->date;
    $me->active = true;
    return $me;
  }
}
class Pk_Allergy {
  //
  public $ugid;
  public $cid;
  public $sid;
  public $date;
  public $ix;
  //
  static function from($string) {
    $pks = explode('|', $string);
    return static::fromArray($pks);
  }
  protected static function fromArray($pks) {
    $me = new static();
    $me->ugid = $pks[0];
    $me->cid = $pks[1];
    $me->sid = $pks[2];
    $me->date = $pks[3];
    $me->ix = $pks[4];
    return $me;
  }
}
require_once "php/data/rec/sql/MedsLegacy.php";
class Med_DataOut extends Med {
  //
  /**
   * @param object $out {'records':{'ugid|cid|ipc':{'fields':{fid:value,..}}}}
   * @return self 
   */
  static function save_from($out) { 
    $rec = static::fromOut($out);
    $rec->save();
    return $rec;
  }
  static function fromOut($out) {
    foreach ($out->records as $pk => $record) {
      $pk = Pk_Med::from($pk);
      $me = static::from($record->fields, $pk);
      return $me;
    }
  }
  static function from($o, $pk) {
    $me = new static();
    foreach ($o as $fid => $value) {
      $cfid = static::sqlToCamel($fid);
      $me->$cfid = $value;
    }
    $me->userGroupId = $pk->ugid;
    $me->clientId = $pk->cid;
    $me->sessionId = $pk->sid;
    $me->date = $pk->date;
    $me->quid = $pk->quid;
    $me->active = true;
    return $me;
  }
}
class Pk_Med {
  //
  public $ugid;
  public $cid;
  public $sid;
  public $date;
  public $quid;
  public $ix;
  //
  static function from($string) {
    $pks = explode('|', $string);
    return static::fromArray($pks);
  }
  protected static function fromArray($pks) {
    $me = new static();
    $me->ugid = $pks[0];
    $me->cid = $pks[1];
    $me->sid = $pks[2];
    $me->date = $pks[3];
    $me->quid = $pks[4];
    $me->ix = $pks[5];
    return $me;
  }
}
require_once "php/data/rec/sql/Diagnoses.php";
class Diagnosis_DataOut extends Diagnosis {
  //
  /**
   * @param object $out {'records':{'ugid|cid|ipc':{'fields':{fid:value,..}}}}
   * @return self 
   */
  static function save_from($out) { 
    $rec = static::fromOut($out);
    $rec->save();
    return $rec;
  }
  static function fromOut($out) {
    foreach ($out->records as $pk => $record) {
      $pk = Pk_Diagnosis::from($pk);
      $me = static::from($record->fields, $pk);
      return $me;
    }
  }
  static function from($o, $pk) {
    $me = new static();
    foreach ($o as $fid => $value) {
      $cfid = static::sqlToCamel($fid);
      $me->$cfid = $value;
    }
    $me->userGroupId = $pk->ugid;
    $me->clientId = $pk->cid;
    $me->sessionId = $pk->sid;
    $me->date = $pk->date;
    $me->parUid = $pk->quid;
    $me->active = true;
    return $me;
  }
}
class Pk_Diagnosis {
  //
  public $ugid;
  public $cid;
  public $sid;
  public $date;
  public $puid;
  //
  static function from($string) {
    $pks = explode('|', $string);
    return static::fromArray($pks);
  }
  protected static function fromArray($pks) {
    $me = new static();
    $me->ugid = $pks[0];
    $me->cid = $pks[1];
    $me->sid = $pks[2];
    $me->date = $pks[3];
    $me->puid = $pks[4];
    return $me;
  }
}
require_once "php/data/rec/sql/Immuns.php";
class Immun_DataOut extends Immun {
  //
  /**
   * @param object $out {'records':{'ugid|cid|ipc':{'fields':{fid:value,..}}}}
   * @return self 
   */
  static function save_from($out) { 
    $rec = static::fromOut($out);
    $rec->save();
    return $rec;
  }
  static function fromOut($out) {
    foreach ($out->records as $pk => $record) {
      $pk = Pk_Immun::from($pk);
      $me = static::from($record->fields, $pk);
      return $me;
    }
  }
  static function from($o, $pk) {
    $me = new static();
    foreach ($o as $fid => $value) {
      $cfid = static::sqlToCamel($fid);
      $me->$cfid = $value;
    }
    $me->userGroupId = $pk->ugid;
    $me->clientId = $pk->cid;
    $me->sessionId = $pk->sid;
    $me->date = $pk->date;
    $me->active = true;
    return $me;
  }
}
class Pk_Immun {
  //
  public $ugid;
  public $cid;
  public $sid;
  public $date;
  public $ix;
  //
  static function from($string) {
    $pks = explode('|', $string);
    return static::fromArray($pks);
  }
  protected static function fromArray($pks) {
    $me = new static();
    $me->ugid = $pks[0];
    $me->cid = $pks[1];
    $me->sid = $pks[2];
    $me->date = $pks[3];
    $me->ix = $pks[4];
    return $me;
  }
}
