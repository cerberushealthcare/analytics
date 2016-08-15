<?php
require_once 'php/data/file/_File.php';
//
class OutputSql extends TextFile {
  //
  static $FILENAME = 'output.sql';
  //
  static function create($recs) {
    $lines = array();
    foreach ($recs as $rec) 
      $lines[] = $rec->toString();
    return parent::create($lines);
  }  
}
