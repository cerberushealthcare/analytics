<?php
require_once 'config/MyEnv.php';
require_once 'php/data/apex/_Apex.php';
//
class CerberusBilling {
  //
  static function login($user, $pw) {
    $loginInfo = CQ_Login::create($user, $pw)->submit();
    return $loginInfo;
  }
}
class CerberusQuery extends ApexQuery {
  //
  public $base = 'https://208.187.161.89';
  //
  public function submit($sessionId = null, $cookie = null) {
    $response = parent::submit($sessionId, $cookie);
    return static::parseResponse($response);
  }
  //
  static function withItems() {
    $me = new static();
    $me->Items = new ApexItems();
    return $me;
  }
  static function parseResponse($response) {
    $a = explode(',', $response->body);
    if ($a[0] == 'OK')
      return $a[1];
    if ($a[0] == 'ERROR')
      throw new CerberusErrorEx($a[1]);
    else 
      throw new CerberusBadResponseEx($response->body);
  }
  //
  protected function asUrl($sessionId = null) {
    $this->app = MyEnv::$CERBERUS_APEX_APP;
    return parent::asUrl($sessionId);
  }
}
class CQ_Login extends CerberusQuery {
  //
  public $page = 'CT_LOGIN';
  public $request = 'LOGIN';
  public $clearCache = '400';
  //
  public function submit() {
    $response = parent::submit();
    return $response;
  }
  //
  static function create($user, $pw) {
    global $login;
    $me = static::withItems();
    $me->Items->P400_USERNAME = $user;
    $me->Items->P400_PASSWORD = $pw;
    $me->Items->P400_LOGIN = 'Y';
    $me->Items->APP_EMR_SESSIONID = $login->sessionId;
    return $me;
  }
  static function parseResponse($response) {
    return CQ_LoginInfo::from($response);
  }
}
class CQ_LoginInfo {
  //
  public $cookie;
  public $sessionId;
  //
  static function from($r) {
    $me = new static();
    $me->cookie = $r->getCookies();
    $me->sessionId = CerberusQuery::parseResponse($r);
    return $me;
  }
}
class CQ_CloseNote extends CerberusQuery {
  //
  public $page = 'CT_CLOSENOTE';
  public $request = 'NEW';
  public $clearCache = '401';
  //
  public function submit($sessionId, $cookie) {
    $encounterId = parent::submit($sessionId, $cookie);
    return $encounterId;
  }
  //
  static function create($clientId, $siteCode, $caseId, $date, $locCode, $icds, $cpts) {
    $me = static::withItems();
    $me->Items->P0_AGENCY_CLIENTID = $clientId;
    $me->Items->P401_SITE_CODE = $siteCode;
    $me->Items->P401_CASE = $caseId;
    $me->Items->P401_ENCOUNTER_DATE = $date;
    $me->Items->P401_LOC_CODE = $locCode; 
    $me->Items->P401_DIAG = $icds;
    $me->Items->P401_PROC = $cpts;
    return $me;
  }
}
class CQ_Page extends CerberusQuery {
  //
  public $page = 'CT_PAGE';
  public $request = 'PAGE';
  public $clearCache = '403';
  //
  const PAGE_SUPERBILL = '748';
  const PAGE_LIST_SUPERBILLS = '765';
  //
  static function create_asSuperbill($user, $pw, $clientId, $encounterId) {
    return static::create(
      $user, 
      $pw, 
      static::PAGE_SUPERBILL, 
      $clientId, 
      $encounterId, 
      static::PAGE_LIST_SUPERBILLS);
  }
  static function create_asListSuperbills($user, $pw, $clientId) {
    return static::create(
      $user, 
      $pw, 
      static::PAGE_LIST_SUPERBILLS, 
      $clientId,
      'A');
  }
  protected static function create($user, $pw, $page, $clientId, $arg1, $arg2 = null, $arg3 = null) {
    $me = static::withItems();
    $me->Items->P403_USERNAME = $user;
    $me->Items->P403_PASSWORD = $pw;
    $me->Items->P403_REQPAGE = $page;
    $me->Items->P403_LOGIN = 'Y';
    $me->Items->P0_AGENCY_CLIENTID = $clientId;
    $me->Items->P403_ARG1 = $arg1;
    $me->Items->P403_ARG2 = $arg2;
    $me->Items->P403_ARG3 = $arg3;
    return $me;
  }
}
class CQ_Superbill extends CerberusQuery {
  //
  public $page = '748';
  public $clearCache = '748';
  //
  public function submit($sessionId, $cookie) {
    $html = parent::submit($sessionId, $cookie);
    return $html;
  }
  //
  static function create($clientId, $encounterId) {
    $me = static::withItems();
    $me->Items->P0_AGENCY_CLIENTID = $clientId;
    $me->P748_EPMRENCT_ID = $encounterId;
    $me->P748_RETURN = '765';
    return $me;
  }
  static function parseResponse($response) {
    return $response->body;
  }
}
//
class CerberusErrorEx extends Exception {}
class CerberusBadResponseEx extends CerberusErrorEx {}