<?php
/**
 * SetterRec
 * @author Warren Hornsby
 */
class SetterRec {
  /*
  public $field1;
  public $field2;
  protected $_other;
  */
  //
  public function __construct($rec, $delim = '|') {
    $values = explode($delim, $rec);
    $this->setValues($values);
  }
  /*
  public function setField1($value) {
    $this->field1 = do_something_to($value);
  }
  */
  //
  protected function setValues($values) {
    $fids = $this->getFids();
    foreach ($fids as $fid => $setter) {
      $value = current($values);
      if ($setter)
        call_user_func(array($this, $setter), $value);
      else
        $this->$fid = $value;
      next($values); 
    } 
  }
  protected function getFids() {  // ['fid1'=>'setFid1','fid2'=>null,..] i.e. no setter for fid2
    static $fids;
    if ($fids == null) {
      $vars = get_object_vars($this);
      $fids = array();
      foreach ($vars as $var => $value) {
        if (self::isFid($var)) {
          $setter = 'set' . strtoupper(substr($var, 0, 1)) . substr($var, 1);
          $fids[$var] = method_exists($this, $setter) ? $setter : null;
        }
      }
    }
    return $fids;
  }
  protected static function isFid($var) {
    $c1 = substr($var, 0, 1);
    return $c1 != '_' && ! isUpper($c1);
  }
  protected static function isUpper($c) {
    return ($c == strtoupper($c)); 
  }
}