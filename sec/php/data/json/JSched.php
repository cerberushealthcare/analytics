<?php
require_once "php/data/json/_util.php";
require_once "php/data/Military.php";

class JSched extends Sched {
  public $_by;
 
	public function out() {
	  $m = new Military($this->timeStart);
	  $dHr = Military::div($this->duration, 60);
	  $dMin = $this->duration - $dHr * 60;
		return cb(qq("id", $this->id) 
		    . C . qq("userId", $this->userId) 
		    . C . qq("userGroupId", $this->userGroupId) 
        . C . qq("clientId", $this->clientId) 
		    . C . qq("date", $this->date) 
		    . C . qq("formatDate", $this->formatDate())
		    . C . qq("dow", $this->formatDate())
        . C . qq("timeStart", $this->timeStart)
        . C . qq("formatTime", $m->formatted())
        . C . qq("timeStartHr", $m->standardHour)
        . C . qq("timeStartMin", $m->formattedMin())
        . C . qq("timeStartAmPm", $m->amPm()) 
        . C . qq("duration", $this->duration) 
        . C . qq("durationHr", $dHr) 
        . C . qq("durationMin", $dMin) 
        . C . qqo("closed", $this->closed) 
        . C . qq("status", $this->status) 
        . C . qq("comment", $this->comment) 
        . C . qq("type", $this->type) 
        . C . qqo("client", jsonencode($this->client))
        . C . qqa("sessions", $this->sessions)
        . C . qqj("event", $this->schedEvent)
        . C . qq("_by", $this->_by)
        . C . qq("_date", formatLongDate($this->date))
        );
	}
	
	public static function constructFromJson($json) {
	  $a = jsondecode($json);
	  logit_r($a, 'constructfromjson');
	  $eventId = get($a, 'schedEventId');
	  $schedEvent = null;
	  if (isset($a->event)) {
	    $eventId = $a->event->id;
	    $schedEvent = new JSchedEvent($a->event->id, $a->event->type, $a->event->every, $a->event->until, null, $a->event->by, convert_line_breaks($a->event->comment,'<br>'));
	    $schedEvent->setFromOnDowArray($a->event->dow);
	  }
	  $j = new JSched(
	     $a->id, 
       $a->userId, 
       $a->userGroupId, 
       $a->clientId, 
       $a->date, 
       $a->timeStart, 
       $a->duration, 
       $a->closed, 
       $a->status,
       convert_line_breaks($a->comment, '<br>'),
       $a->type,
       $eventId);
    $j->duration = $a->durationHr * 60 + $a->durationMin;
    $j->schedEvent = $schedEvent;
    logit_r($j, 'j!');       
    return $j;
	}
	
	private function formatDate() {
	  return date("F j", strtotime($this->date));
	}
	private function formatDow() {
	  return date("w", strtotime($this->date)); 
	}
}
?>