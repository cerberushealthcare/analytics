<?php
require_once 'php/data/rec/sql/_SqlRec.php';
//
/** 
 * SNOMED Lookups
 * @author Warren Hornsby
 */
class Snomeds {
  //
  static function /*string*/getCidFromIcd9(/*string*/$icd) {
    return Icd9Snomed::getCid($icd);
  }
  static function /*string*/getIcd9FromCid(/*string*/$cid) {
    return Icd9Snomed::getIcd($cid);
  }
  static function /*Snomed*/get($cid) {
    return Snomed::fetch($cid);
  }
  static function /*string*/getDesc($cid) {
    $rec = static::get($cid);
    if ($rec)
      return $rec->snomedFsn;
  }
  static function /*string*/getShortDesc($cid) {
    $rec = static::get($cid);
    if ($rec)
      return $rec->getShortDesc();
  }
  static function searchCid($text) {
    return Snomed::searchCid($text);
  }
  static function searchDesc($text) {
    return Snomed::searchDesc($text);
  }
}
//
class Snomed extends SqlRec implements CompositePk, AdminOnly {
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
  public function getShortDesc() {
    $i = strrpos($this->snomedFsn, '(');
    return ($i > 0) ? substr($this->snomedFsn, 0, $i - 1) : $this->snomedFsn; 
  }
  //
  static function fetch($cid) {
    $c = new static();
    $c->snomedCid = $cid;
    return static::fetchOneBy($c);
  }
  static function searchCid($text) {
    $c = new static();
    $c->snomedCid = CriteriaValue::startsWith($text);
    return static::fetchAllBy($c, new RecSort('snomedCid'), 30);
  }
  static function searchDesc($text) {
    $c = new static();
    $c->snomedFsn = CriteriaValue::contains($text);
    return static::fetchAllBy($c, new RecSort('snomedFsn'), 30);
  }
}
class Icd9Snomed extends SqlRec {
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
  static function getCid($icd) {
    if ($icd) {
      $c = new static();
      $c->icdCode = $icd;
      $rec = static::fetchOneBy($c);
      if ($rec)
        return $rec->snomedCid; 
    }
  }
  static function getIcd($cid) {
    if ($cid) {
      $c = new static();
      $c->snomedCid = $cid;
      $recs = static::fetchAllBy($c);
      if ($recs) {
        $rec = end($recs);
        return $rec->icdCode;
      } 
    }
  }
}