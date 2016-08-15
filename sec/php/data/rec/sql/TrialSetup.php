<?php
require_once 'php/data/rec/sql/_SqlRec.php';
require_once 'php/data/http/IpLookup.php';
//
/**
 * User Registrations DAO
 * @author Warren Hornsby 
 */
class TrialSetup {
  //
  const TRIAL_UGID = 73;
  //
  /**
   * @param int $ugid
   * @param int $userId
   */
  static function setup($ugid, $userId) { 
    try {
      Client_Setup::copyAll(static::TRIAL_UGID, $ugid, $userId);
    } catch (Exception $e) {
      // TODO: notify us about exception (since we're eating it to keep trial going)
    }
  }
  /**
   * @param int $ugid
   * @param int $userId
   * @return int number of records left to copy 
   */
  static function setupOne($ugid, $userId) {
    try {
      $left = Client_Setup::copyOne(static::TRIAL_UGID, $ugid, $userId);
      return $left;
    } catch (Exception $e) {
      // TODO: notify us about exception (since we're eating it to keep trial going)
      return 0;
    }
  }
  /**
   * @param LoginSession $login
   */
  static function sendAdminEmail($login) {
    $ip = IpLookup::fetch($login->Login->ipAddress);
    Email_TrialNotification::send($login, $ip);
  }
}
/**
 * Trial Setup Records
 */
