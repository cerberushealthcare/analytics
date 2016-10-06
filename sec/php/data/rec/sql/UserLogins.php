<?php
require_once "php/data/LoginSession.php";
require_once 'php/data/rec/sql/_UserRec.php';
require_once 'php/data/rec/sql/_UserGroupRec.php';
require_once 'php/data/rec/sql/_BillInfoRec.php';
require_once 'php/data/rec/sql/_NcUserRec.php';
require_once 'php/data/rec/sql/_AddressRec.php';
require_once 'php/data/email/Email.php';
//
/**
 * User Logins DAO
 * @author Warren Hornsby 
 */
class UserLogins {
  //
  /**
   * @param string $email
   * @throws LoginNotFoundException
   */
  static function requestPasswordReset($email) {
    $user = UserLogin::fetchByEmail($email);
    if ($user == null)
      throw new LoginNotFoundException();
    $user->generateResetHash();
    Email_ResetPassword::send($user);
  }
  static function completePasswordReset($hash) {
    $user = UserLogin::fetchByResetHash($hash);
    if ($user == null)
      throw new LoginNotFoundException();
    $user->savePassword_asTemporary();
    LoginSession::login($user->uid, $user->_ptpw);
  }
  //
  static function log_asOk($loginSession) {
    return Login::log_asOk($loginSession);
  }
  static function log_asDisallow($user) {  
    return Login::log_asDisallow($user);
  }
  static function log_asBadPw($user) {  // returns Attempts
    $login = Login::log_asBadPw($user);
    return static::checkAttempts($login, $user);
  }
  static function log_asBadUid($uid) {  // returns Attempts
    $login = Login::log_asBadUid($uid);
    return static::checkAttempts($login);
  }
  //
  protected static function checkAttempts($login, $user = null) {
    $attemptsIp = Login::countRecentBadLogins_forIp($login);
    static::sleep($attemptsIp); 
    if ($attemptsIp == 20) 
      Email_BruteForceAlert::send($me, $attemptsIp, 10);
    if ($user) { 
      $attemptsUid = Login::countRecentBadLogins_forIpUid($login);
      if ($attemptsUid >= 7) {
        if ($attemptsUid == 7) {
          $user->savePassword_asTemporary();
          Email_UidLocked::send($user);
        }
        return Attempts::asLocked($attemptsIp);
      }
    }
    return Attempts::asCount($attemptsIp);
  }
  protected static function sleep($attempts) {
    $secs = static::sleeptime($attempts);
    if ($secs > 0) 
      sleep($secs);
  }
  protected static function sleeptime($attempts) {
    if ($attempts < 3)
      return 0;
    if ($attempts < 6)
      return 2;
    if ($attempts < 9)
      return 4;
    if ($attempts < 12)
      return 8;
    return 16;
  }
}
class Attempts extends Rec {
  //
  public $count;   // of recent bad attempts for IP
  public $locked;  // true if last UID attempt now locked out
  //
  static function asCount($count) {
    return new static($count);
  }
  static function asLocked($count) {
    return new static($count, true);
  }
}
/**
 * Login Log
 */
