<?php
require_once "php/data/json/_util.php";

class JWorkflow {

  public $appt;  // JClientEvent of current appt (for workflow)
  public $docs;  // JClientEvent[3] of active sessions (for workflow)
  public $vital;  // JDataVital of today's vitals (for workflow)
    
  public function out() {
    $out = "";
    $out = nqqo($out, "appt", jsonencode($this->appt));
    $out = nqqo($out, "vital", jsonencode($this->vital));
    $out = nqqa($out, "docs", $this->docs);
    return cb($out);    
  }
}
?>