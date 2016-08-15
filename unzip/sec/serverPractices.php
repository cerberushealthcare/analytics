<?php
require_once 'server.php';
require_once 'php/data/rec/sql/UserGroups.php'; 
//
try {
  LoginSession::verify_forServer()->requires($login->super);
  switch ($action) {
    //
    case 'get':
      $practices = UserGroups::getChildren_withAddress();
      AjaxResponse::out($action, $practices);
      exit;
  }
} catch (Exception $e) {
  AjaxResponse::exception($e);
}
