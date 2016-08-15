<?php
require_once 'php/data/csv/_CsvRec.php';
require_once 'php/data/rec/sql/_ClientRec.php';
require_once 'php/data/rec/sql/_AddressRec.php';
//
/**
 * CsvImportFile
 */
abstract class CsvImportFile {
  //
  static $FILENAME;
  static $HAS_FID_ROW;
  static $UGID;
  static $CSVREC_CLASS;
  //
  public /*CsvImportRec[]*/ $recs;
  public /*ClientImport[]*/ $fallouts;
  //
  public function __construct() {
    if (static::$FILENAME == null) 
      throw new CsvImportException('No filename specified');
    if (static::$UGID == null) 
      throw new CsvImportException('No UGID specified');
    if (static::$CSVREC_CLASS == null)
      throw new CsvImportException('No CsvRec class specified');
  }
  /**
   * Read records into file
   * @param int $batch run number (optional)
   * @param int $batchSize (optional)
   */
  public function load($batch = 0, $batchSize = 99999) {
    $filename = static::$FILENAME;
    if (($handle = fopen($filename, 'r', true)) == false) 
      throw new CsvImportException("Unable to open file $filename");
    $to = $batch + $batchSize;
    $i = 0;
    if (static::$HAS_FID_ROW) 
      fgetcsv($handle, 1000, ',');
    while (($values = fgetcsv($handle, 1000, ",")) !== false) {
      if ($i >= $batch && $i < $to) 
        $this->recs[] = new static::$CSVREC_CLASS($values);
      if (++$i >= $to)
        break;
    } 
    fclose($handle);
  }
  /**
   * @return string[] SQL  // also loads $this->fallouts
   */
  public function getSqlStatements() {
    $filename = static::$FILENAME . '.sql';
    $clients = $this->asSqlObjects();
    $sql = array();
    foreach ($clients as $client) {
      $sql[] = $client->getSql();
      $sql[] = $client->Address_Home->getSql($client);
    }
    return $sql;
  } 
  //
  protected function asSqlObjects() {
    $clients = array();
    $this->fallouts = array();
    foreach ($this->recs as $rec) {
      $client = $rec->asClientImport(static::$UGID);
      $reason = $client->isInvalid();
      if ($reason) {
        $rec->falloutReason = $reason;
        $this->fallouts[] = $rec->formatValues() . ",$reason";
      } else {
        $client->Address_Home = $rec->asAddressImport();
        $clients[] = $client;
      }
    }
    return $clients;
  }
}
/**
 * CsvRec CsvImportRec
 */
abstract class CsvImportRec extends CsvRec {
  //
  /**
   * @abstract
   * @param int $ugid
   * @return Client_Import
   */
  abstract public function asClientImport($ugid);
  /**
   * @abstract
   * @return Address_Import
   */
  abstract public function asAddressImport();
  /**
   * For files without a UID, this will use first 5 letters of last + unique index
   * Requires that the file is sorted by last name 
   * @param string $last
   * @return string 'JONES001'
   */
  static function buildUid($lastName) {
    static $lastUid;
    static $index;
    $uid = substr($lastName, 0, 5);
    if ($uid != $lastUid) {
      $lastUid = $uid;
      $index = 0;
    } else { 
      $index++;
    }
    return str_pad($uid, 5, '0') . str_pad($index, 3, '0', STR_PAD_LEFT);
  }
}
/**
 * ClientRec Client_Import
 */
class Client_Import extends ClientRec implements ReadOnly {
  //
  public $clientId;
  public $userGroupId;
  public $uid;
  public $lastName;
  public $firstName;
  public $sex;
  public $birth;
  public $img;
  public $dateCreated;
  public $active;
  public $cdata1;
  public $cdata2;
  public $cdata3;
  public $trial;
  public $livingWill;
  public $poa;
  public $gestWeeks;
  public $middleName;
  public $notes;
  public $dateUpdated;
  //
  public function getSql() {
    return $this->getSqlInsert() . ";";
  }
  public function isInvalid() {
    if (empty($this->uid))
      return 'Missing UID';
    if (empty($this->userGroupId))
      return 'Missing UGID';
    if (empty($this->sex))
      return 'Missing sex';
    if (empty($this->lastName))
      return 'Missing last name';
    if (empty($this->firstName))
      return 'Missing first name';
    if (empty($this->birth))
      return 'Missing DOB';
  }
  //
  static function fromCsv($ugid, $uid, $last, $first, $middle, $sex, $birth, $cdata1 = null, $cdata2 = null, $cdata3 = null, $notes = null) {
    $rec = new self();
    $rec->userGroupId = $ugid;
    $rec->uid = $uid;
    $rec->lastName = $last;
    $rec->firstName = $first;
    $rec->middleName = $middle;
    $rec->sex = $sex;
    $rec->setBirth($birth);
    $rec->cdata1 = $cdata1;
    $rec->cdata2 = $cdata2;
    $rec->cdata3 = $cdata3;
    $rec->notes = $notes;
    $rec->active = true;
    return $rec;
  }
}
/**
 * AddressRec Address_Import
 */
class Address_Import extends AddressRec implements ReadOnly {
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
  public $email3;
  //
  /**
   * @param Client_Import $client
   */
  public function getSql($client) {
    $ugid = $client->userGroupId;
    $uid = addslashes($client->uid);
    $values = $this->getSqlValues();
    $values[2] = 'client_id';
    $values = implode(',', $values);
    $sql = "INSERT INTO addresses SELECT $values FROM clients WHERE user_group_id=$ugid AND uid='$uid';";
    return $sql; 
  }
  //
  static function fromCsv($addr1, $addr2, $addr3, $city, $state, $zip, $primaryPhone = null, $otherPhone = null, $email = null) {
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
    if ($primaryPhone)
      $rec->setPrimaryPhone($primaryPhone);
    if ($otherPhone)
      $rec->setPhone2(self::PHONE_TYPE_OTHER, $otherPhone);
    if ($email) 
      $rec->email1 = $email; 
    return $rec;
  }
}
/**
 * Exceptions
 */
class CsvImportException extends Exception {}
?>