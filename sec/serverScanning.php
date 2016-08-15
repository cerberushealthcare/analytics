<?php
require_once 'server.php';
require_once 'php/data/rec/sql/Scanning.php';
require_once 'php/data/pdf/Pdf_ScanIndex.php';
//
try {
  LoginSession::verify_forServer()->requires($login->Role->Artifact->scan);
  switch ($action) {
    //
    case 'getUnindexed':
      $recs = Scanning::getUnindexedFiles($_GET['id']);
      AjaxResponse::out($action, $recs);
      exit;
    case 'getIndexedToday':
      $recs = Scanning::getIndexedToday();
      AjaxResponse::out($action, $recs);
      exit;
    case 'saveIndex':
      logit_r($obj, 'saveIndex');
      $rec = Scanning::saveIndex($obj->rec, $obj->sfids);
      AjaxResponse::out($action, $rec);
      exit;
    case 'getIndex':
      $rec = Scanning::getIndex($_GET['id']);
      AjaxResponse::out($action, $rec);
      exit;
    case 'reviewed':
      $rec = Scanning::saveAsReviewed($_GET['id']);
      AjaxResponse::out($action, $rec);
      exit;
    case 'deleteIndex':
      $id = Scanning::deleteIndex($_GET['id']);
      AjaxResponse::out($action, $id);
      exit;
    case 'deleteFile':
      $id = Scanning::deleteFile($_GET['id']);
      AjaxResponse::out($action, $id);
      exit;
    case 'rotate':
      $id = Scanning::rotate($_GET['id']);
      AjaxResponse::out($action, $id);
      exit;
    case 'splitBatch':
      Scanning::splitBatch($_GET['id']);
      AjaxResponse::out($action, null);
      exit;
    case 'download':
      $pdf = Pdf_ScanIndex::fetch($obj->id);
      $pdf->download();
      exit;
  }
} catch (Exception $e) {
  AjaxResponse::exception($e);
}
