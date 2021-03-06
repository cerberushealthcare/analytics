<?php
require_once 'server.php';
require_once 'php/data/rec/sql/Providers.php';
//
try {
  LoginSession::verify_forServer()->requires($login->Role->Artifact->scan);
  switch ($action) {
    //
    case 'getAll':
      $recs = Providers::getAll();
      AjaxResponse::out($action, $recs);
      exit;
    case 'getAllActive':
      $recs = Providers::getAll(true);
      AjaxResponse::out($action, $recs);
      exit;
    case 'save':
      $rec = Providers::save($obj);
      AjaxResponse::out($action, $rec);
      exit;
    case 'delete':
      Providers::delete($id);
      AjaxResponse::out($action, $id);
    //
    case 'getFacilities':
      $recs = Providers::getFacilities();
      AjaxResponse::out($action, $recs);
      exit;
    case 'saveFacility':
      $rec = Providers::saveFacility($obj);
      AjaxResponse::out($action, $rec);
      exit;
    case 'deleteFacility':
      Providers::deleteFacility($id);
      AjaxResponse::out($action, $id);
      exit;
  }
} catch (Exception $e) {
  AjaxResponse::exception($e);
}
  