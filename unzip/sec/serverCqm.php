<?php
require_once 'server.php';
require_once 'php/c/cqm-reports/CqmReports.php';
require_once 'php/data/rec/group-folder/GroupFolder_Cqm.php';
//
try { 
  LoginSession::verify_forServer()->requires($login->Role->Report->pqri);
  switch ($action) {
    //
    case 'report':
      $id = $obj->id;
      $from = dateToString($obj->from);
      $to = dateToString($obj->to);
      $userId = $obj->userId;
      if ($id == '(CatIII)') {
        $cqmset = CqmReports::getSet($userId, $from, $to);
        $filename = CqmReports::saveCat3($cqmset);
      } else {
        $cqm = CqmReports::get($id, $from, $to, $userId);
        $filename = CqmReports::zipCat1s($cqm);
      }
      AjaxResponse::out($action, $filename);
      exit;
    case 'download':
      $folder = GroupFolder_Cqm::open();
      $folder->download($id);
      exit;
  }
} catch (Exception $e) {
  AjaxResponse::exception($e);
}
  