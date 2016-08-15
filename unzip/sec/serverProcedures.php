<?php
ob_start('ob_gzhandler');
require_once 'server.php';
require_once 'php/data/rec/sql/Procedures.php';
require_once 'php/data/rec/sql/Procedures_Admin.php';
require_once 'php/data/pdf/Pdf_Proc.php';
//
try {
  LoginSession::verify_forServer()->requires($login->Role->Patient->facesheet);
  switch ($action) {
    //
    case 'getAll':
      $procs = Procedures::getAll($_GET['id']);
      AjaxResponse::out($action, $procs);
      exit;
    case 'get':
      $proc = Procedures::get($_GET['id']);
      AjaxResponse::out($action, $proc);
      exit;
    case 'saveProc':
      $rec = Procedures::saveProc($obj);
      AjaxResponse::out($action, $rec);
      exit;
    case 'savePanel':
      Procedures::savePanel($obj);
      AjaxResponse::out($action);
      exit;
    case 'delete':
      $id = Procedures::delete($_GET['id']);
      AjaxResponse::out($action, $id);
      exit;
    case 'saveResult':
      $rec = Procedures::saveResult($obj->procId, $obj->result);
      AjaxResponse::out($action, $rec);
      exit;
    case 'deleteResult':
      $id = Procedures::deleteResult($_GET['id']);
      AjaxResponse::out($action, $id);
      exit;
    case 'getResultHistory':
      $recs = Procedures::getResultHistory($obj);
      AjaxResponse::out($action, $recs);
      exit;
    case 'record':  // record administrative IPC
      Proc_Admin::record($_GET['cid'], null, null, $_GET['ipc']);
      AjaxResponse::out($action);
      exit;
    case 'download':
      $pdf = Pdf_Proc::fetch($obj->id);
      $pdf->download();
      exit;
  }
} catch (Exception $e) {
  AjaxResponse::exception($e);
}
  