abstract class SqlRec_Setup extends SqlRec {
  //
  public function save_asCopy($ugid, $userId, $cid = null) {
    $this->setKeys($ugid, $userId, $cid);
    $this->save();
    $this->copyChildren($userId);
  }
  public function copyChildren($userId) {
    // if needed
  }
  public function setKeys($ugid, $userId, $cid) {
    $this->_origPk = $this->getPkValue();
    $this->setPkValue(null);
    $this->setUserGroupId($ugid);
    $this->setClientId($cid);
    $this->setUserIds($userId);
  }
  public function setUserGroupId($ugid) {
    $this->_origUgid = $this->userGroupId;
    $this->userGroupId = $ugid;
  }
  public function setClientId($cid) {
    $this->_origCid = $this->clientId;
    $this->clientId = $cid;
  }
  public function setUserIds($userId) {
    // assign any userId references if needed
  }
  public function setUserId($fid, $userId) {
    if (isset($this->$fid))
      $this->$fid = $userId;
  }
  //
  static function copyAll($client, $userId) {
    $recs = static::fetchAll($client->_origPk);
    foreach ($recs as $rec)
      $rec->save_asCopy($client->userGroupId, $userId, $client->clientId);
  }
  //
  static function fetchAll($cid) {
    $c = static::asCriteria($cid);
    return static::fetchAllBy($c);
  }
  static function asCriteria($cid) {
    $c = new static();
    $c->clientId = $cid;
    return $c;
  }
}
//
class Client_Setup extends SqlRec_Setup implements NoAuthenticate {
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
  public $emrId;
  public /*Address_Setup[]*/ $Addresses;
  public /*ICard_Setup[]*/ $ICards;
  //
  public function getSqlTable() {
    return 'clients';
  }
  public function save_asCopy($ugid, $userId, $cid = null) {
    $this->trial = 1;
    parent::save_asCopy($ugid, $userId, $cid);
  } 
  public function copyChildren($userId) {
    Address_Setup::copyAll($this, $userId);
    ICard_Setup::copyAll($this, $userId);
    Sched_Setup::copyAll($this, $userId);
    Session_Setup::copyAll($this, $userId);
    Allergy_Setup::copyAll($this, $userId);
    Diagnosis_Setup::copyAll($this, $userId);
    Immun_Setup::copyAll($this, $userId);
    Med_Setup::copyAll($this, $userId);
    DataSync_Setup::copyAll($this, $userId);
    Vital_Setup::copyAll($this, $userId);
    TrackItem_Setup::copyAll($this, $userId);
  }
  //
  static function copyAll($fromUgid, $toUgid, $userId) {
    if (static::count_for($toUgid) == 0) {
      $recs = static::fetchAll($fromUgid);
      foreach ($recs as $rec)
        $rec->save_asCopy($toUgid, $userId);
    }
  }
  static function copyOne($fromUgid, $toUgid, $userId) {
    $fromCt = static::count_for($toUgid);
    $toCt = static::count_for($fromUgid);
    if ($fromCt < $toCt) {
      $recs = static::fetchAll($fromUgid);
      $rec = $recs[$fromCt];
      $rec->save_asCopy($toUgid, $userId);
      $fromCt++;      
    }
    return $toCt - $fromCt;  // records left to copy
  }
  static function fetchAll($ugid) {
    $c = static::asCriteria($ugid);
    $recs = static::fetchAllBy($c, new RecSort('clientId'));
    return $recs;
  }
  static function count_for($ugid) {
    $c = static::asCriteria($ugid);
    return static::count($c);
  }
  static function asCriteria($ugid) {
    $c = new static();
    $c->userGroupId = $ugid;
    return $c;
  }
}
class ICard_Setup extends SqlRec_Setup implements NoAuthenticate {
  public $clientId;
  public $seq;
  public $planName;
  public $subscriberName;
  public $nameOnCard;
  public $groupNo;
  public $subscriberNo;
  public $dateEffective;
  public $active;
  //
  public function getSqlTable() {
    return 'client_icards';
  }
  public function getPkValue() {
    return null;
  }
  public function setPkValue() {
    // none
  }
  public function setUserGroupId($ugid) {
    // no ugid
  }
}
class Address_Setup extends SqlRec_Setup implements NoAuthenticate {
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
  public function getSqlTable() {
    return 'addresses';
  }
  public function setUserGroupId($ugid) {
    // no ugid
  }
  public function setClientId($cid) {
    $this->tableId = $cid;
  }
  //
  static function asCriteria($cid) {
    $c = new static();
    $c->tableCode = 'C';
    $c->tableId = $cid;
    return $c;
  }
}
class Sched_Setup extends SqlRec_Setup implements NoAuthenticate {
  public $schedId;
  public $userId;  
  public $userGroupId;
  public $clientId;
  public $date;
  public $timeStart;
  public $duration;
  public $closed;
  public $status;
  public $comment;
  public $type;
  public $timestamp;
  public $schedEventId;
  //
  public function getSqlTable() {
    return 'scheds';
  }
  public function setUserIds($userId) {
    $this->setUserId('userId', $userId);
  }
}
class Proc_Setup extends SqlRec_Setup implements NoAuthenticate, AutoEncrypt {
  public $procId;
  public $userGroupId;
  public $clientId;
  public $date;  
  public $ipc;
  public $priority;
  public $location;
  public $providerId;
  public $addrFacility;
  public $recipient;
  public $reviewed;
  public $comments;
  //
  public function getSqlTable() {
    return 'procedures';
  }
  public function getEncryptedFids() {
    return array('comments');
  }
  public function setKeys($ugid, $userId, $cid) {
    parent::setKeys($ugid, $userId, $cid);
    $this->provderId = null;
    $this->addrFacility = null;
  }
  public function copyChildren($userId) {
    ProcResult_Setup::copyAll($this, $userId);
  } 
}
class ProcResult_Setup extends SqlRec_Setup implements NoAuthenticate, AutoEncrypt {
  public $procResultId;
  public $clientId;
  public $procId;
  public $seq;
  public $date;
  public $ipc;
  public $value;
  public $valueUnit;
  public $range;
  public $interpretCode;
  public $comments;
  //
  public function getSqlTable() {
    return 'proc_results';
  }
  public function getEncryptedFids() {
    return array('comments');
  }
  public function save_asCopy($ugid, $userId, $cid, $procId) {
    $this->setKeys($ugid, $userId, $cid);
    $this->procId = $procId;
    $this->save();
  }
  //
  static function copyAll($proc, $userId) {
    $recs = static::fetchAll($proc->_origCid);
    foreach ($recs as $rec) 
      $rec->save_asCopy($proc->userGroupId, $userId, $proc->clientId, $proc->procId);
  }
}
class Session_Setup extends SqlRec_Setup implements NoAuthenticate {
  public $sessionId;
  public $userGroupId;
  public $clientId;
  public $templateId;
  public $dateCreated;
  public $dateUpdated;
  public $dateService;
  public $closed;
  public $closedBy;
  public $dateClosed;
  public $billed;
  public $schedId;
  public $data;
  public $createdBy;
  public $updatedBy;
  public $sendTo;
  public $assignedTo;
  public $html;
  public $title;
  public $standard;
  public $noteDate;
  //
  public function getSqlTable() {
    return 'sessions';
  }
  public function setUserIds($userId) {
    $this->setUserId('closedBy', $userId); 
    $this->setUserId('createdBy', $userId); 
    $this->setUserId('updatedBy', $userId); 
    $this->setUserId('sendTo', $userId); 
    $this->setUserId('assignedTo', $userId); 
  }
  public function copyChildren($userId) {
    Allergy_Setup::copyAll_forSession($this, $userId);
    Diagnosis_Setup::copyAll_forSession($this, $userId);
    Immun_Setup::copyAll_forSession($this, $userId);
    Med_Setup::copyAll_forSession($this, $userId);
    DataSync_Setup::copyAll_forSession($this, $userId);
    Vital_Setup::copyAll_forSession($this, $userId);
    TrackItem_Setup::copyAll_forSession($this, $userId);
  }
}
/**
 * Data (facesheet) records
 */
