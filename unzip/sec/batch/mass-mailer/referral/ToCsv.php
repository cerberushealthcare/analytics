<?php
require_once 'batch/mass-mailer/MassCsvFile.php';
//
class ToCsv extends MassCsvFile {
  //
  static $FILENAME = 'batch/mass-mailer/referral/to.csv';
  static $CSVREC_CLASS = 'ToCsvRec';
  static $HAS_FID_ROW = true;
}
class ToCsvRec extends MassCsvRec {
  //
  public $created;
  public $name;
  public $state;
  public $email;
  public $officePhone;
  public $billPhone;
  public $monthlyCharge;
  public $registerText;
  //
  static $REPLACE_FIELDS = null;
}
