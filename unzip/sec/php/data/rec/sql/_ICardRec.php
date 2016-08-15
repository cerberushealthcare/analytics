<?php
require_once 'php/data/rec/sql/_SqlRec.php';
require_once 'php/data/_BasicRec.php';
//
abstract class ICardRec extends SqlRec implements CompositePk, AutoEncrypt {
  /*
  public $clientId;
  public $seq;
  public $planName;
  public $subscriberName;
  public $nameOnCard;
  public $groupNo;
  public $subscriberNo;
  public $dateEffective;
  public $active;
  public $external;
  */
  const SEQ_PRIMARY = '1';
  const SEQ_SECONDARY = '2';
  static $SEQS = array(
    self::SEQ_PRIMARY => 'Primary',
    self::SEQ_SECONDARY => "Secondary");
  //
  public function getSqlTable() {
    return 'client_icards';
  }
  public function getEncryptedFids() {
    return array('subscriberName','nameOnCard','subscriberNo','external');
  }
  public function getPkFieldCount() {
    return 2;
  }
  public function setExternal(/*CR_InsuranceRec*/$rec) {
    $this->external = $rec->toJson();
  }
  public function toJsonObject(&$o) {
    if (isset($o->external))
      $o->external = jsondecode($o->external);
  }
}
/** External insurance info (Cerberus) */
class CR_InsuranceRec extends BasicRec {
  //
  public $PAYER;
  public $PAYER_ID;
  public $POLICY;
  public $EFF_DATE;
  public $END_DATE; 
  public $BILLING_SEQ;
  public $COPAY; 
  public $COINSURANCE; 
  public $REMAINING_DEDUCT; 
  public $ELIGIBILITY;
  public $INSERT_UPDATE_BY;
}