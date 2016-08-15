<?php
require_once 'batch/mass-mailer/MassCsvFile.php';
//
class ToCsv extends MassCsvFile {
  //
  static $FILENAME = 'batch/mass-mailer/sales-job/to.csv';
  static $CSVREC_CLASS = 'ToCsvRec';
  static $HAS_FID_ROW = true;
}
class ToCsvRec extends MassCsvRec {
  //
  public $greet;
  public $email;
  public $name;
  public $phone;
  public $location;
  public $received;
  //
  static $REPLACE_FIELDS = array('greet');
  //
  public function shouldSend() {
    return ! empty($this->email) && ! empty($this->greet);
  }
}
