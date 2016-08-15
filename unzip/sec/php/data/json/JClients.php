<?php
require_once "php/data/json/_util.php";

class JClients {
		
	public $clients;

	public function __construct($clients) {
		$this->clients = $clients;
	}
	public function out() {
		return cb(qqa("clients", $this->clients));
	}
}
?>