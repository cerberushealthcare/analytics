<?php
require_once 'php/data/csv/client-import/_CsvImportRec.php';
require_once 'php/data/csv/client-import/ClientImport.php';
//
class WilliamsRec extends CsvImportRec {
  //
  // TODO: need uid, birth, and sex
  public $first;
  public $last;
  public $addr1;
  public $addr2;
  public $city;
  public $state;
  public $zip;
  //
  public function getUgid() {
    return 1847;
  }
  public function asClientImport() {
    $c = ClientImport::fromCsv($this->getUgid(), $this->uid, $this->last, $this->first, null, null, null, null, null);
    $c->Address_Home = AddressImport::fromCsv($this->addr1, $this->addr2, null, $this->city, $this->state, $this->zip, null, null, null);
    return $c;
  }
  //
  static function hasHeader() {
    return true;
  }
  static function read() {
    return parent::read('php\data\csv\client-import\williams-eric\williams.csv', __CLASS__);
  }
}
