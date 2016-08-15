<?php
require_once 'php/data/csv/_CsvRec.php';
//
/**
 * CSV File
 * @author Warren Hornsby
 */
abstract class CsvFile {
  //
  static $FILENAME;      // 'php/data/csv/SomeFilename.csv'
  static $CSVREC_CLASS;  // 'SomeRec'
  static $HAS_FID_ROW;   // true if header row
  //
  public /*CsvRec[]*/$recs;
  //
  public function setFilename($name) {
    static::$FILENAME = "$name.csv";
  }
  /**
   * Output file to browser
   */
  public function download() {
    $filename = static::$FILENAME;
    ob_clean();
    header("Pragma: ");
    header("Cache-Control: ");
    header("Content-Type: application/csv"); 
    header("Content-Disposition: attachment; filename=$filename");
    foreach ($this->recs as $rec) { 
      echo $rec->formatValues() . "\n";
    }
  }
  /**
   * Binary search
   * Recs must implement compare($needle) and haystack must be sorted
   * @return CsvRec of match   
   */
  public function bsearch($needle, $start = -1, $end = -1) {
    if ($start == -1) {
      $start = 0;
      $end = count($this->recs) - 1;
    }
    if ($end < $start)
      return null;
    $i = (int)(($end - $start) / 2) + $start;
    $rec = $this->recs[$i];
    $c = $rec->compare($needle);
    if ($c > 0)
      return static::bsearch($needle, $start, $i - 1);
    else if ($c < 0)
      return static::bsearch($needle, $i + 1, $end);
    else
      return $rec;
  }
  //
  /**
   * @return CsvFile loaded with CsvRecs
   */
  static function load($start = 0, $to = 999999) {
    $filename = static::$FILENAME;
    if (($handle = fopen($filename, 'r', true)) == false) 
      throw new Exception("Unable to open file $filename");
    $me = new static();
    $me->recs = array();
    if (static::$HAS_FID_ROW)
      fgetcsv($handle, 1000, ',');
    $i = 0;
    while (($fields = fgetcsv($handle, 1000, ",")) !== false) {
      if ($i >= $start && $i < $to)
        $me->recs[] = new static::$CSVREC_CLASS($fields);
      if (++$i >= $to)
        break;
    } 
    fclose($handle);
    return $me;
  }  
}
