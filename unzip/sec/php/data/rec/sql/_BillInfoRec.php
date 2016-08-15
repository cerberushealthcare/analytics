<?php
require_once 'php/data/rec/sql/_SqlRec.php';
/**
 * User Group Base Class
 * @author Warren Hornsby
 */
abstract class BillInfoRec extends SqlRec implements CompositePk, AutoEncrypt {
  /*
  public $userId;
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
  public $billCode;
  public $lastBillStatus;
  */
  //
  const STATUS_NEW = 0;
  const STATUS_OK = 1;
  const STATUS_CARD_DECLINED = 2;
  //
  public function getSqlTable() {
    return 'billinfo';
  }
  public function getEncryptedFids() {
    return array('cardNumber');
  }
  /**
   * @return true if card was declined last billing
   */
  public function wasDeclined() {
    return $this->lastBillStatus == static::STATUS_CARD_DECLINED;
  }
  /**
   * @return int days until card expiration
   */
  public function getDaysLeft() {
    $exp = strtotime($this->expMonth . "/01/" . $this->expYear);
    $cc_expdt = date("Y-m-d", strtotime($this->expMonth . "/" . date("t", $exp) . "/" . $this->expYear));
    return (strtotime($cc_expdt) - strtotime(date('Y-m-d'))) / 86400 + 1;
  }
}
