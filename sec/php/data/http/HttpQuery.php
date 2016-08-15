<?php
require_once 'php/data/_BasicRec.php';
//
class HttpQuery extends BasicRec {
  /* 
  public $field1;
  public $field2;
  */
  //
  public function submit($url) {  
    $request = static::asGet($url);
    $response = file_get_contents($request);
    return $response;
  }
  public function asGet($url) {  // string url
    $qs = $this->getQueryStrings();
    $url .= '?' . static::qsjoin($qs);
    return $url;
  }
  //
  protected function getQueryStrings() {
    $vars = get_object_vars($this);
    foreach ($vars as $fid => &$value)
      $value = urlencode($value);
    return $vars;
  }
  //
  protected static function qsjoin($qs) {
    $a = array();
    foreach ($qs as $key => $value)
      $a[] = "$key=$value";
    return implode('&', $a);
  }
}