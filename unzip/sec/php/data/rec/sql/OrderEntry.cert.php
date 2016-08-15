<?php
require_once 'php/data/rec/sql/_TrackItemRec.php';
require_once 'php/data/rec/sql/Procedures_Admin.php';
//
/**
 * Order Entry and Tracking
 * @author Warren Hornsby
 */
class OrderEntry {
  //
  /** Get single track item for editing */
  static function /*TrackItem*/get($id) {
    $rec = TrackItem::fetch($id);
    return $rec;
  }
  /** Get active orders */
  static function /*TrackItemStub[]*/getActiveItems($clientId) {
    $c = new TrackItemStub();
    $c->clientId = $clientId;
    $c->status = CriteriaValue::notEquals(TrackItem::STATUS_CLOSED);
    $c->Hd_dueDate = HData_TrackDueDate::join(CriteriaValue::lessThanOrEquals(nowNoQuotes()));
    $recs = TrackItem::fetchAllBy($c, new RecSort('-priority', 'trackDesc'), 2000);
    $recs = static::omitUnschedRepeats($recs);
    return $recs;
  }
  /** Get open/scheduled/unscheduled/closed order list items */
  static function /*TrackItemList[]*/getOpenItems($clientId = null) {
    global $login;
    $c = TrackItemList::asCriteria($login->userGroupId, $clientId);
    $c->status = CriteriaValue::notEquals(TrackItem::STATUS_CLOSED);
    //$c->Hd_dueDate = HData_TrackDueDate::join(CriteriaValue::lessThanOrEquals(nowNoQuotes()));
    $recs = TrackItem::fetchAllBy($c, new RecSort('trackCat', 'ClientStub.name', 'trackDesc', 'dueDate'), 2000);
    $recs = static::omitUnschedRepeats($recs);
    return $recs;
  }
  static function /*TrackItemList[]*/getSchedItems($clientId = null) {
    global $login;
    $c = TrackItemList::asCriteria($login->userGroupId, $clientId);
    $c->status = CriteriaValue::equals(TrackItem::STATUS_SCHED);
    $recs = TrackItem::fetchAllBy($c, new RecSort('-priority', 'schedDate', 'ClientStub.name', 'trackDesc'), 2000);
    return $recs;
  }
  static function /*TrackItemList[]*/getUnschedItems($clientId = null) {
    global $login;
    $c = TrackItemList::asCriteria($login->userGroupId, $clientId);
    $c->status = CriteriaValue::equals(TrackItem::STATUS_ORDERED);
    $recs = TrackItem::fetchAllBy($c, new RecSort('-priority', 'dueDate', 'ClientStub.name', 'trackDesc'), 2000);
    $recs = static::omitUnschedRepeats($recs);
    return $recs;
  }
  static function /*TrackItemList[]*/getClosedItems($clientId = null) {
    global $login;
    $c = TrackItemList::asCriteria($login->userGroupId, $clientId);
    $c->status = CriteriaValue::equals(TrackItem::STATUS_CLOSED);
    $recs = TrackItem::fetchAllBy($c, new RecSort('-closedDate', 'trackDesc'));
    return $recs;
  }
  static function omitUnschedRepeats($from) {
    $recs = array();
    $last = null;
    foreach ($from as $rec) {
      if ($last && $last->trackEventId && $last->trackEventId == $rec->trackEventId && $last->isUnsched() && $rec->isUnsched()) {
        // dupe
      } else {
        $recs[] = $rec;
      }
      $last = $rec;
    }
    return $recs;
  }
  /** Update single tracking item */
  static function /*TrackItem*/saveItem($o) {
    $rec = TrackItem::saveFromUi($o);
    return $rec;
  }
  /**
   * Order entry
   * @param array $orderItems [{'cid':#,'sid':#,'key':$,'tcat':$,'tdesc':$,'cpt':$},..]
   * @return array(
   *   'items'=>array(TrackItem,..),
   *   'add'=>bool      // true if no items have yet been saved
   *   )
   */
  static function order($orderItems) {
    global $login;
    $items = array();
    $sid = null;
    $add = true;
    foreach ($orderItems as $orderItem) {
      if ($orderItem->sid)
        LoginDao::authenticateSessionId($orderItem->sid, $sid);
      $item = ($orderItem->sid == TrackItem::SID_FACESHEET) ? null : TrackItem::fetchByOrderKey($orderItem->sid, $orderItem->key);
      if ($item == null)
        $item = TrackItem::fromOrderItem($login->userGroupId, $login->userId, $orderItem);
      else {
        if ($item->status == 0) { /*still open, so allow changes*/
          $changed = TrackItem::fromOrderItem($login->userGroupId, $login->userId, $orderItem);
          $changed->trackItemId = $item->trackItemId;
          $item = $changed;
        }
        $add = false;
      }
      $title = TrackItem::$TCATS[$item->trackCat];
      $items[] = $item;
      $trackCatItems[$title][] = $item;
    }
    return array(
      'items' => self::sortItems($items),
      'add' => $add);
  }
  static function saveOrder(/*TrackItem[]*/$trackItems) {
    global $login;
    $items = TrackItem::reviveAll($trackItems);
    $sid = null;
    foreach ($items as $item) {
      if ($item->sessionId)
        LoginDao::authenticateSessionId($item->sessionId, $sid);
      $item->userGroupId = $login->userGroupId;
      $item->userId = $login->userId;
      $item->save();
    }
  }
  static function saveOrderSingle(/*TrackItem*/$trackItem) {
    global $login;
    $item = TrackItem::reviveSingle($trackItem, $login->userGroupId, $login->userId);
    $item->save();
  }
  /**
   * @param int $trackItemId
   * @param int $scanIndexId
   * @return TrackItem
   */
  static function receivedByScan($trackItemId, $scanIndexId) {
    global $login;
    $rec = TrackItem::fetch($trackItemId);
    $rec->save_asReceivedScan($login->userId, $scanIndexId);
    return $rec;
  }
  /**
   * @param int $trackItemId
   * @param int $procId
   * @return TrackItem
   */
  static function receivedByProc($trackItemId, $procId) {
    global $login;
    $rec = TrackItem::fetch($trackItemId);
    $rec->save_asReceivedProc($login->userId, $procId);
    return $rec;
  }
  /**
   * @return int PID of ordering template
   */
  static function getPid() {
    return Templates_OrderEntry::getPid();
  }
  //
  private static function sortItems($recs) {
    return Rec::sort($recs, new RecSort(
      'trackCat',
      'closedDate',
      '-priority',
      'schedDate',
      'dueDate',
      'trackDesc'));
  }
}
//
/**
 * TrackItem Record
 */
