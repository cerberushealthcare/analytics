<?php
require_once 'server.php';
require_once 'php/c/user-profile/UserProfile.php'; 
//
try {
  LoginSession::verify_forServer();
  switch ($action) {
    //
    case 'get':
      $profile = UserProfile::getMine();
      AjaxResponse::out($action, $profile);
      exit;
    case 'saveUser':
      $login->requires($login->Role->Profile->name);
      $profile = UserProfile::saveUser($obj);
      AjaxResponse::out($action, $profile);
      exit;
    case 'saveGroup':
      $login->requires($login->Role->Profile->practice);
      $profile = UserProfile::saveGroup($obj);
      AjaxResponse::out($action, $profile);
      exit;
    case 'saveBilling':
      $login->requires($login->Role->Profile->billing);
      $profile = UserProfile::saveBilling($obj);
      AjaxResponse::out($action, $profile);
      exit;
    case 'saveTimeout':
      $login->requires($login->Role->Profile->practice);
      $profile = UserProfile::saveTimeout($id);
      AjaxResponse::out($action, $profile);
      exit;
    case 'changePassword':
      $login->changePassword($obj->cpw, $obj->pw);
      AjaxResponse::out($action);
      exit;
    case 'verifyPassword':
      $valid = $login->verifyPassword($obj->pw);
      AjaxResponse::out($action, $valid);
      exit;
    case 'setPassword':
      $login->setPassword($obj->pw)->setUi($obj->tablet == '1');
      AjaxResponse::out($action);
      exit;
  }
} catch (Exception $e) {
  AjaxResponse::exception($e);
}
