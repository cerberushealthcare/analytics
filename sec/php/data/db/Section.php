<?php
require_once "php/data/db/_util.php";

class Section {

	public $id;
	public $templateId;
	public $uid;
	public $name;
	public $desc;
	public $sortOrder;
	public $title;

	// Children
	public $groups;
	public $pars;

	public function __construct($id, $templateId, $uid, $name, $desc, $sortOrder, $title) {
		$this->id = $id;
		$this->templateId = $templateId;
		$this->uid = $uid;
		$this->name = $name;
		$this->desc = $desc;
		$this->sortOrder = $sortOrder;
		$this->title = $title;
	}
}
?>
