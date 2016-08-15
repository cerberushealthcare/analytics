<?php
require_once "php/data/db/_util.php";

class Group {

	public $id;
	public $sectionId;
	public $uid;
	public $major;
	public $sortOrder;
	public $desc;

	// Children
	public $pars;

	public function __construct($id, $sectionId, $uid, $major, $sortOrder, $desc) {
		$this->id = $id;
		$this->sectionId = $sectionId;
		$this->uid = $uid;
		$this->major = toBool($major);
		$this->sortOrder = $sortOrder;
		$this->desc = $desc;
	}
}
?>
