<?php
require_once 'php/data/rec/sql/_SqlRec.php';
//
class IsoLang_I extends SqlRec {
  //
  public $isolangId;
  public $engName;
  public $engAllNames;
  public $frAllNames;
  public $alpha3Code;
  public $alpha3Terminology;
  public $iso639_1;
  //
  public function getSqlTable() {
    return 'isolangs';
  }
  public function setAlpha3($text) {
    $a = explode('/', $text);
    $this->alpha3Code = $a[0];
    if (count($a) > 1)
      $this->alpha3Terminology = $a[1];
  }
  //
  static function out($recs) {
    $us = static::all($recs);
    return static::getSqlInserts($us);
  }
  static function all($recs) {
    $us = array();
    $id = 1;
    foreach ($recs as $rec) {
      $me = static::from($rec);
      if ($me) {
        $me->isolangId = $id++; 
        $us[] = $me;
      }
    }
    return $us;
  }
  static function from($a) {
    $me = new static();
    $me->engName = reset($a);
    $me->engAllNames = next($a);
    $me->frAllNames = next($a);
    $me->setAlpha3(next($a));
    $me->iso639_1 = next($a);
    if ($me->iso639_1)
      return $me;
  } 
}
