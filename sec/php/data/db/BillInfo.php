<?php
require_once "php/data/db/_util.php";
require_once 'php/data/rec/cryptastic.php';

class BillInfo {

  public $userId;
  public $name;
  public $address1;
  public $address2;
  public $city;
  public $state;
  public $zip;
  public $country;
  public $cardType;
  public $cardNumber;
  public $expMonth;
  public $expYear;
  public $nextBillDate;
  public $balance;
  public $phone;
  public $phoneExt;
  public $startBillDate;
  public $billCodeId;
  public $lastBillStatus;
  
    
  // Child
  public $billCode; 
  
  // Helpers
  public $cardLast4;
  public $phoneAc;
  public $phonePf;
  public $phoneNm;
  public $exists;
  public $upfrontCharge;
  public $registerText;
  
  const CARD_TYPE_MC = 1;
  const CARD_TYPE_VISA = 2;
  const CARD_TYPE_AMEX = 3;
  
  const STATUS_OK = 1;
  const STATUS_CARD_DECLINED = 2;

  public function __construct($userId, $name, 
      $address1, $address2, $city, $state, $zip, $country,
      $cardType, $cardNumber, $expMonth, $expYear, 
      $nextBillDate, $balance, $phone, $phoneExt, $startBillDate, $billCodeId, $lastBillStatus) {

    $this->userId = $userId;
    $this->name = $name;
    $this->address1 = $address1;
    $this->address2 = $address2;
    $this->city = $city;
    $this->state = $state;
    $this->zip = $zip;
    $this->country = $country;
    $this->cardType = $cardType;
    $this->cardNumber = $cardNumber;
    $this->expMonth = $expMonth;
    $this->expYear = $expYear;
    $this->nextBillDate = $nextBillDate;
    $this->balance = $balance;
    $this->phone = $phone;
    $this->phoneExt = $phoneExt;
    $this->startBillDate = $startBillDate;
    $this->billCodeId = $billCodeId;
    $this->lastBillStatus = $lastBillStatus;
    
    // Helpers
    //$cn = MyCrypt::decrypt($cardNumber);
    //if (strlen($cn) >= 4) {
    //  $this->cardLast4 = substr($cn, strlen($cn) - 4);
    //}
  }
  
  public function getCardTypeDesc() {
    switch ($this->cardType) {
      case BillInfo::CARD_TYPE_MC:
        return "MasterCard";
      case BillInfo::CARD_TYPE_VISA:
        return "VISA";
      case BillInfo::CARD_TYPE_AMEX:
        return "AMEX";
      default:
        return "";
    }
  }
}
?>