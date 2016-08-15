<?php
require_once "php/data/json/_util.php";

class JIcdCodes {

  public $codes;  // JIcdCode collection
  public $icd3count;  // # of unique icd3s
  public $more;  // true if not all records shown
  public $searchFor;  // regexp of search terms

  public function __construct($codes, $icd3count, $more, $searchFor) {
	  $this->codes = $codes;
	  $this->icd3count = $icd3count;
	  $this->more = $more;
	  $this->searchFor = $searchFor;
	}
	public function out() {
		return cb(
        qqa("codes", $this->codes) . C . 
        qqo("icd3ct", $this->icd3count) . C . 
        qqo("more", $this->more) . C .
        qq("searchFor", $this->searchFor) 
        );
	}
}
?>