<?php
require_once 'php/c/patient-import/ws-import/CsvFile_Import.php';
//
class CsvFile_G3 extends CsvFile_Import {
  //
  static $CSVREC_CLASS = 'CsvRec_G3';
  static $HAS_FID_ROW = true;
}
class CsvRec_G3 extends CsvRec {
  public $last;
  public $first;
  public $middle;
  public $gen;
  public $guarantor;
  public $dateEntered;
  public $birth;
  public $addr1;
  public $addr2;
  public $city;
  public $state;
  public $zip;
  public $homePhone;
  public $acct;
  public $ssn;
  public $workPhone;
  public $work;
  public $referredBy;
  public $s;
  public $note;
  public $insurance1;
  public $policy1;
  public $insurance2;
  public $policy2;
  public $bal;
  public $email;
}