<?php
require_once 'php/c/patient-list/PatientList_Sql.php';
require_once 'php/c/scheduling/Scheduling.php';
require_once 'php/c/sessions/Sessions.php';
require_once 'php/data/rec/_CachedRec.php';
require_once 'php/data/rec/sql/Messaging.php';
require_once 'php/data/rec/sql/Documentation.php';
require_once 'php/data/rec/sql/_ClientRec.php';
require_once 'php/data/rec/sql/OrderEntry.php';
//
class Dashboard {
  //
  static function /*Dash*/get() {
    global $login;
    return Dash::fetch($login->userGroupId, $login->userId, $login->uid);
  }
  static function /*Dash*/getAppts($date, $doctor = null) {
    return Dash::fetchAppts($date, $doctor);
  }
  static function /*Dash*/getMessages($recip = null) {
    return Dash::fetchMessages($recip);
  }
  static function /*Login[]*/getLoginHist() {
    global $login;
    $recs = Login_Dash::fetchAll($login->uid);
    return $recs;
  }
  static function /*DashKeys*/getKeys() {
    global $login;
    return DashKeys::fetch($login->userGroupId, $login->userId);
  }
}
class Dash extends Rec {
  //
  public /*DashKeys*/ $keys;
  public /*Client_Dash[]*/ $patients;
  public /*MsgThread[]*/ $messages;
  public /*Appt[]*/ $appts;
  public /*DocStub[]*/ $unreviewed;
  public /*SessionNoteStub[]*/ $unsigned;
  public /*LoginCount_Dash*/ $login;
  //
  public function __construct($keys) {
    $this->keys = $keys;
  }
  //
  public function loadPatients() {
    $keys = $this->keys;
    $this->patients = PStub_Mru::fetchLimit($keys->ugid);
    return $this;
  }
  public function loadMessages() {
    if ($this->keys->msgRecipient == $this->keys->userId)
      $this->messages = Messaging::getMyInboxThreads();
    else
      $this->messages = Messaging::getAnotherInboxThreads($this->keys->msgRecipient);
    return $this;
  }
  public function loadAppts() {
    $keys = $this->keys;
    $this->appts = Scheduling::getAppts_2weeks($keys->apptDate, $keys->apptDoctor);
    return $this;
  }
  public function loadUnreviewed() {
    $this->unreviewed = Documentation::getUnreviewed();
    $this->unsigned = Sessions::getUnsigned();
    return $this;
  }
  public function loadLogin($uid) {
    $this->login = LoginCount_Dash::fetch($uid);
  }
  //
  static function fetch($ugid, $userId, $uid) {
    $keys = DashKeys::fetch($ugid, $userId);
    $me = new static($keys);
    $me->loadPatients();
    $me->loadMessages();
    $me->loadAppts();
    $me->loadUnreviewed();
    $me->loadLogin($uid);
    return $me;
  }
  static function fetchAppts($date, $doctor) {
    $keys = DashKeys::fetch()->setAppt($date, $doctor);
    $me = new static($keys);
    return $me->loadAppts();
  }
  static function fetchMessages($recip) {
    $keys = DashKeys::fetch()->setRecip($recip);
    $me = new static($keys);
    return $me->loadMessages();
  }
}
class DashKeys extends CachedRec {
  //
  public $ugid;
  public $userId;
  public $users;
  //
  public $clientsFor;    // user_id of client MRU 
  public $apptDate;
  public $apptDoctor;
  public $msgRecipient;  // user_id of mailbox to review
  //
  public function getJsonFilters() {
    return array(
      'apptDate' => JsonFilter::editableDate());
  }
  public function setAppt($date, $doctor) {
    $this->apptDate = $date;
    $this->apptDoctor = $doctor;
    return $this->save();
  }
  public function setRecip($userId) {
    if ($userId) {
      $this->msgRecipient = $userId;
      $this->save();
    }
    return $this;
  }
  public function setClientsFor($userId) {
    $this->clientsFor = $userId;
    return $this->save();
  }
  //
  static function fetch($ugid = null, $userId = null) {
    $me = parent::fetch();
    if ($ugid && $me->ugid == null) {
      $me->ugid = $ugid;
      $me->userId = $userId;
      $me->users = User_Dash::fetchAll($ugid); 
      $me->msgRecipient = $userId;
      $me->save();
    }
    return $me;
  }
}
class User_Dash extends UserRec {
  //
  public $userId;
  public $active;
  public $name;
  public $roleType;
  public $userGroupId;
  //
  public function getJsonFilters() {
    return array(
      'roleType' => JsonFilter::integer(),
      'userGroupId' => JsonFilter::omit(),
      'active' => JsonFilter::omit());
  }
  //
  static function fetchAll($ugid) {
    $c = new static();
    $c->userGroupId = $ugid;
    $c->active = true;
    return static::fetchAllBy($c, new RecSort('roleType', 'name'));
  }
}
class LoginCount_Dash extends Rec {
  //
  public $recentBad;
  public $lastGood;
  //
  static function fetch($uid) {
    $me = new static();
    $bad = Login::countRecentBadLogins_forUid($uid);
    if ($bad)
      $me->recentBad = $bad;
    $me->lastGood = 'na';  // formatInformalTime(Login::getLastGoodLoginTime($uid));
    return $me;
  }
}
class Login_Dash extends Login {
  //
  public function getJsonFilters() {
    return array(
    	'time' => JsonFilter::informalDateTime());
  }
  //
  static function fetchAll($uid) {
    $sql = "SELECT * FROM logins WHERE uid='$uid' ORDER BY login_id DESC LIMIT 100";
    return static::fromSql($sql);
  }
}