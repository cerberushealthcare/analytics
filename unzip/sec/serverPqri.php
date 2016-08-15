<?php
require_once 'server.php';
require_once 'php/data/rec/sql/cms/CmsReports.php';
require_once 'php/data/rec/group-folder/GroupFolder_Pqri.php';
require_once 'php/data/xml/pqri/PQRI.php';
//
try { 
  LoginSession::verify_forServer()->requires($login->Role->Report->pqri);
  switch ($action) {
    //
    case 'report':
    case 'get':
      $id = str_replace(' ', '', $obj->id);
      $method = "get$id";
      $from = dateToString($obj->from);
      $to = dateToString($obj->to);
      $report = CmsReports::$method($from, $to, $obj->userId);
      if ($action == 'get') {
        $xml = PQRI::from($report);
        $folder = GroupFolder_Pqri::open();
        $file = $folder->save($xml);
        AjaxResponse::out($action, $file);
      } else {
        AjaxResponse::out($action, $report);
      }
      exit;
    case 'download':
      $folder = GroupFolder_Pqri::open();
      $folder->download($id);
      exit;
  }
} catch (Exception $e) {
  AjaxResponse::exception($e);
}
  