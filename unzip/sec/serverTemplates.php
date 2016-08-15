<?php
require_once 'server.php';
require_once 'php/c/template-entry/TemplateEntry.php';
//
try { 
  LoginSession::verify_forServer();
  switch ($action) {
    //
    case 'getMyPar':
      $rec = TemplateEntry::getMyPar($obj->tid, $obj->sid, $obj->puid);
      AjaxResponse::out($action, $rec);
      exit;
    case 'getMyParOe':
      $rec = TemplateEntry::getMyPar_OrderEntry();
      AjaxResponse::out($action, $rec);
      exit;
    case 'getMyParAppt':
      $rec = TemplateEntry::getMyPar_ApptCard();
      AjaxResponse::out($action, $rec);
      exit;
    case 'getPar':
      $rec = TemplateEntry::getPar($_GET['id']);
      AjaxResponse::out($action, $rec);
      exit;
    case 'getImmunEntry':
      require_once 'php/data/rec/sql/Immuns.php';
      $rec = Immuns::getParAndLots($_GET['id']);
      AjaxResponse::out($action, $rec);
      exit;
    case 'getIolEntry':
      require_once 'php/data/rec/sql/Templates_IolEntry.php';
      $rec = Templates_IolEntry::getEntry($_GET['id']);
      AjaxResponse::out($action, $rec);
      exit;
    case 'getIols':
      require_once 'php/data/rec/sql/Templates_IolEntry.php';
      $map = Templates_IolEntry::getIols();
      AjaxResponse::out($action, $map);
      exit;
    case 'saveCustomOthers':
      TemplateEntry::saveCustomOthers($obj);
      exit;
    case 'getPmhxQuestion':
      $rec = TemplateEntry::getPmhxQuestion();
      AjaxResponse::out($action, $rec);
      exit;
    case 'getPshxQuestion':
      $rec = TemplateEntry::getPshxQuestion();
      AjaxResponse::out($action, $rec);
      exit;
    case 'getTemplates':
      $recs = TemplateEntry::getTemplates();
      AjaxResponse::out($action, $recs);
      exit;
    case 'getMap':
      $recs = TemplateEntry::getMap($_GET['id']);
      AjaxResponse::out($action, $recs);
      exit;
    case 'getParWithInjects':
      $recs = TemplateEntry::getParWithInjects($_GET['id']);
      AjaxResponse::out($action, $recs);
      exit;
  }
} catch (Exception $e) {
  AjaxResponse::exception($e);
}
