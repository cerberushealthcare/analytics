<?php
require_once 'server.php';
require_once 'php/data/rec/sql/Snomeds_Admin.php';
//
try {
  LoginSession::verify_forUser()->requires($login->admin);
  switch ($action) {
    //
    case 'getAll':
      $recs = Snomeds_Admin::getAll();
      AjaxResponse::out($action, $recs);
      break;
    case 'save':
      $rec = Snomeds_Admin::save($obj);
      AjaxResponse::out($action, $rec);
      break;
    case 'delete':
      Snomeds_Admin::delete($_GET['id']);
      AjaxResponse::out($action, $_GET['id']);
      break;
    case 'deleteMany':
      Snomeds_Admin::deleteMany($obj);
      AjaxResponse::out($action, null);
      break;
  }
} catch (Exception $e) {
  AjaxResponse::exception($e);
}
      