class TrackItem extends TrackItemRec {
  //
  public $trackItemId;
  public $userGroupId;
  public $clientId;
  public $sessionId;
  public $key;
  public $userId;
  public $priority;
  public $trackCat;
  public $trackDesc;
  public $cptCode; /*note: this is really the IPC!*/
  public $status;
  public $procId;
  public $scanIndexId;
  public $orderDate;
  public $orderBy;
  public $orderNotes;
  public $diagnosis;
  public $icd;
  public $freq;
  public $duration;
  public $schedDate;
  public $schedWith;
  public $schedLoc;
  public $schedBy;
  public $schedNotes;
  public $closedDate;
  public $closedFor;
  public $closedBy;
  public $closedNotes;
  public $selfRefer;
  public $dueDate;
  public $trackEventId;
  public $icd10;
  public /*ClientStub*/ $ClientStub;
  public /*DocSession*/ $DocSession; /*as source*/
  public /*UserStub*/ $UserStub_orderBy;
  public /*UserStub*/ $UserStub_schedBy;
  public /*UserStub*/ $UserStub_closedBy;
  public /*Provider*/ $Provider_schedWith;
  public /*FacilityAddress*/ $Address_schedLoc;
  public /*Ipc*/ $Ipc;
  public /*DocProc*/ $DocProc; /*as received*/
  public /*DocScan*/ $DocScan; /*as received*/
  public /*DocProc*/ $DocProc_PatientSummary;
  public /*TrackEvent*/ $Event;
  //
  public function _toJsonObject() {
    if (isset($this->DocSession))
      $this->DocSession = DocStub::from($this->DocSession);
    if (isset($this->DocProc))
      $this->DocProc = DocStub::from($this->DocProc);
    if (isset($this->DocScan))
      $this->DocScan = DocStub::from($this->DocScan);
    if (isset($this->ClientStub) && is_object($this->ClientStub))
      $this->_client = $this->ClientStub->getFullName();
    return parent::_toJsonObject();
  }
  public function toJsonObject(&$o) {
    $o->_cat = geta(self::$TCATS, $this->trackCat);
    if (isset($o->UserStub_orderBy)) {
      $o->_orderedBy = $this->UserStub_orderBy->name;
      $o->_ordered = $o->orderDate . ' by ' . $o->_orderedBy;
    }
    if (isset($o->UserStub_schedBy))
      $o->_sched = 'by ' . $o->UserStub_schedBy->name;
    if (isset($o->UserStub_closedBy))
      $o->_closed = 'by ' . $o->UserStub_closedBy->name;
    $o->lookup('status', self::$STATUSES);
    $o->lookup('trackCat', self::$TCATS);
    $o->_overdue = daysFrom($this->schedDate, true);
    if (isset($o->DocProc_PatientSummary)) {
      $o->summsent = true;
      unset($o->DocProc_PatientSummary);
    }
    if (isset($o->Event)) {
      $o->rp_id = $o->Event->trackEventId;
      $o->rp_type = $o->Event->type;
      $o->rp_every = $o->Event->every;
      $o->rp_by = $o->Event->by;
      $o->rp_until = formatDate($o->Event->until);
      $dows = $this->Event->getDows();
      foreach ($dows as $i => $b) {
        $fid = "rp_on$i";
        $o->$fid = $b;
      }
      unset($o->Event);
    }
  }
  public function getJsonFilters() {
    return array(
      'dueDate' => JsonFilter::editableDate(),
      '_dueDate' => JsonFilter::informalDate('dueDate'),
      'orderDate' => JsonFilter::reportDateTime(),
      '_orderDate' => JsonFilter::informalDate('orderDate'),
      'schedDate' => JsonFilter::editableDateTime(),
      '_schedDate' => JsonFilter::editableTimeInformal('schedDate'),
      'closedDate' => JsonFilter::editableDate(),
      '_closedDate' => JsonFilter::informalDate('closedDate'),
      'selfRefer' => JsonFilter::boolean());
  }
  public function getAuditLabel() {
    return $this->trackDesc;
  }
  public function isReferral() {
    return $this->trackCat == static::TCAT_REFER;
  }
  public function save() {
    global $login;
    if (! empty($this->Event)) {
      $this->Event->save();
      $this->trackEventId = $this->Event->trackEventId;
    }
    $this->checkStatusChange($login->userId);
    parent::save();
    Hdata_TrackDueDate::from($this)->save();
    if (! empty($this->Event))
      $this->saveRepeats();
    return $this;
  }
  public function save_asReceivedScan($userId, $scanIndexId, $notes = null) {
    $this->set_asReceived($userId, $notes, $scanIndexId, null);
    parent::save();
  }
  public function save_asReceivedProc($userId, $procId, $notes = null) {
    $this->set_asReceived($userId, $notes, null, $procId);
    parent::save();
  }
  //
  protected function saveRepeats($max = 300) {
    $ugid = $this->userGroupId;
    $eid = $this->trackEventId;
    $id = $this->trackItemId;
    if ($ugid && $id && $eid) {
      Dao::query("DELETE FROM track_items WHERE user_group_id=$ugid AND track_event_id=$eid AND track_item_id>$id");
      $dates = $this->Event->getRepeatDates($this->dueDate);
      if (! empty($dates)) {
        $this->insertRepeats($dates);
        $ids = Dao::fetchValues("SELECT track_item_id FROM track_items WHERE user_group_id=$ugid AND track_event_id=$eid AND track_item_id>$id");
        $this->insertHdatas($ugid, $ids, $dates);
        if (count($dates) == $max)
          $this->_maxRepeatDate = end($dates);
      }
    }
  }
  protected function insertRepeats($dates) {
    $us = array();
    foreach ($dates as $date)
      $us[] = $this->asRepeat($date);
    static::insertAll($us);
  }
  protected function asRepeat($date) {
    $me = clone $this;
    $me->trackItemId = null;
    $me->dueDate = dateToString($date);
    return $me;
  }
  protected function insertHdatas($ugid, $ids, $dates) {
    $recs = array();
    for ($i = 0; $i < count($ids); $i++)
      $recs[] = Hdata_TrackDueDate::create($ugid, $ids[$i], $dates[$i]);
    Hdata_TrackDueDate::insertAll($recs);
  }
  private function set_asReceived($userId, $notes, $scanIndexId, $procId) {
    $this->status = static::STATUS_CLOSED;
    $this->closedDate = nowNoQuotes();
    $this->closedBy = $userId;
    $this->closedFor = TrackItem::CLOSED_FOR_RECEIVED;
    $this->closedNotes = $notes;
    $this->scanIndexId = $scanIndexId;
    $this->procId = $procId;
  }
  private function checkStatusChange($userId) {
    $newStatus = $this->determineStatus();
    if ($this->status != $newStatus) {
      switch ($newStatus) {
        case self::STATUS_ORDERED:
          $this->clearSchedInfo();
          $this->clearClosedInfo();
          break;
        case self::STATUS_SCHED:
          $this->schedBy = $userId;
          $this->clearClosedInfo();
          break;
        case self::STATUS_CLOSED:
          $this->closedBy = $userId;
          break;
      }
      $this->status = $newStatus;
    }
  }
  private function determineStatus() {
    if ($this->closedDate)
      return self::STATUS_CLOSED;
    if ($this->schedDate || $this->selfRefer)
      return self::STATUS_SCHED;
    return self::STATUS_ORDERED;
  }
  private function clearSchedInfo() {
    $this->schedBy = null;
  }
  private function clearClosedInfo() {
    $this->closedBy = null;
    $this->closedFor = null;
    $this->closedNotes = null;
  }
  //
  static function reviveAll($objs) {
    global $login;
    $recs = array();
    foreach ($objs as $obj)
      $recs[] = static::reviveSingle($obj, $login->userGroupId, $login->userId);
    return $recs;
  }
  static function reviveSingle($obj, $ugid, $userId) {
    $rec = new static($obj);
    $rec->userGroupId = $ugid;
    $rec->userId = $userId;
    $rec->orderBy = $userId;
    $rec->orderDate = nowNoQuotes();
    $rec->dueDate = $rec->orderDate;
    if ($rec->priority == null)
      $rec->priority = static::PRIORITY_NORMAL;
    return $rec;
  }
  static function saveFromUi($o) {
    $rec = static::fromUi($o);
    $rec->save();
    $rec = TrackItem::fetch($rec->trackItemId);
    $maxRepeatDate = get($rec, '_maxRepeatDate');
    if (isset($o->summsent) && $rec->DocProc_PatientSummary == null)
      Proc_PatientSummary::record_fromTrackItem($rec);
    if ($rec->isReferral() && $rec->schedDate)
      Proc_Referral::record_fromTrackItem($rec);
    if ($maxRepeatDate)
      $rec->_max = $maxRepeatDate;
    return $rec;
  }
  static function fromUi($o) {
    $me = new TrackItem($o);
    $me->Event = TrackEvent::fromUi($o);
    return $me;
  }
  /**
   * @param int $id
   * @return TrackItem
   */
  static function fetch($id) {
    $c = self::asCriteria(null, null);
    $c->trackItemId = $id;
    return parent::fetchOneBy($c);
  }
  /**
   * @param int $sid
   * @param string $key
   * @return TrackItem
   */
  static function fetchByOrderKey($sid, $key) {
    global $login;
    $rec = new TrackItem();
    $rec->userGroupId = $login->userGroupId;
    $rec->sessionId = $sid;
    $rec->key = $key;
    return parent::fetchOneBy($rec);
  }
  /**
   * @param int $ugid
   * @param int @userId
   * @param object $orderItem {'cid':#,'sid':#,'key':$,'tcat':$,'tdesc':$,'cpt':$}
   * @return TrackItem
   */
  static function fromOrderItem($ugid, $userId, $orderItem) {
    $me = new TrackItem(
      null,
      $ugid,
      $orderItem->cid,
      $orderItem->sid,
      $orderItem->key,
      $userId,
      null,
      $orderItem->tcat,
      $orderItem->tdesc,
      get($orderItem, 'cpt'),
      self::STATUS_ORDERED,
      null,
      null,
      null,
      $userId,
      null,
      get($orderItem, 'diag'),
      get($orderItem, 'icd'));
    $me->icd10 = get($orderItem, 'icd10');
    return $me;
  }
  /**
   * @return TrackItem
   */
  static function asCriteria($ugid, $cid) {
    $rec = new TrackItem();
    $rec->userGroupId = $ugid;
    $rec->clientId = $cid;
    $rec->ClientStub = new ClientStub();
    $rec->DocSession = new DocSession();
    $rec->UserStub_orderBy = new UserStub();
    $rec->UserStub_schedBy = new UserStub();
    $rec->UserStub_closedBy = new UserStub();
    $rec->Provider_schedWith = new Provider();
    $rec->Address_schedLoc = new Address();
    $rec->Ipc = Ipc::asOptionalJoin($ugid, 'cptCode');
    $rec->DocProc = new DocProc();
    $rec->DocProc->Ipc = Ipc::asOptionalJoin($ugid);
    $rec->DocScan = new DocScan();
    $rec->DocProc_PatientSummary = Proc_PatientSummary::asOptionalJoin();
    $rec->Event = new TrackEvent();
    return $rec;
  }
}
class TrackEvent extends TrackEventRec {
  //
  public $trackEventId;
  public $type;
  public $every;
  public $until;
  public $on;
  public $by;
  public $comment;
  //
  public function getAuditRecName() {
    return 'Track Event';
  }
  static function fromUi($o) {
    if (get($o, 'rp_type', '0') > '0') {
      $o->dows = array();
      for ($i = 0; $i < 7; $i++) {
        $fid = "rp_on$i";
        $o->dows[] = get($o, $fid) ? 1 : 0;
      }
      $me = new static();
      $me->trackEventId = get($o, 'rp_id');
      $me->type = $o->rp_type;
      $me->every = $o->rp_every;
      $me->until = formatFromDate($o->rp_until);
      $me->by = $o->rp_by;
      $me->setOnByDows($o->dows);
      return $me;
    }
  }
  public function fromJsonObject($o) {
    $this->setOnByDows($o->dows);
  }
}
class TrackItemStub extends TrackItemRec implements ReadOnly {
  //
  public $trackItemId;
  public $clientId;
  public $priority;
  public $trackDesc;
  public $status;
  public $orderDate;
  public $trackEventId;
}
class TrackItemList extends TrackItem implements ReadOnly {
  //
  static function asCriteria($ugid, $cid) {
    $c = new static();
    $c->userGroupId = $ugid;
    $c->clientId = $cid;
    $c->ClientStub = new ClientStub();
    $c->DocSession = new DocSession();
    $c->UserStub_orderBy = new UserStub();
    if ($cid)
      $c->Ipc = Ipc::asOptionalJoin($ugid, 'cptCode');
    return $c;
  }
}
//
require_once 'php/data/rec/sql/Clients.php';
require_once 'php/data/rec/sql/Documentation.php';
require_once 'php/data/rec/sql/UserStub.php';
require_once 'php/data/rec/sql/Templates_OrderEntry.php';
require_once 'php/dao/JsonDao.php';
