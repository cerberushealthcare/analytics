<?php
require_once 'server.php';
require_once 'php/data/rec/sql/Templates_Admin.php';
//
try {
  LoginSession::verify_forUser()->requires($login->admin);  
  switch ($action) {
    //
    case 'getCinfos':
      $recs = Templates_Admin::getCinfos($_GET['id']);
      AjaxResponse::out($action, $recs);
      break;
    case 'saveCinfo':
      $rec = Templates_Admin::saveCinfo($obj);
      AjaxResponse::out($action, $rec);
      break;
    case 'deleteCinfo':
      Templates_Admin::deleteCinfo($_GET['id']);
      AjaxResponse::out($action, $_GET['id']);
      break;
  }
} catch (Exception $e) {
  AjaxResponse::exception($e);
}
  