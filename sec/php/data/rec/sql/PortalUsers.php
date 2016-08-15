<?php
require_once 'php/data/rec/sql/_PortalUserRec.php';
require_once 'php/data/rec/sql/_SqlLevelRec.php';
require_once 'php/data/rec/sql/PortalUsers_Session.php';
require_once 'php/data/rec/sql/Clients.php';
//
/**
 * Patient Portal Users Administration
 * @author Warren Hornsby 
 */
class PortalUsers {
  /**
   * @return array(PortalUserStub,..)
   */
  static function getAll() {
    global $login;
    $recs = PortalUserStub::fetchAll($login->userGroupId);
    return Rec::sort($recs, new RecSort('Client.lastName', 'Client.firstName'));
  }
  /**
   * @param int $clientId
   * @return PortalUserStub or null
   */
  static function getFor($clientId) {
    $rec = PortalUserStub::fetchFor($clientId);
    return $rec;
  }
  /**
   * @param int $clientId
   * @return PortalUser or null
   */
  static function editFor($clientId) {
    $rec = PortalUser_A::fetchFor($clientId);
    return $rec;
  }
  /**
   * @param int $clientId
   * @return PortalUser
   */
  static function reset($clientId) {
    $rec = PortalUser_A::fetchFor($clientId);
    $rec->reset();
    $rec->save();
    return $rec;
  }
  /**
   * @param int $clientId
   * @return PortalUser
   */
  static function suspend($clientId) {
    $rec = PortalUser_A::fetchFor($clientId);
    $rec->suspend();
    $rec->save();
    return $rec;
  }
  /**
   * @param stdClass $json PortalUser
   * @return PortalUser_A
   */
  static function create($json) {
    global $login;
    $rec = PortalUser_A::fromUiAsCreate($json, $login->userGroupId, $login->userId);
    $rec->save();
    return self::getFor($rec->clientId);
  }
  /**
   * @param stdClass $json PortalUser
   * @return PortalUser_A
   */
  static function save($json) {
    global $login;
    $rec = PortalUser_A::fromUi($json, $login->userGroupId);
    $rec->save();
    return $rec;
  }
  /**
   * @param int $userId (optional)
   * @return array(PortalMsgType,..)
   */
  static function getMsgTypes($userId = null) {
    global $login;
    $recs = PortalMsgType_A::fetchTopLevels($login->userGroupId, $userId);
    return $recs;
  }
  /**
   * @param stdClass $json PortalMsgTypes[]
   * @return array(PortalMsgType,..)
   */
  static function saveMsgTypes($json, $userId = null) {
    global $login;
    $recs = PortalMsgType_A::reviveAll($json, $login->userGroupId);
    PortalMsgType_A::saveAll($recs);
    return static::getMsgTypes($userId);
  }
}
/**
 * PortalUser PortalUser_A
 */
class PortalUser_A extends PortalUserRec {
  //
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
  public /*Client*/ $Client;
  //
  static $FRIENDLY_NAMES = array(
    'uid' => 'Login ID',
    'cq1' => 'Challenge Question 1',
    'ca1' => 'Challenge Answer 1',
    'cq2' => 'Challenge Question 2',
    'ca2' => 'Challenge Answer 2',
    'cq3' => 'Challenge Question 3',
    'ca3' => 'Challenge Answer 3',
    'ssn4' => 'Last 4 SSN');
  // 
  public function validate(&$rv) {
    $rv->requires('uid', 'zipCode', 'ssn4', 'cq1', 'ca1', 'cq2', 'ca2', 'cq3', 'ca3');
  }
  public function isActive() {
    return $this->active;
  }
  public function reset() {
    $this->active = true;
    $this->status = self::STATUS_RESET;
    $this->pwSet = null;
    $this->pw = null;
  }
  public function suspend() {
    $this->active = false;
  }
  //
  static function fromUiAsCreate($json, $ugid, $userId) {
    $rec = self::fromUi($json, $ugid);
    $rec->reset();
    $rec->createdBy = $userId;
    $rec->dateCreated = nowNoQuotes();
    return $rec;
  }
  static function fromUi($json, $ugid) {
    $rec = new self($json);
    $rec->userGroupId = $ugid;
    $rec->subscription = static::SUBSCRIPTION_FREE;
    return $rec;
  }
  static function fetchFor($cid) {
    $c = self::asCriteria(null);
    $c->clientId = $cid;
    return self::fetchOneBy($c);
  }
  static function fetch($id) {
    $c = self::asCriteria(null);
    $c->portalUserId = $id;
    return self::fetchOneBy($c);
  }
  static function asCriteria($ugid) {
    $c = new self();
    $c->userGroupId = $ugid;
    $c->Client = new Client();
    return $c;
  }
} 
class PortalUserStub extends PortalUserRec implements ReadOnly {
  //
  public $portalUserId;
  public $userGroupId;
  public $clientId;
  public $uid;
  public $active;
  public $status;
  public $pwSet;
  public $email;
  public $subscription;
  public /*Client*/ $Client;
  public /*PortalLogin_Last*/ $LastLogin;
  //
  static function fetchAll($ugid) {
    $c = self::asCriteria($ugid);
    return SqlRec::fetchAllBy($c);
  }
  static function fetchFor($cid) {
    $c = self::asCriteria(null);
    $c->clientId = $cid;
    return self::fetchOneBy($c);
  }
  static function asCriteria($ugid) {
    $c = new self();
    $c->userGroupId = $ugid;
    $c->Client = new ClientStub();
    $c->LastLogin = PortalLogin_Last::asOptionalJoin();
    return $c;
  }
}
class PortalLogin_Last extends PortalLoginRec implements ReadOnly {
  //
  public $logDate;
  public $logStatus;
  public $portalUserId;
  //
  public function getJsonFilters() {
    return array(
      'logDate' => JsonFilter::informalDateTime());
  }
  //
  static function asOptionalJoin($alias = 'T2') {
    $j1 = self::asJoinCriteria();
    $j2 = self::asJoinCriteria();
    $j2->logDate = CriteriaValue::greaterThanNumeric("$alias.log_date");
    $j1->JoinSelf = CriteriaJoin::notExists($j2, 'portalUserId');
    return CriteriaJoin::optional($j1);
  }
  protected static function asJoinCriteria() {
    $c = new self();
    $c->logStatus = self::STATUS_OK;
    return $c;
  }
}
class PortalMsgType_A extends SqlUserLevelRec {
  //
  public $msgTypeId;
  public $userGroupId;
  public $userId;
  public $name;
  public $active;
  public $sendTo;
  //
  public function getSqlTable() {
    return 'portal_msg_types';
  }
  public function getJsonFilters() {
    return array(
    	'active' => JsonFilter::boolean());
  }
  public function validate(&$rv) {
    $rv->requires('name');
  }
  public function save() {
    if ($this->userId && empty($this->sendTo))
      static::delete($this);
    else
      parent::save();
  }
  //
  static function reviveAll($recs, $ugid) {
    $id = static::getNextCustomId($ugid);
    foreach ($recs as &$rec)
      $rec = static::revive($rec, $ugid, $id);
    return $recs;
  }
  static function revive($rec, $ugid, &$id) {
    $me = new static();
    $me->msgTypeId = get($rec, 'msgTypeId') ?: $id++;
    $me->userGroupId = $ugid;
    $me->userId = get($rec, 'userId', static::GROUP_LEVEL_USER_ID);
    $me->active = $rec->active;
    $me->name = get($rec, 'name');
    $me->sendTo = get($rec, 'sendTo');
    return $me;
  }
}
