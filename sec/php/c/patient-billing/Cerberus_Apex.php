<?php
require_once 'config/MyEnv.php';
require_once 'php/data/rec/sql/_ICardRec.php';
require_once 'php/data/xml/_XmlRec.php';
require_once 'php/data/curl/ApexQuery.php';
require_once 'php/c/patient-billing/Cerberus_Exception.php';
//
/**
 * Cerberus APEX Queries
 */
abstract class CerberusApexQuery extends ApexQuery {
  //
  static function withItems() {
    $me = new static();
    $me->Items = new ApexItems();
    return $me;
  }
  //
  public function /*CerberusApexResponse*/submit($sessionId = null, $cookie = null) {  
    $response = parent::submit($sessionId, $cookie);
    return CerberusApexResponse::from($response);
  }
  //
  public function asUrl($sessionId = null) {
    $clogin = CerberusLogin::fetch();
    $this->base = $clogin->queryBase;
    $this->app = $clogin->queryApp;
    return parent::asUrl($sessionId);
  }
  protected function mmddyyyy($date) {
    return date("m/d/Y", strtotime($date));
  }
}
class CerberusApexResponse extends BasicRec {
  //
  public /*CurlResponse_All*/$response;
  public $status;
  public $data;
  //
  static function from(/*CurlResponse*/$response) {
    $me = new static($response);
    $me->setFrom($response->body);
    $me->throwIfError();
    return $me; 
  }
  //
  public function throwIfError() {
    if ($this->status == 'ERROR')
      throw new CerberusErrorResponse($this->data);
  }
  protected function setFrom($body) {
    $a = explode(',', $body);
    $this->status = $a[0];
    if (count($a) > 1)
      $this->data = $a[1]; 
    $this->validate($body);
  }
  protected function validate($body) {
    switch ($this->status) {
      case 'OK':
      case 'ERROR':
        break;
      default:
        throw new CerberusBadResponse($body);
    }
  }
}
class CQ_Login extends CerberusApexQuery {
  //
  public $page = 'CT_LOGIN';
  public $request = 'LOGIN';
  public $clearCache = '400';
  //
  private $user;
  private $pw;
  //
  static function create($user, $pw) {
    global $login;
    $me = static::withItems();
    $me->Items->P400_USERNAME = $user;
    $me->Items->P400_PASSWORD = $pw;
    $me->Items->P400_LOGIN = 'Y';
    $me->Items->APP_EMR_SESSIONID = $login->sessionId;
    $me->user = $user;
    $me->pw = $pw;
    return $me;
  }
  //
  public function submit() {
    $r = parent::submit();
    $cookie = $r->response->getCookies();
    $sessionId = $r->data;
    $info = new CQ_LoginInfo($this->user, $this->pw, $cookie, $sessionId);
    logit_r($info, 'CQ_LoginInfo');
    return $info;
  }
}
class CQ_LoginInfo extends Rec {
  //
  public $user;
  public $pw;
  public $cookie;
  public $sessionId;
  public $url;
  //
  static function from($user, $pw, $cookieValue = null, $sessionId = null, $url = null) {
    $me = new static();
    $me->user = $user;
    $me->pw = $pw;
    $me->cookie = "PCOOKIE=" . $cookieValue;
    $me->sessionId = $sessionId;
    $me->url = $url;
    return $me;
  }
}
class CQ_CloseNote extends CerberusApexQuery {
  //
  public $page = 'CT_CLOSENOTE';
  public $request = 'NEW';
  public $clearCache = '401';
  //
  static function create($user, $pw, $clientId, $siteCode, $caseId, $date, $locCode, $icds, $cpts) {
    $me = static::withItems();
    $me->Items->P401_USERNAME = $user;
    $me->Items->P401_PASSWORD = $pw;
    $me->Items->P0_AGENCY_CLIENTID = $clientId;
    $me->Items->P401_SITE_CODE = $siteCode;
    $me->Items->P401_CASE = $caseId;
    $me->Items->P401_ENCOUNTER_DATE = static::mmddyyyy($date);
    $me->Items->P401_LOC_CODE = $locCode; 
    $me->Items->P401_DIAG = ApexItems::asArray($icds);
    $me->Items->P401_PROC = ApexItems::asArray($cpts);
    return $me;
  }
  //
  public function submit($sessionId, $cookie) {
    $encounterId = parent::submit($sessionId, $cookie)->data;
    return $encounterId;
  }
}
abstract class CQ_Page extends CerberusApexQuery {
  //
  public $page = 'CT_PAGE';
  public $request = 'PAGE';
  public $clearCache = '403';
  //
  const PAGE_SUPERBILL = '748';
  const PAGE_LIST_SUPERBILLS = '765';
  const PAGE_UNSIGNED_SUPERBILLS = '929';
  const PAGE_INSURANCE = '404';
  const PAGE_APPTS = '405';
  //
  static function create($user, $pw, $sessionId, $page, $clientId, $arg1, $arg2 = null, $arg3 = null) {
    $me = static::withItems();
    $me->Items->P403_USERNAME = $user;
    $me->Items->P403_PASSWORD = $pw;
    $me->Items->P403_REQPAGE = $page;
    $me->Items->P403_LOGIN = 'Y';
    $me->Items->P403_SESSION = $sessionId;
    $me->Items->P0_AGENCY_CLIENTID = $clientId;
    $me->Items->P403_ARG1 = $arg1;
    $me->Items->P403_ARG2 = $arg2;
    $me->Items->P403_ARG3 = $arg3;
    return $me;
  }
  public function asUrl($sessionId = null) {
    $clogin = CerberusLogin::fetch();
    $this->base = $clogin->queryBase;
    $this->app = $clogin->queryApp;
    //$sessionId = $clogin->querySessionId; // get override session value
    return parent::asUrl($sessionId);
  }
}
class CQ_Superbill extends CQ_Page {
  //
  static function create($user, $pw, $sessionId, $clientId, $encounterId) {
    return parent::create(
      $user, 
      $pw,
      $sessionId, 
      static::PAGE_SUPERBILL, 
      $clientId, 
      $encounterId, 
      static::PAGE_LIST_SUPERBILLS);
  }
}
class CQ_ListSuperbills extends CQ_Page {
  //
  static function create($user, $pw, $sessionId, $clientId) {
    return parent::create(
      $user, 
      $pw, 
      $sessionId, 
      static::PAGE_LIST_SUPERBILLS, 
      $clientId,
      'A');
  }
}
class CQ_UnsignedSuperbills extends CQ_Page {
  //
  static function create($user, $pw, $sessionId, $providerId = null) {
    return parent::create(
      $user, 
      $pw, 
      $sessionId, 
      static::PAGE_UNSIGNED_SUPERBILLS, 
      $providerId);
  }
}
class CQ_Appt extends CQ_Page {
  //
  static function create($user, $pw, $sessionId, $providerId, /*'mm/dd/yyyy'*/$start, $end = null) {
    return parent::create(
      $user,
      $pw,
      $sessionId, 
      static::PAGE_APPTS,
      0, /*no patientId*/
      $providerId,
      $start,
      $end);
  } 
  //
  public function /*CR_Appt[]*/fetch() {
    $response = parent::submit_inSession();
    return CR_Appt::all($response);
  }
}
class CR_Appt extends BasicRec {
  //
  public $appt_id;
  public $agency_clientid;
  public $appt_start;
  public $appt_end;
  public $duration_min;
  public $alldayflag; 
  public $firstname;
  public $lastname; 
  public $chart;
  public $reason;
  public $status;
  public $appt_type;
  public $note;
  public $site_code;
  public $pos_code;
  public $waiting_for;
  public $waiting_for_color;
  public $userid; 
  //
  static function all(/*CurlResponse*/$response) {
    $xml = static::extractXml($response->body);
    if (! empty($xml)) {
      $o = XmlRec::parse($xml);
      if (isset($o->appointment))
        return static::fromObjects($o->appointment);
    }
  }  
  //
  protected static function extractXml($html) {
    $tag = '<appointment>';
    $_tag = '</appointment>';
    $a = explode($tag, $html);
    if (count($a) > 1) {
      array_shift($a);
      foreach ($a as &$i) {
        $b = explode($_tag, $i);
        $i = $tag . $b[0] . $_tag;
      }
      return '<appts>' . implode('', $a) . '</appts>';  
    }
  }
}
class CQ_Insurance extends CQ_Page {
  //
  const FORMAT_HTML = 'H';
  const FORMAT_XML = 'X';
  //
  static function create($user, $pw, $sessionId, $clientId, $format = self::FORMAT_HTML) {
    return parent::create(
      $user,
      $pw,
      $sessionId,
      static::PAGE_INSURANCE,
      $clientId,
      $format);
  }
  static function create_forXml($user, $pw, $clientId) {
    return static::create($user, $pw, $clientId, static::FORMAT_XML);
  }
  //
  public function /*CR_Insurance[]*/fetch() {
    $response = parent::submit_inSession();
    return CR_Insurance::all($response);
  }
}
class CR_Insurance extends CR_InsuranceRec {
  /*
  public $PAYER;
  public $PAYER_ID;
  public $POLICY;
  public $EFF_DATE;
  public $END_DATE; 
  public $BILLING_SEQ;
  public $COPAY; 
  public $COINSURANCE; 
  public $REMAINING_DEDUCT; 
  public $ELIGIBILITY;
  public $INSERT_UPDATE_BY;
  */
  //
  static function all(/*CurlResponse*/$response) {
    $xml = static::extractXml($response->body);
    $o = XmlRec::parse($xml);
    if (isset($o->INSURANCE_ENTRY))
      return static::fromObjects($o->INSURANCE_ENTRY);
  }  
  //
  protected static function extractXml($html) {
    $tag = 'INSURANCE_LIST';
    $a = explode($tag, $html);
    if (count($a) == 3)
      return "<$tag" . $a[1] . "$tag>";
  }
}