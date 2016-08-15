<?php
require_once 'php/data/rec/sql/_SqlRec.php';
//
abstract class Hl7InboxRec extends SqlRec implements AutoEncrypt {
  //
  /*
  public $hl7InboxId;
  public $userGroupId;
  public $labId;
  public $msgType; 
  public $source;
  public $filename;
  public $dateReceived;
  public $patientName;
  public $cid;
  public $status;
  public $reconciledBy;
  public $data;
  public $headerTimestamp;
  public $placerOrder;
  public $pdf;
  */
  //
  const STATUS_UNRECONCILED = 0;
  const STATUS_RECONCILED = 9;
  const STATUS_CORRECTED = 10;
  //
  public function getSqlTable() {
    return 'hl7_inbox';
  }
  public function getEncryptedFids() {
    return array('dateReceived','patientName','data','headerTimestamp','pdf');
  }
}
