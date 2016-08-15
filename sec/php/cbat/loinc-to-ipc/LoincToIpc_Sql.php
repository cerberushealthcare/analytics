<?php
require_once 'php/data/rec/sql/_IpcRec.php';
//
class Loinc extends SqlRec {
  //
  public $loincNum;
  public $component;
  public $longCommonName;
  public $_cpt;
  //
  public function getSqlTable() {
    return 'loinc';
  }
  //
  static function fetchAll() {
    $c = new static();
    $us = static::fetchAllBy($c, null, 50000, 'loincNum');
    return $us;
  }
  static function xref(&$recs, &$xrefs) {
    foreach ($xrefs as $xref) {
      $rec = geta($recs, $xref->loinc);
      if ($rec) {
        $rec->_cpt = $xref->cpt;
      }
    }
  }
}
class LoincToCpt extends SqlRec {
  //
  public $loinc;
  public $cpt;
  //
  public function getSqlTable() {
    return 'loinc_to_cpt';
  }
  //
  static function fetchAll() {
    $c = new static();
    $us = static::fetchAllBy($c, null, 50000);
    return $us;
  }
}
class Ipc_Export extends IpcRec {
  //
  public $ipc;
  public $userGroupId;
  public $name;
  public $desc;
  public $cat;
  public $code;  
  public $codeSystem;
  public $qref;
  public $codeSnomed;
  public $codeIcd9;
  public $codeCpt;
  public $codeLoinc;
  public $codeIcd10;
  //
  static function from($ipc, $rec) {
    $me = new static();
    $me->ipc = $ipc;
    $me->userGroupId = 0;
    $me->name = substr($rec->component, 0, 128);
    $me->desc = $rec->longCommonName;
    $me->cat = static::CAT_LAB;
    $me->codeLoinc = $rec->loincNum; 
    $me->codeCpt = $rec->_cpt; 
    return $me;
  } 
  static function out($recs) {
    $us = static::all($recs);
    return static::getSqlInserts($us);
  }
  static function all($recs) {
    $us = array();
    $ipc = 500000;
    foreach ($recs as $rec) {
      $me = static::from($ipc, $rec);
      if ($me) {
        $us[] = $me;
        $ipc++;
      }
    }
    return $us;
  }
}
