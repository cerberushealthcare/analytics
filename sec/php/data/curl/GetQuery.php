<?php
require_once 'php/data/_BasicRec.php';
require_once 'php/data/curl/Curl.php';
//
class GetQuery extends BasicRec {
  /*
  public $field1;
  public $field2;
   */
  //
  public function submit($url) {
    $url .= $this->getQueryStrings($url); 
    $curl = Curl::asReturn($url);
    if ($this->getDebug())
      $curl->debug();
    $response = $curl->exec();
    return $response;
  }
  //
  protected function getQueryStrings($url) {
    $vars = get_object_vars($this);
    $a = array();
    foreach ($vars as $fid => $value) 
      $a[] = $this->getEntry($this->getFormFid($fid), $value);
    return "?" . implode('&', $a);
  }
  protected function getEntry($fid, $value) {
    $fid = $this->getFormFid($fid);
    return $fid . "=" . $value;
  }
  protected function getFormFid($fid) {
    return $fid;
  }
  protected function getDebug() {
    return false;
  }
}
