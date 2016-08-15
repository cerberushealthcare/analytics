<?php
require_once 'php/data/csv/client-import/_CsvImportFile.php';
//
class AgbassiFile extends CsvImportFile {
  //
  static $FILENAME = 'php/data/csv/client-import/agbassi/Bernard.csv';
  static $HAS_FID_ROW = true;
  static $UGID = 2658;
  static $CSVREC_CLASS = 'AgbassiRec';
}
class AgbassiRec extends CsvImportRec {
  //
  public $first;
  public $last;
  public $middle;
  public $dob;
  public $ssn;
  public $gender;
  public $addr;
  public $city;
  public $state;
  public $zip;
  public $phone;
  //
  public function asClientImport($ugid) {
    $uid = self::buildUid($this->last);
    $sex = substr($this->gender, 0, 1);
    return Client_Import::fromCsv($ugid, $uid, $this->last, $this->first, $this->middle, $sex, $this->dob);
  }
  public function asAddressImport() {
    return Address_Import::fromCsv($this->addr, null, null, $this->city, $this->state, $this->zip, $this->phone, null);
  }
}
