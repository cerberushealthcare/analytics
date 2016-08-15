<?php
set_include_path('../sec/');
require_once 'php/data/rec/sql/_ClientRec.php';
require_once 'php/data/rec/sql/_AddressRec.php';
require_once '../api/dao/data/Api.php';
require_once '../api/dao/data/ApiAddress.php';
require_once '../api/dao/data/ApiInsurance.php';
//
/**
 * Patient
 */
class ApiPatient extends Api {
  //
  public $practiceId;
  public $patientId;
  public $patientCode;
  public $lastName;
  public $firstName;
  public $midName;
  public $gender;
  public $birth;
  public $custom1;
  public $custom2;
  public $custom3;
  public $note;
  public $active;
  public $race;
  public $ethnicity;
  public $language;
  public $familyRelease;
  public $releasePref;
  public $releaseData;
  public $livingWill;
  public $poa;
  public $primaryPhys;
  public $nickname;
  public $dod;
  public $externalId/*cid*/;
  //
  public $_insurancePrimary;    // ApiInsurance
  public $_insuranceSecondary;  // ApiInsurance
  //
  public $_addressPrimary;    // ApiAddress
  public $_addressEmergency;  // ApiAddress
  public $_addressRx;         // ApiAddress
  /**
   * Constructor
   * @param ['field'=>value,..] $data
   */
  public function __construct($data) {
    $required = array('practiceId','patientId','patientCode','lastName','firstName','gender','birth');
    $this->load($data, $required);
    $this->_insurancePrimary = new ApiInsurance($data, 'inspri');
    $this->_insuranceSecondary = new ApiInsurance($data, 'inssec');
    $this->_addressPrimary = new ApiAddress($data, 'primary');
    $this->_addressEmergency = new ApiAddress($data, 'emergency');
    $this->_addressRx = new ApiAddress($data, 'rx');
  }
  /**
   * Address builders
   */
  public function saveAddressPrimary($cid, $client) {
    $address = Address_Api::from_asPrimary($this->_addressPrimary, $cid, $client->Address_Home);
    if ($address)
      $address->save();
  }
  public function saveAddressEmergency($cid, $client) {
    $address = Address_Api::from_asEmergency($this->_addressEmergency, $cid, $client->Address_Emer);
    if ($address)
      $address->save();
  }
  public function saveAddressRx($cid, $client) {
    $address = Address_Api::from_asRx($this->_addressRx, $cid, $client->Address_Rx);
    if ($address)
      $address->save();
  }
  /**
   * ICard builders
   */
  public function saveICardPrimary($cid, $active = true) {
    $icard = ICard_Api::from_asPrimary($this->_insurancePrimary, $cid, $active);
    if ($icard)
      $icard->save();
  }
  public function saveICardSecondary($cid, $active = true) {
    $icard = ICard_Api::from_asSecondary($this->_insuranceSecondary, $cid, $active);
    if ($icard)
      $icard->save();
  }
  //
  public function getUserUid() {
    return $this->practiceId . '_' . $this->primaryPhys;
  }
  /**
   * Static constructor from CLIENT
   * $param Client_Api $client
   * @return ApiPatient
   */
  public static function fromClient($client) {
    $data = array(
      'practiceId' => $client->userGroupId,
      'patientId' => $client->clientId,
      'patientCode' => $client->uid,
      'lastName' => $client->lastName,
      'firstName' => $client->firstName,
      'midName' => $client->middleName,
      'nickname' => $client->nickName,
      'gender' => $client->sex,
      'birth' => Data::ymd($client->birth),
      'dod' => Data::ymd($client->deceased),
      'custom1' => $client->cdata1,
      'custom2' => $client->cdata2,
      'custom3' => $client->cdata3,
      'note' => $client->notes,
      'active' => Data::bool($client->active),
      'language' => $client->language,
      'familyRelease' => $client->familyRelease,
      'releasePref' => $client->releasePref,
      'releaseData' => $client->release,
      'livingWill' => $client->livingWill,
      'primaryPhys' => $client->primaryPhys,
    	'poa' => $client->poa);
    $patient = new ApiPatient($data);
    $patient->_insurancePrimary = ApiInsurance::fromICard(Data::get($client->Icards, 0));
    $patient->_insuranceSecondary = ApiInsurance::fromICard(Data::get($client->Icards, 1));
    $patient->_addressPrimary = ApiAddress::fromAddress($client->Address_Home);
    $patient->_addressEmergency = ApiAddress::fromAddress($client->Address_Emergency);
    $patient->_addressRx = ApiAddress::fromAddress($client->Address_Rx);
    return $patient;
  }
}
/**
 * SqlRecs
 */
class Client_Api extends ClientRec {
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
  public $race;
  public $ethnicity;
  public $deceased;
  public $language;
  public $familyRelease;
  public $primaryPhys;
  public $releasePref;
  public $release;
  public $userRestricts;
  public $huid;
  public $emrId;
  public $nickName;
  public /*Address_Api*/ $Address_Home;
  public /*Address_Api*/ $Address_Emer;
  public /*Address_Api*/ $Address_Rx;
  public /*ICard_Api[]*/ $ICards;
  //
  public function attachAll($cid) {
    $this->Address_Home = ClientAddress::fetchHome($cid);
    $this->Address_Emer = ClientAddress::fetchEmergency($cid);
    $this->Address_Rx = ClientAddress::fetchRx($cid);
    $this->ICards = ICard_Api::fetchAllByClient($cid);
  }
  static function from(/*ApiPatient*/ $api, $ugid, $idPhys, $cid = null) {
    if ($cid) {
      $me = parent::fetch($cid);
    } else {
      $me = new static();
      $me->userGroupId = $ugid;
    }
    $me->uid = $api->patientCode;
    $me->lastName = $api->lastName;
    $me->firstName = $api->firstName;
    $me->middleName = $api->midName;
    $me->nickName = $api->nickname;
    $me->sex = $api->gender;
    $me->birth = Data::ymd($api->birth);
    $me->deceased = empty($api->dod) ? null : Data::ymd($api->dod);
    $me->active = $api->active;
    //$me->cdata1 = $api->custom1;
    //$me->cdata2 = $api->custom2;
    //$me->cdata3 = $api->custom3;
    $me->livingWill = $api->livingWill;
    $me->poa = $api->poa;
    //$me->notes = $api->note;
    $me->race = $api->race;
    $me->ethnicity = $api->ethnicity;
    $me->language = $api->language;
    $me->familyRelease = $api->familyRelease;
    $me->releasePref = $api->releasePref;
    $me->release = $api->releaseData;
    $me->primaryPhys = $idPhys;
    return $me;
  }
  static function fetch($cid) {
    $me = parent::fetch($cid);
    if ($me)
      $me->attachAll($cid);
    return $me;
  }
}
