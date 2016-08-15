<?php
require_once 'php/data/rec/sql/_SqlRec.php';
//
abstract class VisitSummaryRec extends SqlRec implements CompositePk, AutoEncrypt {
  /*
  public $clientId;
  public $finalId;  // 0 for pending, otherwise Unix timestamp when produced 
  public $dos;
  public $sessionId;
  public $finalHead;
  public $finalBody;
  public $finalizedBy;
  public $diagnoses;
  public $iols;
  public $instructs;
  public $vitals;
  public $meds;
  */
  //
  const FINAL_ID_PENDING = 0;
  //
  public function getSqlTable() {
    return 'client_visitsums';
  }
  public function getPkFieldCount() {
    return 2;
  }
  public function getEncryptedFids() {
    return array('dos','finalHead','finalBody','diagnoses','iols','instructs','vitals','meds');
  }
  public function getDateProduced() {
    return date("d-M-Y, g:iA", $this->finalId);
  }
  public function getLabel() {
    return 'Visit Summary'; // ' . formatDate($this->dos);
  }
}

