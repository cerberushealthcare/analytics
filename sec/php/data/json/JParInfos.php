<?php
require_once "php/data/json/_util.php";

class JParInfos {
		
	public $parInfos;

	public function __construct($parInfos) {
		$this->parInfos = $parInfos;
	}
	public function out() {
		return cb(qqa("parInfos", $this->parInfos));
	}
}
?>