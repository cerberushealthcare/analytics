<?php
require_once 'php/cbat/csv-import/PatientCsvFile.php';
require_once 'php/cbat/csv-import/Import_Sql.php';
//
class PatientCsv extends PatientCsvFile {
  //
  static $FILENAME = 'input-patients.csv';
  static $CSVREC_CLASS = 'PatientRec';  
  static $HAS_FID_ROW = true;
  static $UGID = 2645;
  static $FIRST_UID_COUNT = 1;
}
class PatientRec extends PatientCsvRec {
  //
  public $last;
  public $first;
  public $middle;
  public $addr;
  public $city;
  public $state;
  public $zip;
  public $dob;
  public $phone;
  public $gender;
  //
  public function asClient($ugid, $ct) {
    $client = Client_Import::from(
      $this,
      $ugid, 
      $ct,
      null, 
      $this->last, 
      $this->first, 
      $this->middle, 
      $this->gender, 
      $this->dob);
    $client->active = false;
    $client->Address_Home = static::asAddress($client);
    return $client;
  }
  public function asAddress() {
    return Address_Import::from(
      $this->addr, 
      null, 
      null, 
      $this->city, 
      $this->state, 
      $this->zip, 
      $this->phone, 
      null);
  }
}
