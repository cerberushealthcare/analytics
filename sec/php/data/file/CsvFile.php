<?php
require_once 'php/data/_BasicRec.php';
require_once 'php/data/file/File.php';
//
/**
 * CSV File
 * @author Warren Hornsby
 */
abstract class CsvFile extends File {
  //
  static $FILENAME;      // 'SomeFilename.csv'
  static $CSVREC_CLASS;  // 'SomeCsvRec'
  static $HAS_FID_ROW;   // true if header row
  //
  public /*CsvRec[]*/$recs;
  public /*CsvHeader*/$header;
  //
  public function setFilename($name) {
    static::$FILENAME = "$name.csv";
  }
  public function save() {
    $lines = $this->toStrings();
    parent::save($lines);
  }
  public function download() {
    $lines = $this->toStrings();
    parent::download('application/csv', $lines);
  }
  public function toStrings() {
    $lines = array();
    if ($this->header)
      $lines[] = $this->header->toString();
    foreach ($this->recs as $rec)
      $lines[] = $rec->toString();
    return $lines;
  }
  //
  static function fetch() {
    $handle = static::fopen_asRead();
    $me = new static();
    if (static::$HAS_FID_ROW)
      $me->header = new CsvHeader(fgetcsv($handle, 1000, ','));
    $recs = array();
    while (($fields = fgetcsv($handle, 1000, ',')) !== false) 
      $recs[] = new static::$CSVREC_CLASS($fields);
    fclose($handle);
    return $me->load($recs);
  }  
}
//
abstract class CsvRec extends BasicRec {
  /*
  public $field1;
  public $field2;
  */  
  public function __construct($arg = null) {
    if ($arg) 
      if (is_array($arg))   
        $this->__constructFromArray($arg);
      else if (is_object($arg))  
        $this->__constructFromObject($arg);
  }
  public function toString() {
    $fids = $this->getFids();
    $values = array();
    foreach ($fids as $fid) 
      $values[] = $this->formatValue($fid);
    return implode(',', $values);
  }
  //
  protected function __constructFromObject($obj) {
    $fids = $this->getFids();
    for ($i = 0, $l = count($fids); $i < $l; $i++) {
      $fid = $fids[$i];
      $value = get($obj, $fid);
      if ($value !== null) 
        $this->set($fid, $value);
    }
  }
  protected function formatValue($fid) {
    $value = $this->$fid;
    return '"' . $value . '"';
  }
}
//
class CsvHeader {
  //
  public /*string[]*/$names;
  //
  public function toString() {
    $values = array();
    foreach ($this->names as $name) 
      $values[] = '"' . $name . '"';
    return implode(',', $values);
  }
  //
  public function __construct($names) {
    $this->names = $names;
  }
}