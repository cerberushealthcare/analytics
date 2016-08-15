<?php
require_once 'php/data/rec/sql/_AddressRec.php';
//
class AddressImport extends AddressRec {
  //
  public $addressId;
  public $tableCode;
  public $tableId;
  public $type;
  public $addr1;
  public $addr2;
  public $addr3;
  public $city;
  public $state;
  public $zip;
  public $country;
  public $phone1;
  public $phone1Type;
  public $phone2;
  public $phone2Type;
  public $phone3;
  public $phone3Type;
  public $email1;
  public $email2;
  public $name;
  //
  public function setFromMatch($match) {
    if ($match) {
      $this->addressId = $match->addressId;
    }
  }
  //
  static function fromCsv($addr1, $addr2, $addr3, $city, $state, $zip, $primaryPhone, $otherPhone = null, $email = null) {
    $rec = new self();
    $rec->tableCode = self::TABLE_CLIENTS;
    $rec->tableId = null;
    $rec->type = self::TYPE_SHIP;
    $rec->addr1 = $addr1;
    $rec->addr2 = $addr2;
    $rec->addr3 = $addr3;
    $rec->city = $city;
    $rec->state = $state;
    $rec->zip = $zip;
    $rec->country = 'US';
    $rec->setPrimaryPhone($primaryPhone);
    if ($otherPhone)
      $rec->setPhone2(self::PHONE_TYPE_OTHER, $otherPhone);
    $rec->email1 = $email; 
    return $rec;
  }
  static function fetchByCid($cid) {
    $c = new self();
    $c->tableCode = self::TABLE_CLIENTS;
    $c->tableId = $cid;
    $c->type = self::TYPE_SHIP;
    return self::fetchOneBy($c);
  }
}
?>