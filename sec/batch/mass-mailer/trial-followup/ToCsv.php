<?php
require_once 'batch/mass-mailer/MassCsvFile.php';
//
class ToCsv extends MassCsvFile {
  //
  static $FILENAME = 'batch/mass-mailer/trial-followup/to.csv';
  static $CSVREC_CLASS = 'ToCsvRec';
  static $HAS_FID_ROW = true;
}
class ToCsvRec extends MassCsvRec {
  //
  public $dateCreated;
  public $trialExpdt;
  public $uname;
  public $greet;
  public $email;
  public $gname;
  public $phone1;
  public $ugid;
  public $userId;
  public $uid;
  public $licenseState;
  public $license;
  //
  static $REPLACE_FIELDS = array('greet');
  //
  public function shouldSend() {
    return parent::shouldSend() && ! empty($this->greet);
  }
}
