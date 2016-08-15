<?php
require_once 'php/data/LoginSession.php';
require_once 'php/dao/UserDao.php';
require_once 'php/dao/FacesheetDao.php';
require_once 'php/data/json/JAjaxMsg.php';
require_once 'php/data/pdf/Pdf_Message.php';
require_once 'php/data/rec/sql/Messaging.php';
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
  LoginSession::verify_forServer()->requires($login->Role->Message->general);
  switch ($action) {
    case 'getMyInbox':
      $threads = Messaging::getMyInboxThreads();
      $m = new JAjaxMsg($action, jsonencode($threads));
      break;
    case 'getMySent':
      $threads = Messaging::getMySentThreads();
      $m = new JAjaxMsg($action, jsonencode($threads));
      break;
    case 'getClientThreads':
      $login->requires($login->Role->Message->patient);
      $threads = Messaging::getThreadsForClient($id);
      $m = new JAjaxMsg($action, jsonencode($threads));
      break;
    case 'getThread':
      $thread = Messaging::openThread($id, $_GET['for']);
      if ($thread->ClientStub) {
        $login->requires($login->Role->Message->patient);
        $thread->facesheet = jsondecode(FacesheetDao::getMsgFacesheet($thread->ClientStub->clientId)->out());
      }
      $m = new JAjaxMsg($action, jsonencode($thread));
      break;
    case 'getFacesheet':
      $login->requires($login->Role->Message->patient);
      $facesheet = FacesheetDao::getMsgFacesheet($id);
      $m = new JAjaxMsg($action, $facesheet->out());
      break;
    case 'reply':
      Messaging::postReply($obj->id, $obj->to, $obj->html, $obj->data, $obj->portalUserId, $obj->stub, $obj->email);
      $m = new JAjaxMsg($action, null);
      break;
    case 'complete':
      Messaging::postComplete($obj->id, $obj->html, $obj->data, $obj->stub);
      $m = new JAjaxMsg($action, null);
      break;
    case 'quickComplete':
      Messaging::newThreadComplete($obj->cid, $obj->priority, $obj->subject, $obj->html, $obj->data, $obj->stub);
      $m = new JAjaxMsg($action, null);
      break;
    case 'newThread':
      Messaging::newThread($obj->cid, $obj->priority, $obj->subject, $obj->to, $obj->html, $obj->data, $obj->portalUserId, $obj->stub, $obj->email, get($obj, 'dateActive'));
      $m = new JAjaxMsg($action, null);
      break;
    case 'getTemplatePar': 
      $m = new JAjaxMsg($action, ServerMsg::getTemplatePar($id));
      break;
    case 'getSessionStubs': 
      $m = new JAjaxMsg($action, ServerMsg::getSessionStubs($id));
      break;
    //case 'previewThread':
      //$m = new JAjaxMsg($action, ServerMsg::previewThread($id));
      //break;
    case 'download':
      $pdf = Pdf_Message::fetch($obj->id);
      $pdf->download();
      exit;
    default:
      $m = new JAjaxMsg('error', $action);
  }
} catch (Exception $e) {
  $m = JAjaxMsg::constructError($e);
}
if ($m != null) 
  echo $m->out();
//
class ServerMsg {
  public static function getTemplatePar($pid) {
    return JsonDao::getJParInfosByPid($pid);
  }
  public static function getSessionStubs($cid) {
    $stubs = SchedDao::getJSessionStubsForClient($cid);
    return assocOutArray($stubs);
  }
  //public static function previewThread($mtid) {
    //return jsonencode(MsgDao::previewThread($mtid));
  //}
}
?>