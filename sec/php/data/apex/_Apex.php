<?php
require_once 'php/data/curl/Curl.php';
//
class ApexQuery {
  //
  public $base;
  public $app;
  public $page;
  public $sessionId;
  public $request;
  public $debug;
  public $clearCache;
  public /*ApexItems*/ $Items;
  //
  /**
   * Submit a request via CURL (expected text response)
   * @param string $sessionId (optional, from prior login) 
   * @param string $cookie (optional, from prior login) 
   * @return CurlResponse
   */
  public function submit($sessionId = null, $cookie = null) {
    $url = $this->asUrl($sessionId);
    if ($cookie == null)
      $curl = Curl_Apex::create_asLogin($url);
    else
      $curl = Curl_Apex::create_inSession($url, $cookie);
    $response = $curl->exec();
    return $response;
  } 
  /**
   * Navigate to request (expected HTML response) 
   * Enter description here ...
   */
  public function navigate() {
    $url = $this->asUrl();
    header("Location: $url");
  }
  //
  protected function asUrl($sessionId = null) {
    if ($sessionId)
      $this->sessionId = $sessionId;
    $base = $this->base;
    $qs = $this->getQueryStrings();
    $q = implode(':', $qs);
    $url = "$base/ords/pms/f?p=$q";
    return $url;
  }
  protected function getQueryStrings() {
    return array(
      $this->app,
      $this->page,
      $this->sessionId,
      $this->request,
      $this->debug,
      $this->clearCache,
      $this->Items->asNameString(),
      $this->Items->asValueString());
  }
}
class ApexItems {
  //
  public function asNameString() {
    $vars = $this->flattenVars();
    $names = array_keys($vars);
    return implode(',', $names);
  }
  public function asValueString() {
    $vars = $this->flattenVars(false);
    return implode(',', $vars);
  }
  //
  protected function flattenVars($reset = true) {
    static $vars;
    if ($reset || empty($vars)) {
      $vars = array();
      $fields = get_object_vars($this);
      foreach ($fields as $fid => $v) {
        if (is_array($v)) 
          $this->push($vars, $fid, $v);
        else
          $vars[$fid] = $v;
      }
    } 
    return $vars;
  }
  protected function push(&$vars, $fid, $values) {
    for ($i = 0; $i < count($values); $i++) {
      $value = $values[$i];
      if (! empty($value)) 
        $vars[$fid . ($i + 1)] = $value;
      else
        return;
    } 
  }
}
class Curl_Apex extends Curl {
  //
  public function exec() {
    $response = parent::exec();
    $this->close();
    return $response;
  }
  //
  static function create_asLogin($url) {
    $me = static::create($url)->withHeader()->debug();
    return $me;
  }
  static function create_inSession($url, $cookie) {
    $me = static::create($url)->cookie($cookie)->withHeader()->verbose();
    return $me;
  }
  protected static function create($url) {
    return static::asReturn($url)->ssl()->followLocation()->cookieFile('');
  }
}