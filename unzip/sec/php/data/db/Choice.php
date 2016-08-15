<?php
require_once "php/data/db/_util.php";

class Choice {
	
	public $id;
	public $itemId;
	public $text;
	public $goto;
	public $sortOrder;
	
	public function __construct($id, $itemId, $text, $goto, $sortOrder) {
		$this->id = $id;
		$this->itemId = $itemId;
		$this->text = $text;
		$this->goto = $goto;
		$this->sortOrder = $sortOrder;
	}
}
?>
