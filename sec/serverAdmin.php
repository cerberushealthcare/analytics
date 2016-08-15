<?php
require_once 'server.php';
require_once 'php/data/rec/sql/UserLoginReqs.php';
//
try {
  LoginSession::verify_forUser()->requires($login->admin);  
  switch ($action) {
    /**
     * Get login requirements
     */
    case 'getLoginReqs':
      $recs = UserLoginReqs::getAll();
      AjaxResponse::out($action, $recs);
      break;
    /**
     * Save login requirement
     */
    case 'saveLoginReq':
      $rec = UserLoginReqs::save($obj);
      AjaxResponse::out($action, $rec);
      break;
  }
} catch (Exception $e) {
  AjaxResponse::exception($e);
}
  