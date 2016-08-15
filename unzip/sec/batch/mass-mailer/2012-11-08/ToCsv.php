<?php
require_once 'batch/mass-mailer/MassCsvFile.php';
//
class ToCsv extends MassCsvFile {
  //
  static $FILENAME = 'batch/mass-mailer/2012-11-08/to.csv';
  static $CSVREC_CLASS = 'ToCsvRec';
  static $HAS_FID_ROW = true;
}
class ToCsvRec extends MassCsvRec {
  //
  public $name;
  public $city;
  public $state;
  public $phone1;
  public $user_group_id;
  public $user_id;
  public $uid;
  public $greet;
  public $uname;
  public $email;
  //
  static $REPLACE_FIELDS = array('greet');
  //
  public function shouldSend() {
    return parent::shouldSend() && ! empty($this->greet);
  }
}
