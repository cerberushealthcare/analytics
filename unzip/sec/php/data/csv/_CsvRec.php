<?php
/**
 * CSV Record
 * @author Warren Hornsby
 */
abstract class CsvRec {
  /**
   * Assigns fields based upon arg supplied:
   *   ([value,..])          array: values assigned in field definition order 
   *   ({fid:value,..})      object: values mapped to fids
   */
  public function __construct($arg = null) {
    if ($arg) 
      if (is_array($arg))   
        $this->__constructFromArray($arg);
      else if (is_object($arg))  
        $this->__constructFromObject($arg);
  }
  public function formatValues() {
    $fids = $this->getFids();
    $values = array();
    foreach ($fids as $fid) 
      $values[] = $this->formatValue($fid);
    return implode(',', $values);
  }
  public function formatValue($fid) {
    $value = $this->$fid;
    return '"' . $value . '"';
  }
  //
  protected function __constructFromArray($arr) {
    $fids = $this->getFids();
    for ($i = 0, $l = count($fids); $i < $l; $i++) {
      $value = current($arr);
      $this->set($fids[$i], $value);
      next($arr);
    }
  }
  protected function __constructFromObject($obj) {
    $fids = $this->getFids();
    for ($i = 0, $l = count($fids); $i < $l; $i++) {
      $fid = $fids[$i];
      $value = get($obj, $fid);
      if ($value !== null) 
        $this->set($fid, $value);
    }
  }
  protected function set($fid, $value) {
    $this->$fid = $value;
  }
  protected function getFids() {
    static $fids;
    if ($fids == null)  
      $fids = array_keys(get_object_vars($this));
    return $fids;    
  }
  //
  /**
   * Read CSV file
   * @param string $filename
   * @param string $class
   * @return array(CsvRec,..) of type $class
   */
  static function read($filename, $class, $start = 0, $to = 99999) {
    $recs = array();
    if (($handle = fopen($filename, "r")) !== false) {
      if ($class::hasHeader())
        fgetcsv($handle, 1000, ',');
      $i = 0;
      while (($fields = fgetcsv($handle, 1000, ",")) !== false) {
        if ($i >= $start && $i < $to) {
          $recs[$i] = new $class($fields);
        }
        if (++$i >= $to)
          break;
      } 
      fclose($handle);
    }    
    return $recs;
  }
}
