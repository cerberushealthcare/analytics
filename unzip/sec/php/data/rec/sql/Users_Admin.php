<?php
require_once 'php/data/rec/sql/_UserRec.php';
require_once 'php/data/rec/sql/_AddressRec.php';
require_once 'php/data/rec/sql/UserStub.php';
//
/**
 * DAO for User Administration
 * @author Warren Hornsby
 */
class Users_Admin {
  //
  public static function searchByName($text) {
    $c = new UserAdmin();
    $c->name = CriteriaValue::contains($text);
    $users = UserAdmin::fetchAllBy($c);
    return $users;
  }
  public static function searchByUid($text) {
    $c = new UserAdmin();
    $c->uid = CriteriaValue::contains($text);
    $users = UserAdmin::fetchAllBy($c);
    return $users;
  }
  public static function getUserByUid($uid) {
    return UserAdmin::fetchByUid($uid);
  }
  public static function getUsersByUgid($ugid) {
    $users = UserAdmin::fetchAllByUgid($ugid);
    return $users;
  }
  public static function getActiveSubscribers($subscription) {
    return UserAdmin::fetchAllActiveSubscribers($subscription);
  }
  public static function getActiveByBillCode($billCode) {
    return UserAdmin::fetchAllActiveByBillCode($billCode);
  }
  public static function getActiveTrials() {
    return UserAdmin::fetchAllActiveTrials();
  }
  public static function getCreatedCounts() {
    $recs = CreatedCount::fetchAll();
    return $recs;
  }
  public static function getUsersByCreateDate($date) {
    $users = UserAdmin::fetchAllByCreateDate($date);
    return $users;
  }  
  public static function getLoginsByUid($uid) {
    $logins = Login_A::fetchAllByUid($uid);
    Rec::sort($logins, new RecSort('-time'));
    return $logins;
  }
  public static function getLoginsByDate($date) {
    $logins = Login_A::fetchAllByDate($date);
    Rec::sort($logins, new RecSort('-time'));
    return $logins;
  }
  public static function getLoginsByIp($ip) {
    $logins = Login_A::fetchAllByIp($ip);
    Rec::sort($logins, new RecSort('-time'));
    return $logins;
  }
  public static function getSubscriptionAnchors() {
    $subs = UserAdmin::fetchDistinctSubscriptions();
    foreach ($subs as &$sub) 
      $sub = self::asAnchorSubscription($sub);
    return self::bullet($subs);
  }
  public static function getBillCodeAnchors() {
    $codes = BillInfoStub::fetchDistinctBillCodes();
    $recs = BillCodeStub::fetchAllIn($codes);
    foreach ($recs as &$rec) 
      $rec = self::asAnchorBillCode($rec);
    return self::bullet($recs);
  }
  public static function asHtmlTable($recs) {
    if (count($recs) == 0) 
      return '(none)';
    $html = array('<table>');
    $html[] = self::asHtmlTr(current($recs), 'TH');
    foreach ($recs as $rec) 
      $html[] = self::asHtmlTr($rec);
    $html[] = '</table> Total record(s): ' . count($recs);
    return implode('', $html);
  }
  public static function asHtmlVTables($recs) {
    if (count($recs) == 0) 
      return '(none)';
    $html = array();
    foreach ($recs as $rec)
      $html[] = self::asHtmlVTable($rec);
    return implode('', $html);
  }
  public static function asHtmlVTable($rec) {
    if ($rec == null) 
      return '(none)';
    $html = array('<table class=v>');
    $values = array_combine($rec->getHtmlThs(true), $rec->getHtmlTds(true));
    foreach ($values as $th => $td) 
      $html[] = "<tr><th>$th</th><td>$td</td></tr>";
    $html[] = '</table>';
    return implode('', $html);
  }
  public static function asAnchor($action, $value, $text = null) {
    if ($text == null)
      $text = $value;
    return "<a class='$action' href='adminUsers.php?a=$action&v=$value'>$text</a>";
  }
  public static function asAnchorSubscription($subscription) {
    return self::asAnchor('usersActiveSubs', $subscription, UserAdmin::$SUBSCRIPTIONS[$subscription]);    
  }
  public static function asAnchorBillCode($BillCode) {
    $label = $BillCode->billCode . ': $' . $BillCode->monthlyCharge;
    return self::asAnchor('usersByBillCode', $BillCode->billCode, $label);    
  }
  public static function asAnchorUser($User) {
    return self::asAnchor('userByUid', $User->uid, $User->name);    
  }
  //
  private static function asHtmlTr($rec, $tag = 'TD') {
    $values = ($tag == 'TD') ? $rec->getHtmlTds() : $rec->getHtmlThs();
    $html = array("<tr><$tag>");
    $html[] = implode("</$tag><$tag>", $values);
    $html[] = "</$tag></tr>";
    return implode('', $html);
  }
  private static function bullet($list) {
    return implode('&nbsp; &bull; &nbsp;', $list);
  }
}
/**
 * User Admin
 */
