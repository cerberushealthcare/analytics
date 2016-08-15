<?php
require_once "php/data/json/_util.php";
//
class JOption {
  //
	public $uid;
	public $desc;
	public $text;
	public $shape;
	public $coords;
	public $sync;
	public $cpt;
	public $trackCat;
	//  
  public $icd;  // for defaulting ICD
	public $icdDesc;
  public $icd10;  // for defaulting ICD
	public $icd10Desc;
	public $icd10Incomplete;
	//
	public function __construct($uid, $desc, $text, $shape, $coords, $sync, $cpt, $trackCat) {
		$this->uid = $uid;
		$this->desc = $desc;
		$this->text = $text;
		$this->shape = $shape;
		$this->coords = $coords;
		$this->sync = $sync;
		$this->cpt = $cpt;
		$this->trackCat = $trackCat;
	}
  public function getText() {
    return ($this->text != null) ? $this->text : $this->desc;
  }
  public function isTrackable() {
    return ($this->trackCat != null);
  }
	public function out() {
	  $out = "";
	  $out = nqq($out, "uid", $this->uid);
    $out = nqq($out, "text", $this->text);
    $out = nqq($out, "coords", $this->coords);
    $out = nqq($out, "sync", $this->sync);
    $out = nqq($out, "cpt", $this->cpt);
    $out = nqq($out, "tcat", $this->trackCat);
    $out = nqq($out, "icd", $this->icd);
    $out = nqq($out, "icdDesc", $this->icdDesc);
    $out = nqq($out, "icd10", $this->icd10);
    $out = nqq($out, "icd10Desc", $this->icd10Desc);
    $out = nqq($out, "icd10Incomplete", $this->icd10Incomplete);
    return cb($out);    
	}
	//
	// Statics
	public static function buildOther() {
	  return new JOption("other", "other", "other", "", "", "", "", "");
	}
}
?>