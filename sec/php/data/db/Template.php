<?php
require_once "php/data/db/_util.php";

class Template {

	public $id;
	public $userId;
	public $uid;
	public $name;
	public $public;
	public $dateCreated;
	public $dateUpdated;
	public $desc;
	public $title;

	// Children
	public $sections;  

	public function __construct($id, $userId, $uid, $name, $public, $dateCreated, $dateUpdated, $desc, $title) {
		$this->id = $id;
		$this->userId = $userId;
		$this->uid = $uid;
		$this->name = $name;
		$this->public = toBool($public);
		$this->dateCreated = $dateCreated;
		$this->dateUpdated = $dateUpdated;
		$this->desc = $desc;
		$this->title = $title; 
	}
	
	public function xml() {
	  //$xml = "<template id=" . q($this->uid);
	}
}
?>
