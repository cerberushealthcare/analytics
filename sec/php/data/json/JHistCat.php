<?php
require_once "php/data/json/_util.php";

/**
 * DEPRECATED
 */
class JHistCat {

  public $cat;  // "pmhx"
  public $fields;  // [field=>label,...]
  public $recs;  // JDataSyncProcGroup  client values
  public $procQuestion;  // JQuestion defining procs  
  //public $procs;  // [proc,..]  all procs for cat 
  
  public function __construct($cat, $recs, $procQuestion = null) {
    $this->cat = $cat;
    $this->fields = JDataSyncProcGroup::$FIELD_DEFS[$cat];
    $this->recs = $recs;
    $this->procQuestion = $procQuestion;
  }
  
  /*
   * Returns {
   *   "cat":cat,
   *   "recs":{proc:{dsync:value,..},..},
   *   "pq":JQuestion
   *   }
   */
	public function out() {
	  $out = "";
    $out = nqq($out, "cat", $this->cat);
    $out = nqqo($out, "fields", jsonencode($this->fields));
	  $out = nqqj($out, "recs", $this->recs);
	  $out = nqqj($out, "pq", $this->procQuestion);
//    $out = nqqk($out, "procs", jsonencode($this->procs));
    return cb($out);    
	}
}
?>