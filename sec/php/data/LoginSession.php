<?php
require_once 'config/MyEnv.php';
require_once 'php/data/rec/sql/UserLogins.php';
require_once 'php/data/rec/sql/UserRoles.php';
require_once 'php/data/rec/sql/UserLoginReqs.php';
require_once 'php/data/rec/_CachedRec.php';
require_once 'php/data/rec/sql/_ApiIdXref.php';
require_once 'php/data/rec/cryptastic.php';
require_once 'inc/serverFunctions.php';
//
/**
 * Login Session
 * Saved into $_SESSION['mylogin'] and global $login
 * @author Warren Hornsby
 */
class LoginSession extends Rec {
  //
  public $env;
  public $cache;
  public $sessionId;
  public $ip;
  public $userGroupId;
  public $userId;
  public $uid;
  public $ui;
  public $active;
  public $docId;  // for providers, self; for support account, NC partnerId or first doctor
  public $admin;
  public $daysLeft;  // either on trial or until card expires
  public $expireReason;  // 'Expiration reason'
  public $cerberus;  // Cerberus practice ID, or null if no interface
  public $super;  // for 'supergroup'
  public $demo;  // for demo accounts (e.g. preprod NewCrop)
  public $salutopia;  // to send patient updates to salutopia interface
  public $json;
  public /*UserLogin*/ $User;
  public /*UserRole*/ $Role;
  public /*Login*/ $Login;
  public /*UserLoginReqs*/ $LoginReqs;
  //
  private $ptpw;
  private $timeout;
  private $lastActivity;
  private $hideStickies;  // array('stickyId'=>1,..) for closed stickies
  private $glassBreaks;  // array(cid=>1,..) for patients chart brought up after restriction warning
  //
  static $for;
  //
  public function __construct() {  // for internal use; to create, use static::login
    $this->env = static::getEnv();
    $this->cache = array();
    $this->hideStickies = array();
    $this->glassBreaks = array();
  }
  public function toJsonObject(&$o) {  // used by Rec
    unset($o->cache);
    unset($o->LoginReqs);
    unset($o->Login);
    unset($o->mcsk);
    unset($o->mchk);
    if ($this->isPapyrus())
      $o->pap = 1;
    if ($this->User && $this->User->isOnTrial())
      $o->trial = 1;
  }
  
