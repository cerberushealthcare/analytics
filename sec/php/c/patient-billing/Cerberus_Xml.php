<?php
require_once 'php/data/xml/_XmlRec.php';
require_once 'php/data/curl/PostFormQuery.php';
require_once 'php/c/patient-billing/Cerberus_Exception.php';
//
class CerberusXmlQuery extends PostFormQuery {
  //
  public $xmlin;
  public $agency;
  public $operation;
  public $username;
  public $password;
  //
  public function submit() {
    $response = parent::submit(static::getUrl());
    $data = static::parse($response->body);
    return $data;
  }
  //
  static function create($agency, $op, $user, $pass, $xml) {
    $me = new static($xml, $agency, $op, $user, $pass);
    return $me;
  }
  static function getUrl() {
    return "https://www.papyrus-pms.com/clicktate/clicktatepkg.clicktate_api";
  }
  //
  protected function parse($body) {
    $a = explode('<BODY>', $body);
    $a = explode('<br', $a[1]);
    $a = explode(',', $a[0]);
    $status = trim($a[0]);
    $data = null;
    if (count($a) > 1) {
      $a = explode('</BODY', $a[1]);
      $data = trim($a[0]);
    }
    switch ($status) {
      case 'OK':
        return $data;
      case 'ERROR':
        throw new CerberusErrorResponse($data);
      default:
        throw new CerberusBadResponse($body);
    }
  }
}
//
class CXQ_Patient extends CerberusXmlQuery {
  //
  const OP = 'PATIENT';
  //
  public function submit() {
    $patientId = parent::submit();
    return $patientId;
  }
  //
  static function create($agency, $user, $pass, /*Client_Cb*/$client) {
    $xml = CR_Patient::from($client)->toString();
    return parent::create($agency, static::OP, $user, $pass, $xml);
  }
}
abstract class CerberusRowset {
  //
  public function toString() {
    $rec = $this->asXml();
    $xml = $rec->toXml_compressed_noHyphenate('ROW');
    return "<ROWSET>$xml</ROWSET>";
  }
  public function asXml() {
    $rec = new XmlRec();
    foreach ($this as $var => $obj) {
      if ($obj)
        foreach ($obj as $fid => $value)
          $rec->$fid = $value;
    }
    return $rec;
  }
}
class CR_Patient extends CerberusRowset {
  //
  public /*CS_PatientId*/$PD;
  public /*CS_PersonInfo*/$PI;
  public /*CS_PersonContact*/$PC;
  public /*CS_PersonSupport*/$PS;
  //
  static function from(/*Client_Cb*/$client) {
    $me = new static();
    $me->PD = CS_PatientId::from($client);
    $me->PI = CS_PersonInfo::from($client);
    $me->PC = CS_PersonContact::asHome($client);
    $me->PS = CS_PersonSupport::asEmergency($client);
    return $me;
  }
}
//
abstract class CerberusSegment extends XmlRec {
  //
  static function formatDate($date) {
    return date("m/d/Y", strtotime($date));
  }
  static function yn($bool) {
    return $bool ? 'Y' : 'N';
  }
}
class CS_PatientId extends CerberusSegment {
  //
  public $PD_FIRSTNAME;
  public $PD_LASTNAME;
  public $PD_DOB;
  public $PD_GENDER;
  public $PD_GOVT_ID;
  public $PD_PRACTICE_MRN;
  public $PD_EXTERNAL_UNIQUE_ID;  /* Cerberus identifier */ 
  public $PD_DATA_SOURCE_ID;
  //
  static function from(/*Client_Cb*/$client) {
    return new static(
      $client->firstName,
      $client->lastName,
      static::formatDate($client->birth),
      $client->sex,
      null,
      $client->uid,  
      $client->_externalId,
      $client->clientId);
  }
}
class CS_PersonInfo extends CerberusSegment {
  //
  public $PI_FIRSTNAME;
  public $PI_LASTNAME;
  public $PI_MI;
  public $PI_PREFIX;
  public $PI_SUFFIX;
  public $PI_DOB;
  public $PI_GENDER;
  public $PI_MARITAL_STATUS;
  public $PI_RELIGION;
  public $PI_RACE;
  public $PI_ETHNICITY;
  public $PI_GOVT_ID;
  public $PI_DOD;
  public $PI_ACTIVE;
  public $PI_PRIMARY_PHYS;
  public $PI_RELEASE_TO;
  public $PI_RELEASE_PREF;
  public $PI_RELEASE_DATA;
  public $PI_LIVING_WILL;
  public $PI_POWER_ATTORNEY;
  public $PI_HIPPA_ON_FILE;
  public $PI_HIE_AUTHORIZED;
  public $PI_PHARM_NAME;
  public $PI_PHARM_STREET1;
  public $PI_PHARM_STREET2;
  public $PI_PHARM_CITY;
  public $PI_PHARM_STATE;
  public $PI_PHARM_ZIP;
  public $PI_PHARM_PHONE;
  public $PI_PHARM_FAX;
  public $PI_DATA_SOURCE_ID;
  //
  static function from(/*Client*/$client) {
    $me = new static();
    $me->PI_FIRSTNAME = $client->firstName;
    $me->PI_LASTNAME = $client->lastName;
    $me->PI_MI = $client->getMiddleInitial();
    $me->PI_DOB = static::formatDate($client->birth);
    $me->PI_GENDER = $client->sex;
    $me->PI_RACE = $client->race;
    $me->PI_ETHNICITY = $client->ethnicity;
    $me->PI_ACTIVE = static::yn($client->active);
    $me->PI_RELEASE_TO = $client->familyRelease;
    $me->PI_RELEASE_PREF = $client->releasePref;
    $me->PI_RELEASE_DATA = $client->release;
    $me->PI_LIVING_WILL = static::yn($client->livingWill);
    $me->PI_POWER_ATTORNEY = static::yn($client->poa);
    $me->PI_DATA_SOURCE_ID = $client->clientId;
    return $me;
  }
}
class CS_PersonContact extends CerberusSegment {
  //
  public $PC_ADDRESS_TYPE1;
  public $PC_ADDR1_STREET1;
  public $PC_ADDR1_STREET2;
  public $PC_CITY1;
  public $PC_STATE1;
  public $PC_ZIPCODE1;
  public $PC_COUNTRY1;
  public $PC_PHONE1;
  public $PC_PHONE1_USE_TYPE;
  public $PC_PHONE2;
  public $PC_PHONE2_USE_TYPE;
  public $PC_PHONE3;
  public $PC_PHONE3_USE_TYPE;
  public $PC_EMAIL;
  public $PC_DATA_SOURCE_ID;
  //
  static function asHome(/*Client*/$client) {
    if (isset($client->Address_Home))
      return static::from($client->Address_Home);    
  }
  static function from(/*Address*/$addr) {
    if (! isset($addr->_empty))
      return new static(
        static::getAddrType($addr->type),
        $addr->addr1,
        $addr->addr2,
        $addr->city,
        $addr->state,
        $addr->zip,
        $addr->country,
        $addr->phone1,
        static::getPhoneType($addr->phone1Type),
        $addr->phone2,
        static::getPhoneType($addr->phone2Type),
        $addr->phone3,
        static::getPhoneType($addr->phone3Type),
        $addr->email1,
        $addr->addressId);
  }
  static function getAddrType($type) {
    switch ($type) {
      case AddressRec::TYPE_SHIP:
        return 'HOME';
      case AddressRec::TYPE_WORK:
        return 'WORK';
    }
  }
  static function getPhoneType($type) {
    switch ($type) {
      case AddressRec::PHONE_TYPE_PRIMARY:
        return 'HOME';
      case AddressRec::PHONE_TYPE_WORK:
        return 'WORK';
      case AddressRec::PHONE_TYPE_CELL:
        return 'CELL';
    }
  }
}
class CS_PersonSupport extends CerberusSegment {
  //
  public $PS_CONTACT_TYPE;
  public $PS_CONTACT_LASTNAME;
  public $PS_CONTACT_FIRSTNAME;
  public $PS_PHONE1;
  public $PS_DATA_SOURCE_ID;
  //
  static function asEmergency(/*Client*/$client) {
    if (isset($client->Address_Emergency)) 
      return static::from($client->Address_Emergency, 'EMERGENCY');
  }
  static function from(/*Address*/$addr, $type) {
    if (! isset($addr->_empty))
      return new static(
        $type,
        $addr->name,
        null,
        $addr->phone1,
        $addr->addressId);
  }
}