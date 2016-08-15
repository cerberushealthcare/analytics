<?php
require_once "php/data/json/_util.php";

class JParInfo {

	public $sectionId;
	public $suid;
	public $parTemplate;
	public $questions;
	public $autoInjects;

	public $cacheable;  // true if can be saved to PARJSON table (e.g. false for IMPR whose ICD's can be defaulted) 
	
	public function __construct($sectionId, $suid, $parTemplate, $questions, $autoInjects) {
		$this->sectionId = $sectionId;
		$this->suid = $suid;
		$this->parTemplate = $parTemplate;
		$this->questions = $questions;
		$this->autoInjects = $autoInjects;
	}
	public function out() {
		return cb(qq("sid", $this->sectionId) 
		    . C . qq("suid", $this->suid) 
		    . C . qqj("par", $this->parTemplate) 
		    . C . qqa("questions", $this->questions)
		    . C . qqas("autoInj", $this->autoInjects)); 
	}
}
?>