<?php
require_once 'php/data/rec/sql/_SqlRec.php';
/**
 * Portal User
 * @author Warren Hornsby
 */
abstract class PortalUserRec extends SqlRec implements AutoEncrypt {
  /*  
  public $portalUserId;
  public $userGroupId;
  public $clientId;
  public $uid;
  public $pw;
  public $active;
  public $status;
  public $createdBy;
  public $dateCreated;
  public $pwSet;
  public $email;
  public $lastName;
  public $ssn4;
  public $zipCode;
  public $cq1;
  public $ca1;
  public $cq2;
  public $ca2;
  public $cq3;
  public $ca3;
  public $pwpt;
  public $tosAccept;
  public $subscription;
  */
  const STATUS_RESET = '0';
  const STATUS_CHALLENGED = '1';
  const STATUS_PW_SET = '2'; 
  //
  const SUBSCRIPTION_FREE = 0;
  const SUBSCRIPTION_PAYING = 1;
  static $SUBSCRIPTIONS = array(
    self::SUBSCRIPTION_FREE => 'Free',
    self::SUBSCRIPTION_PAYING => 'Premium');
  //
  const CUSTOM = 'custom';
  const CUSTOM_Q = '[Custom question]';
  //
  static $QUESTIONS = array(
    '0' => 'What city were you born?',
    '1' => 'What was the name of your favorite pet?',
    '2' => 'What is the maiden name of your mother?',
    '3' => 'What is the first name of your best friend from high school?',
    '4' => 'What is the make of your first car?',
    '5' => 'What is your favorite sport?',
    '6' => 'What is the nickname of your first born child?',
    self::CUSTOM => self::CUSTOM_Q);
  //
  public function getSqlTable() {
    return 'portal_users';
  }
  public function getEncryptedFids() {
    return array('email','lastName','zipCode','ca1','ca2','ca3');
  }
  public function getJsonFilters() {
    return array(
      'active' => JsonFilter::boolean(),
      'pwSet' => JsonFilter::informalDateTime());
  }
  public function isActive() {
    return $this->active;
  }
  //
  protected function setPassword($plain) {
    $password = new Password($plain);
    $this->pw = $password->encrypt();
    logit_r($this, 'setPassword ' . $plain);
  }
}
/**
 * Portal Login
 */
abstract class PortalLoginRec extends SqlRec implements AutoEncrypt {
  /*
  public $logId;
  public $logDate;
  public $logIp;
  public $logSid;
  public $logUid;
  public $logStatus;
  public $portalUserId;
  public $userGroupId;
  */
  const STATUS_OK = '0';
  const STATUS_BAD_UID = '10';
  const STATUS_BAD_PW = '11';
  const STATUS_NOT_ACTIVE = '20';
  //
  public function getSqlTable() {
    return 'portal_logins';
  }
  public function getEncryptedFids() {
    return array('logIp');
  }
  public function isOk() {
    return $this->logStatus == self::STATUS_OK;
  }
}
