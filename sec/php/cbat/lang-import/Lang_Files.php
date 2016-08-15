<?php
require_once 'php/data/file/_File.php';
//
abstract class PipeFile extends TextFile {
  //
  static function /*array(string[])*/open() {
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
class LangFile extends PipeFile {
  //
  static $FILENAME = 'langs-pipedelim-utf8.txt';
}
abstract class SqlFile extends TextFile {
  //
  static function write($recs) {
    $me = static::create($recs);
    $me->save();
  }
}
class LangSql extends SqlFile {
  //
  static $FILENAME = 'IsoLangs.sql';
}
