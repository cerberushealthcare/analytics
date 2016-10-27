<?php
require_once 'php/data/rec/sql/_SqlRec.php';
/**
 * Address Record Base Class
 * @author Warren Hornsby
 */
abstract class AddressRec extends SqlRec implements AutoEncrypt {
  /*
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
  public $county;
  */
  const TABLE_USERS = 'U';
  const TABLE_USER_GROUPS = 'G';
  const TABLE_CLIENTS = 'C';
  //
  const TYPE_SHIP = '0';
  const TYPE_BILL = '1';
  const TYPE_EMER = '2';
  const TYPE_SPOUSE = '3';
  const TYPE_RX = '4';
  const TYPE_MOTHER = '5';
  const TYPE_FATHER = '6';
  const TYPE_BIRTH = '8';
  const TYPE_WORK = '7';
  const TYPE_FACILITY = '9';
  //
  const PHONE_TYPE_PRIMARY = '0';
  const PHONE_TYPE_WORK = '1';
  const PHONE_TYPE_CELL = '2';
  const PHONE_TYPE_EMER = '3';
  const PHONE_TYPE_FAX = '4';
  const PHONE_TYPE_OTHER = '9';
  public static $PHONE_TYPES = array(
    self::PHONE_TYPE_PRIMARY => 'Primary',
    self::PHONE_TYPE_WORK => 'Work',
    self::PHONE_TYPE_CELL => 'Cell',
    self::PHONE_TYPE_EMER=> 'Emergency',
    self::PHONE_TYPE_FAX => 'Fax',
    self::PHONE_TYPE_OTHER => 'Other');
  //
  public static $STATES = array(
    'AK' => 'AK',
    'AL' => 'AL',
    'AR' => 'AR',
    'AZ' => 'AZ',
    'CA' => 'CA',
    'CO' => 'CO',
    'CT' => 'CT',
    'DC' => 'DC',
    'DE' => 'DE',
    'FL' => 'FL',
    'GA' => 'GA',
    'HI' => 'HI',
    'IA' => 'IA',
    'ID' => 'ID',
    'IL' => 'IL',
    'IN' => 'IN',
    'KS' => 'KS',
    'KY' => 'KY',
    'LA' => 'LA',
    'MA' => 'MA',
    'MD' => 'MD',
    'ME' => 'ME',
    'MI' => 'MI',
    'MN' => 'MN',
    'MO' => 'MO',
    'MS' => 'MS',
    'MT' => 'MT',
    'NC' => 'NC',
    'ND' => 'ND',
    'NE' => 'NE',
    'NH' => 'NH',
    'NJ' => 'NJ',
    'NM' => 'NM',
    'NY' => 'NY',
    'NV' => 'NV',
    'OH' => 'OH',
    'OK' => 'OK',
    'OR' => 'OR',
    'PA' => 'PA',
    'RI' => 'RI',
    'SC' => 'SC',
    'SD' => 'SD',
    'TN' => 'TN',
    'TX' => 'TX',
    'UT' => 'UT',
    'VA' => 'VA',
    'VT' => 'VT',
    'WA' => 'WA',
    'WI' => 'WI',
    'WV' => 'WV',
    'WY' => 'WY');
  //
  public function getSqlTable() {
    return 'addresses';
  }
  public function getEncryptedFids() {
    return array('addr2','addr3','city','zip','phone1','phone2','phone3','email1','email2','name');
  }
  public function setPrimaryPhone($phone) {
    $this->setPhone1(self::PHONE_TYPE_PRIMARY, $phone);
  }
  public function setPhone1($type, $phone) {
    $this->phone1Type = $type;
    $this->phone1 = $phone;
  }
  public function setPhone2($type, $phone) {
    $this->phone2Type = $type;
    $this->phone2 = $phone;
  }
}
class Phone {
  //
  public $area;       // 502
  public $local;      // 930-0776
  //
  public $formatted;  // '502-930-2123'
  //
  static function from($unformatted) {
    $me = new static();
    $me->formatted = self::format($unformatted);
    $parts = explode('-', $me->formatted);
    $me->area = array_shift($parts);
    if (! empty($parts))
      $me->local = implode('', $parts);
    return $me;
  }
  //
  private static function format($phone) {
    $num = str_replace('(', '', $phone);
    $num = str_replace(')', '', $phone);
    $num = preg_replace('/[^0-9]/', '', $phone);
    $len = strlen($num);
    if ($len == 7)
      return preg_replace('/([0-9]{3})([0-9]{4})/', '$1-$2', $num);
    else if ($len == 10)
      return preg_replace('/([0-9]{3})([0-9]{3})([0-9]{4})/', '$1-$2-$3', $num);
  }
}
/**
 * Editable Address Base Class
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
  public $county;
  //
  public function getAuditClientId() {
    if ($this->tableCode == self::TABLE_CLIENTS)
      return $this->tableId;
    else
      return null;
  }
  public function toJsonObject(&$o) {
    $o->csz = self::formatCsz($o);
    unset($o->_empty);
  }
  public function authenticateAsCriteria() {
    if ($this->tableCode)
      $this->authenticate();
  }
  public function authenticate() {
    if (isset($this->_authenticated))
      return;
    switch ($this->tableCode) {
      case self::TABLE_CLIENTS:
        $this->authenticateClientId($this->tableId);
        break;
      case self::TABLE_USERS:
        $this->authenticateUserId($this->tableId);
        break;
      case self::TABLE_USER_GROUPS:
        $this->authenticateUserGroupId($this->tableId);
        break;
      default:
        throw new InvalidDataException("Invalid address table code '$address->tableCode'");
    }
  }
  protected function getTableCode() {
    // @abstract, required
  }
  protected function getType() {
    // @abstract, not required
  }
  public function format($includePhoneEmail = true) {
    $a = array();
    pushIfNotNull($a, $this->addr1);
    pushIfNotNull($a, $this->addr2);
    pushIfNotNull($a, self::formatCsz($this));
    if ($includePhoneEmail) {
      pushIfNotNull($a, $this->phone1);
      pushIfNotNull($a, $this->email1);
    }
    return join(' ', $a);
  }
  /**
   * @param int $tableId
   */
  public function save($tableId = null) {
    if ($this->tableCode == null)
      $this->tableCode = $this->getTableCode();
    if ($this->tableId == null)
      $this->tableId = $tableId;
    if ($this->type == null && $this->getType())
      $this->type = $this->getType();
    return parent::save();
  }
  public function isEmpty() {
    return isset($this->_empty);
  }
  public function fetchCountyCode() {
    $code = null;
    if ($this->county && $this->state) {
      $cs = $this->county . ', ' . $this->state; 
      $sql = "SELECT code FROM counties WHERE county_state='$cs'";
      $code = Dao::fetchValue($sql);
    }
    $this->_countyCode = $code;
  }
  //
  /**
   * @param int $id
   * @return Address
   */
  static function fetch($id) {
    $address = parent::fetch($id);
    $address->authenticate();
  }
  static function formatCsz($addr) {
    $a = "";
    if (get($addr, 'city') != "") {
      $a = $addr->city;
      if (get($addr, 'state') != "")
        $a .= ", " . $addr->state;
      if (get($addr, 'zip') != "")
        $a .= " " . $addr->zip;
    }
    return trim($a);
  }
}
/**
 * Clients
 */
