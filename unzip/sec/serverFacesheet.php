<?php
ob_start('ob_gzhandler');
require_once 'php/dao/_util.php';
require_once 'php/data/LoginSession.php';
require_once "php/data/rec/sql/Auditing.php";
require_once 'php/dao/FacesheetDao.php';
require_once 'php/data/json/JAjaxMsg.php';
require_once 'img/charts/ChartIndex.php';
require_once 'php/data/rec/sql/Clients.php';
require_once 'php/data/rec/sql/Documentation.php';
//
if (isset($_GET['action'])) {
  $action = $_GET['action'];
  if ($action != 'getIfUpdated')
    Logger::debug(currentUrl());
} else {
  $_POST['obj'] = stripslashes($_POST['obj']);
  $action = $_POST['action'];
  $obj = jsondecode($_POST['obj']);
  Logger::debug(currentUrl());
  Logger::debug_r($_POST, '$_POST');
}
try {
  LoginSession::verify_forServer()->requires($login->Role->Patient->facesheet);
  switch ($action) {
    /*
     * Get facesheet
     */
    case 'get':
      $cid = $_GET['id'];
      $facesheet = FacesheetDao::getFacesheet($cid);
      Auditing::logReviewFacesheet($facesheet->client, $facesheet->ipcHms);
      $m = new JAjaxMsg($action, $facesheet->out());
      break;
    case 'getd':
      $cid = $_GET['id'];
      $fs = FacesheetDao::getFacesheet($cid);
      echo '<pre>';
      $fs = jsondecode($fs->out());
      p_r($fs);
      exit;
    case 'test1':
      echo '<pre>';
      $cid = 107067;
      $c = Proc::asCriteria();
      $c->clientId = $cid;
      $c->ProcResults = ProcResult::asOptionalJoin();
      Proc::testFetch($c);
      exit;
    case 'test0':
      $cid = 107067;
      $c = Proc::asCriteria();
      $c->clientId = $cid;
      $c->ProcResults = ProcResult::asOptionalJoin();
      $recs = Proc::fetchAllBy($c);
      echo '<pre>';
      p_r($recs);
      exit;
    /*
     * Get facesheet if updated
     */
    case 'getIfUpdated':
      $cid = $_GET['id'];
      $cuLastTimestamp = $_GET['cu'];
      $cuTimestamp = Auditing::getClientUpdateTimestamp($cid);
      if ($cuTimestamp != $cuLastTimestamp) {
        $facesheet = FacesheetDao::getFacesheet($cid, false);
        $m = new JAjaxMsg($action, $facesheet->out());
      } else {
        $m = new JAjaxMsg($action, null);
      }
      break;
    /*
     * List of available charts
     */
    case 'getCharts':
      $charts = ChartIndex::getChartsJson($_GET['sex'], $_GET['age']);
      $m = new JAjaxMsg($action, $charts);
      break;
    /*
     * Patient
     */
    case 'getNextPatientUid':
      $uid = Clients::getNextUid();
      $m = new JAjaxMsg($action, jsonencode($uid));
      break;
    case 'savePatient':
      $client = Clients::save($obj);
      $m = new JAjaxMsg($action, jsonencode($client));
      break;
      /*
    case 'savePatientDupe':
      $client = Clients::save_dupeOk($obj);
      $m = new JAjaxMsg($action, jsonencode($client));
      break;
      */
    case 'savePatientAddress':
      $client = Clients::updateAddress($obj->address, $obj->id);
      $m = new JAjaxMsg($action, jsonencode($client));
      break;
    case 'savePatientICard':
      $client = Clients::updateICard($obj->icard, $obj->id);
      $m = new JAjaxMsg($action, jsonencode($client));
      break;
    case 'savePatientNotes':
      $facesheet = FacesheetDao::saveClientNotes($obj->cid, $obj->text);
      $m = new JAjaxMsg($action, $facesheet->out());
      break;
    case 'breakGlass':
      Clients::breakGlass($_GET['id']);
      $m = new JAjaxMsg($action, null);
      break;
    case 'removeImg':
      $client = Clients::removeImage($_GET['id']);
      $m = new JAjaxMsg($action, jsonencode($client));
      break;
    case 'getClient':
      $client = Clients::get($_GET['id']);
      $m = new JAjaxMsg($action, jsonencode($client));
      break;
    /*
     * Medications
     */
    case 'getMedHist':
      $facesheet = FacesheetDao::getMedClientHistory($_GET['id']);
      $m = new JAjaxMsg($action, $facesheet->out());
      break;
    case 'deactivateMeds':
      $facesheet = FacesheetDao::deactivateMeds($obj);
      $m = new JAjaxMsg('refreshFacesheet', $facesheet->out());
      break;
    case 'saveMed':
      $facesheet = FacesheetDao::saveMed($obj);
      $m = new JAjaxMsg('refreshFacesheet', $facesheet->out());
      break;
    case 'deleteLegacyMeds':
      $facesheet = FacesheetDao::deleteLegacyMeds($_GET['id']);
      $m = new JAjaxMsg($action, $facesheet->out());
      break;
    case 'reconcileMeds2':
      require_once "php/data/rec/sql/Procedures_Admin_MU2.php";
      $cid = $_GET['id'];
      ProcMu2_medRecon::record($cid);
      $facesheet = FacesheetDao::getFacesheet($cid, false);
      $m = new JAjaxMsg($action, $facesheet->out());
      break;
    case 'printRxMeds':
      $facesheet = FacesheetDao::printRxForMeds($obj);
      $m = new JAjaxMsg('refreshFacesheet', $facesheet->out());
      break;
    case 'setMedsNone':
      $facesheet = FacesheetDao::setMedsNone($_GET['id']);
      $m = new JAjaxMsg('refreshFacesheet', $facesheet->out());
      break;
    case 'saveReviewed':
      $facesheet = FacesheetDao::saveReviewed($obj->cid, $obj->meds);
      $m = new JAjaxMsg('refreshFacesheet', $facesheet->out());
      break;
    /*
     * Allergies
     */
    case 'getAllergyQuestion':
      $q = FacesheetDao::getAllergyQuestion();
      $m = new JAjaxMsg('getAllergyQuestion', $q->out());
      break;
    case 'saveAllergy':
      $facesheet = FacesheetDao::saveAllergy($obj);
      $m = new JAjaxMsg('refreshFacesheet', $facesheet->out());
      break;
    case 'deactivateAllergy':
      $facesheet = FacesheetDao::deactivateAllergy($_GET['id']);
      $m = new JAjaxMsg('refreshFacesheet', $facesheet->out());
      break;
    case 'deactivateAllergies':
      $facesheet = FacesheetDao::deactivateAllergies($obj);
      $m = new JAjaxMsg('refreshFacesheet', $facesheet->out());
      break;
    case 'deleteLegacyAllergies':
      $facesheet = FacesheetDao::deleteLegacyAllergies($_GET['id']);
      $m = new JAjaxMsg($action, $facesheet->out());
      break;
    case 'reconcileAllergies':
      $cid = $obj->cid;
      $allergies = $obj->allergies;
      $facesheet = FacesheetDao::reconcileAllergies($cid, $allergies);
      $m = new JAjaxMsg('refreshFacesheet', $facesheet->out());
      break;
    /*
     * Vitals
     */
    case 'getVitalQuestions':
      $vqs = FacesheetDao::getVitalQuestions();
      $m = new JAjaxMsg('getVitalQuestions', aarr($vqs));
      break;
    case 'saveVital':
      $login->requires($login->Role->Patient->vitals);
      $facesheet = FacesheetDao::saveVital($obj);
      $m = new JAjaxMsg('refreshFacesheet', $facesheet->out());
      break;
    case 'deactivateVital':
      $login->requires($login->Role->Patient->vitals);
      $facesheet = FacesheetDao::deactivateVital($_GET['id']);
      $m = new JAjaxMsg('refreshFacesheet', $facesheet->out());
      break;
    /*
     * Immuns
     */
    case 'saveImmun':
      $facesheet = FacesheetDao::saveImmun($obj);
      $m = new JAjaxMsg('refreshFacesheet', $facesheet->out());
      break;
    case 'deleteImmun':
      $facesheet = FacesheetDao::deleteImmun($_GET['id']);
      $m = new JAjaxMsg('refreshFacesheet', $facesheet->out());
      break;
    /*
     * Diagnoses
     */
    case 'saveDiagnosis':
      $facesheet = FacesheetDao::saveDiagnosis($obj);
      $m = new JAjaxMsg('refreshFacesheet', $facesheet->out());
      break;
    case 'deleteDiagnosis':
      $facesheet = FacesheetDao::deleteDiagnosis($_GET['id']);
      $m = new JAjaxMsg('refreshFacesheet', $facesheet->out());
      break;
    case 'setDiagnosisNone':
      $facesheet = FacesheetDao::setDiagnosisNone($_GET['id']);
      $m = new JAjaxMsg('refreshFacesheet', $facesheet->out());
      break;
    case 'copyToMedHx':
      $facesheet = FacesheetDao::copyToMedHx($obj);
      $m = new JAjaxMsg('refreshFacesheet', $facesheet->out());
      break;
    case 'reconcileDiagnoses':
      $cid = $obj->cid;
      $diags = $obj->diags;
      $facesheet = FacesheetDao::reconcileDiagnoses($cid, $diags);
      $m = new JAjaxMsg('refreshFacesheet', $facesheet->out());
      break;
    /*
     * Documentation
     */
    case 'getDocStubs':
      $stubs = Documentation::getAll($_GET['id']);
      $m = new JAjaxMsg($action, jsonencode($stubs));
      break;
    case 'preview':
      $rec = Documentation::preview($obj);
      $m = new JAjaxMsg($action, jsonencode($rec));
      break;
    case 'previewtest':
      $obj = new stdClass();
      $obj->type = 4;
      $obj->id = 168;
      $rec = Documentation::preview($obj);
      echo '<pre>';
      p_r(jsondecode(jsonencode($rec)));
      exit;
      break;
    case 'refetchStub':
      $rec = Documentation::refetch($obj);
      $m = new JAjaxMsg($action, jsonencode($rec));
      break;
    case 'reviewed':
      $rec = Documentation::setReviewed($_GET['id']);
      $m = new JAjaxMsg($action, jsonencode($rec));
      break;
    case 'getMyUnreviewedCt':
      $ct = Messaging_DocStubReview::getUnreviewedCt();
      $m = new JAjaxMsg($action, $ct);
      break;
    case 'getUnreviewed':
      $rec = Documentation::getUnreviewed();
      $m = new JAjaxMsg($action, jsonencode($rec));
      break;
    case 'forward':
      $stubType = $_GET['stubType'];
      $stubId = $_GET['stubId'];
      $userId = $_GET['userId'];
      $rec = Documentation::forward($stubType, $stubId, $userId);
      $m = new JAjaxMsg($action, jsonencode($rec));
      break;
  }
} catch (Exception $e) {
  $m = JAjaxMsg::constructError($e);
}
if ($m != null)
  echo $m->out();
