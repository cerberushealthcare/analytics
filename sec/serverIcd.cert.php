<?php
require_once 'server.php';
require_once 'php/data/rec/sql/IcdCodes.php';
require_once 'php/data/rec/sql/IcdCodes10.php';
//
try {
  LoginSession::verify_forServer();
  switch ($action) {
    /**
     * Search for ICD codes
     * @param $_GET{'text']
     * @return @see IcdCodes::search
     */
    case 'search':
      $result = IcdCodes::search($_GET['text']);
      AjaxResponse::out($action, $result);
      exit;
    case 'search10':
      $result = IcdCodes10::search($_GET['text']);
      AjaxResponse::out($action, $result);
      exit;
  }
} catch (Exception $e) {
  AjaxResponse::exception($e);
}