  //ORACLE CONNECTIONS ONLY
    public function checkOracleLogin($uid, $pw) {
		try {
			$conn = oci_connect(MyEnv::$DB_USER, MyEnv::$DB_PW, MyEnv::$DB_SERVER . '/' . MyEnv::$DB_PROC_NAME);
			if(!$conn) {
				$err = oci_error();
				throw new RuntimeException($err['message']);	
			}
			echo 'checkOracleLogin: Running oracle function with ' . $uid . ' and ' . $pw . '<br>';
			$stid = oci_parse($conn, 'select FN_AUTHENTICATE_USER1(:userid, :pw) as "result" from dual');
			oci_bind_by_name($stid, ":userid", $uid);
			oci_bind_by_name($stid, ":pw", $pw);
			oci_execute($stid);

			$array = oci_fetch_assoc($stid);
			oci_free_statement($stid);
			oci_close($conn);
			
			//print_r($array);

			return $array['result'] == '1';
		}
		catch (Exception $e) {
			echo $e->getMessage();
			return false;
		}
	}
  //
  public function asJson() {
    if ($this->json == null)
      $this->json = jsonencode($this);
    return $this->json;
  }
  public function haveTermsExpired() {
    if (! $this->isPapyrus())
      return $this->User->haveTermsExpired();
  }
  public function isTrialFirstLogin() {
    return $this->User->isTrialFirstLogin();
  }
  public function isInactive() {
    return ! $this->active;
  }
  public function isErx() {
    return $this->User->UserGroup->isErx();
  }
  public function isPapyrus() {
    return MyEnv::getEnv() == Env::ENV_PAPYRUS_PROD;
    // return $this->env == static::ENV_PAPYRUS_TEST || $this->env == static::ENV_PAPYRUS_PROD || $this->env == static::ENV_PAPYRUS_LOCAL;
  }
  public function isSessionExpired() {
    if ($this->cerberus)
      return false;
    if ($this->lastActivity)
      return time() - $this->lastActivity > $this->timeout;
  }
  public function getEstAdjust() {
    return $this->User->UserGroup->estTzAdj;
  }
  public function shouldPopLoginReqs() {
    if ($this->LoginReqs) {
      if (! isset($this->_loginReqsShown)) {
        $this->_loginReqsShown = true;
        $this->save();
        return true;
      }
    }
  }
  public function getPtpw() {
    return $this->ptpw;
  }
  public function getChildrenUgids() {
    if ($this->super)
      return UserGroups::getChildrenUgids();
  }
  public function setMcsk() {
    $mcsk = static::fetchMcsk();
    if ($mcsk == null)
      throw new AppUnavailableException();
    $this->mcsk = $mcsk;
    $this->mchk = MyEnv::getMchk();
    return $this;
  }
  /**
   * Set up next test customer for trial
   * @return number of test customers left to set up; call again if >0
   */
  public function setupTrial() {
    require_once 'php/data/rec/sql/TrialSetup.php';
    $left = TrialSetup::setupOne($this->userGroupId, $this->userId);
    if ($left == 0 && $this->User->tosAccepted == null) {
      TrialSetup::sendAdminEmail($this);
      $this->User->tosAccepted = '1970-01-01';  // indicates trial setup complete but TOS not yet accepted; user will be presented TOS next
      $this->User->save();
      $this->refresh();
    }
    return $left;
  }
  public function acceptTerms() {
    $this->User->acceptTerms();
    return $this->refresh();
  }
  public function acceptBaa($post) {
    $errors = UserLoginReqs::acceptBaa($post);
    if ($errors)
      return $errors;
    else
      $this->refresh();
  }
  public function confirmEmail($post) {
    $errors = UserLoginReqs::confirmEmail($post, $this->User);
    if ($errors) {
      return $errors;
    } else {
      $this->refresh();
    }
  }
  public function needsBaa() {
    return $this->LoginReqs && isset($this->LoginReqs[UserLoginReqs::REQ_BAA]);
  }
  public function needsConfirmEmail() {
    return $this->LoginReqs && isset($this->LoginReqs[UserLoginReqs::REQ_CONFIRM_EMAIL]);
  }
  public function getStickyLoginReqs() {
    if ($this->LoginReqs) {
      $a = array();
      foreach ($this->LoginReqs as $action => $reqs) {
        if (UserLoginReqs::isGenericAction($action)) {
          foreach ($reqs as $req) {
            $a[] = $req;
          }
        }
      }
      return $a;
    }
  }
  public function hideSticky($id) {
    $this->hideStickies[$id] = true;
    $this->save();
  }
  public function isStickyHidden($id) {
    return isset($this->hideStickies[$id]);
  }
  public function isGlassBroken($cid) {
    return isset($this->glassBreaks[$cid]);
  }
  public function breakGlass($cid) {
    $this->glassBreaks[$cid] = 1;
    $this->save();
  }
  /**
   * @param string $old
   * @param string $new
   * @throws UserPasswordException
   */
  public function changePassword($old, $new) {
    $this->User->changePassword($old, $new);
    $this->ptpw = $new;
    return $this->refresh();
  }
  /**
   * @param string $pw
   * @return true if valid
   */
  public function verifyPassword($pw) {
    return $this->User->isPasswordCorrect($pw);
  }
  /**
   * @param string $new
   * @throws UserPasswordException
   */
  public function setPassword($new) {
    $this->User->changePassword($this->ptpw, $new);
    $this->ptpw = $new;
    return $this->refresh();
  }
  /**
   * Refresh after user field update
   */
  public function refresh() {
    $this->setUserFields();
    return $this->save();
  }
  public function save() {
    @session_start();
    $_SESSION['mylogin'] = $this;
    session_write_close();
    return $this;
  }
  public function reauthenticate() {
    return false;  // to invalidate a portal session overwritten by user session
  }
  protected static function fetchGd($s, $r) {
    $r .= '/' . MyEnv::getMcsk() . '.' . $s;
    return file_get_contents($r); 
  }
  //
  //Used for tests so that we can access the protected method fetchUser_withLogging
  static function testFetchUser($uid, $ptpw, $isAutomatedLogin = null) {
	return static::fetchUser_withLogging($uid, $ptpw, $isAutomatedLogin);
  }
  /**
   * Create a login session
   * @param string $uid
   * @param string $ptpw
   * @param string $sessionId (optional)
   * @param isAutomatedLogin - Set this to true if we are doing a 'fake' login where nobody is actually logging in. This is useful for process logins such as the analytics batch processing thing.
   * @return LoginSession
   * @throws LoginInvalidException
   * @throws LoginDisallowedException
   */
  static function login($uid, $ptpw, $sessionId = null, $isAutomatedLogin = false) {
    if ($uid == 'pspc')
      $uid = '846_pspc';
	//echo 'LoginSession login function: Entered!<br>';
    $user = static::fetchUser_withLogging($uid, $ptpw, $isAutomatedLogin);
	//echo 'Got user!<br>';
    $me = new static();
    $me->userGroupId = $user->userGroupId;
    $me->cerberus = ApiIdXref_Cerberus::lookupPracticeId($user->userGroupId);
    require_once 'php/data/rec/sql/_SalutopiaBatch.php';
    $me->salutopia = SalutopiaBatch::isActive($user->userGroupId);
    $me->userId = $user->userId;
    $me->ip = $_SERVER['REMOTE_ADDR'];
    $me->uid = $uid;
    $me->ptpw = $ptpw;
	Logger::debug('LoginSession::login: Got user. login disallowed? ' . $user->isLoginDisallowed());
	Logger::debug('LoginSession: Our backtrace is this:');
	Logger::debug(debug_backtrace());
	
	if (!$isAutomatedLogin) $me->setUserFields($user); //We did this check because when we do an automated login, the database for some reason wants to update the user's information, and when it does it wants to update the user's admin status and subscription status to the user's password hash. No idea why, but it stops us from making progress and I've decided to disable it for now.
	
    if ($user->isLoginDisallowed()) {
      UserLogins::log_asDisallow($user);
      throw new LoginDisallowedException();
    }
    global $login;
    $login = $me->setMcsk()->save();
    $login->sessionId = session_id();
    $login->Login = UserLogins::log_asOk($login);
	/*Logger::debug('LoginSession: Done with login! Returning save().');
	echo '<pre>';
	print_r(debug_backtrace());
	echo '</pre>';*/
    return $login->save();
  }
  static function loginBatch($ugid, $label) {
    global $login;
    $mcsk = $login ? $login->mcsk : null;
    $user = UserLogin_Batch::create($ugid, $label);
    $me = static::fromSysUser($user, $mcsk);
    $login = $me->save();
    $login->sessionId = session_id();
    return $login;
  }
  static function asSys($ugid) {
    $user = UserLogin_Sys::create($ugid);
    return static::fromSysUser($user);
  }
  protected static function fromSysUser($user, $mcsk = null) {
    $me = new static();
    $me->userGroupId = $user->userGroupId;
    $me->userId = $user->userId;
    $me->uid = $user->uid;
    $me->ptpw = null;
    $me->User = $user;
    $me->json = null;
    $me->admin = false;
    $me->timeout = 9999;
    $me->Role = UserRole::from($user, false);
    if ($mcsk)
      $me->mcsk = $mcsk;
    else
      $me->setMcsk();
    return $me;
  }
  static function setUi($isTablet) {
    $login = static::get();
    $login->ui = new stdClass();
    $login->ui->tablet = $isTablet;
    $login->ui->ie = getIeVersion();
    return $login->save();
  }
  /**
   * Verify valid login session is still alive
   * @param bool $updateLastActivity (optional)
   * @param string $sessionId (optional)
   * @return LoginSession
   * @throws SessionExpiredException
   * @throws SessionInvalidException
   */
  static function verify($updateLastActivity = true) {
    $sessionId = static::getSessionIdFromRequest();
    $login = static::get($sessionId);
    if ($updateLastActivity)
      $login->lastActivity = time();
    $user = $login->refetchUser();
    if ($user == null)
      throw new SessionInvalidException();
    return $login->save($sessionId);
  }
  static function verify_forPolling() {
    return static::verify(false);
  }
  static function verify_forUser() {  // see requires() for method chaining example
    static::$for = 1;
    try {
      $login = static::verify(true);
      if ($login->User->isPasswordExpired())
        redirect('index.php?cp=1');
      if ($login->isTrialFirstLogin())
        redirect('welcome-trial.php');
      else if ($login->haveTermsExpired())
        redirect('tos-accept.php');
      else if ($login->needsBaa())
        redirect('baa-accept.php');
      else if ($login->needsConfirmEmail())
        redirect('confirm-email.php');
      header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
      return $login;
    } catch (SessionExpiredException $e) {
      header("Location: index.php?timeout=1");
      exit;
    } catch (SessionInvalidException $e) {
      header("Location: index.php?invalid=1");
      exit;
    }
  }
  static function verify_forServer() {  // see requires() for method chaining example
    static::$for = 2;
    return static::verify(true);
  }
  private static function getSessionIdFromRequest() {
    if (isset($_GET['sess']))
      return $_GET['sess'];
    if (isset($_POST['sess']))
      return $_POST['sess'];
  }
  static function requires($value) {  // e.g. Login_Session::verify_forUser()->requires($login->Role->Patient->any());
    if (! $value) {
      if (static::$for == 1) {
        header('Location: welcome.php');
        exit;
      } else {
        throw new UnauthorizedException('Your account is not authorized to perform the requested function.');
      }
    }
  }
  /**
   * Destroy session
   */
  static function clear() {
    global $login;
    $login = null;
    @session_start();
    unset($_SESSION['mylogin']);
    @session_destroy();
    @session_write_close();
  }
  /**
   * Get session
   * @param string $sessionId (optional)
   * @throws SessionExpiredException
   */
  static function get($sessionId = null) {
    if ($sessionId)
      session_id($sessionId);
    @session_start();
    if (! isset($_SESSION) || ! isset($_SESSION['mylogin']))
      throw new SessionExpiredException();
    global $login;
    $login = $_SESSION['mylogin'];
    if ($login->isSessionExpired()) {
      static::clear();
      throw new SessionExpiredException();
    }
    session_write_close();
    return $login;
  }
  static function isProdEnv() {
    return MyEnv::getEnv() == Env::ENV_PRODUCTION || MyEnv::getEnv() == Env::ENV_PAPYRUS_PROD;
  }
  static function getEnv() {
    return MyEnv::getEnv();
  }
  //
  protected function setUserFields($user = null) {  // null to refresh
    if ($user == null)
      $user = UserLogin::fetchByUid($this->uid);
    $ug = $user->UserGroup;
    $this->User = $user;
    $this->json = null;
    $this->admin = $user->isAdmin() ? true : null;
    $this->timeout = $ug->sessionTimeout * 60;
    if ($user->isDoctor())
      $this->docId = $user->userId;
    else
      $this->docId = $user->getNcPartnerId() ?: $user->fetchDocId();
  
    $this->setActiveStatus();
    $this->Role = UserRole::from($user, $this->cerberus);
    if ($user->userGroupId == 2645/*john richards*/) {
      // $this->Role->Artifact->noteReplicate = 0; -- decided they want to keep replicate, 4/12
      if ($user->userId == 3496/*victoria ewing*/) {
        $this->Role->Artifact->noteSign = 1;
      }
    }
    if (! $this->cerberus) {
      $this->LoginReqs = UserLoginReqs::getAllFor($user, $this->Role);
    }
    $this->super = $ug->isSuper();
    $this->demo = $ug->demo;
    return $this->save();
  }
  protected function setActiveStatus() {  // assigns active and daysLeft
    if (! $this->isPapyrus()) {
      if ($this->User->active) {
        switch ($this->User->subscription) {
          case UserLogin::SUBSCRIPTION_TRIAL:
            $this->daysLeft = $this->User->getTrialDaysLeft();
            if ($this->daysLeft < 0)
              $this->expireReason = $this->User->deactivate(UserLogin::EXPIRE_TRIAL_OVER);
            break;
          case UserLogin::SUBSCRIPTION_CREDITCARD:
            $bill = get($this->User, 'BillInfo');
            if ($bill == null) {
              $this->expireReason = $this->User->deactivate(UserLogin::EXPIRE_MISSING_BILLINFO);
            } else {
              $this->daysLeft = $bill->getDaysLeft();
              if ($bill->getDaysLeft() < 0)
                $this->expireReason = $this->User->deactivate(UserLogin::EXPIRE_CARD_EXPIRED);
            }
            break;
          case UserLogin::SUBSCRIPTION_FREE:
            $this->daysLeft = 1000;
          case UserLogin::SUBSCRIPTION_INVOICE:
            $this->daysLeft = 365;  // TODO
        }
      }
    }
    $this->active = $this->User->active;
  }
  protected function refetchUser() {
    $user = static::fetchUser($this->uid, $this->ptpw, false);
    if ($user) {
      if ($this->userId != $user->userId)
        return null;
      if ($this->active && ! $user->active)
        return null;
    }
    return $user;
  }
  //
  protected static function fetchUser($uid, $ptpw, $logging = false, $isAutomatedLogin = false) {
    //echo 'LoginSession fetchUser: Entered with ' . $uid . ' and ' . $ptpw . '<br>';
	if ($isAutomatedLogin) {
		Logger::debug('fetchUser: Automated login. Calling fetchByUidTest.');
		$user = UserLogin::fetchByUidtest($uid);
		Logger::debug('fetchUser: Did automated login. Got user ' . gettype($user));
	}
	else {
		$user = UserLogin::fetchByUid($uid);
	}
	Logger::debug('LoginSession fetchUser: user is a ' . gettype($user) . ' ' . print_r($user, true));
    if ($user) {
      if (! $user->isPasswordCorrect($ptpw)) {
        if ($logging) {
		  //echo '<b>User password not correct. logging.</b><br>';
		  //var_dump(debug_print_backtrace());
		  if ($isAutomatedLogin) {
			$attempts = new Attempts();
		  }
		  else {
			$attempts = UserLogins::log_asBadPw($user);
		  }
          throw new LoginInvalidException($attempts);
        }
        return null;
      }
    } else {
      if ($logging) {
        if (static::isEmrUid($uid)) { //very hacky and unnecessary IS_BATCH check - covers up a very odd login issue.
          throw new LoginEmrException();
		}
		//echo '<b>No user! Logging.</b><br>';
		 if ($isAutomatedLogin) {
			$attempts = new Attempts();
		 }
		  else {
			$attempts = UserLogins::log_asBadUid($uid);
			
		 }
        
        throw new LoginInvalidException($attempts);
      }
    }
	Logger::debug('LoginSession.php::fetchUser: Returning a ' . gettype($user));
    return $user;
  }
  protected static function fetchUser_withLogging($uid, $ptpw, $isAutomatedLogin = false) {
    //echo 'fetch user with logging...';
    return static::fetchUser($uid, $ptpw, true, $isAutomatedLogin);
  }
  protected static function isEmrUid($uid) {
	$col = 'uid';
	if (MyEnv::$IS_ORACLE) $col = 'uid_';
    $sql = "SELECT * FROM users WHERE " . $col . "='$uid'";
    $row = Dao::fetchRow($sql, 'emr');
    return (empty($row)) ? false : true;
  }
  static function fetchMcsk() {
    //return MyCrypt::getEncryptKey(null, 'jjW1p3!afs');
    $mcsk = static::fetchGd('php', MyEnv::$GD_URL);
    if ($mcsk == null)
      throw new AppUnavailableException();
    return MyCrypt::getEncryptKey(null, $mcsk);
  }
  static function getActiveUgids() {
    $sql = "select distinct u.user_group_id from users u join user_groups g on u.user_group_id=g.user_group_id where active=1";
    return Dao::fetchValues($sql);
  }
}
/**
 * Session Caches
 */
