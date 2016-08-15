<?php
require_once 'php/data/rec/sql/_SqlRec.php';
//
abstract class AuditMruRec extends SqlRec implements NoAudit, AutoEncrypt {
  /*
  public $clientId;
  public $userId;
  public $date;
  public $action;
  public $recName;
  public $recId;
  public $label;
  */
  //
  public function getSqlTable() {
    return 'audit_mrus';
  }
  public function getEncryptedFids() {
    return array('label');
  }
}
