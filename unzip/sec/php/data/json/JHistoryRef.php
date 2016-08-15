<?php
require_once "php/data/json/_util.php";

class JHistoryRef {

  public $type;
  public $id;
  public $date;
  
  // Derived
  public $timestamp;

  const TYPE_APPT = 0;
  const TYPE_SESSION = 1;
  const TYPE_MSG = 2;
  
	public function __construct($type, $id, $date, $sortDate) {  
    $this->type = $type;
    $this->id = $id;
    $this->date = $date;
    $this->timestamp = strtotime($sortDate);
	}
	
	public static function cmp($a, $b) {  // sort by timestamp (desc), type
	  if ($a->timestamp < $b->timestamp) {
	    return 1;
	  }
    if ($a->timestamp > $b->timestamp) {
      return -1;
    }
    if ($a->type < $b->type) {
      return -1;
    }
    if ($a->type > $b->type) {
      return 1;
    }
    return 0; 
	}
		
	public function out() {
    $out = "";
    $out = nqqo($out, "type", $this->type);
    $out = nqqo($out, "id", $this->id);
    $out = nqq($out, "date", formatInformalDate($this->date));
    return cb($out);    
	}
}
?>