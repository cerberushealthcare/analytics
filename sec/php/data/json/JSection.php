<?php
require_once "php/data/json/_util.php";

class JSection {

  public $id;
	public $uid;
	public $name;
	public $desc;
	public $pars;
	public $sortOrder;
	
	public function __construct($id, $uid, $name, $desc, $pars, $sortOrder) {
	  $this->id = $id;
		$this->uid = $uid;
		$this->name = $name;
		$this->desc = $desc;
		$this->pars = $pars;
		$this->sortOrder = $sortOrder;
	}
	public function out() {
		return cb(
		    qq("id", $this->id) . C . 
		    qq("uid", $this->uid) . C . 
		    qq("name", $this->name) . C . 
		    qq("desc", $this->desc) . C . 
		    qqaa("pars", $this->pars) . C . 
		    qqo("parct", count($this->pars))
		    );
	}
	public static function cmp($a, $b) {
	  return ($a->sortOrder < $b->sortOrder) ? -1 : 1;
	}
}
?>