<?php
require_once 'php/data/rec/sql/_AddressRec.php';
/**
 * Addresses
 * DAO for Address
 * @author Warren Hornsby
 */
class Addresses {
  /**
   * @param stdClass $object Address JSON
   * @return Address 
   */
  public static function save($object) {
    $rec = new Address($object);
    $rec->save();
    return $rec;
  }
  /**
   * @param int cid
   * @param stdClass $object Address JSON
   * @return Address 
   */
  public static function saveForClient($cid, $object) {
    $rec = new Address($object);
    if ($rec->tableCode != Address::TABLE_USERS && $rec->tableId != $cid) 
      throw new SecurityException("Invalid address save, code $rec->tableCode/ID $rec->tableId");      
    $rec->save();
    return $rec;
  }
}
/**
 * Address Record
 */
class Address extends AddressRec {
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
  public function getAuditClientId() {
    if ($this->tableCode == self::TABLE_CLIENTS)
      return $this->tableId;
    else
      return null;
  }
  public function toJsonObject() {
    $o = parent::toJsonObject();
    $o->csz = self::formatCsz($this);
    unset($o->_empty);
    return $o;
  }
  public function authenticateAsCriteria() {
    if ($this->tableCode)
      $this->authenticate();
  }
  public function authenticate() {
    switch ($this->tableCode) {
      case self::TABLE_CLIENTS:
        LoginDao::authenticateClientId($this->tableId);
        break;
      case self::TABLE_USERS:
        LoginDao::authenticateUserId($this->tableId);
        break;
      case self::TABLE_USER_GROUPS:
        LoginDao::authenticateUserGroupId($this->tableId);
        break;
      default:
        throw new InvalidDataException("Invalid address table code '$address->tableCode'");
    }
  }
  public function isEmpty() {
    return isset($this->_empty);
  }
  //
  /**
   * @param int $id
   * @return Address
   */
  public static function fetch($id) {
    $address = parent::fetch($id, 'Address');
    $address->authenticate();
  }
  /**
   * @param string $tableCode AddressRec::TABLE_
   * @param int $tableId
   * @param string $type AddressRec::TYPE_
   * @return Address
   */
  public static function fetchByTable($tableCode, $tableId, $type = self::TYPE_SHIP) {
    $rec = new Address();
    $rec->tableCode = $tableCode;
    $rec->tableId = $tableId;
    $rec->type = $type;
    return parent::fetchOneBy($rec);
  }
  /**
   * @param int $cid
   * @param string $type AddressRec::TYPE_
   * @param bool $returnEmpty true to return empty Address rather than null (optional)
   * @return Address
   */
  public static function fetchByClient($cid, $type, $returnEmpty = false) {
    $c = self::asCriteria($cid, $type);
    $address = self::fetchOneBy($c);
    if ($returnEmpty && $address == null) {
      $address = self::asCriteria($cid, $type);
      $address->_empty = true;
    }
    return $address;
  }
  //
  private static function asCriteria($cid, $type = self::TYPE_SHIP) {
    return new Address(null, self::TABLE_CLIENTS, $cid, $type);
  }
  private static function formatCsz($addr) {
    $a = "";
    if ($addr->city != "") {
      $a = $addr->city;
      if ($addr->state != "") {
        $a .= ", " . $addr->state;
      }
      if ($addr->zip != "") {
        $a .= " " . $addr->zip;
      }
    }
    return trim($a);
  }
}
?>