class ClientAddress extends Address {
  //
  public function getTableCode() {
    return self::TABLE_CLIENTS;
  }
  public function save($cid) {
    return parent::save($cid);
  }
  //
  /**
   * @param int $cid
   * @param string $type AddressRec::TYPE_
   * @param bool $returnEmpty true to return empty Address rather than null (optional)
   * @return Address
   */
  static function fetch($cid, $type, $returnEmpty = false, $class = __CLASS__) {
    $c = self::asCriteria($cid, $type, $class);
    $address = self::fetchOneBy($c);
    if ($returnEmpty && $address == null) {
      $address = self::asCriteria($cid, $type, $class);
      $address->_empty = true;
    }
    return $address;
  }
  static function fetchHome($cid, $returnEmpty = false, $class = __CLASS__) {
    return self::fetch($cid, self::TYPE_SHIP, $returnEmpty, $class);
  }
  static function fetchEmergency($cid, $returnEmpty = false, $class = __CLASS__) {
    return self::fetch($cid, self::TYPE_EMER, $returnEmpty, $class);
  }
  static function fetchSpouse($cid, $returnEmpty = false, $class = __CLASS__) {
    return self::fetch($cid, self::TYPE_SPOUSE, $returnEmpty, $class);
  }
  static function fetchFather($cid, $returnEmpty = false, $class = __CLASS__) {
    return self::fetch($cid, self::TYPE_FATHER, $returnEmpty, $class);
  }
  static function fetchMother($cid, $returnEmpty = false, $class = __CLASS__) {
    return self::fetch($cid, self::TYPE_MOTHER, $returnEmpty, $class);
  }
  static function fetchRx($cid, $returnEmpty = false, $class = __CLASS__) {
    return self::fetch($cid, self::TYPE_RX, $returnEmpty, $class);
  }
  static function fetchBirth($cid, $returnEmpty = false, $class = __CLASS__) {
    return self::fetch($cid, self::TYPE_BIRTH, $returnEmpty, $class);
  }
  static function asCriteria($cid = null, $type = Address::TYPE_SHIP, $class = __CLASS__) {
    $c = new $class();
    $c->tableCode = $c->getTableCode();
    $c->tableId = $cid;
    $c->type = $type;
    return $c;
  }
  static function asJoin() {
    $c = static::asCriteria();
    return CriteriaJoin::optional($c, 'tableId');
  }
  static function asJoinHome() {
    $c = new static();
    $c->tableCode = static::TABLE_CLIENTS;
    $c->type = static::TYPE_SHIP;
    return CriteriaJoin::optional($c, 'tableId');
  }
  static function asJoinFather() {
    $c = new static();
    $c->tableCode = static::TABLE_CLIENTS;
    $c->type = static::TYPE_FATHER;
    return CriteriaJoin::optional($c, 'tableId');
  }
  static function asJoinMother() {
    $c = new static();
    $c->tableCode = static::TABLE_CLIENTS;
    $c->type = static::TYPE_MOTHER;
    return CriteriaJoin::optional($c, 'tableId');
  }
}
/**
 * User Groups
 */
