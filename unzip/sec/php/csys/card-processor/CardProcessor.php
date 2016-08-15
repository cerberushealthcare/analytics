<?php
require_once "php/data/email/Email.php";
require_once "php/linkpoint/LinkPoint.php";
//
class CardProcessor {
  //
  public function preauth(/*BillSource*/$source, $amt) {
    $lp = LinkPoint_Cp::create($source)->setAmt_asPreauth($amt);
    $reponse = $lp->transmit();
    if ($response->isBad())
      EmailAdmin_Cp::sendAsPreauthFailure($source, $response);
    return /*LpResponse*/$response;
  }
  public function charge(/*BillSource*/$source, $amt) {
    $lp = LinkPoint_Cp::create($source)->setAmt_asCharge($amt);
    $reponse = $lp->transmit();
    if ($response->isBad())
      EmailAdmin_Cp::sendAsChargeFailure($source, $response);
    return /*LpResponse*/$response;
  }
  public function preauthAndCharge(/*BillSource*/$source, $amt) {
    $response = static::preauth($source, $amt);
    if ($this->isApproved())
      $response = static::charge($source, $amt);
    return /*LpResponse*/$response;
  }
}
class LinkPoint_Cp extends LinkPoint {
  //
  public function setCard($rec) {
    return parent::setCard(
      $rec->name,
      "For user: $rec->userId",
      "U-$rec->userId-D-" . date("Y-m-d-H:i:s", strtotime("now")),
      $rec->cardNumber,
      $rec->expMonth,
      $rec->expYear);
  }
  public function setAddress($rec) {
    return parent::setAddress(
      $rec->addr1,
      $rec->city,
      $rec->state,
      $rec->zip);
  }
  //
  static function create(/*BillSource*/$source) {
    $me = parent::create();
    $me->setCard($source);
    $me->setAddress($source);
    return $me;
  }
}
class EmailAdmin_Cp extends EmailAdmin {
  //
  public $to = 'wghornsby@clicktatemail.com, pstewart@clicktatemail.com';
  //
  static function send_asPreauthFailure(/*BillSource*/$source, /*LpResponse*/$resp) {
    static::send('Card Preauth Failure', $source, $resp);
  }
  static function send_asChargeFailure(/*BillSource*/$source, /*LpResponse*/$resp) {
    static::send('* CARD CHARGE FAILURE *', $source, $resp);
  }
  protected static function send($subject, $source, $resp) {
    $e = new static();
    $e->html()->table()
      ->tr()->th('User:')->td($source->userId)->_()
      ->tr()->th('Name:')->td($source->name)->_()
      ->tr()->th('Time:')->td($resp->time)->_()
      ->tr()->th('Error:')->td($resp->error)->_()
      ->tr()->th('Message:')->td($resp->message)->_()
      ->tr()->th('Approved:')->td($resp->approved)->_()
      ->tr()->th('AVS:')->td($resp->getAvsResultText())->_();
    $e->mail();
  }
}