<?php
require_once 'php/data/rec/sql/_SqlRec.php';
require_once 'php/data/Military.php';
require_once 'php/data/rec/sql/_HdataRec.php';
//
/**
 * Sched Base Class
 * @author Warren Hornsby
 */
class SchedRec extends SqlRec implements AutoEncrypt {
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
  //
  public function getSqlTable() {
    return 'scheds';
  }
  public function getEncryptedFids() {
    return array('date','comment');
  }
  public function setDateCriteria(/*CriteriaValues|CriteriaValue|string*/$cv) {
    $this->Hd_date = Hdata_SchedDate::join($cv);
  }
  public function toJsonObject(&$o) {
    $o->_label = $this->getLabel();
  }
  public function isEvent() {
    return $this->clientId == 0;
  }
  //
  public function getLabel($type = null, $status = null) {
    require_once 'php/data/rec/sql/LookupScheduling.php';
    $time = new Military($this->timeStart);
    $label = formatDate($this->date) . " " . $time->formatted();
    if ($this->isEvent()) {
      $label .= ' - ';
      $time->increment($this->duration);
      $label .= $time->formatted();
      return $label;
    }
    if ($type == null && $status == null) {
      static $types;
      static $statuses;
      if ($types == null) {
        $types = LookupScheduling::getApptTypes();
        $statuses = LookupScheduling::getStatuses();
      }
      $type = LookupScheduling::getApptTypeName($types, $this->type);
      $status = LookupScheduling::getStatusName($statuses, $this->status);
    }
    if ($type)
      $label .= ": $type";
    if ($status)
      $label .= " ($status)";
    return $label;
  }
}
class SchedEventRec extends SqlRec implements AutoEncrypt {
  //
  public $schedEventId;
  public $rpType;
  public $rpEvery;
  public $rpUntil;
  public $rpOn;
  public $rpBy;
  public $comment;
  //
  const TYPE_NONE = 0;
  const TYPE_DAY = 1;
  const TYPE_WEEK = 2;
  const TYPE_MONTH = 3;
  const TYPE_YEAR = 4;
  //
  const BY_DAY = 1;
  const BY_DATE = 2;
  const BY_DAY_OF_LAST_WEEK = 3;
  //
  protected static $DOW_BITS = array(1, 2, 4, 8, 16, 32, 64);
  //
  public function getSqlTable() {
    return 'sched_events';
  }
  public function getEncryptedFids() {
    return array('comment');
  }
  public function setOnByDows(/*bool[]*/$dows) {
    $this->rpOn = 0;
    for ($i = 0; $i < 7; $i++) 
      $this->rpOn += $dows[$i] * static::$DOW_BITS[$i];
  }
  public function isOnDow($dow) {
    $dows = $this->getDows();
    return $dows[$dow];
  }
  public function /*string[]*/getRepeatDates($from, $max = 300) {
    logit_r($this, 'repeat');
    logit_r($from, 'repeat from');
    $dates = array();
    if ($this->rpType != static::TYPE_NONE) {
      $date = strtotime($from);
      $until = strtotime($this->rpUntil);
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
    switch ($this->rpType) {
      case static::TYPE_DAY:
        $dt = mktime(0, 0, 0, date("n", $dt), date("j", $dt) + $this->rpEvery, date("Y", $dt)); 
        return $dt;
      case static::TYPE_WEEK:
        $dow = date("w", $dt);
        for ($i = 0; $i < 7; $i++) {
          $dt = mktime(0, 0, 0, date("n", $dt), date("j", $dt) + 1, date("Y", $dt));  // next day
          $dow = ($dow + 1) % 7;
          if ($dow == 0) 
            $dt = mktime(0, 0, 0, date("n", $dt), date("j", $dt) + 7 * ($this->rpEvery - 1), date("Y", $dt));  // skip (every-1) weeks
          if ($this->isOnDow($dow)) 
            return $dt;
        }
        return null;
      case static::TYPE_MONTH:
        if ($this->rpBy == static::BY_DATE) {  // e.g. '15th'
          $nextm = (date("n", $dt) + $this->rpEvery - 1) % 12 + 1;  // next month expected  
          $dt = mktime(0, 0, 0, date("n", $dt) + $this->rpEvery, date("j", $dt), date("Y", $dt));  
          if (date("n", $dt) > $nextm)   
            $dt = mktime(0, 0, 0, date("n", $dt), 0, date("Y", $dt));  // went too far, use last day of month instead
          return $dt;
        } else {  // e.g. '4th wednesday'
          $week = ceil(date("j", $dt) / 7);
          $dow = date("w", $dt);
          if ($week == 5 || $this->rpBy == static::BY_DAY_OF_LAST_WEEK) {
            $dt = mktime(0, 0, 0, date("n", $dt) + $this->rpEvery + 1, 0, date("Y", $dt));  // last day of next month
            for ($i = 0; $i < 7; $i++) {
              if (date("w", $dt) == $dow) {
                return $dt;
              }
              $dt = mktime(0, 0, 0, date("n", $dt), date("j", $dt) - 1, date("Y", $dt));  // prev day
            }
            return null;
          }
          $dt = mktime(0, 0, 0, date("n", $dt) + $this->rpEvery, 1, date("Y", $dt));  // next month
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
        $dt = mktime(0, 0, 0, date("n", $dt), date("j", $dt), date("Y", $dt) + $this->rpEvery);
        return $dt;
    }
  }
  protected function /*bool[0..6]*/getDows() {
    $dows = array();
    for ($i = 0; $i < 7; $i++) 
      $dows[$i] = $this->rpOn & static::$DOW_BITS[$i] ? 1 : 0;
    return $dows;
  }
}

