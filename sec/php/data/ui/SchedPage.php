<?php
class SchedPage {
  
  public $profile;  // SchedProfile
  
  // Collections
  public $days;  // SchedDay, to allow week-view; will be singleton for day-view (or if showing multiple users)

  public function __construct($profile) {
    $this->profile = $profile;
    $this->days = array(); 
  }
}

class SchedDay {
  
  public $date;  // e.g. "2008-09-23"
  public $today;  // boolean, true if date is today's
  
  // Collections
  public $users;  // SchedUser[], will contain singleton for single-provider view, multiple for practice view

  public function __construct($date) {
    $this->date = $date;
    $this->today = (date("Y-m-d") == $date);
    $this->users = array(); 
  }
}

class SchedUser {
  
  public $userId;
  public $uid;
  public $name;
 
  // Collections
  public $scheds;  // one Sched for each userId in SchedDay
  
  public function __construct($userId, $uid, $name) {
    $this->userId = $userId;
    $this->uid = $uid;
    $this->name = $name;
  }
}
?>