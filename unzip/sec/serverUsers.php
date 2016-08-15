<?php
require_once 'server.php';
require_once 'php/data/rec/sql/UserManager.php'; 
//
try {
  LoginSession::verify_forServer()->requires($login->Role->Account->manage);
  switch ($action) {
    //
    case 'get':
      $users = UserManager::getMine();
      AjaxResponse::out($action, $users);
      exit;
    case 'save':
      $user = UserManager::save($obj);
      AjaxResponse::out($action, $user);
      exit;
    case 'deactivate':
      $user = UserManager::deactivate($id);
      AjaxResponse::out($action, $user);
      exit;
    case 'activate':
      $user = UserManager::activate($id);
      AjaxResponse::out($action, $user);
      exit;
    case 'saveErx':
      $ncuser = UserManager::saveErx($obj);
      AjaxResponse::out($action, $ncuser);
      exit;
    case 'removeErx':
      $id = UserManager::removeErx($id);
      AjaxResponse::out($action, $id);
      exit;
  }
} catch (Exception $e) {
  AjaxResponse::exception($e);
}
