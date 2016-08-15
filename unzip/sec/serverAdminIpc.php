<?php
ob_start('ob_gzhandler');
require_once 'server.php';
require_once 'php/data/rec/sql/IprocCodes_Admin.php';
//
try {
  LoginSession::verify_forUser()->requires($login->admin);
  switch ($action) {
    //
    case 'getAll':
      $recs = IProcCodes_Admin::getAll();
      AjaxResponse::out($action, $recs);
      break;
    case 'save':
      $rec = IProcCodes_Admin::save($obj);
      logit_r($rec, 'rec after saving');
      AjaxResponse::out($action, $rec);
      break;
    case 'delete':
      IProcCodes_Admin::delete($_GET['id']);
      AjaxResponse::out($action, $_GET['id']);
      break;
    case 'deleteMany':
      IProcCodes_Admin::deleteMany($obj);
      AjaxResponse::out($action, null);
      break;
    case 'copyOptions':
      IProcCodes_Admin::copyToQuestion($obj->ids, $obj->qid);
      AjaxResponse::out($action, null);
      break;
    case 'getQuestion':
      $rec = IProcCodes_Admin::getQuestion($_GET['id']);
      AjaxResponse::out($action, $rec);
      break;
  }
} catch (Exception $e) {
  AjaxResponse::exception($e);
}
