<?php
require_once 'php/data/curl/PostQuery.php';
require_once 'php/c/patient-billing/Cerberus_Exception.php';
//
abstract class CerberusWsQuery extends PostQuery {
  //
  //static $BASE_URL = "https://papyrus-pms.com/ords/ws1/";
  //
  public function submit($url) {
    $url = static::baseurl() . $url;
    $response = parent::submit($url);
    $body = $response->body;
    return CerberusWsResponse::from($body);
  }
  protected function mmddyyyy($date) {
    return date("m/d/Y", strtotime($date));
  }
  //
  static function baseurl($queryBase) {
    $clogin = CerberusLogin::fetch();
    $url = str_replace('f?p=', 'ws1/', $clogin->queryBase);
    return $url;
  }
}
class CerberusWsResponse extends Rec {
  //
  public $status;
  public $msg;
  //
  public function isOk() {
    return $this->status == 'OK';
  }
  //
  static function from($body) {
    $j = jsondecode($body);
    $me = new static();
    $me->status = $j->A_STATUS;
    $me->msg = $j->A_MSG;
    return $me;
  }
}
//
class CWS_CloseNote extends CerberusWsQuery {
  //
  public $A_USERNAME;
  public $A_PASSWORD;
  public $A_AGENCY_CLIENTID;
  public $A_PROVIDER_ID;
  public $A_ENCOUNTER_DATE;
  public $A_LOC_CODE;
  public $A_SITE_CODE;
  public $A_CASE;
  public $A_DIAG1;  
  public $A_DIAG2;  
  public $A_DIAG3;  
  public $A_DIAG4;  
  public $A_DIAG5;  
  public $A_DIAG6;  
  public $A_DIAG7;  
  public $A_DIAG8;
  public $A_PROC1;  
  public $A_PROC2;  
  public $A_PROC3;  
  public $A_PROC4;  
  public $A_PROC5;  
  public $A_PROC6;  
  public $A_PROC7;  
  public $A_PROC8;  
  public $A_PROC9;  
  public $A_PROC10;  
  public $A_PROC11;  
  public $A_PROC12;  
  public $A_PROC13;  
  public $A_PROC14;  
  public $A_PROC15;  
  public $A_PROC16;  
  public $A_PROC17;  
  public $A_PROC18;  
  public $A_PROC19;  
  public $A_PROC20;  
  //
  public function submit() {
    $encounterId = null;
  $r = parent::submit("note/close");
    if ($r->isOk()) {
      $encounterId = $r->msg;
      return $encounterId;
    } else {
      throw new CerberusBadResponse($r->msg);
    }
  }
  //
  static function create($user, $pw, $clientId, $siteCode, $caseId, $date, $locCode, $icds, $cpts, $providerId) {
    $me = new static();
    $me->A_USERNAME = $user;
    $me->A_PASSWORD = $pw;
    $me->A_AGENCY_CLIENTID = $clientId;
    $me->A_PROVIDER_ID = $providerId;
    $me->A_ENCOUNTER_DATE = static::mmddyyyy($date);
    $me->A_LOC_CODE = $locCode; 
    $me->A_SITE_CODE = $siteCode;
    $me->A_CASE = $caseId;
    foreach ($icds as $i => $icd) {
      $j = $i + 1;
      $fid = "A_DIAG$j";
      $me->$fid = $icd;
    }
    foreach ($cpts as $i => $cpt) {
      $j = $i + 1;
      $fid = "A_PROC$j";
      $me->$fid = $cpt;
    }
    return $me;
  }
}
