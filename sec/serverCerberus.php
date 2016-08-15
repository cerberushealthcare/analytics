<?php
require_once 'server.php';
require_once 'php/c/patient-billing/CerberusBilling.php';
//
try {
  LoginSession::verify_forServer();
  switch ($action) {
    //
    case 'login':
      global $login;
      $ugid = $login->userGroupId;
      $user = CerberusLogin::extractUid($login->uid);
      $pw = $login->getPtpw();
      $practiceId = $login->cerberus;
      CerberusBilling::login($ugid, $user, $pw, $practiceId);
      AjaxResponse::out($action, null);
      exit;
    case 'refreshICards':
      $cid = $_GET['id'];
      CerberusBilling::refreshICards($cid);
      require_once 'php/data/rec/sql/Clients.php';
      $client = Clients::get($cid);
      AjaxResponse::out($action, $client);
      exit;
  }
} catch (Exception $e) {
  AjaxResponse::exception($e);
}
