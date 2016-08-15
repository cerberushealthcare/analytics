<?php
require_once 'php/cbat/csv-import/PatientCsvFile.php';
require_once 'php/cbat/csv-import/Import_Sql.php';
//
class PatientCsv extends PatientCsvFile {
  //
  static $FILENAME = 'input-patient.csv';
  static $CSVREC_CLASS = 'PatientRec';  
  static $HAS_FID_ROW = true;
  static $UGID;  // TODO
}
class PatientRec extends CsvRec {
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
  public function getSex() {
    return substr($this->gender, 0, 1);
  }
  public function asClient($ugid) {
    $client = Client_Import::from(
      $this,
      $ugid, 
      null, 
      $this->last, 
      $this->first, 
      $this->middle, 
      $this->getSex(), 
      $this->dob);
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
