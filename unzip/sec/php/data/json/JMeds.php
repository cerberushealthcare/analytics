<?php
require_once "php/data/json/_util.php";

class JMeds {
	
	public $meds;

	public function __construct($meds) {
		$this->meds = $meds;
	}
	public function out() {
		return cb(qqa("meds", $this->meds));
	}
}
?>