<?php
require_once 'server.php';
require_once 'php/c/reporting/Reporting.php'; 
require_once 'php/data/csv/report-download/ReportCsvFile.php';
//
try {
  LoginSession::verify_forServer()->requires($login->Role->Report->any());
  switch ($action) {
    //
    case 'newReport':
      $report = Reporting::newReport($_GET['id']);
      AjaxResponse::out($action, $report);
      exit;
    case 'getJoin':
      $join = Reporting::getJoin($_GET['id']);
      AjaxResponse::out($action, $join);
      exit;
    case 'getReport':
      $report = Reporting::getReport($_GET['id']);
      AjaxResponse::out($action, $report);
      exit;
    case 'deleteReport':
      $report = Reporting::deleteReport($_GET['id']);
      AjaxResponse::out($action, $_GET['id']);
      exit;
    case 'getStubs':
      $stubs = Reporting::getStubs();
      AjaxResponse::out($action, $stubs);
      exit;
    case 'save':
      $report = Reporting::save($obj);
      AjaxResponse::out($action, $report);
      exit;
    case 'generate':
      //set_time_limit(120);
      $report = Reporting::generate($obj);
      AjaxResponse::out($action, $report);
      exit;
    case 'download':
      logit_r($obj, 'obj');
      $duenow = $_POST['duenow'];
      $report = Reporting::generateForDownload($obj, $_POST['num'], $_POST['nc'], $duenow);
      $recs = $duenow ? $report->recsDenom : $report->recs;
      $file = ReportCsvFile::from($report, $recs);
      $file->download();
      exit;
    case 'fetchImmunAudits':
      $recs = Reporting::fetchImmunAudits($obj->cid, $obj->immunId);
      AjaxResponse::out($action, $recs);
      exit;
    case 'test':
      exit;
  }
} catch (Exception $e) {
  AjaxResponse::exception($e);
}
  