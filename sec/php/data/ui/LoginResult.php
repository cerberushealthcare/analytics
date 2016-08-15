<?php
require_once "php/data/db/User.php";
require_once "php/data/db/UserGroup.php";
require_once "php/data/rec/sql/UserRoles.php";
require_once "php/data/ui/Permissions.php";
//
class LoginResult {  // global $myLogin
  //
  public $uid;  // user's login ID
  public $pw;  // user's login password
  public $name;  // user's name
  public $sessionId;  // php session ID
  public $success;  // true if login succeeded (ID and password valid)
  public $userId;  // user's USERS table ID
  public $userGroupId;  // user's USER_GROUPS table ID
  public $usageLevel;  // 0=basic, 1=EMR, 2=ePrescribe
  public $timeout;  // session timeout in min
  public $userType;  // user's USER_TYPE column
  public $roles;  // user's ROLES column  
  public $admin;  // true if Clicktate admin
  public $subscription;  // 0=trial, 1=paying, 2=free
  public $active;  // true if user's account still active
  public $onTrial;  // true if still a trial account
  public $daysLeft;  // days left until trial expires / CC expires
  public $expireReason;  // why account is not active
  //public $permissions;  // access levels for site functions
  public $onProd;  // true if on production environment
  public $estAdjust;  // EST timezone adjustment
  public $pwExpired;  // true if pw has expired
  public $tablet;  // true if a touch screen
  public $hideStickies = array(); // array('stickyId'=>1,..) for closed stickies
  public $glassBreaks = array();  // array(cid=>1,..) for patients chart brought up after restriction warning
  public $env;
  public $loginReqs;  // UserLoginReqs
  public $showReqs;
  public $tosExpired;  // needs to re-accept TOS
  public $cache;
  public /*UserRole*/ $role;
  //
  const ENV_LOCAL = 1;
  const ENV_TEST = 2;
  const ENV_CERT = 8;
  const ENV_PRODUCTION = 9;
  //
  const ENV_PAPYRUS_LOCAL = 11;
  const ENV_PAPYRUS_TEST = 12;
  const ENV_PAPYRUS_PROD = 19;
  //
  public function __construct() {
    $this->env = LoginResult::getEnv();
    $this->cache = array();
  }
  // Called once userType and active established
  public function setPermissions() {
    $this->permissions = new Permissions($this->userType, $this->active, $this->usageLevel);
    //$this->Role = UserRole::from($this->userType, $this->active, $this->roles, $this->usageLevel);
  }
  //
  // Helper functions
  public function isAdmin() {
    return $this->admin;
  }
  public function isBasic() {
    return $this->usageLevel == UserGroup0::USAGE_LEVEL_BASIC;
  }
  public function isEmr() {
    return $this->usageLevel >= UserGroup0::USAGE_LEVEL_EMR;
  }
  public function isErx() {
    return $this->usageLevel == UserGroup0::USAGE_LEVEL_EPRESCRIBE;
  }
  public function isDoctor() {
    return $this->userType == User0::USER_TYPE_DOCTOR;
  }
  public function isInactiveDoctor() {
    return LoginResult::isDoctor() && ! $this->active;
  }
  public function isNeedNewBilling() {
    return User0::isExpireNeedNewBilling($this->expireReason);
  }
  public function isLoginNotAllowed() {
    if (! $this->active && ! $this->isDoctor()) 
      return true;
    return User0::isExpireNoLogin($this->expireReason);
  }
  public function getInactiveReason() {
    return User0::getExpireReasonDesc($this->expireReason);
  }
  public function isOnProd() {
    return ($this->env == LoginResult::ENV_PRODUCTION);
  }
  public function isOnCert() {
    return ($this->env == LoginResult::ENV_CERT);
  }
  public function isTablet() {
    return ($this->tablet);
  }
  public function isPapyrus() {
    static $isPapyrus;
    if ($isPapyrus === null) {
      $isPapyrus = $this->env == LoginResult::ENV_PAPYRUS_TEST || $this->env == LoginResult::ENV_PAPYRUS_PROD || $this->env == LoginResult::ENV_PAPYRUS_LOCAL;
    }
    return $isPapyrus;
  }
  public function tosAccept() {
    $sql = "UPDATE users SET tos_accepted=" . now() . " WHERE user_id=" . $this->userId;
    query($sql);
    LoginDao::refreshLogin();
  }
  public function isGlassBroken($cid) {
    return isset($this->glassBreaks[$cid]);
  }
  public function breakGlass($cid) {
    $this->glassBreaks[$cid] = 1;
    $this->save();
  }
  public function setTimeout($min) {
    $min = intval($min);
    if ($min < 10 || $min > 60)
      $min = 60;
    $sql = "UPDATE user_groups SET session_timeout='" . $min . "' WHERE user_group_id=" . $this->userGroupId;
    query($sql);
    LoginDao::refreshLogin();
  }
  public function hideSticky($id) {
    $this->hideStickies[$id] = true;
    $this->save();
  }
  //
  public function save() {
    @session_start();
    $_SESSION['login'] = $this;
    session_write_close();
  }
  /**
   * Statics
   */
  static function getEnv() {
    static $env;
    if ($env == null) {
      switch ($_SERVER['SERVER_PORT']) {
        case '80':
          $prefix = substr($_SERVER['HTTP_HOST'], 0, 4);
          if ($prefix == 'loca' || $prefix == '192.')
            $env = strpos($_SERVER['REQUEST_URI'],'papyrus') ? LoginResult::ENV_PAPYRUS_LOCAL : LoginResult::ENV_LOCAL;
          else if ($prefix == 'test')
            $env = LoginResult::ENV_TEST;
          else if ($prefix == 'papy')
            $env = LoginResult::ENV_PAPYRUS_TEST;
          else 
            $env = LoginResult::ENV_PRODUCTION;
          break;
        case '443':
          if (strpos($_SERVER['REQUEST_URI'], "cert/"))
            $env = LoginResult::ENV_CERT;
          else
            $env = LoginResult::ENV_PRODUCTION;
          break;
      }
    }
    return $env;
  }
  static function testingLabel() {
    switch (LoginResult::getEnv()) {
      // case LoginResult::ENV_LOCAL:
        // return "<span style='font-size:21pt; font-weight:bold; font-family:Calibri; color:#1e90ff; vertical-align:top; padding-left:5px; letter-spacing:1px;'>LOCAL MACHINE</span>";
      case LoginResult::ENV_TEST:
        return "<span style='font-size:21pt; font-weight:bold; font-family:Calibri; color:orange; vertical-align:top; padding-left:5px; letter-spacing:1px;'>TEST</span>";
    }
  }
}

