<?php
require_once 'server.php';
require_once 'php/data/rec/sql/_ClientRec.php';
require_once 'php/data/file/client-pdf/_ClientPdfFile.php';
//
try { 
  LoginSession::verify_forServer()->requires($login->Role->Artifact->any());
  switch ($action) {
    case 'download':
      $client = ClientStub::fetch($obj->cid);
      $title = $obj->title;
      $dos = $obj->dos;
      $html = $obj->html;
      ClientPdf::create($client, $title, $html, $prefix, $dos)->download();
      exit;
  }
} catch (Exception $e) {
  AjaxResponse::exception($e);
}
  