<?php
require_once 'batch/mass-mailer/MassCsvFile.php';
//
class ToCsv extends MassCsvFile {
  //
  static $FILENAME = 'batch/mass-mailer/cert-migrate/to.csv';
  static $CSVREC_CLASS = 'ToCsvRec';
  static $HAS_FID_ROW = true;
}
class ToCsvRec extends MassCsvRec {
  //
  public $userId;
  public $userGroupId;
  public $dateCreated;
  public $name;
  public $state;
  public $email;
  public $monthlyCharge;
  public $registerText;
  //
  static $REPLACE_FIELDS = null;
}

