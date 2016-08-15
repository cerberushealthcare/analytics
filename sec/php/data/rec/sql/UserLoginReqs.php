<?php
require_once 'php/data/rec/sql/_SqlRec.php';
require_once 'php/data/rec/sql/LoginReqs.php';
require_once 'php/data/rec/sql/UserLogins.php';
require_once 'php/data/rec/sql/UserStub.php';
//
/**
 * User Login Requirements
 * @author Warren Hornsby
 */
class UserLoginReqs {
  //
  const REQ_BAA = 'BAA';                     // need a signed BAA
  const REQ_ACTION_NOTIFY = 'NOTIFICATION';  // not yet received
  const REQ_ACTION_WARN = 'WARNING';         // good now but <30 days until expiration
  const REQ_ACTION_EXPIRE = 'EXPIRATION';    // expired
  const REQ_CONFIRM_EMAIL = 'EMAIL';
  //
  /**
   * Get existing or create any that apply for particular user 
   * @param UserLogin $user;
   * @param UserRole $role;
   * @return array(
   *   REQ_ACTION=>[
   *     UserLoginReq(+LoginReq),..],..) or null if none  
   */
  static function getAllFor($user, $role) {
    $reqs = UserLoginReqs::buildFor($user, $role);
    $reqsByAction = array();
    foreach ($reqs as $req) {
      if ($req->isBaa() && $req->dateExpires) {
        $reqsByAction[UserLoginReqs::REQ_BAA][] = $req;
      } else if ($req->isConfirmEmail() && $req->dateExpires) {
        $reqsByAction[UserLoginReqs::REQ_CONFIRM_EMAIL][] = $req;
      } else {
        switch ($req->status) {
          case UserLoginReq::STATUS_NOTIFIED:
            $reqsByAction[UserLoginReqs::REQ_ACTION_NOTIFY][] = $req;
            break;
          case UserLoginReq::STATUS_GOOD:
            if ($req->dateExpires && $req->_daysLeft <= 30)
              $reqsByAction[UserLoginReqs::REQ_ACTION_WARN][] = $req;
            break;
          case UserLoginReq::STATUS_EXPIRED:
          case UserLoginReq::STATUS_PAST_GRACE:
            $reqsByAction[UserLoginReqs::REQ_ACTION_EXPIRE][] = $req;
            break;
        }
      }
    } 
    return (empty($reqsByAction)) ? null : $reqsByAction; 
  }
  static function isGenericAction($action) {
    switch ($action) { 
      case static::REQ_ACTION_EXPIRE:
      case static::REQ_ACTION_NOTIFY:
      case static::REQ_ACTION_WARN:
        return true;
      default:
        return false;
    }
  }
  /**
   * Get existing for all users
   * @return array(
   */
  static function getAll() {
    $c = new UserLoginReq();
    $c->active = true;
    $c->LoginReq = new LoginReq(); 
    $c->UserStub = UserStub_Req::asRequiredJoin();
    $recs = UserLoginReq::fetchAllBy($c);
    $recs = UserLoginReq::checkExpiration($recs);
    Rec::sort($recs, new RecSort('loginReqId','status','_daysLeft','-dateRcvd'));
    return $recs;
  }
  /**
   * Save object
   * @param stdClass $obj 
   * @return UserLoginReq updated rec
   */
  static function save($obj) {
    $rec = new UserLoginReq($obj);
    $rec->save();
    return $rec;
  }
  /**
   * Accept BAA
   * @param HttpPost $post
   * @return string[] $errors;
   */
  static function acceptBaa($post) {
    $form = new BaaForm($post);
    $errors = $form->validate();
    if ($errors)
      return $errors;
    
    UserLoginReq_Baa::save_fromForm($form);
  }
  static function confirmEmail($post, $user) {
    $form = new EmailForm($post);
    $errors = $form->validate();
    if ($errors)
      return $errors;
    $user->saveEmail($form->email);
    UserLoginReq_Email::save_fromForm($form);    
  }
  //
  /*
   * Fetch existing UserLoginReqs 
   * Create any that need added
   * Expire any that need expired
   */
  private static function buildFor($user, $role) {
    $allReqs = LoginReqs::getApplicablesFor($user, $role);
    $userReqs = UserLoginReq::fetchAllFor($user);
    $recs = array();
    foreach ($allReqs as $name => $allReq) {
      $userReq = geta($userReqs, $name);
      if ($userReq == null) 
        $userReq = UserLoginReq::create($user, $allReq);
      $userReq->LoginReq = $allReq;
      $recs[] = $userReq;    
    }
    $recs = UserLoginReq::checkExpiration($recs);
    return $recs;
  }
}
class UserStub_Req extends UserRec implements ReadOnly {
  //
  public $userId;
  public $userGroupId;
  public $uid;
  public $name;
  public $trialExpdt;
  //
  static function asRequiredJoin() {
    $me = new static();
    $me->trialExpdt = CriteriaValues::_or(CriteriaValue::equals('0000-00-00'), CriteriaValue::withinPast(array('3', 'w')));
    return CriteriaJoin::requires($me);
  }
}
/**
 * User Login Requirement
 */
