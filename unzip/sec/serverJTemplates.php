<?php
require_once 'php/data/LoginSession.php';
require_once 'php/dao/UserDao.php';
require_once 'php/data/json/JAjaxMsg.php';
require_once 'php/data/rec/sql/Templates_Map.php';
//
if (isset($_GET['action'])) {
  $action = $_GET['action'];
  $id = geta($_GET, 'id');
  Logger::debug(currentUrl());
} else {
  $_POST['obj'] = stripslashes($_POST['obj']);
  $action = $_POST['action'];
  $obj = jsondecode($_POST['obj']);
  Logger::debug(currentUrl());
  Logger::debug_r($_POST, '$_POST');
}
try { 
  LoginSession::verify_forServer();
  switch ($action) {
    //
    case 'getParInfos':
      $m = new JAjaxMsg($action, JsonDao::getJParInfosByPid($id));
      break;
    case 'debug':
      $m = jsondecode(JsonDao::getJParInfosByPid($id));
      p_r($m);
      exit;
      break;
    case 'getParInfosByRef':
      $m = new JAjaxMsg($action, JsonDao::getJParInfosByRef($obj->ref, $obj->tid));
      break;
    case 'preview':
      $m = new JAjaxMsg($action, TemplateReaderDao::parPreview($id, $_GET["tid"], $_GET["nd"]));
      break;
    case 'cinfo':
      $rec = Templates_Map::getCinfo($_GET['id']);
      $m = new JAjaxMsg($action, jsonencode($rec));
      break;
    default:
      $m = new JAjaxMsg('error', $action);
  }
} catch (Exception $e) {
  $m = JAjaxMsg::constructError($e);
}
if ($m != null) 
  echo $m->out();
