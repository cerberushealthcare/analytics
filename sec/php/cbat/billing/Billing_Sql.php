<?php
require_once 'php/data/rec/sql/_BillingRecs.php';
require_once 'php/data/rec/sql/_BillingResellerRecs.php';
require_once 'php/data/rec/sql/_UserRec.php';
//
class BillStatus extends BillStatusRec {
  //
  public $userId;
  public $planId;
  public $sourceId;
  public $active;
  public $balance;
  public $startDate;
  public $nextBillDate;
  public $failDate;
  public $failCount;
  public $lastStatus;
  public $inactiveCode;
  public /*BillPlan*/$Plan;
  public /*BillSource*/$Source;
  public /*User_Bill*/$User;
  //
  public function exceedsFailThreshold() {
    
  }
  public function calcAmt() {
    
  }
  public function saveAsBilled($amt) {
    
  }
  public function saveAsFailed() {

  }
  //
  static function fetchAllDue($billDate) {
    
  }
} 
class BillPlan extends BillPlanRec {
  //
  public $billPlanId;
  public $active;
  public $freqm;
  public $charge;
  public $upfront;
  public $dateCreated;
  public $desc;
}
class BillSource extends BillSourceRec {
  //
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
  public $email;
}
class BillHistory extends BillHistRec {
  //
  public $billHistId;
  public $userId;
  public $billDate;
  public $billPlanId;
  public $cycleStartDate;
  public $cycleBillDate;
  public $balance;
  public $amt;
  public $pdf;
  //
  public function savePdf(/*InvoicePdf*/$pdf) {
    $this->pdf = $pdf->output();
    return $this->save();
  }
  //
  static function record(/*BillStatus*/$status, $billDate, $amt) {
    $me = new static();
    $me->userId = $status->userId;
    $me->billDate = $billDate;
    $me->billPlanId = $status->Plan->billPlanId;
    $me->cycleStartDate = $status->cycleStartDate;
    $me->cycleBillDate = $status->cycleBillDate;
    $me->balance = $status->balance;
    $me->amt = $amt;
    return $me->save();
  }
}
//
class Referral extends ReferralRec {
  //
  public $userId;
  public $resellerId;
  public $amt;
  public $type;
  public $validFrom;
  public $validTo;
  public /*Reseller*/$Reseller;
  //
  public function calcAccrual($billedAmt) {
    switch ($this->type) {
      case static::TYPE_FLAT:
        return $this->amt;
      case static::TYPE_PERCENT:
        return $billedAmt * ($this->amt / 100);
    }
  }
  //
  static function fetchAllValid($userId) {
    
  } 
} 
class Reseller extends ResellerRec {
  //
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
}
class Accrual extends RefAccrualRec {
  //
  public $refAccrualId;
  public $resellerId;
  public $userId;
  public $billDate;
  public $amt;
  public $status;
  public $refPaymentId;
  //
  static function recordPending($resellerId, $userId, $billDate, $amt) {
    $me = new static();
    $me->resellerId = $resellerId;
    $me->userId = $userId;
    $me->billDate = $billDate;
    $me->amt = $amt;
    $me->status = static::STATUS_PENDING;
    return $me->save();
  }
}
class Payment extends RefPaymentRec {
  //
  public $refPaymentId;
  public $amt;
  public $datePaid;
  public $check;
  public $comment;
}
class User_Bill extends UserRec {
  //
  public $userId;
  public $uid;
  public $name;
  public $admin;
  public $subscription;
  public $active;
  public $userGroupId;
  public $userType;
  public $expiration;
  public $expireReason;
  //
  public function deactivate() {
    
  }
}