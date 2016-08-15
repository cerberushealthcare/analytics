<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once 'server.php';
require_once 'php/c/immun-charting/ImmunCharting.php';
require_once 'php/c/immun-cds/ImmunCds.php';
//
try {
  LoginSession::verify_forServer()->requires($login->Role->Patient->facesheet);
  switch ($action) {
    //
    case 'downloadPdf':
      Auditing::logPrint($obj->cid, 'Immunization Certificate', $obj->cid, $obj->form);
      ImmunCharting::downloadPdf($obj->cid, $obj->form, $obj->until);
      exit;
    case 'downloadSinglePdf':
      ImmunCharting::downloadSinglePdf($_GET['id']);
      exit;
    case 'debugPdf':
      ImmunCharting::downloadPdf($_GET['id'], 'KY');
      exit;
    case 'getImmunCd':
      $cd = ImmunCds::get($_GET['id']);
      AjaxResponse::out($action, /*Immun_Cd*/$cd);
      exit;
    case 'getImmunCdHtml':
      $cd = ImmunCds::get($_GET['id'], true);
      p_r($cd->_html);
      //AjaxResponse::out($action, /*Immun_Cd*/$cd);
      exit;
  }
} catch (Exception $e) {
  AjaxResponse::exception($e);
}
