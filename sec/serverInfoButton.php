<?php
require_once 'server.php';
require_once 'php/c/info-button/InfoButton.php';
//
try {
  LoginSession::verify_forServer();
  switch ($action) {
    //
    case 'diag':
      $result = InfoButton::searchDiag($_GET['id']);
      AjaxResponse::out($action, $result);
      exit;
    case 'med':
      $result = InfoButton::searchMed($_GET['id']);
      AjaxResponse::out($action, $result);
      exit;
    case 'lab':
      $result = InfoButton::searchLab($_GET['id']);
      AjaxResponse::out($action, $result);
      exit;
  }
} catch (Exception $e) {
  AjaxResponse::exception($e);
}
