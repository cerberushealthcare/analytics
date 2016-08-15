<?php
require_once "php/data/db/_util.php";

class Par {

	public $id;
	public $sectionId;
	public $uid;
	public $major;
	public $sortOrder;
	public $desc;
	public $noBreak;
	public $injectOnly;
	public $published;
	public $effective;
  public $inTable;
  public $inType;
  public $inCond;
  public $current;
  public $dev;

  const TYPE_AUTO_ADD = 1;
  const TYPE_AUTO_ADD_IF_COND_FIELDS_NOT_NULL = 2;
  const TYPE_ON_DEMAND = 3;

	// Children
	public $questions;

	public function __construct($id, $sectionId, $uid, $major, $sortOrder, $desc, $noBreak, $injectOnly, $effective, $inTable, $inType, $inCond, $dev) {
		$this->id = $id;
		$this->sectionId = $sectionId;
		$this->uid = $uid;
		$this->major = toBool($major);
		$this->sortOrder = $sortOrder;
		$this->desc = $desc;
		$this->noBreak = toBool($noBreak);
		$this->injectOnly = toBool($injectOnly);
		$this->effective = $effective;
    $this->inTable = $inTable;
    $this->inType = $inType;
	  $this->inCond = $inCond;
	  $this->dev = $dev;
	}
}
?>
