<?php
require_once 'php/dao/LoginDao.php';
require_once 'php/dao/UserDao.php';
require_once 'php/dao/MsgDao.php';
require_once 'php/data/json/JAjaxMsg.php';
require_once 'php/data/rec/sql/MsgThread.php';
//
if (LoginDao::authenticateSession() < 0) {
  $m = new JAjaxMsg('save-timeout', 'null');
  echo $m->out();
  exit;
}
if (isset($_GET['action'])) {
  $action = $_GET['action'];
  $id = geta($_GET, 'id');
  if ($action != 'getMyInboxCt') logit('serverMsg.php?' . implode_with_keys('&', $_GET));
} else {
  $_POST['obj'] = stripslashes($_POST['obj']);
  $action = $_POST['action'];
  $obj = jsondecode($_POST['obj']);
  logit('serverMsg.php (posted)');
  logit_r($_POST);
}
switch ($action) {
  case 'getMyInboxCt':
    $ct = MsgThread::fetchMyUnreadCt();
    $m = new JAjaxMsg($action, $ct);
    break;
  case 'getMyInbox':
    $recs = MsgThread::fetchAllFromInbox();
    $m = new JAjaxMsg($action, jsonencode($recs));
    break;
  case 'getMySent':
    $recs = MsgThread::fetchAllFromSent();
    $m = new JAjaxMsg($action, jsonencode($recs));
    break;
  case 'getClientThreads':
    $recs = MsgThread::fetchAllByClient($id);
    $m = new JAjaxMsg($action, jsonencode($recs));
    break;
  case 'getThread':
    $rec = MsgThread::fetchForReading($id);
    if ($rec->Client) 
      $rec->facesheet = jsondecode(FacesheetDao::getMsgFacesheet($rec->Client->clientId)->out());
    $m = new JAjaxMsg($action, jsonencode($rec));
    break;
  case 'getFacesheet':
    $facesheet = FacesheetDao::getMsgFacesheet($id);
    $m = new JAjaxMsg($action, $facesheet->out());
    break;
  case 'reply':
    MsgThread::addPostReply($obj);
    $m = new JAjaxMsg($action, null);
    break;
  case 'complete':
    MsgThread::addPostComplete($obj);
    $m = new JAjaxMsg($action, null);
    break;
  case 'newThread':
    MsgThread::newThread($obj);
    $m = new JAjaxMsg($action, null);
    break;
  case 'getTemplatePar': 
    $m = new JAjaxMsg($action, ServerMsg::getTemplatePar($id));
    break;
  case 'getSessionStubs': 
    $m = new JAjaxMsg($action, ServerMsg::getSessionStubs($id));
    break;
  case 'previewThread':
    $m = new JAjaxMsg($action, ServerMsg::previewThread($id));
    break;
  default:
    $m = new JAjaxMsg('error', $action);
}
if ($m != null) {
  echo $m->out();
}

class ServerMsg {
  public static function getTemplatePar($pid) {
    return JsonDao::getJParInfosByPid($pid);
  }
  public static function getSessionStubs($cid) {
    $stubs = SchedDao::getJSessionStubsForClient($cid);
    return assocOutArray($stubs);
  }
  public static function previewThread($mtid) {
    return jsonencode(MsgDao::previewThread($mtid));
  }
}
?>