<?php
require_once "php/data/json/_util.php";

class JAction {
	
	public $cond;
	public $action;
	
	public function __construct($cond, $action) {
		$this->cond = $cond;
		$this->action = $action;
	}
	public function out() {
		return cb(qq("cond", $this->cond) . C . qq("action", $this->action));
	}
}
?>