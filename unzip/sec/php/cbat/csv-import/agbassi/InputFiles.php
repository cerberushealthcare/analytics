<?php
require_once 'php/cbat/csv-import/PatientCsvFile.php';
require_once 'php/cbat/csv-import/Import_Sql.php';
//
class PatientCsv extends PatientCsvFile {
  //
  static $FILENAME = 'input-patient.csv';
  static $CSVREC_CLASS = 'PatientRec';  
  static $HAS_FID_ROW = true;
  static $UGID = 2658;
}
class PatientRec extends PatientCsvRec {
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
  public function getKey() {
    return static::makeKey($this);
  }
  public function getSex() {
    return substr($this->gender, 0, 1);
  }
  public function asClient($ugid, $ct) {
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
  //
  static function makeKey($rec) {
    return "$rec->first|$rec->last|$rec->middle";
  }
}
class InsuranceCsv extends CsvFile {
  //
  static $FILENAME = 'input-insurance.csv';
  static $REC_CLASS = 'InsuranceRec';  
  static $HAS_FID_ROW = true;
  //
  public function addICardsTo(&$clients) {
    $map = $this->getMap($clients);
    foreach ($this->recs as $rec) {
      $key = $rec->getKey();
      $client = geta($map, $key); 
      if ($client)
        $rec->addICardTo($client);
    }
  }
  public function getMap($clients) {
    $map = array();
    foreach ($clients as &$client)
      $map[$client->_source->getKey()] = $client;
    return $map;
  } 
}
class InsuranceRec extends CsvRec {
  //
  public $first;
  public $last;
  public $middle;
  public $type;  // "primary" or "secondary"
  public $source;
  public $policy;
  public $group;
  public $plan;
  //
  public function getKey() {
    return PatientRec::makeKey($this);
  }
  public function getSeq() {
    return ($this->type == 'Secondary') ? 2 : 1;
  }
  public function addICardTo(&$client) {
    $seq = $this->getSeq();
    $fid = "ICard" . $seq;
    $client->$fid = $this->asICard($seq);
  }
  public function asICard($seq) {
    return ICard_Import::from(
      $this->getSeq(),
      $this->source,
      null,
      null, 
      $this->group,
      $this->policy);
  }
}