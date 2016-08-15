<?php
require_once "php/data/json/_util.php";

class JIcdCode {

	public $code;
	public $desc;
	public $synonym;
	public $includes;
	public $excludes;
	public $notes;

	public function __construct($code, $desc, $synonym, $includes, $excludes, $notes) {
	  $this->code = utf8_encode($code);
	  $this->desc = utf8_encode($desc);
	  $this->synonym = utf8_encode($synonym);
	  $this->includes = utf8_encode($includes);
	  $this->excludes = utf8_encode($excludes);
	  $this->notes = utf8_encode($notes);
	}
	public function out() {
	  return jsonencode($this);
//		return cb(
//        qq("code", $this->code) . C . 
//        qq("desc", $this->desc) . C . 
//        qq("syn", $this->synonym) . C . 
//        qq("inc", $this->includes) . C . 
//        qq("exc", $this->excludes) . C . 
//        qq("notes", $this->notes) 
//        );
	}
}
?>