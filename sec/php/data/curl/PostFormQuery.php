<?php
require_once 'php/data/_BasicRec.php';
require_once 'php/data/curl/Curl.php';
//
class PostFormQuery extends BasicRec {
  /*
  public $field1;
  public $field2;
   */
  const EOL = "\r\n";
  //
  public function submit($url) {
    $mb = $this->getMimeBoundary();
    $data = $this->getFormData($mb);
    $headers = $this->getHeaders($mb, strlen($data));
    $curl = Curl_Post::create($url, $data, $headers);
    if ($this->getDebug())
      $curl->debug();
    $response = $curl->exec();
    return $response;
  }
  //
  protected function getFormData($mb) {
    $vars = get_object_vars($this);
    $a = array();
    foreach ($vars as $fid => $value) 
      $a[] = $this->getEntry($fid, $value, $mb);
    $a[] = "--$mb--" . static::EOL . static::EOL;
    return implode('', $a);
  }
  protected function getHeaders($mb, $len) {
    return array(
    	"Content-Type: multipart/form-data; boundary=$mb",
      "Content-Length: $len");
  }
  protected function getEntry($fid, $value, $mb) {
    $fid = $this->getFormFid($fid);
    return "--$mb" 
      . static::EOL
      . "Content-Disposition: form-data; name=\"$fid\""
      . static::EOL
      . static::EOL
      . $value
      . static::EOL;
  }
  protected function getMimeBoundary() {
    return md5(time());
  }
  protected function getFormFid($fid) {
    return $fid;
  }
  protected function getDebug() {
    return false;
  }
}