class UserAdmin extends UserRec implements ReadOnly, AdminOnly, NoAuthenticate {
  //
  public $userId;
  public $name;
  public $uid;
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
  public /*UserGroupAdmin*/ $UserGroup;
  public /*Login*/ $Login;
  public /*BillInfoStub*/ $NextBilling;
  public /*BillCodeStub*/ $BillCode;
  public $time;
  //
  public function isSupport() {
    return $this->userType > static::TYPE_DOCTOR;
  }
  public function getHtmlThs($vert = false) {
    $fids = $this->getValues();
    unset($fids['regId']);
    unset($fids['userGroupId']);
    unset($fids['licenseState']);
    unset($fids['BillInfo']);
    unset($fids['_authenticated']);
    if (! $vert) {
      unset($fids['admin']); 
      unset($fids['userType']); 
      unset($fids['expiration']); 
      unset($fids['expireReason']); 
    }
    $fids['time'] = 1;
    return array_keys($fids);
  }
  public function getHtmlTds($vert = false) {
    $rec = clone $this;
    $rec->userId = $this->userId;
    if ($rec->Login) 
      $rec->time = formatInformalTime($rec->Login->time);
    $rec->name = Users_Admin::asAnchor('userByUid', $rec->uid, $rec->name);
    $rec->uid = Users_Admin::asAnchor('loginsByUid', $rec->uid);
    $rec->dateCreated = ($vert) ? formatInformalTime($rec->dateCreated) : formatInformalDate($rec->dateCreated);
    $rec->active = ($rec->active) ? 'Yes' : '';
    if ($rec->UserGroup)
      $rec->UserGroup = $rec->UserGroup->asAnchor();
    if ($rec->Login)
      $rec->Login = $rec->Login->asAnchor();
    if ($rec->NextBilling) {
      $label = formatInformalDate($rec->NextBilling->nextBillDate);
      if ($label == '31-Dec-1969')
        $label = '<span style="color:red">(none)</span>';
      else if ($rec->NextBilling->lastBillStatus) 
        $label .= " ($rec->NextBilling->lastBillStatus)";
      $rec->NextBilling = $label;
    } 
    if ($rec->BillCode) 
      $rec->BillCode = Users_Admin::asAnchorBillCode($rec->BillCode); 
      $rec->license = "$rec->licenseState $rec->license";
    $rec->trialExpdt = $rec->trialExpdt == null || $rec->trialExpdt == '0000-00-00' ? '' : (formatDate($rec->trialExpdt) . ' (' . daysUntil($rec->trialExpdt, true)) . 'd)';
    if ($rec->isSupport()) 
      $rec->subscription = '(Support)';
    else
      $rec->subscription = Users_Admin::asAnchorSubscription($rec->subscription);
    unset($rec->regId);
    unset($rec->userGroupId);
    unset($rec->licenseState);
    unset($rec->BillInfo);
    if (! $vert) {
      unset($rec->admin); 
      unset($rec->userType); 
      unset($rec->expiration); 
      unset($rec->expireReason); 
    } else {
      $rec->admin = ($rec->admin) ? 'Admin' : '';
      $rec->userType = self::$TYPES[$rec->userType];
    }
    unset($rec->_authenticated);
    return $rec->getValues();
  }
  //
  private function attachChildren() {
    $this->Login = Login_A::fetchLast($this->userId);
    $this->UserGroup = UserGroupAdmin::fetchWithAddress($this->userGroupId);
    $this->NextBilling = BillInfoStub::fetch($this->userId);
    if ($this->NextBilling)
      $this->BillCode = $this->NextBilling->BillCode;
  }
  //
  public static function fetchDistinctSubscriptions() {
    $sql = "SELECT DISTINCT subscription FROM users ORDER BY subscription";
    return Dao::fetchValues($sql);
  }
  public static function fetchOneBy($criteria) {
    $rec = parent::fetchOneBy($criteria);
    $rec->attachChildren();
    return $rec;
  }
  public static function fetchByUid($uid) {
    $c = new UserAdmin();
    $c->uid = $uid;
    return self::fetchOneBy($c);
  }
  public static function fetchAllBy($criteria) {
    $recs = parent::fetchAllBy($criteria);
    foreach ($recs as &$rec) 
      $rec->attachChildren();
    return $recs;
  }
  public static function fetchAllByCreateDate($date) {
    $c = new UserAdmin();
    $c->dateCreated = CriteriaValue::datePortionEquals($date);
    return self::fetchAllBy($c); 
  }
  public static function fetchAllByUgid($ugid) {
    $c = new UserAdmin();
    $c->userGroupId = $ugid;
    return self::fetchAllBy($c, new RecSort('type'));
  }
  public static function fetchAllActiveSubscribers($subscription = null) {
    if ($subscription == UserRec::SUBSCRIPTION_TRIAL)
      return self::fetchAllActiveTrials();
    $c = new UserAdmin();
    $c->active = true;
    $c->userType = UserRec::TYPE_DOCTOR;
    $c->trialExpdt = '0000-00-00';
    if ($subscription !== null)
      $c->subscription = $subscription; 
    else
      $c->subscription = CriteriaValue::notEquals(UserRec::SUBSCRIPTION_TRIAL);
    return self::fetchAllBy($c); 
  }
  public static function fetchAllActiveByBillCode($billCode) {
    $c = new UserAdmin();
    $c->active = true;
    $c->userType = UserRec::TYPE_DOCTOR;
    $c->BillInfo = new BillInfoStub();
    $c->BillInfo->billCode = $billCode;
    return self::fetchAllBy($c); 
  }
  public static function fetchAllActiveTrials() {
    $c = new UserAdmin();
    $c->active = true;
    $c->subscription = self::SUBSCRIPTION_TRIAL;
    $c->trialExpdt = CriteriaValue::greaterThanOrEquals(nowNoQuotes());
    return self::fetchAllBy($c); 
  }
}
//
class Login_A extends SqlRec implements AdminOnly {
  //
  public $userId;
  public $uid;
  public $time;
  public $ipAddress;
  public $result;
  public /*UserStub*/ $User;
  //
  public function getSqlTable() { 
    return 'logins';
  }
  public function getHtmlThs() {
    $fids = $this->getValues();
    unset($fids['_authenticated']);
    return array_keys($fids);
  }
  public function getHtmlTds() {
    $this->uid = Users_Admin::asAnchor('loginsByUid', $this->uid);
    $this->time = formatInformalTimeDay($this->time);
    $this->ipAddress = $this->asAnchor();
    if ($this->User) 
      $this->User = Users_Admin::asAnchorUser($this->User); 
    return $this->getValues();
  }
  public function asAnchor() {
    return Users_Admin::asAnchor('loginsByIp', $this->ipAddress);
  }
  //
  public static function fetch($userId, $time) {
    $c = new Login_A();
    $c->userId = $userId;
    $c->time = $time;
    return parent::fetchOneBy($c);
  }
  public static function fetchLast($userId) {
    $sql = "SELECT MAX(time) FROM logins WHERE user_id=$userId";
    $time = fetchField($sql);
    return self::fetch($userId, $time);
  }
  public static function fetchAllByUid($uid) {
    $c = static::asCriteria();
    $c->uid = $uid;
    return self::fetchAllBy($c);
  }
  public static function fetchAllByDate($date) {
    $c = static::asCriteria();
    $c->time = CriteriaValue::datePortionEquals($date);
    return self::fetchAllBy($c);
  }
  public static function fetchAllByIp($ipAddress) {
    $c = static::asCriteria();
    $c->ipAddress = $ipAddress;
    return self::fetchAllBy($c);
  }
  //
  static function asCriteria() {
    $c = new static();
    $c->User = CriteriaJoin::requires(new UserStub());
    return $c;
  }
}
/**
 * UserGroup Admin 
 */
