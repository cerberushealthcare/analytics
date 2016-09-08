<?php
require_once 'ApiLogin.php';
require_once 'ApiAddress.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/analytics/sec/php/data/rec/sql/_UserRec.php';
/**
 * User
 */
class ApiUser extends ApiLogin {
  // 
  public $name;
  public $role;
  public $license;
  public $licenseState;
  public $dea;
  public $npi;
  public $password;
  public $practiceId;
  public $internalStaffId;
  // 
  public $_address;  // ApiAddress
  /**
   * Constructor
   * @param ['field'=>value,..] $data
   */
  public function __construct($data) {
    $required = array('practiceId','userId','name','role');
    $this->load($data, $required);
    $this->_address = new ApiAddress($data);
  }
  /**
   * Build USER user_type
   * @return User::USER_TYPE_
   */
  public function getUserType() {
    switch (strtoupper($this->role)) {
      case 'DOCTOR':
        return UserRec::TYPE_DOCTOR;
      case 'ADMIN':
        return UserRec::TYPE_OFFICE_EDITOR;
      case 'SUPPORT':
        return UserRec::TYPE_OFFICE_EDITOR;
      default:
        $this->throwApiException("Invalid role $this->role");
    }
  }
  public function getRoleType() {
    switch (strtoupper($this->role)) {
      case 'DOCTOR':
        return UserRole::TYPE_PROVIDER;
      case 'ADMIN':
        return UserRole::TYPE_CLERICAL;
      case 'SUPPORT':
        return UserRole::TYPE_CLINICAL;
      default:
        $this->throwApiException("Invalid role $this->role");
    }
  }
}
//
class User_Api extends UserLogin {
  //
  public $userId;
  public $uid;
  public $pw;
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
  public $pwExpires;
  public $tosAccepted;
  public $roleType;
  public $mixins;
  public $resetHash;
  //
  static function from(/*ApiUser*/$api, $ugid, $userId = null) {
    $me = new static();
    $me->userId = $userId;
    $me->uid = $api->getUserUid();
    if (! empty($api->password))
      $me->pw = static::generateHash($api->password);
    $me->name = $api->name;
    $me->admin = false;
    $me->subscription = static::SUBSCRIPTION_FREE;
    $me->active = true;
    $me->userGroupId = $ugid;
    $me->userType = $api->getUserType();
    $me->licenseState = $api->licenseState;
    $me->license = $api->license;
    $me->dea = $api->dea;
    $me->npi = $api->npi;
    $me->email = $api->_address->email;
    $me->roleType = $api->getRoleType();
    return $me;
  }
  static function asUpdate(/*ApiUser*/$api, $ugid, $userId) {
    $c = new static($userId);
    log2_r($c, 'criteria');
    $me = static::fetchOneBy($c);
    log2_r($me, 'fetched');
    $me->uid = $api->getUserUid();
    if (! empty($api->password))
      $me->pw = static::generateHash($api->password);
    $me->name = $api->name;
    $me->active = true;
    $me->licenseState = $api->licenseState;
    $me->license = $api->license;
    $me->dea = $api->dea;
    $me->npi = $api->npi;
    $me->email = $api->_address->email;
    log2_r($me, 'asUpdate');
    return $me;
  }
}
?>