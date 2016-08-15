<?php
require_once 'lib/rec/_BasicRec.php';
require_once 'File.php';
//
/**
 * CSV File
 * @author Warren Hornsby
 */
abstract class CsvFile extends RecFile {
  //
  static $FILENAME;
  static $BASEPATH;
  static $CSVREC_CLASS;
  static $HAS_FID_ROW/* true if header row */;
  //
  protected /*CsvRec[]*/$recs;
  protected /*CsvHeader*/$header;
  //
  public function setFilename($name) {
    static::$FILENAME = "$name.csv";
  }
  public function getLines() {
    $lines = parent::getLines();
    if (static::$HAS_FID_ROW)
      array_unshift($lines, $this->header->toString());
    return $lines;
  }
  public function read() {
    $handle = $this->fopen_asRead();
    if (static::$HAS_FID_ROW)
      $this->header = new CsvHeader(fgetcsv($handle, 1000, ','));
    $recs = array();
    while (($fields = fgetcsv($handle, 1000, ',')) !== false) 
      $recs[] = new static::$CSVREC_CLASS($fields);
    fclose($handle);
    $this->load($recs);
    return $this;
  }
  public function save() {
    $handle = static::fopen_asWrite();
    if (static::$HAS_FID_ROW) {
      $header = $this->header ?: CsvHeader::asFieldNames(static::$CSVREC_CLASS);
      fwrite($handle, $header->toString() . "\n");
    }  
    foreach ($this->recs as $rec)
      fwrite($handle, $rec->toString() . "\n");  
    fclose($handle);
    return $this;
  }
  public function download() {
    parent::download('application/csv');
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
    $value = str_replace('"', '""', $value);
    $value = str_replace(array("\r\n","\n","\r"), "\\n", $value);
    return '"' . $value . '"';
  }
}
//
class CsvHeader {
  //
  public /*string[]*/$names;
  //
  public function __construct($names) {
    $this->names = $names;
  }
  public function toString() {
    $values = array();
    foreach ($this->names as $name) 
      $values[] = '"' . $name . '"';
    return implode(',', $values);
  }
  //
  static function asFieldNames(/*string*/$csvRecClass) {
    $rec = new $csvRecClass();
    return new static(static::fixCamelNames($rec->getFids()));
  } 
  static function fixCamelNames($fids) {
    foreach ($fids as &$fid) {
      $func = create_function('$c', 'return "_" . strtolower($c[1]);');
      $fid = strtoupper(preg_replace_callback('/([A-Z])/', $func, $fid));
    }
    return $fids; 
  }
}