class SessionCache {
  /**
   * @param string $key
   * @return mixed
   */
  static function get($key) {
    global $login;
    return geta($login->cache, $key);
  }
  /**
   * @param string $key
   * @param mixed $data
   */
  static function set($key, $data) {
    global $login;
    $login->cache[$key] = $data;
    $login->save();
  }
  /**
   * @param string key
   */
  static function clear($key) {
    global $login;
    unset($login->cache[$key]);
    $login->save();
  }
  /**
   * @param string $partial 'startsWith'
   */
  static function clearAll($partial) {
    $len = strlen($partial);
    global $login;
    foreach ($login->cache as $key => $value)
      if (substr($key, 0, $len) == $partial)
        unset($login->cache[$key]);
    $login->save();
  }
  /**
   * @param string $key
   * @param closure $fn to retrieve data (and cache) if not found in cache
   * @return mixed
   */
  static function getset($key, $fn) {
    global $login;
    if (! isset($login))
      return $fn();
    if (array_key_exists($key, $login->cache)) {
      $data = $login->cache[$key];
    } else {
      $data = $fn();
      self::set($key, $data);
    }
    return $data;
  }
}
class MethodCache extends SessionCache {
  /**
   * @param string $method __METHOD__
   * @param array $args func_get_args()
   * @param closure $fn
   * @example
   *   function getStuff($stuffId) {
   *     return MethodCache::getset(__METHOD__, func_get_args(), function() use ($stuffId) {
   *       $stuff = Dao::getStuffFromDatabase($stuffId);
   *       return $stuff;
   *     });
   *   }
   */
  static function getset($method, $args, $fn) {
    $key = self::makeKey($method, $args);
    return parent::getset($key, $fn);
  }
  /**
   * @param string $class 'UserGroups'
   */
  static function clearAll($class) {
    $partial = "$class::";
    parent::clearAll($partial);
  }
  //
  private static function makeKey($method, $args) {
    return $method . "(" . implode(',', $args) . ")";  // 'Class::method(arg1,arg2)'
  }
}
class AuthCache extends MethodCache {
  //
  static function needsAuth($key) {
    global $login;
    return ! isset($login->cache[$key]);
  }
  static function user($id, $fn) {
    $key = "u$id";
    if (self::needsAuth($key)) {
      $fn($id);
      self::set($key, true);
    }
  }
  static function ugid($ugid, $fn) {
    $key = "ugid$ugid";
    if (self::needsAuth($key)) {
      $fn($ugid);
      self::set($key, true);
    }
  }
  static function ugidWithin($table, $col, $id, $fn) {
    $key = "ugid[$table,$id]";
    if (self::needsAuth($key)) {
      $fn($table, $col, $id);
      self::set($key, true);
    }
  }
}
class CerberusLogin extends CachedRec {
  //
  public $ugid;
  public $practiceId;
  public $user;
  public $pw;
  public $cookie;
  public $sessionId;
  public $url/*URL for return to PMS*/;
  public $queryBase/*for apex queries*/;
  public $queryApp;
  public $querySessionId;
  //
  public function lookupPatientId($cid) {
    return ApiIdXref_Cerberus::lookupPatientId($this->practiceId, $cid);
  }
  protected function setQueryFields($url) {
    $aa = explode('=', $url);
    $base = $aa[0] . '=';
    $ab = explode(':', $aa[1]);
    $app = intval($ab[0]) + 1;
    $this->queryBase = $base;
    $this->queryApp = strval($app);
    $this->querySessionId = "0";
  }
  //
  static function fetch() {
    $me = parent::fetch();
    if ($me->user == null)
      throw new CerberusLoginExpired();
    return $me;
  }
  static function cache($ugid, $loginInfo, $practiceId = null) {
	echo 'LoginCache started...';
    $me = static::from($ugid, $loginInfo, $practiceId);
    $me->save();
  }
  static function from($ugid, $loginInfo, $practiceId = null) {
	echo 'LoginSession.php: doing from with ' . $ugid . ' | ' . '. The practice ID is ' . $practiceId;
    if ($practiceId == null)
      $practiceId = ApiIdXref_Cerberus::lookupPracticeId($ugid);
    if ($practiceId == null) {
      throw new CerberusPracticeNotFound('LoginSession: Invalid ugid ' . $ugid);
	}
    $me = new static(
      $ugid,
      $practiceId,
      $loginInfo->user,
      $loginInfo->pw,
      $loginInfo->cookie,
      $loginInfo->sessionId,
      $loginInfo->url);
    $me->setQueryFields($loginInfo->url);
    return $me;
  }
  static function extractUid($uid) {
    $a = explode('_', $uid, 2);
    return end($a);
  }
}
/**
 * Exceptions
 */
class LoginInvalidException extends Exception {
  public $attempts;  // count of recent bad attempts for IP
  public $locked;    // true if last UID attempt now locked out
  //
  public function __construct($attempts) {
    $this->attempts = $attempts->count;
    $this->locked = $attempts->locked;
    parent::__construct();
  }
}
class LoginDisallowedException extends Exception {}
class LoginEmrException extends Exception {}
class SessionInvalidException extends Exception {}
class SessionExpiredException extends SessionInvalidException {}
class UnauthorizedException extends DisplayableException {}
class AppUnavailableException extends Exception {}