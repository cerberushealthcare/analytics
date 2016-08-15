<?php
require_once "php/data/json/_util.php";

class JPar {

	public $id;
	public $uid;
	public $desc;
	public $major;
	public $auto = false;  // auto-include, defaults to false, may be overriden by lookup setting
	
	// Helpers
	public $defMajor;  // default, i.e. what setting was at object creating (prior to any override)
  private $cloneable;  // true if uid starts "+" 
	
	public function __construct($id, $uid, $desc, $major) {
		$this->id = $id;
		$this->uid = $uid;
		$this->desc = $desc;
		$this->major = $major;
		$this->defMajor = $major;
    if (substr($uid, 0, 1) == "+") {
      $this->cloneable = true;
    }
	}
 	public function out() {
 	  $out = "";
 	  $out = nqq($out, "id", $this->id);
 	  $out = nqq($out, "uid", $this->uid);
 	  $out = nqq($out, "desc", $this->desc);
 	  if ($this->major) {
 	    $out = nqqo($out, "major", $this->major);
 	  }
 	  if ($this->defMajor) {
   	  $out = nqqo($out, "defMajor", $this->defMajor);
 	  }
 	  $out = nqqo($out, "auto", $this->auto);
    $out = nqqo($out, "cloneable", $this->cloneable);
 	  return cb($out);
	}
	public static function cmp($a, $b) {
	  return ($a->desc < $b->desc) ? -1 : 1;
	}
}
?>