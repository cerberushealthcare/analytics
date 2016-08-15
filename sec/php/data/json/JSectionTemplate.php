<?php
require_once "php/data/json/_util.php";

class JSectionTemplate {

	public $id;
	public $uid;
	public $title;
	public $hide;
	public $noSyncIn;
	public $sortOrder;

	public function __construct($id, $uid, $title, $hide, $noSyncIn, $sortOrder) {
		$this->id = $id;
		$this->uid = $uid;
		$this->title = $title;
		$this->hide = $hide;
		$this->noSyncIn = $noSyncIn;
    $this->sortOrder = $sortOrder;
	}
	public function out() {
		// Reserve pars property for associated list built by frontend as paragraphs added to section
		$out = "";
		$out = nqq($out, "id", $this->id);
		$out = nqq($out, "uid", $this->uid);
		$out = nqq($out, "title", $this->title);
		$out = nqqo($out, "pars", "[]");
		$out = nqqo($out, "hide", $this->hide);
		$out = nqqo($out, "noSyncIn", $this->noSyncIn);
		return cb($out);
	}
  public static function cmp($a, $b) {
    return ($a->sortOrder < $b->sortOrder) ? -1 : 1;
  }
}
?>