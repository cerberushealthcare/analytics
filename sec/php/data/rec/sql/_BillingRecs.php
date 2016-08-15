<?php
require_once 'php/data/rec/sql/_SqlRec.php';
//
abstract class BillStatusRec extends SqlRec {
  /*
  public $userId;
  public $planId;
  public $sourceId;
  public $active;
  public $balance;
  public $cycleStartDate;
  public $cycleBillDate;
  public $failDate;
  public $failCount;
  public $lastStatus;
  public $inactiveCode;
  */
  //
  public function getSqlTable() {
    return 'bill_status';
  } 
  public function isSharingSource() {
    return $this->sourceId != $this->userId;
  }
} 
abstract class BillPlanRec extends SqlRec implements ReadOnly {
  /*
  public $billPlanId;
  public $active;
  public $freqm;
  public $charge;
  public $upfront;
  public $dateCreated;
  public $desc;
  */
  //
  public function getSqlTable() {
    return 'bill_plans';
  }
}
abstract class BillSourceRec extends SqlRec implements AutoEncrypt {
  /*
  public $userId;
  public $type;
  public $name;
  public $addr1;
  public $addr2;
  public $city;
  public $state;
  public $zip;
  public $country;
  public $phone;
  public $cardType;
  public $cardNumber;
  public $expMonth;
  public $expYear;
  */
  //
  const TYPE_CARD = 1;
  const TYPE_INVOICE = 2;
  //
  const CARD_TYPE_MC = 1;
  const CARD_TYPE_VISA = 2;
  const CARD_TYPE_AMEX = 3;
  static $CARD_TYPES = array(
    self::CARD_TYPE_MC => 'MC',
    self::CARD_TYPE_VISA => 'VISA',
    self::CARD_TYPE_AMEX => 'AMEX');
  //
  static $EXP_MONTHS = array(
    '1' => '01','2' => '02','3' => '03','4' => '04','5' => '05','6' => '06','7' => '07','8' => '08','9' => '09','10' => '10','11' => '11','12' => '12');
  //
  public function getSqlTable() {
    return 'bill_sources';
  }
  public function getEncryptedFids() {
    return array('name','addr1','addr2','city','zip','phone','cardNumber','email');
  }
  public function isCard() {
    return $this->type == static::TYPE_CARD;
  }
}
abstract class BillHistoryRec extends SqlRec {
  /*
  public $billHistId;
  public $userId;
  public $billDate;
  public $billPlanId;
  public $cycleStartDate;
  public $cycleBillDate;
  public $balance;
  public $amt;
  public $pdf;
  */
  //
  public function getSqlTable() {
    return 'bill_history';
  }
}
