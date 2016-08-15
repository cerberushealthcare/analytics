<?php
require_once 'php/data/file/_File.php';
//
abstract class PipeFile extends TextFile {
  //
  static function open() {
    $me = new static();
    $me->read();
    $recs = $me->getLines();
    array_shift($recs);
    foreach ($recs as &$rec) {
      $rec = explode('|', $rec);
    }
    return $recs;
  }
}
class SnomedFile extends PipeFile {
  //
  static $FILENAME = 'SNOMEDCT_CORE_SUBSET_201402.txt';
}
class IcdFile extends PipeFile {
  //
  static $FILENAME = 'ICD9CM_SNOMED_MAP_1TO1_201312.txt';
}
abstract class SqlFile extends TextFile {
  //
  static function write($recs) {
    $me = static::create($recs);
    $me->save();
  }
}
class SnomedSql extends SqlFile {
  //
  static $FILENAME = 'SNOMED.sql';
}
class IcdSql extends SqlFile {
  //
  static $FILENAME = 'ICD_SNOMED.sql';
}