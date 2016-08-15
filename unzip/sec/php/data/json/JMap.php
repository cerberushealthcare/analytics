<?php
require_once "php/data/json/_util.php";

class JMap {

	public $name;
	public $sections;
	public $startSection;

	public function __construct($name, $sections, $startSection) {
		$this->name = $name;
		$this->sections = $sections;
		$this->startSection = $startSection;
	}
	public function out() {
		return cb(qq("name", $this->name) . C . qqaa("sections", $this->sections) . C . qq("startSection", $this->startSection));
	}
}
?>