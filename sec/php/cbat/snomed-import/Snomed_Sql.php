<?php
require_once 'php/data/rec/sql/_SqlRec.php';
//
class Snomed_I extends SqlRec {
  //
  public $snomedCid;
  public $snomedFsn;
  public $snomedConceptStatus;
  public $umlsCui;
  public $occurrence;
  public $usage;
  public $firstInSubset;
  public $isRetiredFromSubset;
  public $lastInSubset;
  public $replacedBySnomedCid;
  //
  public function getSqlTable() {
    return 'snomed';
  }
  //
  static function out($recs) {
    $us = static::all($recs);
    return static::getSqlInserts($us);
  }
  static function all($recs) {
    $us = array();
    foreach ($recs as $rec) {
      $me = static::from($rec);
      if ($me)
        $us[] = $me;
    }
    return $us;
  }
  static function from($a) {
    $me = new static();
    $me->snomedCid = reset($a);
    $me->snomedFsn = next($a);
    $me->snomedConceptStatus = next($a);
    $me->umlsCui = next($a);
    $me->occurrence = next($a);
    $me->usage = next($a);
    $me->firstInSubset = next($a);
    $me->isRetiredFromSubset = next($a) == 'True';
    $me->lastInSubset = next($a);
    $me->replacedBySnomedCid = next($a);
    if ($me->replacedBySnomedCid == 'NA')
      $me->replacedBySnomedCid = null;
    if ($me->snomedCid)
      return $me;
  } 
}
class IcdSnomed_I extends SqlRec {
  //
  public $icdCode;
  public $icdName;
  public $isCurrentIcd;
  public $ipUsage;
  public $opUsage;
  public $avgUsage;
  public $isNec;
  public $snomedCid;
  public $snomedFsn;
  public $is1To1;
  public $inCore;
  //
  public function getSqlTable() {
    return 'icd9_snomed';
  }
  //
  static function out($recs) {
    $us = static::all($recs);
    return static::getSqlInserts($us);
  }
  static function all($recs) {
    $us = array();
    foreach ($recs as $rec) {
      $me = static::from($rec);
      if ($me)
        $us[] = $me;
    }
    return $us;
  }
  static function from($a) {
    $me = new static();
    $me->icdCode = reset($a);
    $me->icdName = next($a);
    $me->isCurrentIcd = next($a);
    $me->ipUsage = next($a);
    $me->opUsage = next($a);
    $me->avgUsage = next($a);
    $me->isNec = next($a);
    $me->snomedCid = next($a);
    $me->snomedFsn = next($a);
    $me->is1To1 = next($a);
    $me->inCore = next($a);
    if ($me->icdCode)
      return $me;
  } 
}