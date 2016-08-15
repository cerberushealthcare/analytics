<?php
//
require_once 'php/data/rec/sql/_SqlRec.php';
//
/**
 * Procedure
 */
abstract class ProcRec extends SqlRec implements AutoEncrypt {
  //
  public $procId;
  public $userGroupId;
  public $clientId;
  public $date;  
  public $ipc;
  public $priority;
  public $location;
  public $providerId;
  public $addrFacility;
  public $recipient;
  public $scanIndexId; /*scanned procedure*/
  public $hl7InboxId; /*from reconciling lab msg*/
  public $trackItemId; /*links admin IPC to order item*/
  public $userId;
  public $comments;
  public $dateTo;
  //
  public function getSqlTable() {
    return 'procedures';
  }
  public function getEncryptedFids() {
    return array('date','comments','dateTo');
  }
  public function setDateCriteria(/*CriteriaValues|CriteriaValue|string*/$cv) {
    if ($cv) {
      $this->Hd_date = Hdata_ProcDate::join($cv);
    }
  }
  public function formatName() {
    return (isset($this->Ipc)) ? $this->Ipc->name : null;
  }
  public function formatDesc() {
    if (isset($this->Ipc)) {
      $s = Ipc::$CATS[$this->Ipc->cat];
      if (isset($this->Provider)) 
        $s .= " - " . Provider::formatProviderFacility($this);
      return $s;
    }
  }  
}
/**
 * Result
 */
abstract class ProcResultRec extends SqlRec implements AutoEncrypt {
  //
  public $procResultId;
  public $clientId;
  public $procId;
  public $seq;
  public $date;
  public $ipc;
  public $value;
  public $valueUnit;
  public $range;
  public $interpretCode;
  public $comments;
  //
  const IC_BETTER = 'B';
  const IC_DECREASED = 'D';
  const IC_INCREASED = 'U';
  const IC_WORSE = 'W';
  const IC_NORMAL = 'N';
  const IC_INTERMEDIATE = 'I';
  const IC_RESISTANT = 'R';
  const IC_SUSCEPTIBLE = 'S';
  const IC_VERY_SUSCEPTIBLE = 'VS';
  const IC_ABNORMAL = 'A';
  const IC_ABNORMAL_ALERT = 'AA';
  const IC_HIGH_ALERT = 'HH';
  const IC_LOW_ALERT = 'LL';
  const IC_LOW = 'L';
  const IC_HIGH = 'H';
  public static $INTERPRET_CODES = array(
    self::IC_BETTER => 'Better',
    self::IC_DECREASED => 'Decreased',
    self::IC_INCREASED => 'Increased',
    self::IC_WORSE => 'Worse',
    self::IC_NORMAL => 'Normal',
    self::IC_INTERMEDIATE => 'Intermediate',
    self::IC_RESISTANT => 'Resistant',
    self::IC_SUSCEPTIBLE => 'Susceptible',
    self::IC_VERY_SUSCEPTIBLE => 'Very Susceptible',
    self::IC_ABNORMAL => 'Abnormal',
    self::IC_ABNORMAL_ALERT => 'Abnormal Alert',
    self::IC_HIGH_ALERT => 'High Alert',
    self::IC_LOW_ALERT => 'Low Alert',
    self::IC_HIGH => 'High',
    self::IC_LOW => 'Low');
  //
  public function getSqlTable() {
    return 'proc_results';
  }
  public function getEncryptedFids() {
    return array('comments');
  }
}
