<?php
/**
 * BasicRec
 * @author Warren Hornsby
 */
abstract class BasicRec {
  /*
  public $field1;
  public $field2;
  */
  /**
   * @param (value,value,..) assigned to instance in field definition order 
   */
  public function __construct() {
    $args = func_get_args();
    $this->__constructFromArray($args);
  }
  public function set($fid, $value) {
    $this->$fid = $value;
  }
  //
  public function getFids() {
    static $fids;
    if ($fids == null)
      $fids = array_keys(get_object_vars($this));
    return $fids;
  }
  protected function __constructFromArray($arr) {
    $fids = $this->getFids();
    for ($i = 0, $l = count($fids); $i < $l; $i++) {
      $value = current($arr);
      $this->set($fids[$i], $value);
      next($arr);
    }
  }
}