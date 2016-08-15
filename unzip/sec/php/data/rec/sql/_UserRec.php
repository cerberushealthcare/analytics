<?php
require_once 'php/data/rec/sql/_SqlRec.php';
/**
 * User Base Class
 * @author Warren Hornsby
 */
abstract class UserRec extends SqlRec {
  public $userId;
  /*  
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
  public $dateCreated;
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
  */
  const SUBSCRIPTION_TRIAL = 0;
  const SUBSCRIPTION_CREDITCARD = 1;
  const SUBSCRIPTION_FREE = 2;
  const SUBSCRIPTION_INVOICE = 3;
  static $SUBSCRIPTIONS = array(
    self::SUBSCRIPTION_TRIAL => 'Trial',
    self::SUBSCRIPTION_CREDITCARD => 'Paying',
    self::SUBSCRIPTION_FREE => 'Free',
    self::SUBSCRIPTION_INVOICE => 'Invoice');
  //
    const EXPIRE_USER_CANCELLED = 1;  
  const EXPIRE_BILLING_PLAN = 2;  
  const EXPIRE_SUPPORT_ACCT_DEACTIVATED = 3;
  const EXPIRE_MISSING_BILLINFO = 4;
  const EXPIRE_CARD_EXPIRED = 5;  
  const EXPIRE_CARD_DECLINED = 6;  
  const EXPIRE_INVALID_REGISTRATION = 7;  
  const EXPIRE_TRIAL_OVER = 8;
  static $EXPIRE_REASONS = array(
    self::EXPIRE_USER_CANCELLED => 'This account was cancelled by user request.',
    self::EXPIRE_BILLING_PLAN => 'The billing plan for this account has expired.',
    self::EXPIRE_SUPPORT_ACCT_DEACTIVATED => "This support account has been deactived by the group's administrator.",
    self::EXPIRE_MISSING_BILLINFO => 'Billing information is missing for this account and must be supplied.',
    self::EXPIRE_CARD_EXPIRED => 'The billing source on file for this account has expired.',
    self::EXPIRE_CARD_DECLINED => 'The billing source on file for this account could not be charged.',
    self::EXPIRE_INVALID_REGISTRATION => 'The registration information was invalid.',
    self::EXPIRE_TRIAL_OVER => 'The trial period for this account has expired.');
  //
  const TYPE_DOCTOR = 1;
  const TYPE_SUPPORT = 11;
  /* Deprecated types */
  const TYPE_OFFICE_EDITOR = 2; 
  const TYPE_OFFICE_READER = 3;
  const TYPE_RECIPIENT_EDITOR = 4;
  const TYPE_RECIPIENT_READER = 5;
  public static $TYPES = array(
    self::TYPE_DOCTOR => 'Doctor',
    self::TYPE_OFFICE_EDITOR => 'OfficeEditor',
    self::TYPE_OFFICE_READER => 'OfficeReader',
    self::TYPE_RECIPIENT_EDITOR => 'RecipEditor',
    self::TYPE_RECIPIENT_READER => 'RecipReader');
  //
  public function getSqlTable() {
    return 'users';
  }
  public function isOnTrial() {
    return $this->subscription == static::SUBSCRIPTION_TRIAL;
  }
  public function isDoctor() {
    return $this->userType == static::TYPE_DOCTOR;
  }
  public function isSupport() {
    return $this->userType > static::TYPE_DOCTOR;
  }
  /**
   * @param string $ptpw plaintext
   * @param string $expires (optional, 'yyyy-mm-dd')
   * @throws UserPasswordException
   */
  public function setPassword($ptpw, $expires = null) {
    if (strlen($ptpw) < 8) 
      throw new UserPasswordException("The new password must be at least eight characters long.");
    $this->pwExpires = $expires;
    $this->pw = static::generateHash($ptpw);
    $this->resetHash = null;
  }
  public function setPassword_asTemporary($ptpw) {
    $this->setPassword($ptpw, nowShortNoQuotes());
  }
  //
  protected static function generateHash($plainText, $salt = null) {
    if ($salt === null) {
      $salt = substr(md5(uniqid(rand(), true)), 0, 9);
    } else {
      $salt = substr($salt, 0, 9);
    }
    return $salt . sha1($salt . $plainText);
  }
}
