<?php
require_once 'server.php';
require_once 'php/data/hl7-2.5.1/msg/VXUMessage.php';
require_once 'php/data/rec/group-folder/GroupFolder_Vxu.php';
require_once 'php/data/rec/sql/Facesheets.php';
//
try { 
  LoginSession::verify_forServer()->requires($login->Role->Artifact->hl7);
  switch ($action) {
    //
    case 'get':
      $password = geta($_GET, 'pw');
      $fs = Facesheet_Hl7Immun::from($id); 
      $vxu = VXUMessage::from($fs, 'X68', 'LCD');
      $folder = GroupFolder_Vxu::open();
      $file = $folder->save($vxu, $password);
      AjaxResponse::out($action, $file);
      exit;
    case 'download':
      $folder = GroupFolder_Vxu::open();
      $folder->download($id);
      exit;
  }
} catch (Exception $e) {
  AjaxResponse::exception($e);
}
  