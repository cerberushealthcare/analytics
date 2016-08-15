<?php
require_once 'php/data/rec/sql/_SqlRec.php';
//
abstract class Hl7ProcXrefRec extends SqlRec {
  //
  /*
  public $hpxId;
  public $clientId;
  public $labId;
  public $hl7InboxId; 
  public $orderNo;
  public $ipc;
  public $procId;
  public $supercededBy;
  */
  //
  public function getSqlTable() {
    return 'hl7_proc_xref';
  }
}
