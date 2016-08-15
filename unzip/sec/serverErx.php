<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once 'server.php';
require_once 'php/data/rec/erx/ErxStatus.php'; 
require_once 'php/data/rec/erx/ErxPharm.php'; 
require_once 'php/data/rec/erx/ErxStatusCount.php'; 
require_once 'php/data/json/JAjaxMsg.php'; 
require_once 'php/newcrop/NewCrop.php';
require_once 'php/data/rec/sql/Clients.php';
require_once 'php/data/rec/sql/Procedures_Admin_MU2.php';
//
try {
  LoginSession::verify_forServer()->requires($login->Role->erx);
  switch ($action) {
    /**
     * Validate schema
     */
    case 'validate':
      $newcrop = new NewCrop();
      try {
        $xml = $newcrop->buildClickThru($_GET['id']);
        $m = new JAjaxMsg($action, 'null');
      } catch (DomDataRequiredException $e) {
        $m = new JAjaxMsg($action, jsonencode($e->required));
      }
      break;
    case 'debug2':
      $newcrop = new NewCrop();
      $current = $newcrop->pullCurrentMedAllergyU1($_GET['id']);
      echo '<pre>';
      print_r($current);
      exit;
    case 'dailyMuReport':
      global $login;
      $newcrop = new NewCrop();
      $date = geta($_GET, 'date')/*default today*/;
      $mus = $newcrop->pullDailyMuReport($date);
      Proc_Admin_MU2::save($date, $mus);
      //$xmits = DailyMeaningfulUseReport::extractPatientTransmits($mus);
      //Proc_PatientSecureMessage::recordAll($xmits);
      exit;
    case 'debug3':
      $newcrop = new NewCrop();
      $date = geta($_GET, 'id')/*default today*/;
      $mus = $newcrop->pullDailyMuReport($date);
      echo '<pre>';
      p_r($mus);
      $map = Mu2DailyMap::fromDailyMUReport($date, $mus);
      p_r($map);
      exit;
    case 'debug':
      $newcrop = new NewCrop();
      try {
        $id = $_GET['id'];
        $allergies = Allergies::getAll($id);
        $xml = $newcrop->buildClickThru($id);
        $m = new JAjaxMsg($action, 'null');
        p_r(htmlentities($xml['xml']));
        exit;
      } catch (DomDataRequiredException $e) {
        $m = new JAjaxMsg($action, jsonencode($e->required));
      }
      break;
    /**
     * Refresh meds/allergies
     */
    case 'refresh':
      $cid = $_GET['id'];
      $since = geta($_GET, 'since');
      $auditless = geta($_GET, 'auditless');
      $withAudits = ! ($auditless && $auditless != 'false');
      logit_r('refresh, withAudits=' . $withAudits); 
      try {
        FacesheetDao::refreshFromNewCrop($cid, $withAudits);
        $facesheet = FacesheetDao::getClientActiveMedsAllergies($cid);
        if ($since) 
          $facesheet->audits = FacesheetDao::getNewCropAuditsSince($cid, $since);
        $m = new JAjaxMsg($action, $facesheet->out());
      } catch (Exception $e) {
        $m = JAjaxMsg::constructError($e);
      }
      break;
    case 'drefresh':
      $cid = $_GET['id'];
      $since = geta($_GET, 'since');
      $withAudits = geta($_GET, 'auditless') ? false : true; 
      try {
        FacesheetDao::refreshFromNewCrop($cid, $withAudits);
        $facesheet = FacesheetDao::getClientActiveMedsAllergies($cid);
        if ($since) 
          $facesheet->audits = FacesheetDao::getNewCropAuditsSince($cid, $since);
        $m = new JAjaxMsg($action, $facesheet->out());
      } catch (Exception $e) {
        $m = JAjaxMsg::constructError($e);
      }
      break;
    /**
     * Get pharmacy requests for logged-in LP
     */
    case 'getPharmReqs':
      $newcrop = new NewCrop();
      $resp = $newcrop->pullRenewalRequests();
      $recs = ErxPharm::fromRenewalRequests($resp);
      $m = new JAjaxMsg($action, jsonencode($recs));
      break;
    /**
     * Get pharmacy requests for entire group of LPs
     */
    case 'getAllPharmReqs':
      $newcrop = new NewCrop();
      $resp = $newcrop->pullAllRenewalRequests();
      $recs = ErxPharm::fromRenewalRequests($resp);
      $m = new JAjaxMsg($action, jsonencode($recs));
      break;
    /**
     * Search for client matches to pharm request
     */
    case 'matchClients':
      $recs = Clients::search($obj->patientLastName, $obj->patientFirstName, $obj->patientDOB, $obj->patientGender);
      //$recs = PatientList::match($obj->patientLastName, $obj->patientFirstName, $obj->patientDOB, $obj->patientGender); TODO - return assoc array like above
      $m = new JAjaxMsg($action, jsonencode($recs));
      break;
    /**
     * Get status details 
     * Note: record may not include a ClientStub if no externalPatientId was supplied by NewCrop
     * @return {'recs':[ErxStatus,..],'ct':ErxStatusCount} 
     */
    case 'getStatusDetail':
      $newcrop = new NewCrop();
      $statuses = $newcrop->pullAcctStatusDetails();
      $statusct = ErxStatusCount::fromNewCrop($statuses, null);
      $ers = array();
      foreach ($statuses as $status => &$recs) {
        if ($recs) 
          foreach ($recs as &$rec) 
            $ers[] = ErxStatus::fromNewCrop($status, $rec); 
      }
      $o = array('recs' => $ers, 'ct' => $statusct);
      $m = new JAjaxMsg($action, jsonencode($o));
      break;
    case 'debugStatusDetail':
      error_reporting(E_ALL);
      ini_set('display_errors', '1');
      $newcrop = new NewCrop();
      echo '<pre>';
      $statuses = $newcrop->pullAcctStatusDetails();
      p_r($statuses, 'statuses');
      $ers = array();
      foreach ($statuses as $status => &$recs) {
        if ($recs) 
          foreach ($recs as &$rec) 
            $ers[] = ErxStatus::fromNewCrop($status, $rec); 
      }
      p_r($ers, 'ers');
      exit;
  }
} catch (Exception $e) {
  $m = JAjaxMsg::constructError($e);
}
if ($m != null) 
  echo $m->out();
