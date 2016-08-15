<?php
require_once 'php/data/rec/sql/_SqlRec.php';
//
/**
 * Login Requirements
 * DAO for LoginReq
 * @author Warren Hornsby
 */
class LoginReqs {
  //
  /**
   * @param UserLogin $user
   * @return array(name=>LoginReq,..)
   */
  public static function getApplicablesFor($user, $role) {
    $apps = array();
    $recs = LoginReq::fetchAllActive();
    foreach ($recs as $rec) { 
      if ($rec->isApplicableFor($user, $role)) 
        if (! isset($apps[$rec->name])) 
          $apps[$rec->name] = $rec;
    }
    return $apps;
  }
}
//
/**
 * Login Requirement
 * Natural key: name, applies  
 */
class LoginReq extends SqlRec implements NoAudit {
  //
  public $loginReqId;
  public $name;
  public $active;
  public $applies;
  public $grace;
  public $notifyText;
  //
  const ID_BAA = 1;  // loginReqId=1 reserved for business associate agreement
  const NAME_CONFIRM_EMAIL = 'Confirm Email';
  /*
   * Application order comes from a simple DESC sort intended to follow this sequence:
   * - Trial (all)
   * - Pay (most exclusive)
   * - Pay (..)
   * - Pay (all)
   * - All
   */ 
  const APPLIES_TRIAL = 'Trial';
  const APPLIES_PAY = 'Pay';
  const APPLIES_DOC_PRIMARY = 'PayDocPrimary';
  const APPLIES_DOC = 'PayDoc';
  const APPLIES_ALL = null;
  //
  public function getSqlTable() {
    return 'login_reqs';
  } 
  /**
   * Determines if this requirement applies
   * @param UserLogin $user
   * @return bool
   */
  public function isApplicableFor($user, $role) {
    logit_r($user, 'user');
    logit_r($role, 'role');
    logit_r($this, 'this');
    logit_r($user->isDoctor(), 'isDoctor');
    logit_r($role->isPrimaryProvider(), 'isPP');
    switch ($this->applies) {
      case LoginReq::APPLIES_ALL:
        return true;
      case LoginReq::APPLIES_TRIAL:
        return $user->isOnTrial();
      case LoginReq::APPLIES_PAY:
        return $user->isPaying();
      case LoginReq::APPLIES_DOC:
        return $user->isPaying() && $user->isDoctor();
      case LoginReq::APPLIES_DOC_PRIMARY:
        return $role->isPrimaryProvider();
    }
  }
  //
  /**
   * @return array(LoginReq,..)
   */
  public static function fetchAllActive() {
    $c = new LoginReq();
    $c->active = true;
    return parent::fetchAllBy($c, new RecSort('name', '-applies'));
  }
}
