<?php
require_once "php/data/db/_util.php";
require_once "php/data/json/JQuestion.php";

class Question {
	
	public $id;
	public $parId;
	public $uid;
	public $desc;
	public $bt;
	public $at;
	public $btms;
	public $atms;
	public $btmu;
	public $atmu;
	public $listType;
	public $break;
	public $test;
	public $actions;
	public $defix;
	public $mix;
	public $mcap;
	public $mix2;
	public $mcap2;
	public $img;
	public $sortOrder;
	public $syncId;
	public $outData;
	public $inActions;
	public $type;
	public $dsync;
  public $billing;
	
	// Children
	public $options;
	
	// Uncle?
	public $inTable;

	public function __construct($id, $parId, $uid, $desc, $bt, $at, $btms, $atms, $btmu, $atmu, $listType, $break, $test, $actions, $defix, $mix, $mcap, $mix2, $mcap2, $img, $sortOrder, $sync, $outData, $inActions, $dsync, $billing) {
		$this->id = $id;
		$this->parId = $parId;
		$this->uid = $uid;
    $this->type = JQuestion::setType($uid);
		$this->desc = $desc;
		$this->bt = $bt;
		$this->at = $at;
		$this->btms = $btms;
		$this->atms = $atms;
		$this->btmu = $btmu;
		$this->atmu = $atmu;
		$this->listType = $listType;
		$this->break = $break;
		$this->test = $test;
		$this->actions = $actions;
		$this->defix = $defix;
		$this->mix = $mix;
		$this->mcap = $mcap;
		$this->mix2 = $mix2;
		$this->mcap2 = $mcap2;
		$this->img = $img;
		$this->sortOrder = $sortOrder;
		$this->sync = $sync;
		$this->outData = $outData;
		$this->inActions = $inActions;
		$this->dsync = $dsync;
		$this->billing = $billing;
	}
}
?>
