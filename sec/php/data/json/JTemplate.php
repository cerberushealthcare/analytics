<?php
require_once "php/data/json/_util.php";

class JTemplate {

	public $id;
	public $uid;
	public $name;
	public $title;
	public $sectionTemplates;
	public $autoParInfos;
  public $custom;  // from lookup table TEMPLATE_CUSTOM

	public function __construct($id, $uid, $name, $title, $sectionTemplates, $autoParInfos) {
		$this->id = $id;
		$this->uid = $uid;
		$this->name = $name;
		$this->title = $title;
		$this->sectionTemplates = $sectionTemplates;
		$this->autoParInfos = $autoParInfos;
	}
	public function out() {
		return cb(qq("id", $this->id)
        . C . qq("uid", $this->uid) 
        . C . qq("name", $this->name) 
        . C . qq("title", $this->title) 
    		. C . qqaa("sections", $this->sectionTemplates) 
    		. C . qqa("autos", $this->autoParInfos)
    		. C . qqo("custom", $this->custom)
    		);
	}
}
?>