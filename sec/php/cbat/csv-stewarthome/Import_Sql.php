<?php
require_once 'php/data/rec/sql/_ClientRec.php';
require_once 'php/data/rec/sql/_ICardRec.php';
require_once 'php/data/rec/sql/_AddressRec.php';
//
class Client_Import extends ClientRec implements ReadOnly, AutoEncrypt {
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
  public /*Address_Import*/$Address_Home;
  public /*ICard_Import*/$ICard1;
  public /*ICard_Import*/$ICard2;
  public /*Hd_CDob_Import*/$Hd_Dob;
  public /*Hd_CName_Import*/$Hd_Name;
  public /*CsvRec*/$_source;
  //
  public function getEncryptedFids() {
    return array();
    return array('lastName','firstName','middleName','cdata1','cdata2','cdata3','notes','familyRelease','release');
  }
  public function toString() {
    $sql = $this->getSqlInsert() . ";";
    if (isset($this->Address_Home))
      $sql .= "\n" . $this->Address_Home->toString($this);
    if (isset($this->ICard1))
      $sql .= "\n" . $this->ICard1->toString($this);
    if (isset($this->ICard2))
      $sql .= "\n" . $this->ICard2->toString($this);
    if (isset($this->Hd_Dob))
      $sql .= "\n" . $this->Hd_Dob->toString($this);
    if (isset($this->Hd_Name))
      $sql .= "\n" . $this->Hd_Name->toString($this);
    return $sql;
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
  static function from(/*CsvRec*/$csv, $ugid, $uid, $last, $first, $middle, $sex, $birth, $cdata1 = null, $cdata2 = null, $cdata3 = null, $notes = null) {
    $rec = new static();
    $rec->userGroupId = $ugid;
    if (empty($uid)) 
      $uid = static::buildUid();
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
    $rec->_source = $csv;
    $rec->Hd_Dob = Hd_CDob_Import::from($rec);
    $rec->Hd_Name = Hd_CName_Import::from($rec);
    return $rec;
  }
  static function extractFallouts(&$recs) {
    $fallouts = array();
    $good = array();
    foreach ($recs as $rec) {
      $reason = $rec->isInvalid();
      if ($reason) 
        $fallouts[] = $rec->_source;  //->toString() . ",$reason";
      else
        $good[] = $rec;
    }
    $recs = $good;
    return $fallouts;
  }
  protected static function buildUid() {
    static $ct = 0;
    $ct++;
    return 'A' . str_pad($ct, 5, '0', STR_PAD_LEFT);
  }
  protected static function buildUidFromName($lastName) {
    static $uids;
    $uid = strtoupper(substr($lastName, 0, 5));
    if ($uids == null)
      $uids = array();
    if (isset($uids[$uid])) 
      $index = $uids[$uid];
    else 
      $index = 0;
    $uids[$uid] = $index + 1;
    return str_pad($uid, 5, '0') . str_pad($index, 3, '0', STR_PAD_LEFT);
  }  
}
class Hd_CDob_Import extends Hdata_ClientDob  {
  //
  public function toString($client) {
    $ugid = $client->userGroupId;
    $uid = $client->uid;
    $table = static::$TABLE;
    $type = static::$TYPE;
    $hk = MyCrypt_Auto::getHashKey();
    $source = "SHA1(CONCAT('$ugid','$table','$type',client_id,'$hk'))";
    $data = $this->data;
    $sql = "INSERT INTO hdata SELECT $source,$data FROM clients WHERE user_group_id=$ugid AND uid='$uid';";
    return $sql; 
  }
}
class Hd_CName_Import extends Hdata_ClientName {
  //
  public function toString($client) {
    $ugid = $client->userGroupId;
    $uid = $client->uid;
    $table = static::$TABLE;
    $type = static::$TYPE;
    $hk = MyCrypt_Auto::getHashKey();
    $source = "SHA1(CONCAT('$ugid','$table','$type',client_id,'$hk'))";
    $data = $this->data;
    $sql = "INSERT INTO hdata SELECT $source,$data FROM clients WHERE user_group_id=$ugid AND uid='$uid';";
    return $sql; 
  }
}
class Address_Import extends AddressRec implements ReadOnly, AutoEncrypt {
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
  public function getEncryptedFids() {
    return array();
    return array('addr1','addr2','addr3','city','zip','phone1','phone2','phone3','email1','email2','name');
  }
  public function toString($client) {
    $ugid = $client->userGroupId;
    $uid = addslashes($client->uid);
    $values = $this->getSqlValues();
    $values[2] = 'client_id';
    $values = implode(',', $values);
    $sql = "INSERT INTO addresses SELECT $values FROM clients WHERE user_group_id=$ugid AND uid='$uid';";
    return $sql; 
  }
  //
  static function from($addr1, $addr2, $addr3, $city, $state, $zip, $primaryPhone = null, $otherPhone = null, $email = null) {
    $rec = new static();
    $rec->tableCode = static::TABLE_CLIENTS;
    $rec->tableId = null;
    $rec->type = static::TYPE_SHIP;
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
      $rec->setPhone2(static::PHONE_TYPE_OTHER, $otherPhone);
    if ($email) 
      $rec->email1 = $email; 
    return $rec;
  }
}
class ICard_Import extends ICardRec implements ReadOnly {
  //
  public $clientId;
  public $seq;
  public $planName;
  public $subscriberName;
  public $nameOnCard;
  public $groupNo;
  public $subscriberNo;
  public $dateEffective;
  public $active;
  public $external;
  //
  public function toString($client) {
    $ugid = $client->userGroupId;
    $uid = addslashes($client->uid);
    $values = $this->getSqlValues();
    $values[0] = 'client_id';
    $values = implode(',', $values);
    $sql = "INSERT INTO client_icards SELECT $values FROM clients WHERE user_group_id=$ugid AND uid='$uid';";
    return $sql; 
  }
  public function getEncryptedFids() {
    return array();
  }
  //
  static function from($seq, $planName, $subscriberName, $nameOnCard, $groupNo, $subscriberNo, $dateEffective = null) {
    return new static(
      null, 
      $seq, 
      $planName,
      $subscriberName, 
      $nameOnCard, 
      $groupNo, 
      $subscriberNo, 
      $dateEffective, 
      true);
  }
}