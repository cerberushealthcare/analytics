<?php
require_once 'php/data/rec/sql/_SqlRec.php';
//
abstract class ResellerRec extends SqlRec {
  /*
  public $resellerId;
  public $name;
  public $addr1;
  public $addr2;
  public $city;
  public $state;
  public $zip;
  public $phone;
  public $email;
  public $taxId;
  */
  //
  public function getSqlTable() {
    return 'resellers';
  } 
}
abstract class ReferralRec extends SqlRec {
  /*
  public $userId;
  public $resellerId;
  public $amt;
  public $type;
  public $validFrom;
  public $validTo;
  */
  //
  const TYPE_FLAT = 1;
  const TYPE_PERCENT = 2;
  //
  public function getSqlTable() {
    return 'referrals';
  }
} 
abstract class RefAccrualRec extends SqlRec {
  /*
  public $refAccrualId;
  public $resellerId;
  public $userId;
  public $billDate;
  public $amt;
  public $status;
  public $refPaymentId;
  */
  //
  const STATUS_PENDING = 0;
  const STATUS_PAID = 1;
  //
  public function getSqlTable() {
    return 'ref_accruals';
  }
}
abstract class RefPaymentRec extends SqlRec {
  /*
  public $refPaymentId;
  public $amt;
  public $datePaid;
  public $check;
  public $comment;
  */
  //
  public function getSqlTable() {
    return 'ref_payments';
  }
}