class UserGroupAddress extends Address {
  //
  public function getTableCode() {
    return self::TABLE_USER_GROUPS;
  }
  public function getType() {
    return self::TYPE_SHIP;
  }
  public function save($ugid) {
    return parent::save($ugid);
  }
  //
  /**
   * @param int $ugid
   * @return UserGroupAddress
   */
  static function fetch($ugid) {
    $c = self::asCriteria($ugid);
    return self::fetchOneBy($c);
  }
  static function asCriteria($ugid) {
    $c = new self();
    $c->tableCode = $c->getTableCode();
    $c->tableId = $ugid;
    $c->type = $c->getType();
    return $c;
  }
  static function asJoin() {
    $c = static::asCriteria(null);
    return CriteriaJoin::requires($c, 'tableId');
  }
}
/**
 * Facilities
 */
class FacilityAddress extends Address {
  //
  public function getTableCode() {
    return self::TABLE_USER_GROUPS;
  }
  public function getType() {
    return self::TYPE_FACILITY;
  }
  public function save($ugid) {
    return parent::save($ugid);
  }
  //
  /**
   * @param int $ugid
   * @return array(FacilityAddress,..)
   */
  static function fetchAll($ugid) {
    $c = self::asCriteria($ugid);
    return self::fetchAllBy($c, null, 2000);
  }
  static function asCriteria($ugid) {
    $c = new self();
    $c->tableCode = $c->getTableCode();
    $c->tableId = $ugid;
    $c->type = $c->getType();
    return $c;
  }
  static function asOptionalJoin($fid = 'addrFacility') {
    $c = new self();
    return CriteriaJoin::optional($c, $fid);
  }
  static function formatName($addr) {
    $name = $addr->name;
    $csz = static::formatCsz($addr);
    if ($csz != '')
      $name .= ' (' . $csz . ')';
    return $name;
  }

}