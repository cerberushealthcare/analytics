<?php
require_once 'php/data/rec/sql/_LookupRec.php';
//
/**
 * Lookup Areas
 * @author Warren Hornsby 
 */
class LookupAreas {
  //
  /**
   * @return array(LuArea,..)
   */
  static function get() {
    $recs = LuArea::fetchAll();
    Rec::sort($recs, new RecSort('name'));
    return $recs;
  }
  static function save($o) {
    //TODO
  }
}
class LuArea extends LookupRec {
  //
  public $name;
  public $inactive;
  //
  const FIRST_CUSTOM_ID = 1000;  // first ID to use when creating custom recs 
  //
  public function getLookupTable() {
    return 28;
  }
  public function getLookupName() {
    return 'AREAS';
  }
  public function getListValue() {
    return $this->name;
  }
  //
  static function fetchAll() {
    $recs = parent::fetchAll();
    return self::eliminateInactives($recs);
  }
}
