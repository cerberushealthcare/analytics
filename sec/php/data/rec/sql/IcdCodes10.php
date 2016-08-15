<?php
require_once 'php/data/rec/_Search.php';
require_once 'php/data/rec/sql/_SqlRec.php';
//
/**
 * ICD 10 Codes
 * @author Warren Hornsby
 */
class IcdCodes10 {
  //
  static function get($icd) {
    return IcdCode10::fetch($icd);
  }
  static function getDesc($icd) {
    $rec = static::get($icd);
    if ($rec)
      return $rec->icdDesc;
  }
  /**
   * @param string $text
   * @return array(
   *   'icdCodes'=>[IcdCode10,..],
   *   'expr'=>'words|searched',
   *   'bestFit'=>'icd',
   *   'icd3Ct'=>#)
   */
  public static function search($text) {
    $search = new SearchText($text);
    $search->isNumeric = is_numeric($text) || is_numeric(substr($text, 1));
    $icd3s = static::distinctIcd3s($search);  // ['icd3','icd3',..]
    $icd3Recs = static::fetchAllByIcd3s($icd3s);  // {'icd3':[IcdCode,..],..}
    $tallies = SearchTally_Icd10::fromIcd3Groups($search, $icd3Recs);
    $icdCodes = array();
    $bestFit = null;
    if ($tallies) { 
      $bestFit = $tallies[0]->highestFitSubKey;
      foreach ($tallies as &$tally) 
        array_splice($icdCodes, count($icdCodes), 0, $icd3Recs[$tally->key]);
    }
    $return = array(
      'icdCodes' => $icdCodes,
      'expr' => $search->expr,
      'icd3Ct' => count($tallies),
      'bestFit' => $bestFit);
    return $return;
  }
  private static function distinctIcd3s($search, $max = 200) {
    if ($search->isNumeric) 
      $expr = "icd_code RLIKE '$search->expr'";
    else 
      $expr = "(icd_desc RLIKE '$search->expr' OR notes RLIKE '$search->expr')";
    $sql = <<<eos
SELECT DISTINCT icd3
FROM icd10_codes
WHERE $expr
eos;
    return array_slice(fetchSimpleArray($sql), 0, $max);
  }
  private static function fetchAllByIcd3s($icd3s) {
    $recs = array();
    foreach ($icd3s as $icd3) {
      $crit = new IcdCode10();
      $crit->icd3 = $icd3;
      $recs[$icd3] = SqlRec::fetchAllBy($crit, null, 2000);
    }
    return $recs;
  }
}
/**
 * ICD Code 
 */
class IcdCode10 extends SqlRec implements ReadOnly {
  //
  public $icdCode;
  public $hipaa;
  public $icdDesc;
  public $notes;
  public $icd3;
  //
  public function _toJsonObject() {
    $o = new stdClass();
    $o->code = utf8_encode($this->icdCode);
    $o->hipaa = utf8_encode($this->hipaa);
    $o->desc = utf8_encode($this->icdDesc);
    $o->notes = utf8_encode($this->notes);
    return $o;
  }
  public function getSqlTable() {
    return 'icd10_codes';
  }
  //
  static function fetch($icd) {
    $c = new static();
    $c->icdCode = $icd;
    return static::fetchOneBy($c);
  }
}
/**
 * SearchTally SearchTally_Icd
 */
class SearchTally_Icd10 extends SearchTally {
  //
  public $icdLen;
  public $highestFixTextLen;
  public $distinctWordCt;
  public $icdGroupMatchRate;
  public $icdGroupRecs = 0;
  public $icdGroupMatchRecs = 0;
  public $_lastTotalMatchCt = 0;
  //
  public function next() {
    $this->icdGroupRecs++;
    if ($this->totalMatchCt > $this->_lastTotalMatchCt) 
      $this->icdGroupMatchRecs++;
    $this->_lastTotalMatchCt = $this->totalMatchCt;
  }
  /**
   * @param SearchText $search
   * @param array('icd3' => [IcdCodes,..]) $icd3Recs
   */
  static function fromIcd3Groups($search, $icd3Recs, $max = 20) {
    $tallies = array();
    foreach ($icd3Recs as $icd3 => &$recs) {
      $tally = static::fromIcdCodes($search, $icd3, $recs);
      $tally->icdLen = strlen($tally->highestFitSubKey);
      $tally->highestFitTextLen = strlen($tally->highestFitText);
      $tally->distinctWordCt = count($tally->distinctWords);
      $tally->icdGroupMatchRate = $tally->icdGroupMatchRecs / $tally->icdGroupRecs;
      $tallies[] = $tally;
    }
    //p_r($tallies);
    if ($search->isNumeric) 
      Rec::sort($tallies, new RecSort('-distinctWordCt', 'highestFitSubKey'));
    else
      Rec::sort($tallies, new RecSort('-distinctWordCt', '-icdGroupMatchRate', '-highestFit', 'highestFitTextLen', 'icdLen', 'highestFitSubKey'));
    return array_slice($tallies, 0, $max);
  }
  static function fromIcdCodes($search, $icd3, $recs) {
    $me = static::fromIcd($icd3);
    if ($search->isNumeric)
      foreach ($recs as &$rec) { 
        $me->tally($search, $rec->icdCode, $rec->icdCode);
        $me->next();
      }
    else
      foreach ($recs as &$rec) {
        $me->tally($search, $rec->icdDesc, $rec->icdCode);
        $me->tally($search, $rec->notes, $rec->icdCode);
        $me->next();
      }
    return $me;
  }
  static function fromIcd($icd3) {
    $me = new static($icd3);
    return $me;
  }
}
