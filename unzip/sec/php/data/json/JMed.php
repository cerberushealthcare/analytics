<?php
require_once "php/data/json/_util.php";

class JMed {

	public $name;
	public $form;
	public $dosage;

	public function __construct($name, $form, $dosage) {
		$this->name = $name;
		$this->form = $form;
		$this->dosage = $dosage;
	}
	public function out() {
		return cb(qq("name", $this->name) . C . qq("form", $this->form) . C . qq("dosage", $this->dosage));
	}
}
?>