<?php
require_once "php/data/json/_util.php";

class JParTemplate {

	public $id;
	public $uid;
	public $desc;
	public $noBreak;
	public $sortOrder;
	public $questionTemplates;
	public $inTable;
	
	public function __construct($id, $uid, $desc, $noBreak, $sortOrder, $questionTemplates, $inTable) {
		$this->id = $id;
		$this->uid = $uid;
		$this->desc = $desc;
		$this->noBreak = $noBreak;
		$this->sortOrder = $sortOrder;
		$this->questionTemplates = $questionTemplates;
		$this->inTable = $inTable;
	}
	function out() {
    $out = "";
    $out = nqq($out, "id", $this->id);
    $out = nqq($out, "uid", $this->uid);
    $out = nqq($out, "desc", $this->desc);
    $out = nqqo($out, "nb", $this->noBreak);
    $out = nqq($out, "sort", $this->sortOrder);
    $out = nqqa($out, "qts", $this->questionTemplates);
    $out = nqq($out, "in", $this->inTable);
    return cb($out);    
	}
}
?>