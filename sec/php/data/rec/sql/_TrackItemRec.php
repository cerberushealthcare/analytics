<?php
require_once 'php/data/rec/sql/_SqlRec.php';
//
class TrackItemRec extends SqlRec implements AutoEncrypt {
  /*
  public $trackItemId;
  public $userGroupId;
  public $clientId;
  public $sessionId;
  public $key;
  public $userId;
  public $priority;
  public $trackCat;
  public $trackDesc;
  public $cptCode;
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
  public $drawnDate;
  */
  //
  const SID_FACESHEET = '0';
  //
  const PRIORITY_NORMAL = '0'; 
  const PRIORITY_STAT = '9';
  static $PRIORITIES = array(
    self::PRIORITY_NORMAL => 'Normal',
    self::PRIORITY_STAT => 'STAT');
  //
  const TCAT_LAB = '1';
  const TCAT_NUCLEAR = '2';
  const TCAT_RADIO = '3';
  const TCAT_REFER = '4';
  const TCAT_TEST = '5';
  const TCAT_PROC = '6';
  const TCAT_IMMUN = '7';
  const TCAT_RTO = '8';
  const TCAT_IOL = '9'; 
  const TCAT_SURG = '10'; 
  const TCAT_OTHER = '99';
  public static $TCATS = array(
    self::TCAT_LAB => 'Labs',
    self::TCAT_NUCLEAR => 'Nuclear Medicine',
    self::TCAT_RADIO => 'Radiology',
    self::TCAT_REFER => 'Referrals',
    self::TCAT_TEST => 'Tests',
    self::TCAT_PROC => 'Procedures',
    self::TCAT_IMMUN => 'Immunizations',
    self::TCAT_RTO => 'Return to Office',
    self::TCAT_IOL => 'In-Office Labs',
    self::TCAT_SURG => 'Surgical',
    self::TCAT_OTHER => '(Other)');
  //
  const STATUS_ORDERED = '0';
  const STATUS_SCHED = '1';
  const STATUS_CLOSED = '9';
  static $STATUSES = array(
    self::STATUS_ORDERED => 'Ordered',
    self::STATUS_SCHED => 'Scheduled/Performed',
    self::STATUS_CLOSED => 'Closed');
  //
  const CLOSED_FOR_RECEIVED = '1';
  const CLOSED_FOR_CANCELLED = '2';
  const CLOSED_FOR_REFUSED = '3';
  static $CLOSED_FORS = array(
    self::CLOSED_FOR_RECEIVED => 'Received',
    self::CLOSED_FOR_CANCELLED => 'Cancelled',
    self::CLOSED_FOR_REFUSED => 'Refused');
  //
  public function getSqlTable() {
    return 'track_items';
  }
  public function getEncryptedFids() {
    return array('orderDate','schedDate','trackDesc','orderNotes','diagnosis','schedNotes','closedNotes','dueDate','drawnDate');
  }
  public function setDueDate(/*CriteriaValues|CriteriaValue|string*/$cv) {
    if ($cv) {
      $this->Hd_dueDate = Hdata_TrackDueDate::join($cv);
    }
  }
  public function isUnsched() {
    return $this->status == static::STATUS_ORDERED;
  }  
  public function isOpen() {
    return $this->status != static::STATUS_CLOSED; 
  }
  //
  static function asActiveCriteria($cid = null) {
    $c = new static();
    $c->status = CriteriaValue::notEquals(static::STATUS_CLOSED);
    $c->setDueDate(CriteriaValue::lessThanOrEquals(nowNoQuotes()));
    $c->clientId = $cid;
    return $c;
  }
}
class TrackEventRec extends SqlRec implements AutoEncrypt {
  //
  public $trackEventId;
  public $type;
  public $every;
  public $until;
  public $on;
  public $by;
  public $comment;
  //
  const TYPE_NONE = 0;
  const TYPE_DAY = 1;
  const TYPE_WEEK = 2;
  const TYPE_MONTH = 3;
  const TYPE_YEAR = 4;
  static $TYPES = array(
    self::TYPE_NONE => '(None)',
    self::TYPE_DAY => 'daily',
    self::TYPE_WEEK => 'weekly',
    self::TYPE_MONTH => 'monthly',
    self::TYPE_YEAR => 'annually');
  static $LTYPES = array(
    self::TYPE_NONE => '',
    self::TYPE_DAY => 'day',
    self::TYPE_WEEK => 'week',
    self::TYPE_MONTH => 'month',
    self::TYPE_YEAR => 'year');
  //
  const BY_DAY = 1;
  const BY_DATE = 2;
  const BY_DAY_OF_LAST_WEEK = 3;
  static $BYS = array(
    self::BY_DAY => 'day (e.g. every 3rd Wed)',
    self::BY_DATE => 'date (e.g. every 15th)');
  //
  static $EVERYS = array(
    '1' => 'single',
    "2" => "other",
    "3" => "3rd",
    "4" => "4th",
    "5" => "5th",
    "6" => "6th",
    "7" => "7th",
    "8" => "8th",
    "9" => "9th",
    "10" => "10th",
    "11" => "11th",
    "12" => "12th",
    "13" => "13th",
    "14" => "14th");
  //
  protected static $DOW_BITS = array(1, 2, 4, 8, 16, 32, 64);
  //
  public function getSqlTable() {
    return 'track_events';
  }
  public function getEncryptedFids() {
    return array('until','comment');
  }
  public function setOnByDows(/*bool[]*/$dows) {
    $this->on = 0;
    for ($i = 0; $i < 7; $i++) 
      $this->on += $dows[$i] * static::$DOW_BITS[$i];
  }
  public function isOnDow($dow) {
    $dows = $this->getDows();
    return $dows[$dow];
  }
  public function /*string[]*/getRepeatDates($from, $max = 300) {
    logit_r($this, 'repeat');
    logit_r($from, 'repeat from');
    $dates = array();
    if ($this->type != static::TYPE_NONE) {
      $date = strtotime($from);
      $until = strtotime($this->until);
      logit_r($date, 'date');
      logit_r($until, 'until');
      for ($i = 0; $i < $max; $i++) {
        $date = $this->nextRepeatDate($date);
        logit_r($date, 'date+');
        if ($date == null || $date > $until) 
          break;
        $dates[] = date("d-M-Y", $date);
      }
    }
    return $dates;
  }
  protected function nextRepeatDate($dt) {
    switch ($this->type) {
      case static::TYPE_DAY:
        $dt = mktime(0, 0, 0, date("n", $dt), date("j", $dt) + $this->every, date("Y", $dt)); 
        return $dt;
      case static::TYPE_WEEK:
        $dow = date("w", $dt);
        for ($i = 0; $i < 7; $i++) {
          $dt = mktime(0, 0, 0, date("n", $dt), date("j", $dt) + 1, date("Y", $dt));  // next day
          $dow = ($dow + 1) % 7;
          if ($dow == 0) 
            $dt = mktime(0, 0, 0, date("n", $dt), date("j", $dt) + 7 * ($this->every - 1), date("Y", $dt));  // skip (every-1) weeks
          if ($this->isOnDow($dow)) 
            return $dt;
        }
        return null;
      case static::TYPE_MONTH:
        if ($this->by == static::BY_DATE) {  // e.g. '15th'
          $nextm = (date("n", $dt) + $this->every - 1) % 12 + 1;  // next month expected  
          $dt = mktime(0, 0, 0, date("n", $dt) + $this->every, date("j", $dt), date("Y", $dt));  
          if (date("n", $dt) > $nextm)   
            $dt = mktime(0, 0, 0, date("n", $dt), 0, date("Y", $dt));  // went too far, use last day of month instead
          return $dt;
        } else {  // e.g. '4th wednesday'
          $week = ceil(date("j", $dt) / 7);
          $dow = date("w", $dt);
          if ($week == 5 || $this->by == static::BY_DAY_OF_LAST_WEEK) {
            $dt = mktime(0, 0, 0, date("n", $dt) + $this->every + 1, 0, date("Y", $dt));  // last day of next month
            for ($i = 0; $i < 7; $i++) {
              if (date("w", $dt) == $dow) {
                return $dt;
              }
              $dt = mktime(0, 0, 0, date("n", $dt), date("j", $dt) - 1, date("Y", $dt));  // prev day
            }
            return null;
          }
          $dt = mktime(0, 0, 0, date("n", $dt) + $this->every, 1, date("Y", $dt));  // next month
          $dt = mktime(0, 0, 0, date("n", $dt), (8 + ($dow - date("w", $dt))) % 7, date("Y", $dt));  // first dow of month
          for ($i = 0; $i <= 5; $i++) {
            if (ceil(date("j", $dt) / 7) == $week) {
              return $dt;
            }
            $dt = mktime(0, 0, 0, date("n", $dt), date("j", $dt) + 7, date("Y", $dt));  // skip week   
          }
        }
        return null;
      case static::TYPE_YEAR:
        $dt = mktime(0, 0, 0, date("n", $dt), date("j", $dt), date("Y", $dt) + $this->every);
        return $dt;
    }
  }
  public function /*bool[0..6]*/getDows() {
    $dows = array();
    for ($i = 0; $i < 7; $i++) 
      $dows[$i] = $this->on & static::$DOW_BITS[$i] ? 1 : 0;
    return $dows;
  }
}


