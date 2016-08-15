<?php
require_once 'server.php';
require_once 'php/data/hl7-2.5.1/msg/AdtMessage.php';
require_once 'php/data/rec/group-folder/GroupFolder_Adt.php';
//require_once 'php/c/facesheets/Facesheets.php';
require_once 'php/data/rec/sql/Facesheets.php';

//
try { 
  LoginSession::verify_forServer()->requires($login->Role->Artifact->hl7);
  switch ($action) {
    //
    case 'get':
      //$password = geta($_GET, 'pw');
      //$fs = Facesheets::asPubHealth($id);
      //$msg = ADTMessage::asPubHealth($fs);
      $id = $obj->cid;
      $npi = '1231231234';
      $password = null;
      $fs = Facesheet_Hl7Syndrome::from($id, $npi, $obj); 
      $msg = ADTMessage::byType($obj->type, $fs);
      logit_r($msg, 'adt message');
      $folder = GroupFolder_Adt::open();
      $file = $folder->save($msg, $password);
      logit_r($file, 'adt file');
      AjaxResponse::out($action, $file);
      exit;
    case 'download':
      $folder = GroupFolder_Adt::open();
      $folder->download($id);
      exit;
  }
} catch (Exception $e) {
  AjaxResponse::exception($e);
}
  