class UserGroupAdmin extends SqlRec implements ReadOnly, AdminOnly {
  //
  public $userGroupId;
  public $name;
  public $usageLevel;
  public $estTzAdj;
  public /*Address*/ $Address;
  //
  const USAGE_LEVEL_BASIC = '0';
  const USAGE_LEVEL_PREMIUM = '1';
  const USAGE_LEVEL_ERX = '2';
  //
  public function getSqlTable() {
    return 'user_groups';
  }
  public function asAnchor() {
    return Users_Admin::asAnchor('usersByUgid', $this->userGroupId, $this->name);    
  }
  //
  /**
   * @param int $ugid
   * @return UserGroup 
   */
  public static function fetchWithAddress($ugid) {
    $rec = self::fetch($ugid);
    $rec->Address = AddressAdmin::fetchForUgid($ugid);
    return $rec;
  }
}
/**
 * Address Admin 
 */
class AddressAdmin extends AddressRec implements ReadOnly {
  //
  public $type;
  public $addr1;
  public $addr2;
  public $addr3;
  public $city;
  public $state;
  public $zip;
  public $country;
  public $phone1;
  public $phone1Type;
  public $email1;
  //
  public static function fetchForUgid($ugid) {
    $rec = new AddressAdmin();
    $rec->tableCode = self::TABLE_USER_GROUPS;
    $rec->tableId = $ugid;
    $rec->type = self::TYPE_SHIP;
    return parent::fetchOneBy($rec);
  }
}
/**
 * Usage Detail
 */
