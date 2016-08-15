<?php
require_once "php/data/json/_util.php";

// Unavailability schedule event
class JSchedEvent {

  public $id;
  
  // Repeat parameters
  public $type;
  public $every;
  public $until;
  public $on;
  public $by;
  public $comment;
  
  // For reporting to user repeats exceeded max
  public $maxRepeatDate;  // the last date repeated
  
  // Helpers
  public $dowArray;
  
  const TYPE_NONE = 0;
  const TYPE_DAY = 1;
  const TYPE_WEEK = 2;
  const TYPE_MONTH = 3;
  const TYPE_YEAR = 4;
  
  const BY_DAY = 1;
  const BY_DATE = 2;
  const BY_DAY_OF_LAST_WEEK = 3;
  
  const ON_SUN = 0;
  const ON_MON = 1;
  const ON_TUE = 2;
  const ON_WED = 3;
  const ON_THU = 4;
  const ON_FRI = 5;
  const ON_SAT = 6;
  
  const BIT_SUN = 1;
  const BIT_MON = 2;
  const BIT_TUE = 4;
  const BIT_WED = 8;
  const BIT_THU = 16;
  const BIT_FRI = 32;
  const BIT_SAT = 64;
  
	public function __construct($id, $type, $every, $until, $on, $by, $comment) {
	  $this->id = $id;
	  $this->type = $type;
	  $this->every = $every;
	  $this->until = $until;
	  $this->on = $on;
	  $this->by = $by;
	  $this->comment = $comment;
	}
	
	public function getOnDowArray() {  // [0=Sun...6=Sat]  
	  $dow = array();
    $dow[JSchedEvent::ON_SUN] = $this->on & JSchedEvent::BIT_SUN ? 1 : 0;
    $dow[JSchedEvent::ON_MON] = $this->on & JSchedEvent::BIT_MON ? 1 : 0;
    $dow[JSchedEvent::ON_TUE] = $this->on & JSchedEvent::BIT_TUE ? 1 : 0;
    $dow[JSchedEvent::ON_WED] = $this->on & JSchedEvent::BIT_WED ? 1 : 0;
    $dow[JSchedEvent::ON_THU] = $this->on & JSchedEvent::BIT_THU ? 1 : 0;
    $dow[JSchedEvent::ON_FRI] = $this->on & JSchedEvent::BIT_FRI ? 1 : 0;
    $dow[JSchedEvent::ON_SAT] = $this->on & JSchedEvent::BIT_SAT ? 1 : 0;
    $this->dowArray = $dow;
    return $dow;
	}
	
	public function setFromOnDowArray($dow) {
	  $this->on = $dow[JSchedEvent::ON_SUN] * JSchedEvent::BIT_SUN + 
          $dow[JSchedEvent::ON_MON] * JSchedEvent::BIT_MON +
          $dow[JSchedEvent::ON_TUE] * JSchedEvent::BIT_TUE +
          $dow[JSchedEvent::ON_WED] * JSchedEvent::BIT_WED +
          $dow[JSchedEvent::ON_THU] * JSchedEvent::BIT_THU +
          $dow[JSchedEvent::ON_FRI] * JSchedEvent::BIT_FRI +
          $dow[JSchedEvent::ON_SAT] * JSchedEvent::BIT_SAT;
	}
	
	public function out() {
		return cb(qq("id", $this->id)
        . C . qq("type", $this->type) 
        . C . qq("every", $this->every) 
        . C . qq("until", $this->until) 
        . C . qq("on", $this->on) 
        . C . qq("by", $this->by) 
        . C . qq("comment", $this->comment) 
        . C . qq("max", $this->maxRepeatDate) 
        . C . qqai("dow", $this->getOnDowArray())
        );
	}
}
?>