<?php
require_once 'server.php';
//
try {
  LoginSession::verify_forServer();
  switch ($action) {
    //
    case 'setup':
      $left = $login->setupTrial();
      AjaxResponse::out($action, $left);
      exit;
  }
} catch (Exception $e) {
  AjaxResponse::exception($e);
}
  