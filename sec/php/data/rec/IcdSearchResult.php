<?php
require_once 'php/data/rec/_Rec.php';
require_once 'php/data/rec/sql/IcdCode.php';
require_once 'php/data/rec/_Search.php';
/**
 * ICD Search Result 
 */
class IcdSearchResult extends Rec {
  //
  public $expr;     // regexp of search terms
  public $icd3Ct;   // unique ICD3 count
  public $bestFit;  // ICD 
  public /*[IcdCode]*/ $icdCodes;
  //
  /**
   * @param string $expr
   * @param int $icd3Ct
   */
  public function __construct($expr, $icd3Ct) {
    $this->expr = $expr;
    $this->icd3Ct = $icd3Ct;
    $this->icdCodes = array();
  }
  //
  public static function search($text) {
    if (is_numeric($text)) 
      return IcdSearchResult::searchForCode($text);
    else 
      return IcdSearchResult::searchForText($text);
  }
  /**
   * @param string $text
   * @return array(IcdCode,..)
   */
  public static function searchForText($text) {
    $search = new SearchText($text);
    $icd3s = IcdSearchResult::distinctIcd3s($search);  // ['icd3','icd3',..]
    $icd3Recs = IcdSearchResult::fetchAllByIcd3s($icd3s);  // {'icd3':[IcdCode,..],..}
    $tallies = IcdSearchResult::tallyIcd3Recs($search, $icd3Recs);  // [SearchTally,..]
    $result = new IcdSearchResult($search->expr, count($tallies));
    if ($tallies) { 
      $result->bestFit = $tallies[0]->highestFitSubKey;
      foreach ($tallies as &$tally) 
        array_splice($result->icdCodes, count($result->icdCodes), 0, $icd3Recs[$tally->key]);
    }
    return $result;
  }
  //
  private static function tallyIcd3Recs($search, $icd3Recs, $max = 20) {
    $tallies = array();
    foreach ($icd3Recs as $icd3 => &$recs) {
      $tally = IcdSearchResult::tallyRecs($search, $icd3, $recs);
      $tally->icdLen = strlen($tally->highestFitSubKey);
      $tally->highestFitTextLen = strlen($tally->highestFitText);
      $tally->distinctWordCt = count($tally->distinctWords);
      $tallies[] = $tally;
    }
    Rec::sort($tallies, new RecSort(array(
      'distinctWordCt' => RecSort::DESC,
    	'highestFit' => RecSort::DESC,
    	'highestFitTextLen' => RecSort::ASC,
    	'icdLen' => RecSort::ASC,
      'highestFitSubKey' => RecSort::ASC)));
    return array_slice($tallies, 0, $max);
  }
  private static function tallyRecs($search, $icd3, $recs) {
    $tally = new SearchTally($icd3);
    foreach ($recs as &$rec) 
      $tally->tally($search, $rec->icdDesc, $rec->icdCode);
    return $tally;
  }
  private static function distinctIcd3s($search, $max = 200) {
    $expr = "'$search->expr'";
    $sql = <<<eos
SELECT DISTINCT icd3
FROM icd_codes
WHERE icd_code <= '999' AND (icd_desc RLIKE $expr OR synonyms RLIKE $expr)
eos;
    return array_slice(fetchSimpleArray($sql), 0, $max);
  }
  private static function fetchAllByIcd3s($icd3s) {
    $recs = array();
    foreach ($icd3s as $icd3) {
      $crit = new IcdCode();
      $crit->icd3 = $icd3;
      $recs[$icd3] = SqlRec::fetchAllBy($crit);
    }
    return $recs;
  }
}
?>

