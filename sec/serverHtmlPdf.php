<?php
require_once 'php/data/json/JAjaxMsg.php'; 
//
if (LoginDao::authenticateSession() < 0) {
  $m = new JAjaxMsg('save-timeout', 'null');
  echo $m->out();
  exit;
}
if (isset($_GET['action'])) {
  $action = $_GET['action'];
  Logger::debug(currentUrl());
} else {
  $_POST['obj'] = stripslashes($_POST['obj']);
  $action = $_POST['action'];
  $obj = jsondecode($_POST['obj']);
  Logger::debug(currentUrl());
  Logger::debug_r($_POST, '$_POST');;
}
try {
  switch ($action) {
    //
    case 'createVisit':
      $rec = HtmlPdfDocs::createVisit($obj->cid, $obj->dos, $obj->out);
      $m = new JAjaxMsg($action, $rec);
      break;
    //
    default:
      $m = new JAjaxMsg('error', $action);
  }
} catch (DisplayableException $e) {
  $m = JAjaxMsg::constructError($e);
} catch (Exception $e) {
  $m = JAjaxMsg::constructError(Logger::logException($e));
} 
if ($m != null) 
  echo $m->out();