class Login extends SqlRec implements NoAuthenticate {
  //
  //If you add a public property here it will show up in the insert query that is generated.
  public $loginId;
  public $time;
  public $ipAddress;
  public $sessionId;
  public $uid;
  public $userId;
  public $userGroupId;
  public $result;
  //
  const RESULT_OK = 1;
  const RESULT_BAD_PW = 91;
  const RESULT_BAD_UID = 92;
  const RESULT_DISALLOW = 93;
  //
  public function getSqlTable() {
    return 'logins';
  }
  public function isNotOk() {
    return $this->result != static::RESULT_OK;
  }
  public function isBadPassword() {
    return $this->result == static::RESULT_BAD_PW;
  }
  //
  static function log_asOk($loginSession) {
    echo 'Logging as OK!';
    return static::log($loginSession->sessionId, $loginSession->uid, $loginSession->userId, $loginSession->userGroupId, static::RESULT_OK, $user->name);
  }
  static function log_asBadPw($user) {
    return static::log(null, $user->uid, $user->userId, $user->userGroupId, static::RESULT_BAD_PW, $user->name);
  }
  static function log_asBadUid($uid) {
    return static::log(null, $uid, null, null, static::RESULT_BAD_UID, $user->name);
  }
  static function log_asDisallow($user) {
    return static::log(null, $user->uid, $user->userId, $user->userGroupId, static::RESULT_DISALLOW, $user->name);
  }
  protected static function log($sid, $uid, $userId, $ugid, $result, $username = null) { //Assign the values for an insert statement.
    $me = new static();
	if ($username) {
		$me->uid = $username;
	}
	//$me->time = 'sysdate';
    //$me->time = nowNoQuotes();
    $me->ipAddress = $_SERVER['REMOTE_ADDR'];
    $me->sessionId = $sid ?: session_id();
    //$me->uid = ;//'Happy go Lucky';//$uid;
    $me->userId = $userId;
    $me->userGroupId = $ugid;
    $me->result = $result;
    $me->save();
    return $me;
  }
  static function countRecentBadLogins_forIp($login) {
    return static::countRecentBadLogins($login->ipAddress);
  }
  static function countRecentBadLogins_forIpUid($login) {
    return static::countRecentBadLogins($login->ipAddress, $login->uid);
  }
  static function countRecentBadLogins_forUid($uid) {
    return static::countRecentBadLogins(null, $uid, '5 DAY');
  }
  protected static function countRecentBadLogins($ip, $uid = null, $interval = '10 MINUTE') {
    $me = new static();
    $me->ipAddress = $ip;
    $me->uid = $uid;
    $me->time = CriteriaValue::withinInterval($interval);
    $me->result = CriteriaValue::greaterThan(static::RESULT_OK);
    return static::count($me);
  }
  static function getLastGoodLoginTime($uid) {
    $sql = "SELECT MAX(time) FROM logins WHERE uid='$uid' AND result=1";
    return Dao::fetchValue($sql); 
  }
}
class Email_BruteForceAlert extends Email_Alert {
  //
  public $subject = 'Possible Brute Force Attack';
  //
  static function send($login, $attempt, $within) {
    $e = new static();
    $e->html()
      ->p("Bad login attempt #$attempt within $within minutes. Login info:")
      ->ul_()
      ->li("IP: $login->ipAddress")
      ->li("Time: $login->time")
      ->li("UID: $login->uid");
    $e->mail();
  }
}
class Email_ResetPassword extends Email {
  //
  public $subject = 'Password Reset';
  //
  static function send($user) {
    $e = new static();
    $e->to = $user->email;
    $e->html()
      ->p($user->name)
      ->p_()->add('Your user ID is ')->b($user->uid)->_()
      ->p_()
        ->br('Click this link to reset your password:')
        ->a(MyEnv::url('reset-pw.php?h=' . $user->resetHash))->_()
      ->hr()->br()
      ->p("If you didn't ask to reset your password, you do not need to take further action and can safely disregard this email.");
    $e->mail();
  }
}
class Email_UidLocked extends Email {
  //
  public $subject = 'Lockout Notice';
  //
  static function send($user) {
    $e = new static();
    $e->to = $user->email;
    $e->html()
      ->p($user->name)
      ->p('This is a notice that your Clicktate ID has been locked out as a result of too many login attempts.')
      ->p_()
      ->br('To re-establish your login, you will need to request a password change at this link:')
      ->a(MyEnv::url('forgot-login.php'))->_()
      ->p('If you are having problems, you can also call us at 1-888-825-4258.');
    $e->mail();
  }
}
/**
 * User Login
 */
