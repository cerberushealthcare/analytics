<?php
require_once 'php/data/rec/sql/_SqlRec.php';
//
/**
 * Drug Class DAO
 * @author Warren Hornsby
 */
class DrugClasses {
  //
  static function getSubclasses() {
    $recs = DrugSubclass::fetchAll();
    Rec::sort($recs, new RecSort('name'));
    return $recs;
  }
  static function getSubclassJsonList() {
    return MethodCache::getset(__METHOD__, func_get_args(), function() {
      $recs = DrugClasses::getSubclasses();
      $list = array();
      foreach ($recs as $rec) 
        $list[$rec->subclassId] = $rec->name;
      return jsonencode($list);
    });
  }
  static function has_BetaBlocker($meds) {
    return static::has($meds, static::getRegExp_BetaBlockers());
  }
  static function has_ACEInhibitor($meds) {
    return static::has($meds, static::getRegExp_ACEInhibitors());
  }
  static function has_ARB($meds) {
    return static::has($meds, static::getRegExp_ARB());
  }
  static function has($meds, $regexp) {
    $has = false;
    if ($meds) { 
      foreach ($meds as $med)  
        $has = $has || preg_match($regexp, $med->name);
    }
    return $has;
  }
  //
  protected static function getRegExp_BetaBlockers() {
    return static::getRegExp(1);
  }
  protected static function getRegExp_ACEInhibitors() {
    return static::getRegExp(9);
  }
  protected static function getRegExp_ARB() {
    return static::getRegExp(10);
  }
  protected static function getRegExp($subclassId) {
    return MethodCache::getset(__METHOD__, func_get_args(), function() use ($subclassId) {
      $sql = "SELECT GROUP_CONCAT(name SEPARATOR '|') FROM drug_names WHERE subclass_id=$subclassId";
      return "/" . Dao::fetchValue($sql) . "/i";
    });
  }
}
/**
 * Drug Class
 */
class DrugClass extends SqlRec implements ReadOnly {
  //
  public $classId;
  public $name;
  public /*DrugSubclass[]*/ $DrugSubclasses;
  //
  function getSqlTable() {
    return 'drug_classes';
  }
}
/**
 * Drug Subclass
 */
class DrugSubclass extends SqlRec implements ReadOnly {
  //
  public $subclassId;
  public $classId;
  public $name;
  //
  function getSqlTable() {
    return 'drug_subclasses';
  }
  //
  static function fetchAll() {
    $c = new self();
    $recs = self::fetchAllBy($c);
    return $recs;
  }
}
/**
 * Drug Name
 */
class DrugName extends SqlRec implements ReadOnly {
  //
  public $nameId;
  public $subclassId;
  public $name;
  //
  function getSqlTable() {
    return 'drug_names';
  }
  //
  static function fetchAll($drugSubclass) {
    $c = new self();
    $c->subclassId = $drugSubclass->subclassId;
    return self::fetchAllBy($c); 
  }
  static function asRegexpValue($subclassId) {
    return "(SELECT GROUP_CONCAT(name SEPARATOR '|') FROM drug_names WHERE subclass_id=$subclassId)";
  }
}
