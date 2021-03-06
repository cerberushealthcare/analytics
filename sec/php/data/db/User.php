<?php
require_once "php/data/db/_util.php";

class User0 {

  public $id;
  public $uid;
  public $pw;
  public $name;
  public $admin;
  public $subscription;
  public $active;
  public $regId;
  public $trialExpDt;
  public $userGroupId;
  public $userType;
  public $dateCreated;
  public $licenseState;
  public $license;
  public $dea;
  public $npi;
  public $email;
  public $expiration;
  public $expireReason;
  
  // Helpers
  public $userTypeDesc;

  // Children
  public $userGroup;
  public $billInfo;

  // Subscription types
  const SUBSCRIPTION_TRIAL = 0;
  const SUBSCRIPTION_PAYING = 1;
  const SUBSCRIPTION_FREE = 2;
  const SUBSCRIPTION_INVOICE = 3;
  
  // User types
  const USER_TYPE_DOCTOR = 1;
  const USER_TYPE_OFFICE_EDITOR = 2;
  const USER_TYPE_OFFICE_READER = 3;
  const USER_TYPE_RECIPIENT_EDITOR = 4;
  const USER_TYPE_RECIPIENT_READER = 5;
  
  // Expire reasons
  const EXPIRE_USER_CANCELLED = 1;  // you cancelled service
  const EXPIRE_BILLING_PLAN = 2;  // your billing plan expired
  const EXPIRE_SUPPORT_ACCT_DEACTIVATED = 3;  // for support accts: your doc deactivated you, or your doc switched to basic plan
  const EXPIRE_MISSING_BILLINFO = 4;  // no billinfo record
  const EXPIRE_CARD_EXPIRED = 5;  // your card has expired
  const EXPIRE_CARD_DECLINED = 6;  // your card could not be billed
  const EXPIRE_INVALID_REGISTRATION = 7;  // your registration info was invalid (e.g. license #)
  
  public function __construct($id, $uid, $pw, $name, $admin, $subscription,
                              $active, $regId, $trialExpDt, $userGroupId, $userType,
                              $dateCreated, $licenseState, $license, $dea, $npi, $email,
                              $expiration, $expireReason) {
    $this->id = $id;
    $this->uid = $uid;
    $this->pw = $pw;
    $this->name = $name;
    $this->admin = $admin;
    $this->subscription = $subscription;
    $this->active = $active;
    $this->regId = $regId;
    $this->trialExpDt = $trialExpDt;
    $this->userGroupId = $userGroupId;
    $this->userType = $userType;
    $this->userTypeDesc = $this->getUserTypeDesc($userType);
    $this->dateCreated = $dateCreated;
    $this->licenseState = $licenseState;
    $this->license = $license;
    $this->dea = $dea;
    $this->npi = $npi;
    $this->email = $email;
    $this->expiration = $expiration;
    $this->expireReason = $expireReason;
  }
  
  public static function getUserTypeDesc($userType) {
    switch ($userType) {
      case static::USER_TYPE_DOCTOR:
        return "Provider";
      case static::USER_TYPE_OFFICE_EDITOR:
        return "Clinical";
      case static::USER_TYPE_OFFICE_READER:
        return "Office/Clerical";
      case static::USER_TYPE_RECIPIENT_EDITOR:
        return "Recipient Editor";
      case static::USER_TYPE_RECIPIENT_READER:
        return "Recipient Reader";
    }
  }
  
  // Returns true if the expire reason can be corrected supplying new billing info
  public static function isExpireNeedNewBilling($expireReason) {
    switch ($expireReason) {
      case static::EXPIRE_MISSING_BILLINFO:
      case static::EXPIRE_CARD_EXPIRED;
      case static::EXPIRE_CARD_DECLINED;
        return true;
    }
  }
  
  // Returns true if the expire reason disallows login
  public static function isExpireNoLogin($expireReason) {
    switch ($expireReason) {
      case static::EXPIRE_SUPPORT_ACCT_DEACTIVATED:
      case static::EXPIRE_INVALID_REGISTRATION:
        return true;
    }
  }
  
  public static function getExpireReasonDesc($expireReason) {
    switch ($expireReason) {
      case static::EXPIRE_USER_CANCELLED:
        return "The user of this account has requested to cancel service.";
      case static::EXPIRE_BILLING_PLAN:
        return "The billing plan for this account has expired.";
      case static::EXPIRE_SUPPORT_ACCT_DEACTIVATED:
        return "This support account has been deactived by the group's administrator.";
      case static::EXPIRE_MISSING_BILLINFO:
        return "Billing information is missing for this account and must be supplied."; 
      case static::EXPIRE_CARD_EXPIRED:
        return "The billing source on file for this account has expired.";
      case static::EXPIRE_CARD_DECLINED:
        return "The billing source on file for this account could not be charged.";
      case static::EXPIRE_INVALID_REGISTRATION:
        return "The registration information was invalid.";
    }
  }
  
  public static function getInits($name) {
    $a = explode(" ", strtoupper($name));
    $k = sizeof($a) - 1;
    if ($k == 0) {
      return substr($a[0], 0, 1);
    }
    $j = 0;
    if ($a[0] == "DR." || $a[0] == "DR") {
      $j++;
    }
    if ($a[$k] == "MD." || $a[$k] == "MD" || $a[$k] == "M.D.") {
      $k--;
    }
    $inits = "";
    for ($i = $j; $i <= $k; $i++) {
      $inits .= substr($a[$i], 0, 1);
    }
    return $inits;
  }
}
?>
