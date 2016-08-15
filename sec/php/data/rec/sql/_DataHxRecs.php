<?php
require_once 'php/data/rec/_Rec.php';
require_once 'php/dao/DataDao.php';
require_once 'php/data/json/JDataSyncFamGroup.php';
//
class HxRec extends Rec {
  //
  public function setFromValues($values) {
    foreach ($values as $fid => $json)
      $this->setJson($fid, $json);
  }
  public function setJson($fid, $json) {
    $value = jsondecode($json);
    $this->setValue($fid, $value);
  }
  public function setValue($fid, $value) {
    if (is_array($value))
      $value = implode(', ', $value);
    $this->$fid = $value;
  }
  protected static function fromGroup($group) {
    $us = static::fromRecordArray($group->records);
    return $us;
  }
  protected static function fromRecordArray($array) {
    $us = array();
    if (is_array($array)) {
      foreach ($array as $key => $values) {
        $me = static::fromRecord($key, $values); 
        if ($me)
          $us[] = $me;
      }
    } 
    return $us;
  }
  protected static function fromRecord($key, $rec) {
    $values = $rec->fieldValues;
    return static::fromFieldValues($key, $values);
  }
  protected static function fromFieldValues($key, $values) {
    $me = static::create($key);
    $me->setFromValues($values);
    return $me;
  }
  protected static function create($key) {
    $me = new static($key);
    return $me;
  }
}
/**
 * Medical History
 */
class MedHx extends HxRec {
  //
  public $name;
  public $date;
  public $type;
  public $rx;
  public $comment;
  //
  static function fetchAll($cid) {
    $group = DataDao::fetchDataSyncProcGroup(JDataSyncProcGroup::CAT_MED, $cid);
    return static::fromGroup($group);
  }
}
/**
 * Family History
 */
class FamHx extends HxRec {
  //
  public $relative;
  public $status;
  public $deathAge;
  public $age;
  public $history;
  public $comment;
  //
  static function fetchAll($cid) {
    $group = DataDao::fetchDataSyncFamGroup(JDataSyncFamGroup::SUID_FAM, $cid, false);
    return static::fromGroup($group);
  }
  protected static function create($key) {
    $relative = JDataSyncFamGroup::$PUIDS_TEXT[JDataSyncFamGroup::SUID_FAM][$key];
    return parent::create($relative);
  } 
} 
/**
 * PsychoSocial History
 */
class SocHx extends HxRec {
  //
  public $name;
  /*
   Remaining fields assigned dynamically, e.g. for $name='Alcohol':
   public $uses;
   public $amt;
   public $intox;
   */
  //
  public function setJson($fid, $json) {
    $fid = static::extractFid($fid);
    $value = jsondecode($json);
    $this->setValue($fid, $value->v);
  }
  public function /*'STATUS: Married'*/getValuesString() {
    $a = array();
    foreach ($this as $fid => $value) {
      if ($fid != 'name' && $value) {
        $a[] = strtoupper($fid) . ': ' . $value;
      }
    }
    return implode(', ', $a);
  }
  //
  static function fetchAll($cid) {
    $group = DataDao::fetchDataSyncGroup(JDataSyncGroup::GROUP_SOCHX, $cid);
    return static::fromGroup($group);
  }
  protected static function fromRecord($key, $rec) {
    if ($rec->nonNull) {
      $values = $rec->fields;
      $me = static::fromFieldValues($key, $values);
      $me->_values = $me->getValuesString();
      return $me;
    }
  }
  protected static function extractFid($fid) {
    $a = explode('.', $fid);
    $fid = end($a);
    $a = explode('?', $fid);
    if (count($a) > 1)
      $fid = $a[0] . '_' . (intval($a[1]) + 1);
    return $fid;
  }
}