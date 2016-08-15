<?php
require_once 'php/data/rec/sql/Snomeds.php';
//
/** 
 * SNOMED Lookups
 * @author Warren Hornsby
 */
class Snomeds_Admin {
  //
  static function getAll() {
    $recs = Snomed_A::fetchAll();
    return $recs;
  }
  static function save($o) {
    $rec = Snomed_A::from($o);
    $rec->save();
    return $rec;
  }
}
//
class Snomed_A extends Snomed {
  //
  static function fetchAll() {
    $c = new static();
    $recs = static::fetchAllBy($c, null, 8000);
    $recs = RecSort::sort($recs, 'snomedFsn');
    return $recs;
  }
  static function from($ui) {
    $me = new static();
    $me->snomedCid = $ui->snomedCid;
    $me->snomedFsn = $ui->snomedFsn;
    $me->snomedConceptStatus = 'Added';
    return $me;
  }
}