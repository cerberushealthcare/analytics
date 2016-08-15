<?php
ob_start('ob_gzhandler');
require_once 'server.php';
require_once 'php/data/rec/sql/Dashboard.php';
//
try {
  LoginSession::verify_forServer();
  switch ($action) {
    //
    case 'get':
      $dash = Dashboard::get();
      AjaxResponse::out($action, $dash);
      exit();
    case 'getAppts':
      $dash = Dashboard::getAppts($obj->date, $obj->provider);
      AjaxResponse::out($action, $dash);
      exit();
    case 'getMessages':
      $dash = Dashboard::getMessages($id);
      AjaxResponse::out($action, $dash);
      exit();
    case 'getLoginHist':
      $recs = Dashboard::getLoginHist();
      AjaxResponse::out($action, $recs);
      exit();
  }
} catch (Exception $e) {
  AjaxResponse::exception($e);
}
  