abstract class DataRec_Setup extends SqlRec_Setup {
  //
  public function save_asCopy($ugid, $userId, $cid, $sid = null) {
    $this->setKeys($ugid, $userId, $cid);
    $this->sessionId = $sid;
    $this->save();
  }
  //
  static function copyAll_forSession($session, $userId) {
    $c = static::asCriteria_forSession($session);
    $recs = static::fetchAllBy($c);
    foreach ($recs as $rec)
      $rec->save_asCopy($session->userGroupId, $userId, $session->clientId, $session->sessionId);
  }
  static function asCriteria_forSession($session) {
    $c = new static();
    $c->clientId = $session->_origCid;
    $c->sessionId = $session->_origPk;
    return $c;
  }
  static function asCriteria($cid) {
    $c = new static();
    $c->clientId = $cid;
    $c->sessionId = CriteriaValue::isNull();
    return $c;
  }
}
class Allergy_Setup extends DataRec_Setup implements NoAuthenticate {
  public $dataAllergyId;
  public $userGroupId;
  public $clientId;
  public $sessionId;
  public $date;  
	public $index;
	public $agent;
	public $reactions;
	public $active;   
	public $dateUpdated;
	public $source;
  //
	public function getSqlTable() {
    return 'data_allergies';
  }
}
class Diagnosis_Setup extends DataRec_Setup implements NoAuthenticate {
  public $dataDiagnosesId;
  public $userGroupId;
  public $clientId;
  public $sessionId;
  public $date;  
  public $parUid;
  public $text;
  public $parDesc;
  public $icd;
  public $active;
  public $dateUpdated;
  public $dateClosed;
  public $status;
  //
  public function getSqlTable() {
    return 'data_diagnoses';
  }
}
class Immun_Setup extends DataRec_Setup implements NoAuthenticate {
  public $dataImmunId;
  public $userGroupId;
  public $clientId;
  public $sessionId;
  public $dateGiven;
  public $name;
  public $tradeName;
  public $manufac;
  public $lot;
  public $dateExp;
  public $dateVis;
  public $dateVis2;
  public $dateVis3;
  public $dateVis4;
  public $dose;
  public $route;
  public $site;
  public $adminBy;
  public $comment;
  public $dateUpdated;
  public $formVis;
  public $formVis2;
  public $formVis3;
  public $formVis4;
  //
  public function getSqlTable() {
    return 'data_immuns';
  }
}
class Med_Setup extends DataRec_Setup implements NoAuthenticate {
  public $dataMedId;
  public $userGroupId;
  public $clientId;
  public $sessionId;
  public $date;  
	public $quid;
	public $index;
	public $name;
	public $amt;
	public $freq;
	public $asNeeded;
	public $meals;
	public $route;
	public $length;
	public $disp;
	public $text;
	public $rx;
	public $active;   
	public $expires;
	public $dateUpdated;
	public $source;
	public $ncDrugName;
	public $ncGenericName;
	public $ncRxGuid;
	public $ncOrderGuid;
	public $ncOrigrxGuid;
	public $ncDosageNum;
	public $ncDosageForm;
	public $ncDosageNumId;
	public $ncDosageFormId;
	public $ncRouteId;
	public $ncFreqId;
  //
	public function getSqlTable() {
    return 'data_meds';
  }
}
class DataSync_Setup extends DataRec_Setup implements NoAuthenticate {
  //
  public $dataSyncId;
  public $userGroupId;
  public $clientId;
  public $dsyncId;
  public $dsync;
  public $dateSort;
  public $sessionId;
  public $value;
  public $active;
  public $dateUpdated;
  //
  public function getSqlTable() {
    return 'data_syncs';
  }
}
class Vital_Setup extends DataRec_Setup implements NoAuthenticate {
  public $dataVitalsId;
  public $userGroupId;
  public $clientId;
  public $sessionId;
  public $date;  
  public $pulse;
  public $resp;
  public $bpSystolic;
  public $bpDiastolic;
  public $bpLoc;
  public $temp;
  public $tempRoute;
  public $wt;
  public $wtUnits;
  public $height;
  public $hc;
  public $hcUnits;
  public $wc;
  public $wcUnits;
  public $o2Sat;
  public $o2SatOn;
  public $bmi;
  public $htUnits;
  public $dateUpdated;
  public $active;
  public $wtLbs;
  public $htIn;
  //
  public function getSqlTable() {
    return 'data_vitals';
  }
}
class TrackItem_Setup extends DataRec_Setup implements NoAuthenticate {
  public $trackItemId;
  public $userGroupId;
  public $clientId;
  public $sessionId;
  public $key;
  public $userId;
  public $priority;
  public $trackCat;
  public $trackDesc;
  public $cptCode;
  public $status;  
  public $orderDate;
  public $orderBy;
  public $orderNotes;
  public $schedDate;
  public $schedWith;
  public $schedLoc;
  public $schedBy;
  public $schedNotes;
  public $closedDate;
  public $closedFor;
  public $closedBy;
  public $closedNotes;
  public $diagnosis;
  public $icd;
  public $freq;
  public $duration;
  //
  public function getSqlTable() {
    return 'track_items';
  }
  public function setUserIds($userId) {
    $this->setUserId('userId', $userId); 
    $this->setUserId('orderBy', $userId); 
    $this->setUserId('schedBy', $userId); 
    $this->setUserId('closedBy', $userId); 
  }
}
class Email_TrialNotification extends Email_Admin {
  //
  public $subject = 'New Trial Account';
  //
  /**
   * @param LoginSession $loginSession
   * @param IpLookup $ip
   */
  static function send($loginSession, $ip) {
    $login = $loginSession->Login;
    $user = $loginSession->User;
    $phone = $user->UserGroup->Address->phone1;
    $e = new static();
    $e->html()
      ->p('LOGIN INFO')
      ->ul_()
        ->li("Time: $login->time")
        ->li("UID: $login->uid")
        ->li("Name: $user->name")
        ->li("License: $user->licenseState $user->license")
        ->li("Email: $user->email")
        ->li("Phone: $phone")
      ->_()
      ->p("IP INFO")
      ->ul_()
        ->li("IP: $ip->ip")
        ->li("Country: $ip->country")
        ->li("State/Region: $ip->region")
        ->li("City: $ip->city")
        ->_();
      $e->mail();
  }
}
