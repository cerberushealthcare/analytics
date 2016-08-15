<?php
require_once 'php/data/LoginSession.php';
require_once 'php/data/rec/AjaxResponse.php';
//
ob_start();
//if (MyEnv::$LOG) {
//  error_reporting(E_ALL ^ (E_STRICT | E_NOTICE | E_WARNING | E_DEPRECATED));
//  ini_set('display_errors', '1');
//}
if (isset($_GET['debug']))
  echo '<pre>';
else
  register_shutdown_function('onfatal');
Logger::debug_server();
if (isset($_GET['action'])) {
  $action = $_GET['action'];
  $id = geta($_GET, 'id');
} else {
  //$_POST['obj'] = ($_POST['obj']);
  $action = $_POST['action'];
  $obj = json_decode($_POST['obj']);
}
//
function onfatal() {
  try {
    $error = error_get_last();
    //logit_r($error, 'onfatal');
    if ($error && $error['type'] == 1) {
      $headers = array();
      //if (! headers_sent())
        //$headers = apache_response_headers();
      ob_end_clean();
      ob_start();
      if (! empty($headers))
        foreach ($headers as $name => $value)
          header("$name: $value");
      throw new FatalException($error);
    }
  } catch (Exception $e) {
    AjaxResponse::exception($e);
  }
}
class FatalException extends Exception {
  public function __construct($error) {
    $this->message = 'Fatal Exception';
    $this->code = $error;
  }
}