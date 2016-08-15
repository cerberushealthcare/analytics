<?php
require_once 'Billing_Sql.php';
require_once 'Invoice.php';
require_once 'php/csys/card-processor/CardProcessor.php';
//
class Billing {
  //
  static function chargeAllDue($date = null/*default today*/) {
    if ($date == null)
      $date = nowShortNoQuotes(); 
    $recs = BillStatus::fetchAllDue($date);
    foreach ($recs as $status) {
      $hist = static::charge($status, $date);
      if ($hist) 
        static::invoice($status->Source, $hist);
    }
  }
  static function charge(/*BillStatus*/$status, $date) { 
    $hist = null;
    try {
      $amt = $status->calcAmt();
      $response = CardProcessor::preauthAndCharge($status->Source, $amt);
      if ($response->isGood()) {
        $hist = BillHistory::record($status, $date, $amt);
        $status->saveAsBilled($date, $amt);
        static::accrueReferrals($hist);
      } else {
        $status->saveAsFailed();
        if ($status->exceedsFailThreshold())
          $status->User->deactivate();
      }
    } catch (Exception $e) {
      blog($e);
    }
    return /*BillHistory*/$hist;
  }
  static function invoice(/*BillHistory*/$hist, /*BillSource*/$source) {
    try {
      $invoice = InvoicePdf::create($Source, $hist);
      $statement = StatementPdf::create($Source, $invoice);
      BillingEmail::send($ource, $statement, $invoice);
      $hist->savePdf($invoice);
    } catch (Exception $e) {
      blog($e);
    }
  }
  static function accrueReferrals(/*BillHistory*/$hist) {
    $refs = Referral::fetchAllValid($hist->userId, $hist->billDate);
    foreach ($recs as $ref) 
      static::accrueReferral($hist, $ref);
  }
  static function accrueReferral(/*BillHistory*/$hist, /*Referral*/$ref) {
    try {
      $amt = $ref->calcAccrual($hist->amt);
      Accrual::recordPending(
        $ref->Reseller->resellerId,
        $hist->userId,
        $hist->billDate,
        $amt);
    } catch (Exception $e) {
      blog($e);
    }
  }
}
class BillingEmail extends Email {
  //
  static function create(/*BillSource*/$source, $statement, $invoice) {
    
  }
  
}
