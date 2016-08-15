<?php
require_once "php/data/db/_util.php";

class BillCode {

  public $billCode;
  public $newSignups;
  public $upFrontCharge;
  public $monthlyCharge;
  public $minCharge;
  public $maxCharge;
  public $registerText;
  public $createDate;
  public $discountCode;
  public $firstBill;
  
  public function __construct($billCode, $newSignups,
      $upFrontCharge, $monthlyCharge, $minCharge, $maxCharge,
      $registerText, $createDate, $discountCode, $firstBill) {

    $this->billCode = $billCode;
    $this->newSignups = $newSignups;
    $this->upFrontCharge = $upFrontCharge;
    $this->monthlyCharge = $monthlyCharge;
    $this->minCharge = $minCharge;
    $this->maxCharge = $maxCharge;
    $this->registerText = $registerText;
    $this->createDate = $createDate;
    $this->discountCode = $discountCode;
    $this->firstBill = $firstBill;
  }
}
