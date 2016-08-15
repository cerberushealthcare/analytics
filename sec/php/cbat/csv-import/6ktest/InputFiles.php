<?php
require_once 'php/cbat/csv-import/PatientCsvFile.php';
require_once 'php/cbat/csv-import/Import_Sql.php';
require_once 'php/data/rec/sql/_HdataRec.php';
//
class PatientCsv extends PatientCsvFile {
  //
  static $FILENAME = 'input-patient.csv';
  static $CSVREC_CLASS = 'PatientRec';  
  static $HAS_FID_ROW = false;
  static $UGID = 3;
  //
  public function getHdatas($cid) {
    $hds = array();
    foreach ($this->recs as $rec) { 
      $hds[] = Hdata_ClientDob_Imp::create(static::$UGID, $cid, $rec->dob);
      $hds[] = Hdata_ClientName_Imp::create(static::$UGID, $cid, $rec->last);
      $cid++;
    }
    return $hds;
  }
  public function getPsis($cid) {
    $psis = array();
    foreach ($this->recs as $rec) 
      $psis[] = GroupPsi_Import::create(static::$UGID, $cid++, $rec->last);
    return $psis;
  }
  //
  static function create() {
    $me = new static();
    $me->recs = PatientRec::createAll();
    $me->save();
  }
}
class Hdata_ClientDob_Imp extends Hdata_ClientDob {
  //
  public function toString() {
    return $this->getSqlInsert() . ";";
  }
}
class Hdata_ClientName_Imp extends Hdata_ClientName {
  //
  public function toString() {
    return $this->getSqlInsert() . ";";
  }
}
class GroupPsi_Import extends SqlRec implements CompositePK, ReadOnly {
  //
  public $ugid;
  public $cid;
  public $h1;
  //
  public function getSqlTable() {
    return 'group_psi';
  }
  public function getPkFieldCount() {
    return 2;
  }
  public function setH1($lastName) {
    $value = strtoupper(substr($lastName, 0, 2));
    $this->setH('h1', $value);
  }
  protected function setH($fid, $value) {
    $this->$fid = MyCrypt_Auto::hash($value);
  }
  //
  static function create($ugid, $cid, $lastName) {
    $me = new static();
    $me->ugid = $ugid;
    $me->cid = $cid;
    $me->setH1($lastName);
    return $me;
  }
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
  //
  static function createAll() {
    $us = array();
    for ($i = 1; $i <= 6000; $i++)
      $us[] = static::create($i);
    return $us;
  }
  static function create($i) {
    static $prev;
    $dob = static::past($i);
    $first = Random::first();
    $middle = Random::middle();
    $gender = Random::gender();
    $last = Random::last();
    if ($prev && $prev->last == $last) {
      $addr = $prev->addr;
      $city = $prev->city;
      $zip = $prev->zip;
      $phone = $prev->phone;
    } else {
      $addr = Random::addr();
      $city = Random::city();
      $zip = Random::zip();
      $phone = Random::phone();
    }
    $me = new static(array(
      $first,
      $last,
      $middle,
      $dob,
      "",
      $gender,
      $addr,
      $city,
      "KY",
      $zip,
      $phone));
    $prev = $me;
    return $me;
  }
  static function past($days) {
    $dt = strtotime(date("Y-m-d"));
    $dt = mktime(0, 0, 0, date("n", $dt), date("j", $dt) - $days, date("Y", $dt));
    return date("Y-m-d", $dt);
  }
}
class Random {
  //
  static function last() {
    return static::make(false, 8);
  }
  static function first() {
    return static::make(true, 6);
  }
  static function middle() {
    return static::pick('', static::upper());
  }
  static function addr() {
    $n = mt_rand(100, 9999);
    return $n . ' ' . static::make(true) . ' ' . static::pick('St','Ave','Rd','Blvd');
  }
  static function gender() {
    return static::pick('M','F');
  }
  static function city() {
    return static::pick(
    	'Louisville','Lexington','Georgetown','Newport','Edgewood','Erlanger','Ft Mitchell','Ft Wright','Ft Thomas','Ludlow','Winchester','St Matthews','Bardstown','Bowling Green','Covington','Richmond','Hopkinsville','Owensboro','Florence','Elizabethtown','Nicholasville','Henderson','Frankfort',
      'Louisville','Lexington','Louisville','Lexington','Louisville','Lexington','Louisville','Lexington');
  }
  static function zip() {
    return mt_rand(40001, 49999);
  }
  static function phone() {
    return static::pick('502','859') . ' ' . mt_rand(210, 970) . '-' . mt_rand(1000, 9999);
  }
  //
  static function pick() {
    $array = func_get_args();
    return $array[mt_rand(1, count($array)) - 1];
  }
  static function make($alwaysUnique = false, $maxLen = 12) {
    static $last;
    if ($last && ! $alwaysUnique) {
      if (mt_rand(0, 10) <= 2)
        return $last;
    }
    $len = mt_rand(3, $maxLen);
    $name = static::upper() . static::lowers($len);
    if (! $alwaysUnique) 
      $last = $name;
    return $name;
  }
  static function upper() {
    return chr(65 + mt_rand(0, 25));
  }
  static function lower() {
    return chr(97 + mt_rand(0, 25));
  }
  static function lowers($len) {
    $a = '';
    for ($i = 0; $i < $len; $i++)
      $a .= static::lower();
    return $a; 
  }
}