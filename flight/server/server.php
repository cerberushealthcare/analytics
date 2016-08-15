<?php
//
if (isset($_GET['action'])) {
  $action = $_GET['action'];
} else {
  $_POST['obj'] = stripslashes($_POST['obj']);
  $action = $_POST['action'];
  $obj = json_decode($_POST['obj']);
}
if (isset($_GET['debug']))
  echo '<pre>';
//
class AjaxResponse {
  //
  public $r;
  //
  public function out() {
    if (isset($_GET['debug']))
      echo htmlentities(print_r($this, true));
    else
      echo json_encode($this);  
  }
  //
  static function from($response) {
    $me = new static();
    $me->r = $response;
    return $me;
  }
  static function asNull() {
    return static::from(null);
  }
}  
class AjaxResponseJson extends AjaxResponse {
  //
  public function out() {
    if (isset($_GET['debug']))
      echo htmlentities(print_r($this, true));
    else
      echo "{\"r\":$this->r}";  
  }
}
class AjaxResponseError extends AjaxResponse {
  //
  public $error;
  //
  static function from($ex) {
    $error = static::makeErrorObject($ex);
    $me = parent::from($error);
    $me->error = 1;
    return $me;
  }
  //
  protected static function makeErrorObject($ex) {
    $obj = new stdClass();
    $obj->type = get_class($e);
    $obj->message = $e->getMessage();
    return $obj;
  }
}