class UserLogin extends UserRec implements NoAuthenticate {
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
  public /*UserGroup_Login*/ $UserGroup;
  public /*BillInfo_Login*/ $BillInfo;
  public /*NcUser_Login*/ $NcUser;
  //
  public function toJsonObject(&$o) {
    unset($o->uid);
    unset($o->pw);
    unset($o->admin);
    unset($o->subscription);
    unset($o->regId);
    unset($o->trialExpdt);
    unset($o->userGroupId);
    unset($o->tosAccepted);
    unset($o->BillInfo);
  }
  public function isLoginDisallowed() {
    if (! $this->active && $this->isSupport())
      return true;
    if ($this->expireReason) {
      switch ($this->expireReason) {
        case static::EXPIRE_SUPPORT_ACCT_DEACTIVATED:
        case static::EXPIRE_INVALID_REGISTRATION:
          return true;
      }
    }
  }
  public function isPasswordCorrect($ptpw) {
	echo 'isPasswordCorrect: Check ' . $this->name . ' and ' . $ptpw;
	if (MyEnv::$IS_ORACLE) {
		return LoginSession::checkOracleLogin($this->name, $ptpw);
	}
	else {
		$pw = static::generateHash($ptpw, $this->pw);
		return ($this->pw == $pw); 
	}
  }
  public function isPasswordExpired() {
    if ($this->pwExpires)
      return isTodayOrPast(dateToString($this->pwExpires));
  }
  public function haveTermsExpired() {
    return $this->tosAccepted < MyEnv::$TOS_DATE;
  }
  public function isTrialFirstLogin() {
    if ($this->isOnTrial())
      return $this->tosAccepted == null;
  }
  public function canErx() {
    return isset($this->NcUser);
  }
  public function getNcPartnerId() {
    if (isset($this->NcUser))
      return $this->NcUser->partnerId;
  }
  public function fetchDocId() {
    $ugid = $this->userGroupId;
	if (MyEnv::$IS_ORACLE) {
		$sql = "SELECT user_id FROM users WHERE user_group_id=$ugid AND user_type=1 AND active=1";
	}
	else {
		$sql = "SELECT user_id FROM users WHERE user_group_id=$ugid AND user_type=1 AND active=1 LIMIT 1";
	}
    return Dao::fetchValue($sql); 
  }
  public function hasTrialExpired() {
    return isTodayOrPast($this->trialExpdt);
  }
  public function getTrialDaysLeft() {
    if ($this->isOnTrial())
      return intval((strtotime($this->trialExpdt) - strtotime(date("Y-m-d"))) / 86400); 
  }
  public function isPaying() {
    return ! $this->isOnTrial();
  }
  public function isAdmin() {
    return $this->admin == '1';
  }
  public function needsNewBilling() {
    if (! $this->active) {
      switch ($this->expireReason) {
        case static::EXPIRE_MISSING_BILLINFO:
        case static::EXPIRE_CARD_EXPIRED;
        case static::EXPIRE_CARD_DECLINED;
          return true;
      }
    }
  }
  public function acceptTerms() {
    TosAccepts::record();
    $this->tosAccepted = nowNoQuotes();
    $this->save();
  }
  /**
   * @param UserLogin.EXPIRE $reason
   * @return string 
   */
  public function deactivate($reason) {
    $this->active = false;
    $this->expireReason = $reason;
    $this->save();
    return static::$EXPIRE_REASONS[$reason];
  }
  /**
   * @return string random hash for reset password email
   */
  public function generateResetHash() {
    $this->resetHash = static::generateHash('');
    $this->save();
    return $this->resetHash;
  }
  /**
   * @param string $old plaintext
   * @param string $new plaintext
   * @throws UserPasswordException
   */
  public function changePassword($old, $new) {
    if (! $this->isPasswordCorrect($old)) 
      throw new UserPasswordException("The current password supplied is incorrect.");
    if ($new == $old) 
      throw new UserPasswordException("The new password must be different from the old password.");
    $this->setPassword($new);
    $this->save();
  }
  /**
   * @return User with ptpw prop assigned 
   */
  public function savePassword($ptpw, $expires = null) {
    $this->setPassword($ptpw, $expires);
    $this->_ptpw = $ptpw;
    $this->save();
    return $this;
  }
  public function savePassword_asTemporary($pw = null) {
    if ($pw == null)
      $pw = "temp" . mt_rand(10000, 99999);
    $expires = nowShortNoQuotes();
    $this->savePassword($pw, $expires);
    return $this;
  }
  public function saveEmail($email) {
    $this->email = $email;
    $this->save();
    return $this;
  }
  //
  static function fetchByUid($uid) {
    $c = static::asCriteria($uid);
    $me = static::fetchOneBy($c);
	//echo 'fetchByUID: ME is ';
	//var_dump($me);
    return $me;
  }
  static function fetchByEmail($email) {
    $c = new static();
    $c->email = $email;
    $c->active = true;
    $me = static::fetchOneBy($c);
	
    return $me;
  }
  static function fetchByResetHash($hash) {
    $c = new static();
    $c->resetHash = $hash;
    $me = static::fetchOneBy($c);
    return $me;
  }
  static function asCriteria($uid) {
    if ($uid == null) 
      throw new UserLoginException('UserLogin criteria required');
    $c = new static();
    $c->uid = $uid;
    $c->UserGroup = UserGroup_Login::asJoin();
    $c->BillInfo = new BillInfo_Login();
    $c->NcUser = new NcUser_Login();
    return $c;
  }
}
class UserLogin_Batch extends UserLogin implements NoAuthenticate {
  //
  const USER_ID_BATCH = 0;
  //
  static function create($ugid, $label) {
    $me = new static();
    $me->userId = static::USER_ID_BATCH;
    $me->uid = 'BATCH';
    $me->name = "BATCH $label";
    $me->subscription = static::SUBSCRIPTION_FREE;
    $me->active = true;
    $me->userGroupId = $ugid;
    $me->userType = static::TYPE_DOCTOR;
    $me->UserGroup = UserGroup_Login::fetch($ugid);
    return $me;
  }
}
class UserLogin_Sys extends UserLogin_Batch implements NoAuthenticate {
  //
  const USER_ID_SYS = -1;
  //
  static function create($ugid) {
    $me = parent::create($ugid, '');
    $me->userId = static::USER_ID_SYS;
    $me->uid = 'SYS';
    $me->name = 'SYS';
    $me->sys = true;
    return $me;
  }
}
class UserGroup_Login extends UserGroupRec implements NoAuthenticate {
  //
  public $userGroupId;
  public $name;
  public $usageLevel;
  public $estTzAdj;
  public $sessionTimeout;
  public $demo;
  public $Address;
  //
  static function asJoin() {
    $c = new static();
    $c->Address = AddressUserGroup_Login::asJoin();
    return CriteriaJoin::requires($c);
  }
}
class AddressUserGroup_Login extends Address {
  //
  public function toJsonObject(&$o) {
    parent::toJsonObject($o);
    unset($o->tableCode);
    unset($o->tableId);
  }
  static function asJoin() {
    $c = new static();
    $c->tableCode = static::TABLE_USER_GROUPS;
    $c->type = static::TYPE_SHIP;
    return CriteriaJoin::requires($c, 'tableId');
  }
}
class BillInfo_Login extends BillInfoRec implements ReadOnly {
  //
  public $userId;
  public $expMonth;
  public $expYear;
  public $lastBillStatus;
}
class NcUser_Login extends NcUserRec implements ReadOnly {
  //
  public $userId;
  public $userType;
  public $roleType;
  public $partnerId;
  public $nameLast;
  public $nameFirst;
  public $nameMiddle;
  public $namePrefix;
  public $nameSuffix;
  public $freeformCred;
} 
class TosAccepts extends SqlRec {
  //
  public $tosAcceptId;
  public $userId;
  public $userGroupId;
  public $version;
  public $dateAccepted;
  //
  public function getSqlTable() {
    return 'tos_accepts';
  }
  //
  static function record() {
    global $login;
    $me = new static();
    $me->userId = $login->userId;
    $me->userGroupId = $login->userGroupId;
    $me->version = MyEnv::$TOS_VERSION;
    $me->dateAccepted = nowNoQuotes();
    $me->save();
  }
}
/**
 * Exceptions
 */
class UserLoginException extends Exception {}
class UserPasswordException extends DisplayableException {}
class LoginNotFoundException extends Exception {}
