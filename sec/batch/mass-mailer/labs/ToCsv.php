<?php
require_once 'batch/mass-mailer/MassCsvFile.php';
//
class ToCsv extends MassCsvFile {
  //
  static $FILENAME = 'batch/mass-mailer/labs/to.csv';
  static $CSVREC_CLASS = 'ToCsvRec';
  static $HAS_FID_ROW = true;
}
class ToCsvRec extends MassCsvRec {
  //
  public $ugid;
  public $userId;
  public $uid;
  public $name;
  public $email;
  public $officeName;
  //
  static $REPLACE_FIELDS = null;
}
