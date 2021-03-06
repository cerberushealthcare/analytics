<?php
require_once 'php/data/rec/sql/_SqlRec.php';
//
/**
 * Billing Infos
 * DAO for BillInfo
 * @author Warren Hornsby
 */
class BillInfos {
  //
  /**
   * Create new billing info from signup
   * @param BillInfoForm $form
   */
  public static function signUp($form) {
    global $myLogin;
    BillInfo::delete($myLogin->userId);
    $billCode = BillCode::fetch($form->billCode);
    $rec = BillInfo::asSignUp($myLogin->userId, $form, $billCode);
    $rec->save();
      
  }
  /**
   * Update billing info
   * @param BillInfo $bi
   */
  public static function update($bi) {
    global $myLogin;
    $rec = BillInfo::fetch($myLogin->userId);
  }
  //
  private static function nextBillDateFromNow() {
    $dnow = strtotime('now');
    $dyear = date('Y', $dnow);
    $dmth = date('m', $dnow);
    $dday = date('d', $dnow);
    if ($dday < 26) 
      return date('Y-m-d', strtotime('+1 month'));
    else 
      return date('Y-m-d', strtotime('+2 months', mktime(0, 0, 0, $dmth, 1, $dyear)));
  }
}
//
/**
 * Billing Info
 */
class BillInfo extends SqlRec implements CompositePk {
  //
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
  //
  const STATUS_NEW = 0;
  const STATUS_OK = 1;
  const STATUS_CARD_DECLINED = 2;
  //
  public function getSqlTable() {
    return 'bill_info';
  }
  //
  /**
   * @param int $userId
   * @param BillInfoForm $form
   * @param BillCode $billCode
   * @return BillInfo
   */
  public static function fromSignUp($userId, $form, $billCode) {
    if ($billCode == null || ! $billCode->newSignups) 
      throw new SqlRecException($billCode, 'Billing code invalid for signup');
    $rec = BillInfo::fromUiForm($userId, $form);
    $rec->nextBillDate = BillInfos::nextBillDateFromNow();
    $rec->balance = 0;
    $rec->startBillDate = nowNoQuotes();
    $rec->billCode = $billCode->billCode;
    $rec->lastBillStatus = BillInfo::STATUS_NEW;
    return $rec;
  }
  /**
   * Delete existing record
   * @param int $userId
   */
  public static function delete($userId) {
    parent::delete(new BillInfo($userId));
  }
  //
  private static function fromUiForm($userId, $form) {
    $rec = new BillInfo();
    $rec->userId = $userId;
    $rec->name = $form->name;
    $rec->address1 = $form->address1;
    $rec->address2 = $form->address2;
    $rec->city = $form->city;
    $rec->state = $form->state;
    $rec->zip = $form->zip;
    $rec->cardType = $form->card_type;
    $rec->cardNumber = $form->card_number;
    $rec->expMonth = $form->exp_month;
    $rec->expYear = $form->expYear;
    return $rec;
  }
}
