<?php
require_once 'server.php';
require_once 'php/data/rec/sql/UserGroups.php'; 
require_once 'php/data/rec/sql/PortalUsers.php'; 
require_once 'php/data/rec/sql/PortalMessaging.php'; 
//
try {
  LoginSession::verify_forServer()->requires($login->Role->Account->portal);
  switch ($action) {
    //
    case 'getPortalUsers':
      $pusers = PortalUsers::getAll();
      AjaxResponse::out($action, $pusers);
      exit;
    case 'createPortalUser':
      $puser = PortalUsers::create($obj);
      AjaxResponse::out($action, $puser);
      exit;
    case 'savePortalUser':
      $puser = PortalUsers::save($obj);
      AjaxResponse::out($action, $puser);
      exit;
    case 'editPortalUserFor':
      $puser = PortalUsers::editFor($id);
      AjaxResponse::out($action, $puser);
      exit;
    case 'resetPortalUser':
      $puser = PortalUsers::reset($id);
      AjaxResponse::out($action, $puser);
      exit;
    case 'suspendPortalUser':
      $puser = PortalUsers::suspend($id);
      AjaxResponse::out($action, $puser);
      exit;
    case 'getPortalMsgTypes':
      $types = PortalUsers::getMsgTypes($id);
      AjaxResponse::out($action, $types);
      exit;
    case 'savePortalMsgTypes':
      $types = PortalUsers::saveMsgTypes($obj);
      AjaxResponse::out($action, $types);
      exit;
  }
} catch (Exception $e) {
  AjaxResponse::exception($e);
}
  