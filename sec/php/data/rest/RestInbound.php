<?php 
//
class RestInbound {
  //
  public $username;
  public $password;
  public $body;
  //
  static function get() {
    $me = new static();
    $me->setBody();
    $me->setAuth();
    return $me;
  } 
  static function setHeader_BadRequest() {
    header("HTTP/1.1 400 Bad Request");
  }
  static function setHeader_Unauthorized() {
    header("HTTP/1.1 401 Unauthorized");
  }
  //
  public function hasAuth() {
    if (! empty($this->username) && ! empty($this->password))
      return true; 
  }
  public function hasBody() {
    if (! empty($this->body))
      return true;
  }
  public function isComplete() {
    return $this->hasAuth() && $this->hasBody();
  }
  //
  protected function setBody() {
    $this->body = file_get_contents("php://input");
  }
  protected function setAuth() {
    $username = "";
    $password = "";
    if (isset($_SERVER['PHP_AUTH_USER'])) {
      $username = $_SERVER['PHP_AUTH_USER'];
      $password = $_SERVER['PHP_AUTH_PW'];
    } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
      if (strpos(strtolower($_SERVER['HTTP_AUTHORIZATION']), 'basic') === 0)
        list($username, $password) = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
    }
    $this->username = $username;
    $this->password = $password;
  }
}
