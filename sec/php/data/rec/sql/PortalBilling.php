<?php
/**
 * Portal Billing DAO
 * @author Warren Hornsby
 */
class PortalBilling {
  //
  static function activate($ui) {
    $sess = PortalSession::get();
    $Info = PortalBillInfo::fromUi($ui, $sess->portalUserId, $sess->userGroupId);
    static::bill($Info);
  }
  static function billAll($date) {
    $Infos = PortalBillInfo::fetchAllDue($date);
    foreach ($Infos as $Info) 
      static::bill($Info);
  }
  static function payAll($date) {
    $Payees = Payee::fetchAll();
    foreach ($Payees as $Payee) {
      $Payments = Payment::fetchAllPending($Payee->userGroupId, $date);
      $PaymentHist = PaymentHist::from($Payments, $Payee->userGroupId);
      if ($PaymentHist) {
        $PaymentHist->save();
        Payment::setAllProcessed($Payments, $PaymentHist);
        Payment::saveAll($Payments);
      }
    }
  }
  //
  static function bill($Info) {
    $Info->setBillDates();
    // linkpoint
    $Info->save();
    $Hist = PortalBillHist::from($Info);
    $Hist->save();
    $Payments = Payment::from($Hist, $Info);
    $Payments::saveAll($Payments);
  }
}
//
class PortalBillInfo extends SqlRec {
  public $portalUserId;
  public $userGroupId;
  public $name;
  public $address1;
  public $address2;
  public $city;
  public $state;
  public $zip;
  public $country;
  public $cardNumber;
  public $expMonth;
  public $expYear;
  public $nextBillDate;
  public $balance;
  public $phoneNum;
  public $phoneExt;
  public $cardType;
  public $startBillDate;
  public $portalBillCode;
  public $lastBillStatus;
  public $active;
  public $dateCancelled;
  public /*PortalBillCode*/ $PortalBillCode;
  //
  public function getSqlTable() {
    return 'portal_billinfo';
  }
  public function setBillDates() {
    $this->startBillDate = $this->nextBillDate ?: nowNoQuotes();
    $this->nextBillDate = $this->PortalBillCode->nextDate($this->startBillDate);
  }
  //
  static function fetch($portalUserId) {
    $c = static::asCriteria();
    $c->portalUserId = $portalUserId;
    return static::fetchOneBy($c);
  }
  /**
   * @param string $billDate 'yyyy-mm-dd'
   * @return array(static,..)
   */
  static function fetchAllDue($billDate) {
    $c = new static();
    $c->startBillDate = CriteriaValue::lessThanOrEquals($billDate);
    return static::fetchAllBy($c);
  }
  /**
   * @param stdClass $ui
   * @param int @portalUserId;
   * @param int $ugid
   * $return static
   */
  static function fromUi($ui, $portalUserId, $ugid) {
    $me = new static();
    $me->portalUserId = $portalUserId;
    $me->userGroupId = $ugid;
    $me->name = $ui->name;
    $me->address1 = $ui->address1;
    $me->address2 = $ui->address2;
    $me->city = $ui->city;
    $me->state = $ui->state;
    $me->zip = $ui->zip;
    $me->country = $ui->country;
    $me->cardNumber = $ui->cardNumber;
    $me->expMonth = $ui->expMonth;
    $me->expYear = $ui->expYear;
    $me->phoneNum = $ui->phoneNum;
    $me->phoneExt = $ui->phoneExt;
    $me->cardType = $ui->cardType;
    $me->portalBillCode = $ui->portalBillCode;
    return $me;
  }
  //
  protected static function asCriteria() {
    $c = new static();
    $c->PortalBillCode = new PortalBillCode();
    return $c;
  }
}
class PortalBillCode extends SqlRec {
  public $portalBillCode;
  public $desc;
  public $newSignups;
  public $cycleLength;
  public $charge;
  public $payback;
  public $dateCreated;
  //
  public function getSqlTable() {
    return 'portal_bill_codes';
  } 
  public function nextDate($date) {
    return futureDate(0, $this->cycleLength, 0, $date);
  }
  public function isMonthly() {
    return $this->cycleLength == 1;
  }
  public function getPaymentCount() {
    return intval(($this->cycleLength + 2) / 3);
  }
  public function getPaymentAmount() {
    return $payback / $this->getPaymentCount();
  }
}
class PortalBillHist extends SqlRec {
  public $portalBillhistId;
  public $portalUserId;
  public $startDate;
  public $endDate;
  public $billedAmt;
  public $paybackAmt;
  //
  public function getSqlTable() {
    return 'portal_billhist';
  }
  //
  /**
   * @param PortalBillInfo $BillInfo
   * @return static
   */
  static function from($BillInfo) {  
    $me = new static();
    $me->portalUserId = $BillInfo->portalUserId;
    $me->startDate = $BillInfo->startBillDate;
    $me->endDate = $BillInfo->nextBillDate;
    $me->billedAmt = $BillInfo->BillCode->charge;
    $me->paybackAmt = $BillInfo->BillCode->payback;
    return $me;
  }
}
class Payment extends SqlRec {
  public $paymentId;
  public $userGroupId;
  public $portalBillhistId;
  public $payDate;
  public $payAmt;
  public $payStatus;
  public $paymentHistId;
  //
  const PAY_STATUS_PENDING = 0;
  const PAY_STATUS_PROCESSED = 1;
  //
  public function getSqlTable() {
    return 'payments';
  }
  public function setProcessed($histId) {
    $this->status = static::PAY_STATUS_PROCESSED;
    $this->paymentHistId = $histId;
  }
  //
  /**
   * @param int $ugid
   * @param string $date 'yyyy-mm-dd'
   */
  static function fetchAllPending($ugid, $date) {
    $c = new static();
    $c->userGroupId = $ugid;
    $c->payDate = CriteriaValue::lessThanOrEquals($date);
    $c->payStatus = static::PAY_STATUS_PENDING;
    return static::fetchAllBy($c);
  }
  /**
   * @param static[] $Payments
   * @param PaymentHist $Hist
   * @return static[]
   */
  static function setAllProcessed($Payments, $Hist) {
    foreach ($Payments as &$me) 
      $me->setProcessed($Hist->paymentHistId);
    return $Payments;
  }
  /**
   * @param static[] $Payments
   * @return float
   */
  static function totalAmount($Payments) {
    $amt = 0.;
    foreach ($Payments as $me)
      $amt += $me->amt;
    return $amt;
  }
  /**
   * @param PortalBillHist $BillHist
   * @param PortalBillInfo $BillInfo
   * @return array(static,..)
   */
  static function from($BillHist, $BillInfo) {
    $mes = array();
    $n = $BillInfo->BillCode->getPaymentCount();
    $amt = $BillInfo->BillCode->getPaymentAmount();
    $date = $BillInfo->startBillDate;
    for ($i = 0; $i < n; $i++) {
      $mes[] = static::asPending($BillInfo->userGroupId, $BillHist->portalBillhistId, $date, $amt);
      $date = futureDate(0, 3, 0, $date);
    }
    return $mes;
  }
  static function asPending($ugid, $histId, $date, $amt) {
    $me = new static();
    $me->userGroupId = $ugid;
    $me->portalBillhistId = $histId;
    $me->payDate = $date;
    $me->payAmt = $amt;
    $me->payStatus = static::PAY_STATUS_PENDING;
    return $me;
  }
}
class PaymentHist extends SqlRec {
  public $paymentHistId;
  public $userGroupId;
  public $amt;
  public $dateCreated;
  public $status;
  public $dateSent;
  public $check;
  //
  const STATUS_PENDING = 0;
  const STATUS_SENT = 1;
  //
  public function getSqlTable() {
    return 'payments_hist';
  }
  //
  /**
   * @param Payment[] $Payments
   * @param int $ugid
   * @return static if total exceeds minAmt threshhold, else null
   */
  static function from($Payments, $ugid, $minAmt = 5) {
    $amt = Payment::totalAmount($Payments);
    if ($amt >= $minAmt) 
      return static::asPending($ugid, $amt);
  }
  static function asPending($ugid, $amt) {
    $me = new static();
    $me->userGroupId = $ugid;
    $me->amt = $amt;
    $me->date = nowNoQuotes();
    $me->status = static::STATUS_PENDING;
    return $me;
  }
}
class Payee extends SqlRec {
  public $userGroupId;
  public $name;
  public $addr1;
  public $addr2;
  public $city;
  public $state;
  public $zip;
  public $phone;
  //
  public function getSqlTable() {
    return 'payees';
  }
  //
  static function fetchAll() {
    $c = new static();
    return static::fetchAllBy($c);
  }
}