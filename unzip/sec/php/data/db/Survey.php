<?php
require_once "php/data/db/_util.php";

class Survey {
	
	// Database fields 	
	public $id;
	public $name;
	public $desc;
	public $password;
	public $active;
	public $userId;
	
	// Children
	public $items;  // Associated by ID
	
	// UI-helper fields
	public $page;
	
	public function __construct($id, $name, $desc, $password, $active, $userId) {
		$this->id = $id;
		$this->name = $name;
		$this->desc = $desc;
		$this->password = $password;
		$this->active = toBool($active); 
		$this->userId = $userId;
	}
}
?>
