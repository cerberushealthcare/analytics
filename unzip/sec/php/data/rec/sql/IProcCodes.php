<?php
require_once 'php/data/rec/sql/_IpcRec.php';
//
/**
 * Internal Proc Codes DAO
 * @author Warren Hornsby
 */
class IProcCodes {
  //
  static function /*Ipc[]*/getAll($cat) {
    global $login;
    $recs = Ipc::fetchAll($login->userGroupId, $cat);
    foreach ($recs as &$rec) {
      if ($rec) 
        $rec->desc = substr($rec->desc, 0, 50);
    }
    return Rec::sort($recs, new RecSort('name'));
  }
  static function /*Ipc*/saveCustom($obj) {
    global $login;
    $ugid = $login->userGroupId;
    $rec = Ipc::revive($ugid, $obj);
    if ($rec->ipc == null) {
      $rec->ipc = Ipc::fetchNextAvailableIpc($ugid);
      $rec->saveAsInsert();
    } else {
      $rec->save();
    }
    return $rec;
  }
}
//
/**
 * Internal Proc Code
 */
class Ipc extends IpcRec {
  //
  public $ipc;
  public $userGroupId;
  public $name;
  public $desc;
  public $cat;
  public $codeSystem;
  public $code;
  public $codeSnomed;
  public $codeIcd9;
  public $codeCpt;
  public $codeLoinc;
  public $codeIcd10;
  //
  public function validate(&$rv) {
    $rv->requires('cat', 'name', 'desc');
  }
  //
  static function fetchByName($ugid, $name) {
    $c = self::asCriteria($ugid);
    $c->name = $name;
    return self::fetchOneBy($c);
  }
  static function fetchCustomByName($ugid, $name) {
    if ($ugid && $name) {
      $c = new static();
      $c->userGroupId = $ugid;
      $c->name = substr($name, 0, 127);
      return self::fetchOneBy($c);
    }
  }
  static function fetchAll($ugid, $cat = null) {
    $c = self::asCriteria($ugid);
    $c->cat = $cat;
    return parent::fetchAllBy($c, null, 8000);
  }
  static function fetchMapByName($ugid) {
    return parent::fetchMapBy(self::asCriteria($ugid), 'name');
  }
  static function fetchSurgMap($ugid) {
    $c = self::asCriteria($ugid);
    $c->cat = self::CAT_SURG;
    return parent::fetchMapBy($c, 'desc');
  }
  static function asCriteria($ugid = null) {
    if ($ugid == null) {
      global $login;
      $ugid = $login->userGroupId;
    }
    return parent::asCriteria($ugid);
  }
  static function asRequiredJoin($ugid = null, $cat = null) {
    $c = static::asCriteria($ugid);
    $c->cat = $cat;
    return CriteriaJoin::requires($c, 'ipc');
  }
  static function asRequired($c, $fid = 'ipc') {
    return CriteriaJoin::requires($c, $fid);
  }
  static function asRequired_noAdmin($fid = 'ipc') {
    $c = static::asCriteria();
    $c->cat = CriteriaValue::notIn(array(static::CAT_ADMIN, static::CAT_ADMIN_POC, static::CAT_ADMIN_FS));
    return static::asRequired($c, $fid);
  }
  static function asRequired_noAdminOrLab() {
    $c = static::asCriteria();
    $c->cat = CriteriaValue::notIn(array(static::CAT_ADMIN, static::CAT_ADMIN_POC, static::CAT_ADMIN_FS, static::CAT_LAB));
    return static::asRequired($c);
  }
  static function asRequired_planOfCare() {
    $c = static::asCriteria();
    $c->cat = static::CAT_ADMIN_POC;
    return static::asRequired($c);
  }
  static function asRequired_funcStatus() {
    $c = static::asCriteria();
    $c->cat = static::CAT_ADMIN_FS;
    return static::asRequired($c);
  }
  static function asRequired_smoking() {
    $c = static::asCriteria();
    $c->cat = static::CAT_ADMIN;
    $c->name = CriteriaValue::startsWith('Smoking Status:');
    return static::asRequired($c);
  }
  static function asOptionalJoin($ugid = null, $fk = 'ipc') {
    if ($ugid == null) {
      global $login;
      $ugid = $login->userGroupId;
    }
    $c = new static();
    $c->setUserGroupCriteria($ugid);
    return CriteriaJoin::optional($c, $fk);
  }
  static function revive($ugid, $obj) {
    $rec = new static($obj);
    $rec->userGroupId = $ugid;
    return $rec;
  }
  static function fetchNextAvailableIpc($ugid) {
    $max = Dao::fetchValue("SELECT MAX(ipc) FROM iproc_codes WHERE user_group_id=$ugid");
    if ($max)
      return intval($max) + 1;
    else
      return 1000000;
  }
  static function saveAsNewCustom($ugid, $name, $cat) {
    $rec = new static();
    $rec->ipc = self::fetchNextAvailableIpc($ugid);
    $rec->userGroupId = $ugid;
    $rec->name = substr($name, 0, 127);
    $rec->desc = $name;
    $rec->cat = $cat;
    $rec->saveAsInsert();
    return $rec;
  } 
}
class Ipcs {
  //
  const OfficeVisit = 600186;
  const ReasonForVisit = 604506;
  const FollowupVisit = 603790;
  const TransitionOfCare = 600214;
}
