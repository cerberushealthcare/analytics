<?php
require_once 'server.php';
require_once 'php/c/visit-summaries/VisitSummaries.php';
//
try {
  LoginSession::verify_forServer()->requires($login->Role->Patient->facesheet);
  switch ($action) {
    //
    case 'getPending':
      $rec = VisitSummaries::getPending($id);
      AjaxResponse::out($action, $rec);
      exit;
    case 'finalize':  // submitted by VisitSummaryPop
      $rec = VisitSummaries::finalize($obj);
      VisitSummaries::printPdf($rec);
      exit;
    case 'reprint':
      VisitSummaries::reprintPdf($obj->cid, $obj->fid);
      exit;
  }
} catch (Exception $e) {
  AjaxResponse::exception($e);
}