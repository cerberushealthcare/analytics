<?php
require_once 'php/data/rec/sql/_SchedRec.php';
require_once 'php/data/rec/sql/_ClientRec.php';
require_once 'php/data/rec/sql/_HdataRec.php';
//
/** View recs */
class Appt extends SchedRec {
  //
  public $schedId;
  public $userId;  
  public $userGroupId;
  public $clientId;
  public $date;
  public $timeStart;
  public $duration;
  public $closed;
  public $status;
  public $comment;
  public $type;
  public $schedEventId;
  public /*Client_Appt*/$Client;
  //
  public function toJsonObject(&$o) {
    parent::toJsonObject($o);
    $o->_date = formatLongDate($o->date);
    $o->_date2 = date("D, d-M-Y", strtotime($o->date));
  }
  public function addPast($today, $now) {
    if ($this->date < $today) {
      $this->_past = 1;
    } else if ($this->date == $today) {
      $time = new Military($this->timeStart);
      if ($time->minus($now) < 0)
        $this->_past = 1;
    }
  }
  //
  static function fetchAll($ugid, $date, $days = 1, $userId = null) {
    $date = $date ? dateToString($date) : nowShortNoQuotes();
    $c = new static();
    $c->userGroupId = $ugid;
    if ($days == 1) 
      $c->setDateCriteria($date);
    else 
      $c->setDateCriteria(CriteriaValue::betweenDates(array($date, futureDate($days, 0, 0, $date))));
    $c->userId = $userId;
    $c->Client = Client_Appt::asJoin();
    return static::fetchAllBy($c, null, 500);
  }
  static function fetchHistory($cid) {
    $c = new static();
    $c->clientId = $cid;
    return static::fetchAllBy($c, new RecSort('-date', '-timeStart'));
  }
  static function addPasts($recs) {
    $now = Military::asNow();
    $today = nowShortNoQuotes() . ' 00:00:00';
    foreach ($recs as &$rec)
      $rec->addPast($today, $now);
    return $recs;
  }
}
class Appt_Next extends Appt {
  //
  public function toJsonObject(&$o) {
    parent::toJsonObject($o);
    $o->_date = formatInformalDate($o->date, true);
  }
  //
  static function fetch($cid) {
    $c = new static();
    $c->clientId = $cid;
    $c->setDateCriteria(CriteriaValue::greaterThanOrEquals(nowNoQuotes()));
    $recs = static::fetchAllBy($c, new RecSort('date', 'timeStart'));
    return current($recs);
  }
}
class Appt_Cerberus extends Appt {
  //
  public $schedId;
  public $userId;  
  public $userGroupId;
  public $clientId;
  public $date;
  public $timeStart;
  public $duration;
  public $closed;
  public $status;
  public $comment;
  public $type;
  public $schedEventId;
  public /*Client_Appt*/$Client;
  public /*CR_Appt*/$CrAppt;
  //
  public function toJsonObject(&$o) {
    $o->_label = $this->getLabel($this->CrAppt->reason, $this->CrAppt->status);
    $o->_date = formatLongDate($o->date);
    $o->_date2 = date("D, d-M-Y", strtotime($o->date));
  }
  //
  static function fetchAll($ugid, $date, $days, $userId, $practiceId) {
    require_once 'php/c/patient-billing/CerberusBilling.php';
    $recs = CerberusBilling::fetchAppts($userId, $date, $days);
    return static::all($recs, $userId, $ugid, $practiceId);
  }
  static function all(/*CR_Appt[]*/$recs, $userId, $ugid, $practiceId) {
    $us = array();
    foreach ($recs as $rec) 
      $us[] = static::from($rec, $userId, $ugid, $practiceId);
    return $us;
  }
  static function from(/*CR_Appt*/$rec, $userId, $ugid, $practiceId) {
    $me = new static();
    $me->userId = $userId;
    $me->userGroupId = $ugid;
    $me->clientId = $rec->agency_clientid;
    $d = strtotime($rec->appt_start);
    $me->date = date('Y-m-d', $d);
    $me->timeStart = intval(date('Gi', $d));
    $me->duration = $rec->duration_min;
    if ($rec->agency_clientid) {
      $cid = ApiIdXref_Cerberus::lookupClientId($practiceId, $rec->agency_clientid); 
      $me->clientId = $cid;
      if ($cid)
        $me->Client = Client_Appt::fetch($cid);
    } else { 
      $me->comment = $rec->lastname;
      $me->schedEventId = 999;
    }
    $me->CrAppt = $rec;
    return $me;
  }
}
class Client_Appt extends ClientStub {
  //
  public function asJoin() {
    $me = new static();
    $me->Address = ClientAddress::asJoinHome();
    return $me;
  }
}
//
/** Edit recs */
class Appt_Edit extends SchedRec {
  //
  public $schedId;
  public $userId;  
  public $userGroupId;
  public $clientId;
  public $date;
  public $timeStart;
  public $duration;
  public $closed;
  public $status;
  public $comment;
  public $type;
  public $schedEventId;
  public /*Event*/$Event;
  public /*Client_Appt*/$Client;
  public $_formatDate;
  public $_formatTime;
  public $_durationHr;
  public $_durationMin;
  public $_maxRepeatDate/*last date created if repeats max out*/;
  //
  static function from($o, $ugid = null) {
    $me = new static($o);
    if ($ugid)
      $me->userGroupId = $ugid;
    return $me;
  }
  static function fetch($id) {
    $c = new static($id);
    $c->Client = Client_Appt::asJoin();
    $c->Event = Event::asOptionalJoin();
    return static::fetchOneBy($c);
  }
  static function delete($schedId, $withFutureRepeats = false) {
    $me = static::fetch($schedId);
    $ugid = $me->userGroupId;
    $event = $me->Event;
    if ($event && $withFutureRepeats)
      Dao::query("DELETE FROM scheds WHERE sched_event_id=" . $event->schedEventId . " AND sched_id>=" . $me->schedId);
    else
      parent::delete($me);
    if ($event) {
      $recs = static::fetchAllRepeats($ugid, $event->schedEventId);
      if (empty($recs)) 
        Event::delete($event);
    }
  }
  //
  public function toJsonObject(&$o) {
    $m = new Military($this->timeStart);
    $o->_formatDate = date("F j", strtotime($this->date));
    $o->_formatTime = $m->formatted();
    $o->_durationHr = Military::div($this->duration, 60);
    $o->_durationMin = $this->duration - $o->_durationHr * 60;
    $o->_date = formatLongDate($o->date);
  }
  public function fromJsonObject($o) {
    if (! isset($o->clientId))
      $this->clientId = 0;
  }
  public function getJsonFilters() {
    return array(
    	'date' => JsonFilter::editableDate(),
      'closed' => JsonFilter::boolean());
  }
  public function getAuditLabel() {
    return $this->getLabel();
  }
  public function getAuditRecName() {
    return 'Appointment';
  }
  public function save() {
    if (! empty($this->Event)) {
      $this->Event->save();
      $this->schedEventId = $this->Event->schedEventId;
    }
    parent::save();
    Hdata_SchedDate::from($this)->save();
    if (! empty($this->Event)) 
      $this->saveRepeats();
    return $this;
  }
  //
  protected function saveRepeats($max = 300) {
    $ugid = $this->userGroupId;
    $eid = $this->schedEventId;
    $id = $this->schedId;
    if ($ugid && $id && $eid) {
      Dao::query("DELETE FROM scheds WHERE user_group_id=$ugid AND sched_event_id=$eid AND sched_id>$id");
      $dates = $this->Event->getRepeatDates($this->date);
      if (! empty($dates)) {
        $this->insertRepeats($dates);
        $ids = Dao::fetchValues("SELECT sched_id FROM scheds WHERE user_group_id=$ugid AND sched_event_id=$eid AND sched_id>$id");
        $this->insertHdatas($ugid, $ids, $dates);
        if (count($dates) == $max)
          $this->_maxRepeatDate = end($dates);
      }
    }
  }
  protected function hasClientId() {
    return $this->clientId > 0/*to allow client auth security for events*/;
  }
  protected function insertRepeats($dates) {
    $us = array();
    foreach ($dates as $date)
      $us[] = $this->asRepeat($date);
    static::insertAll($us);
  }
  protected function asRepeat($date) {
    $me = clone $this;
    $me->schedId = null;
    $me->date = dateToString($date);
    return $me;
  }
  protected function insertHdatas($ugid, $ids, $dates) {
    $recs = array();
    for ($i = 0; $i < count($ids); $i++) 
      $recs[] = Hdata_SchedDate::create($ugid, $ids[$i], $dates[$i]);
    Hdata_SchedDate::insertAll($recs);
  }
  //
  protected static function fetchAllRepeats($ugid, $eid) {
    $c = new static();
    $c->userGroupId = $ugid;
    $c->schedEventId = $eid;
    return static::fetchAllBy($c);
  }
}
class Event extends SchedEventRec {
  //
  public $schedEventId;
  public $rpType;
  public $rpEvery;
  public $rpUntil;
  public $rpOn;
  public $rpBy;
  public $comment;
  //
  public function getAuditRecName() {
    return 'Schedule Event';
  }
  public function getJsonFilters() {
    return array(
    	'rpUntil' => JsonFilter::editableDate());
  }
  public function toJsonObject(&$o) {
    $o->dows = $this->getDows();
  }
  public function fromJsonObject($o) {
    $this->setOnByDows($o->dows);
  }
}
