<?php
require_once 'php/data/csv/client-import/_CsvImportRec.php';
require_once 'php/data/csv/client-import/ClientImport.php';
//
class HeneinRec extends CsvImportRec {
  //
  public $uid;
  public $last;
  public $first;
  public $_middleInitial;
  public $addr1;
  public $addr2;
  public $city;
  public $state;
  public $zip;
  public $_zip4;
  public $phone1;
  public $phone2;
  public $ssn;
  public $dob;
  public $_marital;
  public $sex;
  public $_rpi;
  public $_rp;
  public $_bal;
  public $_chg;
  public $_pay;
  public $_priCode;
  public $_priType;
  public $_priPolicy;
  public $_priId;
  public $_secCode;
  public $_secType;
  public $_secPolicy;
  public $_secId;
  public $_secAuto;
  public $_secAa;
  public $_lpd;
  public $_hos1;
  public $_deathDate;
  public $_fac;
  public $_rpc;
  public $_ss;
  public $_er;
  public $_emer;
  public $_pct;
  public $_sigOnFile;
  public $_releaseInfo;
  public $_epsdt;
  public $_fp;
  public $_tpl;
  public $_lp;
  public $_icd1;
  public $_icd2;
  public $_icd3;
  public $_icd4;
  public $_pcod;
  public $_ptyp;
  public $_lv;
  public $_mc;
  public $_cop;
  public $_npd;
  public $email;
  public $middle;
  public $_race;
  public $_ethn;
  public $_lang;
  //
  public function getUgid() {
    return 2085;
  }
  public function asClientImport() {
    $c = ClientImport::fromCsv($this->getUgid(), $this->uid, $this->last, $this->first, null, $this->sex, $this->dob, $this->ssn);
    $c->Address_Home = AddressImport::fromCsv($this->addr1, $this->addr2, null, $this->city, $this->state, $this->zip, $this->phone1, $this->phone2, $this->email);
    return $c;
  }
  //
  static function hasHeader() {
    return true;
  }
  static function read($step = null, $batchSize = null) {
    return parent::read('php\data\csv\client-import\henein\henein.csv', __CLASS__, $step, $batchSize);
  }
}
