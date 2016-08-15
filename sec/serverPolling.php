<?php
require_once 'php/data/LoginSession.php';
require_once 'php/data/rec/sql/Messaging_DocStubReview.php';
require_once 'php/data/rec/sql/HL7_Labs.php';
require_once 'php/data/rec/AjaxResponse.php'; 
require_once 'php/data/rec/sql/Messaging.php';
require_once 'php/data/rec/erx/ErxStatusCount.php'; 
require_once 'php/newcrop/NewCrop.php';
require_once "php/data/rec/sql/Auditing.php";
//
Logger::debug_server();
$action = $_GET['action'];
try {
  LoginSession::verify_forPolling(); 
  switch ($action) {
    case 'getMyInboxCt':
      $ct = Messaging::getMyUnreadCt();
      AjaxResponse::out($action, $ct);
      break;
    case 'getMyUnreviewedCt':
      $ct = Messaging_DocStubReview::getUnreviewedCt();
      AjaxResponse::out($action, $ct);
      break;
    case 'getMyLabCt':
      $ct = HL7_Labs::getInboxCt();
      AjaxResponse::out($action, $ct);
      break;
    case 'pollCuTimestamp':
      $cid = $_GET['id']; 
      $timestamp = Auditing::getClientUpdateTimestamp($cid);
      AjaxResponse::out($action, $timestamp);
      break;
    case 'getStatusCount':
      $newcrop = new NewCrop();
      try {
        $ncStatuses = $newcrop->pullAcctStatusDetails(true);
        $ncPharmReqs = $newcrop->pullAllRenewalRequests(true);
        $status = ErxStatusCount::fromNewCrop($ncStatuses, $ncPharmReqs);
        AjaxResponse::out($action, $status);
      } catch (SoapResultException $e) {
        // will throw 'acct not found' prior to first time entering
        AjaxResponse::out($action, null);
      }
      break;
  }
} catch (SessionInvalidException $e) {
  // no need to poll 
} catch (Exception $e) {
  AjaxResponse::logException($e);
}
  