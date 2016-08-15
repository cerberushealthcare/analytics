<?php
require_once 'php/data/rec/sql/_SchedRec.php';
//
class Scheduling {
  //
  static function getAppts($date = null, $providerId = null) {
    global $login;
    if ($providerId == null)
      $providerId = $login->docId;
    $recs = Appt::fetchAll($login->userGroupId, $date, $providerId);
    return Appt::addPasts($recs);
  }
}
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
  public /*ClientStub*/ $Client;
  //
  public function toJsonObject(&$o) {
    parent::toJsonObject($o);
    $o->_date = formatLongDate($o->date);
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
  static function fetchAll($ugid, $date = null, $userId = null) {
    $date = $date ? dateToString($date) : nowShortNoQuotes();
    $c = new static();
    $c->userGroupId = $ugid;
    $enddate = futureDate(14, 0, 0, $date);
    $c->date = CriteriaValue::betweenDates(array($date, $enddate));
    $c->userId = $userId;
    $c->Client = new ClientStub();
    return static::fetchAllBy($c, new RecSort('date', 'timeStart'), 500);
  }
  static function addPasts($recs) {
    $now = Military::asNow();
    $today = nowShortNoQuotes() . ' 00:00:00';
    foreach ($recs as &$rec)
      $rec->addPast($today, $now);
    return $recs;
  }
}
