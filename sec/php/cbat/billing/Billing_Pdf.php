<?php
require_once "php/data/email/Email.php";
//
class BillingPdf {
  //
  public /*FPDF*/$pdf;
  //
  public function output() {
    if ($this->_output == null)
      $this->_output = $this->pdf->Output('', 'S'); 
    return $this->_output;
  }
}
//
class InvoicePdf extends BillingPdf {
  //
  public $invoiceNum;
  //
  static function create(/*BillSource*/$source, /*BillHist*/$hist) {
    $this->invoiceNum = 100000 + $hist->billHistId;
  }
}
class StatementPdf extends BillingPdf {
  //
  static function create(/*BillStatus*/$status, /*InvoicePdf*/$invoice) {
    
  }
}
