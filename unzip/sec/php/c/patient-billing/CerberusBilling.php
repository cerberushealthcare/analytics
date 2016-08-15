<?php
require_once 'php/c/patient-billing/Cerberus_Apex.php';
require_once 'php/c/patient-billing/Cerberus_Sql.php';
require_once 'php/c/patient-billing/Cerberus_Xml.php';
require_once 'php/c/patient-billing/Cerberus_Ws.php';
require_once 'php/c/patient-billing/Cerberus_Exception.php';
//
/**
 * Cerberus Patient Billing
 * @author Warren Hornsby
 */
class CerberusBilling {
  //
  /** Login to Cerberus */
  static function /*CQ_LoginInfo*/login($ugid, $user, $pw) {
    $query = CQ_Login::create($user, $pw);
    $loginInfo = $query->submit();
    CerberusLogin::cache($ugid, $loginInfo);
    return $loginInfo;
  }
  /** Login to an already established Cerberus session */
  static function /*CQ_LoginInfo*/activateSession($ugid, $user, $pw, $session, $cookie, $url) {
    $loginInfo = CQ_LoginInfo::from($user, $pw, $cookie, $session, $url);
    CerberusLogin::cache($ugid, $loginInfo);
    return $loginInfo;
  }
  /** Send updated client information to Cerberus */
  static function /*Client_Cb*/updateClient($cid) {
    $clogin = CerberusLogin::fetch();
    $client = Client_Cb::fetch($clogin->practiceId, $cid);
    $query = CXQ_Patient::create($clogin->practiceId, $clogin->user, $clogin->pw, $client);
    $patientId = $query->submit();
    $client->saveExternalId($clogin->practiceId, $patientId);
    return $client;
  }
  /** Create Cerberus encounter */
  static function /*int*/closeNote($sid, /*string[]*/$cpts = null, $loc = null, $site = null, $case = null) {
    global $login;
    $clogin = CerberusLogin::fetch();
    $session = Session_Cb::fetch($clogin->practiceId, $sid);
    if ($session->_externalId)
      throw new CerberusEncounterAlreadyExists($sid);
    $cid = $session->clientId;
    $patientId = $session->Client->_externalId;
    $date = $session->dateService;
    $loc = $loc ?: '11'/*X12 loc_code for office*/;
    $icds = $session->getIcds();
    $cpts = Proc_Cb::fetchCpts($cid, $date);
    //$providerId = ApiIdXref_Cb::lookupUserId($clogin->practiceId, $login->userId);/*doesn't work*/
    $providerId = null;
    //$query = CQ_CloseNote::create($clogin->user, $clogin->pw, $patientId, $site, $case, $date, $loc, $icds, $cpts);
    $query = CWS_CloseNote::create($clogin->user, $clogin->pw, $patientId, $site, $case, $date, $loc, $icds, $cpts, $providerId);
    $encounterId = $query->submit($clogin->sessionId, null);
    $session->saveExternalId($clogin->practiceId, $encounterId);
    return $encounterId;
  }
  /** Navigate browser to particular superbill */
  static function navSuperbill($sid) {
    $clogin = CerberusLogin::fetch();
    $session = Session_Cb::fetch($clogin->practiceId, $sid);
    $patientId = $session->Client->_externalId;
    $encounterId = $session->_externalId;
    if ($encounterId == null)
      throw new CerberusEncounterNotFound($sid);
    $query = CQ_Superbill::create($clogin->user, $clogin->pw, $clogin->sessionId, $patientId, $encounterId);
    $query->navigate($clogin->sessionId);
  }
  /** Navigate browser to superbills list for patients */
  static function navListSuperbills($cid) {
    $clogin = CerberusLogin::fetch();
    $patientId = $clogin->lookupPatientId($clogin->practiceId, $cid);
    $query = CQ_ListSuperbills::create($clogin->user, $clogin->pw, $clogin->sessionId, $patientId);
    $query->navigate($clogin->sessionId);
  }
  /** Navigate browser to unsigned superbills list */
  static function navUnsignedSuperbills() {
    $clogin = CerberusLogin::fetch();
    $query = CQ_UnsignedSuperbills::create($clogin->user, $clogin->pw, $clogin->sessionId, null);
    $query->navigate($clogin->sessionId);
  }
  /** Navigate browser to insurance */
  static function navInsurance($cid) {
    $clogin = CerberusLogin::fetch();
    $client = Client_Cb::fetch($clogin->practiceId, $cid);
    $patientId = $client->_externalId;
    $query = CQ_Insurance::create($clogin->user, $clogin->pw, $clogin->sessionId, $patientId);
    $query->navigate($clogin->sessionId);
  }
  /** Fetch schedule entries */
  static function /*CR_Appt[]*/fetchAppts($userId = null, $date = null, $days = 14) {
    global $login;
    $clogin = CerberusLogin::fetch();
    if ($userId == null)
      $userId = $login->userId;
    $date = $date ? date("m/d/Y", strtotime($date)) : date('m/d/Y');
    $to = futureDate($days, 0, 0, $date, 'm/d/Y');
    $providerId = ApiIdXref_Cb::lookupUserId($clogin->practiceId, $userId);
    $query = CQ_Appt::create($clogin->user, $clogin->pw, $clogin->sessionId, $providerId, $date, $to);
    $entries = $query->fetch();
    return $entries;
  }
  /** Fetch insurance entries */
  static function /*CR_Insurance[]*/fetchInsurance($cid) {
    $clogin = CerberusLogin::fetch();
    $client = Client_Cb::fetch($clogin->practiceId, $cid);
    $patientId = $client->_externalId;
    $query = CQ_Insurance::create_forXml($clogin->user, $clogin->pw, $clogin->sessionId, $patientId);
    $entries = $query->fetch();
    return $entries;
  }
  /** Refresh CLIENT_ICARDS with external insurance info */
  static function /*ICard_Cb[]*/refreshICards($cid) {
    $entries = static::fetchInsurance($cid);
    $icards = ICard_Cb::refresh($cid, $entries);
    return $icards;
  }
  /** Fetch superbill stubs */
  static function /*ApiIdXref_Cb[]*/getSuperbillStubs($cid) {
    $clogin = CerberusLogin::fetch();
    $stubs = ApiIdXref_Cb::fetchEncounters($clogin->practiceId, $cid);
    return $stubs;
  }
}