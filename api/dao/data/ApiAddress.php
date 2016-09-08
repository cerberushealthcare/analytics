<?php
require_once 'Api.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/analytics/sec/php/data/rec/sql/_AddressRec.php';
/**
 * Address
 */
class ApiAddress extends Api {
  //
  public $addr1;
  public $addr2;
  public $addr3;
  public $city;
  public $state;
  public $zip;
  public $phone;
  public $phoneCell;
  public $phoneFax;
  public $email;
  public $url;
  public $name;
  public $country = 'US';
  /**
   * Constructor
   * @param ['field'=>value,..] $data
   * @param(opt) string $prefix
   */
    public function __construct($data, $prefix = null) {
    $this->load($data, Api::NONE_REQUIRED, $prefix);
  }
  /**
   * Static constructor from ADDRESS
   * $param Address_Api $addr
   * @return ApiAddress
   */
  public static function fromAddress($addr) {
    $data = array(
      'addr1' => $addr->addr1,
      'addr2' => $addr->addr2,
      'addr3' => $addr->addr3,
      'city' => $addr->city,
      'state' => $addr->state,
      'zip' => $addr->zip,
      'phone' => $addr->phone1,
      'phoneCell' => $addr->phone2,
      'phoneFax' => $addr->phone3,
      'email' => $addr->email1,
      'url' => $addr->email2,
      'name' => $addr->name,
      'country' => 'US');
    return new ApiAddress($data);
  }
}
/**
 * SqlRec
 */
class Address_Api extends Address {
  //
  public function save() {
    return SqlRec::save();
  }
  //
  static function from(/*ApiAddress*/ $api, $tableCode, $tableId, $type, /*Address_Api*/ $address = null) {
    $id = ($address) ? $address->addressId : null;
    if ($api)
      return new static(
        $id,
        $tableCode,
        $tableId,
        $type,
        $api->addr1,
        $api->addr2,
        $api->addr3,
        $api->city,
        $api->state,
        $api->zip,
        null,
        $api->phone,
        static::PHONE_TYPE_PRIMARY,
        $api->phoneCell,
        static::PHONE_TYPE_CELL,
        $api->phoneFax,
        static::PHONE_TYPE_FAX,
        $api->email,
        $api->url,
        $api->name);
  }
  static function from_asPrimary($api, $cid, $address) {
    return static::from($api, static::TABLE_CLIENTS, $cid, static::TYPE_SHIP, $address);
  }
  static function from_asEmergency($api, $cid, $address) {
    return static::from($api, static::TABLE_CLIENTS, $cid, static::TYPE_EMER, $address);
  }
  static function from_asRx($api, $cid, $address) {
    return static::from($api, static::TABLE_CLIENTS, $cid, static::TYPE_RX, $address);
  }
  static function fetch($cid, $type) {
    $c = static::asCriteria($cid, $type);
    $rec = static::fetchOneBy($c);
    return $rec;
  }
  static function fetchHome($cid) {
    return static::fetch($cid, static::TYPE_SHIP, $returnEmpty, $class);
  }
  static function fetchEmergency($cid) {
    return static::fetch($cid, static::TYPE_EMER, $returnEmpty, $class);
  }
  static function fetchRx($cid) {
    return static::fetch($cid, static::TYPE_RX, $returnEmpty, $class);
  }
  static function asCriteria($cid, $type) {
    $c = new static();
    $c->tableCode = static::TABLE_CLIENTS;
    $c->tableId = $cid;
    $c->type = $type;
    return $c;
  }
}
