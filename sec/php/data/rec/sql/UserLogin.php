<?php
require_once 'php/data/rec/sql/_UserRec.php';
/**
 * User Login 
 */
class UserLogin extends UserRec implements ReadOnly {
  //
  public $userId;
  public $uid;
  public $name;
  public $admin;
  public $subscription;
  public $active;
  public $regId;
  public $trialExpdt;
  public $userGroupId;
  public $userType;
  public $licenseState;
  public $license;
  public $dea;
  public $npi;
  public $email;
  public $expiration;
  public $expireReason;
  //
  public $_authenticated = true;  // lookups made prior to $myLogin set to session
  //
  public function isPrimaryDoc() {
    static $doc;
    if ($doc == null)
      $doc = self::fetchPrimaryDoc($this->userGroupId);
    return ($this->userId == $doc->userId);
  }
  //
  /**
   * @param int $ugid
   * @return UserLogin
   */
  public static function fetchPrimaryDoc($ugid) {
    $c = new UserLogin();
    $c->userGroupId = $ugid;
    $c->userType = UserLogin::TYPE_DOCTOR;
    $c->active = true;
    return current(parent::fetchAllBy($c, new RecSort('userId')));
  }
}