class UsageDetail extends SqlRec implements ReadOnly, AdminOnly {
  //
  public $userId;
  public $sessionId;
  public $usageType;
  public $date;
  public $cid;
  //
  const TYPE_NOTE_CREATE = 0;
  const TYPE_NOTE_DOWNLOAD = 1;
  const TYPE_NOTE_COPY = 2;
  //
  public function getSqlTable() {
    return 'usage_details';
  }
  //
  public static function countNoteCreate($userId) {
    $c = new UsageDetail();
    $c->userId = $userId;
    $c->usageType = self::TYPE_NOTE_CREATE;
    return self::count($c);
  } 
  public static function countNoteCreateYTD($userId) {
    $c = new UsageDetail();
    $c->userId = $userId;
    $c->usageType = self::TYPE_NOTE_CREATE;
    $c->date = CriteriaValue::greaterThan('2011-01-01');
    return self::count($c);
  } 
}
/**
 * User Count By Create Date 
 */
class CreatedCount extends Rec {
  //
  public $date;
  public $count;
  //
  public function getHtmlThs() {
    return $this->getFids();
  }
  public function getHtmlTds() {
    $text = substr('*************************************', 0, $this->count) . " $this->count";
    $this->count = Users_Admin::asAnchor('usersByCreateDate', $this->date, $text);
    $this->date = formatInformalDay($this->date);
    return $this->getValues();
  }
  //
  public static function fetchAll() {
    $sql = <<<eos
SELECT SUBSTR(date_created,1,10) AS date, 
  COUNT(*) AS count 
  FROM users 
  WHERE user_type=1
  GROUP BY date 
  ORDER BY date DESC;
eos;
    $rows = fetchArray($sql);
    $recs = array();
    foreach ($rows as $row) 
      $recs[] = new CreatedCount($row['date'], $row['count']);
    return $recs;
  }
}
class UgidCounter extends SqlRec {
  //
  public $userGroupId;
  protected $_table;
  //
  public function getSqlTable() {
    return $this->_table;
  }
  //
  /**
   * @param int $ugid
   * @param string $table 
   * @return int
   */
  public static function count($ugid, $table) {
    $c = new UgidCounter($ugid, $table);
    return parent::count($c);
  }
  public static function countClients($ugid) {
    return self::count($ugid, 'clients');
  }
  public static function countScheds($ugid) {
    return self::count($ugid, 'scheds');
  }
}
class Usage extends Rec {
  //
  public $notes;
  public $patients;
  public $appts;
  //
  public function getHtmlThs() {
    return $this->getFids();
  }
  public function getHtmlTds() {
    return $this->getValues();
  }
  //
  public static function fetch($user) {
    $rec = new Usage();
    $rec->notes = UsageDetail::countNoteCreate($user->userId);
    $rec->patients = UgidCounter::countClients($user->userGroupId);
    $rec->appts = UgidCounter::countScheds($user->userGroupId);
    return $rec;
  }
}
class BillInfoStub extends SqlRec implements ReadOnly, CompositePk, AdminOnly {
  //
  public $userId;
  public $startBillDate;
  public $nextBillDate;
  public $lastBillStatus;
  public $balance;
  public $billCode;
  public /*BillCodeStub*/ $BillCode;
  //
  public function getSqlTable() {
    return 'billinfo';
  }
  //
  public static function fetch($userId) {
    $c = new BillInfoStub($userId);
    $c->BillCode = new BillCodeStub();
    return parent::fetchOneBy($c);
  }
  public static function fetchDistinctBillCodes() {
    $sql = "SELECT DISTINCT bill_code FROM billinfo ORDER BY bill_code";
    return Dao::fetchValues($sql);
  }
  public function getHtmlThs() {
    $fids = $this->getValues();
    unset($fids['userId']);
    unset($fids['billCode']);
    return array_keys($fids);
  }
  public function getHtmlTds() {
    $rec = clone $this;
    unset($rec->userId);
    if ($rec->BillCode)  
      $rec->BillCode = Users_Admin::asAnchorBillCode($rec->BillCode);
    unset($rec->billCode);
    $rec->startBillDate = formatInformalTime($rec->startBillDate);
    $rec->nextBillDate = formatInformalTime($rec->nextBillDate);
    return $rec->getValues();
  }
}
class BillCodeStub extends SqlRec implements ReadOnly, AdminOnly {
  //
  public $billCode;
  public $monthlyCharge;
  //
  public function getSqlTable() {
    return 'bill_codes';    
  }
  public function fetchAllIn($billCodes) {
    $c = new BillCodeStub();
    $c->billCode = CriteriaValue::in($billCodes);
    return parent::fetchAllBy($c);
  }
}
