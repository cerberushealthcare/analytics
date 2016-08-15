<?php
require_once 'server.php';
require_once 'php/c/patient-docs/PatientDocs.php';
//
try {
  LoginSession::verify_forServer();
  switch ($action) {
    //
    case 'print':
      $doc = PatientDocs::get($obj->id);
      $doc->download();
      exit;
    case 'createReferralCard':
      $doc = PatientDocs::createReferralCard($obj->cid, $obj->html);
      if ($obj->print)
        $doc->download();
      else
        AjaxResponse::out($action, $doc);
      exit;
  }
} catch (Exception $e) {
  AjaxResponse::exception($e);
}
  