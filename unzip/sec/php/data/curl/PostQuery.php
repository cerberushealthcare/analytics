<?php
require_once 'php/data/_BasicRec.php';
require_once 'php/data/curl/Curl.php';
//
class PostQuery extends BasicRec {
  /*
  public $field1;
  public $field2;
   */
  public function submit($url) {
    $data = $this->getFormData();
    $curl = Curl_Post::create($url, $data);
    $response = $curl->exec();
    return $response;
  }
  //
  protected function getFormData() {
    $vars = get_object_vars($this);
    $a = array();
    foreach ($vars as $fid => $value) 
      $a[] = $this->getEntry($this->getFormFid($fid), $value);
    return implode('&', $a);
  }
  protected function getEntry($fid, $value) {
    $fid = $this->getFormFid($fid);
    return $fid . "=" . $value;
  }
  protected function getFormFid($fid) {
    return $fid;
  }
}
