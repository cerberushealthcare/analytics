<?php
ob_start('ob_gzhandler');
require_once 'server.php';
require_once 'php/data/rec/sql/IProcCodes.php';
require_once 'php/c/health-maint/HealthMaint.php';
require_once 'php/data/csv/report-download/ReportCsvFile.php';
//
try {
  LoginSession::verify_forServer()->requires($login->Role->Patient->facesheet);
  switch ($action) {
    //
    case 'getAll':
      $cat = geta($_GET, 'id');
      $recs = IProcCodes::getAll($cat);
      AjaxResponse::out($action, $recs);
      exit;
    case 'getIpcHmsFor':
      $recs = HealthMaint::getForClient($_GET['id']);
      AjaxResponse::out($action, $recs);
      exit;
    case 'saveCustom':
      $login->requires($login->Role->Patient->cds);
      $rec = IProcCodes::saveCustom($obj);
      AjaxResponse::out($action, $rec);
      exit;
    case 'saveIpcHm':
      $login->requires($login->Role->Patient->cds);
      $rec = HealthMaint::save($obj);
      AjaxResponse::out($action, $rec);
      exit;
    case 'delIpcHm':
      $login->requires($login->Role->Patient->cds);
      $rec = HealthMaint::del($obj);
      AjaxResponse::out($action, $rec);
      exit;
    case 'allHmIpc':
      $recs = HealthMaint::getAllIpcs();
      AjaxResponse::out($action, $recs);
      exit;
    case 'recordReminders':
      HealthMaint::recordReminders($obj->cids, $obj->name);
      AjaxResponse::out($action);
      exit;
    case 'allDueNow':
      $recs = HealthMaint::getAllDueNow($_GET['id']);
      AjaxResponse::out($action, $recs);
      exit;
    case 'downloadAllDueNow':
      $recs = HealthMaint::getAllDueNow($obj->ipc);
      //$recs = Client_Rep::reviveAll($obj->recs);
      $file = ReportCsvFile::asAllDueNow($recs);
      $file->download();
      exit;
  }
} catch (Exception $e) {
  AjaxResponse::exception($e);
}
  