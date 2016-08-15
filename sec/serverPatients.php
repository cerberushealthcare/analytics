<?php
ob_start('ob_gzhandler');
require_once 'server.php';
require_once 'php/c/patient-list/PatientList.php';
require_once 'php/c/patient-entry/PatientEntry.php';
//
try {
  LoginSession::verify_forServer();
  switch ($action) {
    //
    case 'search':
      $recs = PatientList::search($obj->last, $obj->first, $obj->uid, $obj->dob, $obj->active);
      AjaxResponse::out($action, $recs);
      exit;
    case 'getMru':
      $recs = PatientList::mru($_GET['active']);
      AjaxResponse::out($action, $recs);
      exit;
    case 'getPage':
      $page = $_GET['page'];
      $active = $_GET['show'] == '0';
      $page = PatientList::page($page, $active);
      AjaxResponse::out($action, $page);
      exit;
    case 'getNextUid':
      $uid = PatientEntry::getNextUid();
      AjaxResponse::out($action, $uid);
      exit;
    case 'add':
      $rec = PatientEntry::add($obj);
      AjaxResponse::out($action, $rec);
      exit;
    case 'addDupeOk':
      $rec = PatientEntry::add($obj, true);
      AjaxResponse::out($action, $rec);
      exit;
    case 'getLangs':
      $recs = PatientEntry::getLangs();
      AjaxResponse::out($action, $recs);
      exit;
  }
} catch (Exception $e) {
  AjaxResponse::exception($e);
}
  