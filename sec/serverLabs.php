<?php
require_once 'server.php';
require_once 'php/data/rec/sql/HL7_Labs.php';
//
try {
  LoginSession::verify_forServer()->requires($login->Role->Artifact->labs);
  switch ($action) {
    //
    case 'getInboxCt':
      $ct = HL7_Labs::getInboxCt();
      AjaxResponse::out($action, $ct);
      exit;
    case 'getInboxes':
      $recs = HL7_Labs::getInboxes();
      AjaxResponse::out($action, $recs);
      exit;
    case 'removeInbox':
      HL7_Labs::removeInbox($_GET['id']);
      AjaxResponse::out($action, null);
      exit;
    case 'getInboxMessage':
      $rec = HL7_Labs::getInboxMessage($_GET['id']);
      AjaxResponse::out($action, $rec);
      exit;
    case 'getRecon':
      $rec = HL7_Labs::getLabRecon($_GET['id']);
      AjaxResponse::out($action, $rec);
      exit;
    case 'saveRecon':
      $rec = HL7_Labs::saveLabRecon($obj->id, $obj->msg);
      logit_r($rec, 'return of saverecon');
      AjaxResponse::out($action, $rec);
      exit;
    case 'setClient':
      $rec = HL7_Labs::assignInboxToClient($_GET['id'], $_GET['cid']);
      AjaxResponse::out($action, $rec);
      exit;
  }
} catch (Exception $e) {
  AjaxResponse::exception($e);
}
  