class UserLoginReq extends SqlRec implements NoAudit {
  //
  public $userLoginReqId;
  public $userId;
  public $loginReqId;
  public $name;
  public $active;
  public $status;
  public $dateNotified;
  public $dateRcvd;
  public $dateExpires;
  public $dateUpdated;
  public $updatedBy;
  public $comments;
  public /*LoginReq*/ $LoginReq;
  public /*UserStub*/ $UserStub;
  //
  public $_daysLeft;  // days until expiration
  public $_daysSince; // days since notification
  //
  const STATUS_NOTIFIED = '1';
  const STATUS_PAST_GRACE = '2';
  const STATUS_EXPIRED = '3';
  const STATUS_GOOD = '9';
  static $STATUSES = array(
    UserLoginReq::STATUS_NOTIFIED => 'Notified',
    UserLoginReq::STATUS_PAST_GRACE => 'Past Grace',
    UserLoginReq::STATUS_EXPIRED => 'Expired',
    UserLoginReq::STATUS_GOOD => 'Received');
  //
  public function getSqlTable() {
    return 'user_login_reqs';
  }
  public function toJsonObject(&$o) {
    $o->_isExpired = $this->isExpired();
    $o->_isNotified = $this->isNotified();
    $o->_isReceived = $this->isReceived();
    if ($this->LoginReq)
      $o->_name = $this->name . ' (' . $this->LoginReq->applies . ')';
  }
  public function getJsonFilters() {
    return array(
      'LoginReq' => JsonFilter::omit(),
    	'dateNotified' => JsonFilter::editableDateTime(),
      '_dateNotified' => JsonFilter::informalDateTime('dateNotified'),
    	'dateRcvd' => JsonFilter::editableDateTime(),
      '_dateRcvd' => JsonFilter::informalDateTime('dateRcvd'),
    	'dateExpires' => JsonFilter::editableDate());
  }
  public function isBaa() {
    return $this->loginReqId == LoginReq::ID_BAA;
  }
  public function isConfirmEmail() {
    return $this->name == LoginReq::NAME_CONFIRM_EMAIL;
  }
  public function isExpired() {
    return $this->status == UserLoginReq::STATUS_PAST_GRACE || $this->status == UserLoginReq::STATUS_EXPIRED;
  }
  public function isNotified() {
    return $this->status == UserLoginReq::STATUS_NOTIFIED;
  }
  public function isReceived() {
    return $this->status == UserLoginReq::STATUS_GOOD;
  }
  /**
   * @see parent::save()
   */
  public function save($expire = false) {
    global $login;
    $this->dateUpdated = nowNoQuotes();
    $this->updatedBy = $login->userId;
    if ($this->status == UserLoginReq::STATUS_EXPIRED && $this->dateRcvd) {
      if ($this->dateExpires && compareDates($this->dateExpires, $this->dateRcvd) > 0) { 
        // date received prior to expire date; keep expired
      } else {
        $this->status = self::STATUS_GOOD;
      }
    }
    parent::save();
    if (! $expire)
      UserLoginReq::_checkExpiration($this);
  }
  /**
   * Save record as expired (STATUS_EXPIRED or STATUS_PAST_GRACE)
   */
  public function expire() {
    logit_r($this, 'expire'); 
    $status = ($this->status == UserLoginReq::STATUS_NOTIFIED) ? UserLoginReq::STATUS_PAST_GRACE : UserLoginReq::STATUS_EXPIRED;
    logit_r($status, 'status');
    $this->status = $status;
    $this->save(true);
  }
  //
  /**
   * @param LoginUser $user
   * @return array(name=>UserLoginReq,..)
   */
  static function fetchAllFor($user) {
    $c = new UserLoginReq();
    $c->userId = $user->userId;
    return parent::fetchMapBy($c, 'name');
  }
  /**
   * @param LoginUser $user
   * @param LoginReq $loginReq
   */
  static function create($user, $loginReq) {
    $rec = new UserLoginReq();
    $rec->userId = $user->userId;
    $rec->loginReqId = $loginReq->loginReqId;
    $rec->name = $loginReq->name;
    $rec->active = true;
    $rec->status = UserLoginReq::STATUS_NOTIFIED;
    $rec->dateNotified = nowNoQuotes();
    $rec->dateExpires = futureDate($loginReq->grace + 1);
    $rec->save();
    return $rec; 
  }
  /**
   * @param [UserLoginReq,..] $recs
   * @return array(UserLoginReq,..)
   */
  static function checkExpiration($recs) {
    foreach ($recs as &$rec) 
      UserLoginReq::_checkExpiration($rec); 
    return $recs;
  }
  //
  private static function _checkExpiration(&$rec) {
    if ($rec->status == UserLoginReq::STATUS_NOTIFIED)  
      $rec->_daysSince = -daysUntil($rec->dateNotified);
    if ($rec->dateExpires && ! $rec->isExpired()) {
      $rec->_daysLeft = daysUntil($rec->dateExpires) - 1;
      if ($rec->_daysLeft < 0) 
        $rec->expire();
    }
  }
}
class BaaForm extends Rec {
  //
  public $init;
  public $name;
  public $title;
  public $lic;
  public $date;
  public $acc;
  //
  public function validate() {
    unset($this->acc);
    $rv = RecValidator::from($this)
      ->requires('init', 'name', 'lic', 'date')
      ->isDate('date');
    return $rv->errors;
  }
}
class UserLoginReq_Baa extends UserLoginReq {
  //
  static function fetch() {
    global $login;
    $c = new static();
    $c->userId = $login->userId;
    $c->loginReqId = LoginReq::ID_BAA;
    return static::fetchOneBy($c);
  }
  static function save_fromForm(/*BaaForm*/$rec) {
    $me = static::fetch();
    $me->dateRcvd = nowNoQuotes();
    $me->dateExpires = null;
    $me->comments = jsonencode($rec);
    $me->status = static::STATUS_GOOD;
    $me->save();
  }
}
class EmailForm extends Rec {
  //
  public $email;
  public $acc;
  //
  public function validate() {
    unset($this->acc);
    $rv = RecValidator::from($this)->requires('email');
    return $rv->errors;
  }
}
class UserLoginReq_Email extends UserLoginReq {
  //
  static function fetch() {
    global $login;
    $c = new static();
    $c->userId = $login->userId;
    $c->name = LoginReq::NAME_CONFIRM_EMAIL;
    return static::fetchOneBy($c);
  }
  static function save_fromForm(/*BaaForm*/$rec) {
    $me = static::fetch();
    $me->dateRcvd = nowNoQuotes();
    $me->dateExpires = null;
    $me->comments = jsonencode($rec);
    $me->status = static::STATUS_GOOD;
    $me->save();
  }
}
