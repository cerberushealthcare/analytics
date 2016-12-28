<?php
require_once 'php/c/patient-entry/PatientEntry.php';
require_once 'php/data/rec/sql/Diagnoses.php';
require_once 'php/data/rec/sql/AllergiesLegacy.php';
require_once 'php/data/rec/sql/Vitals.php';
require_once 'php/data/rec/sql/MedsLegacy.php';
require_once 'php/data/rec/sql/_ImmunRec.php';
require_once 'php/data/rec/sql/_ProcRec.php';
require_once 'php/data/rec/sql/IprocCodes.php';
//
abstract class Client_Ci extends Patient_Add {
  //
  public $clientId;
  public $userGroupId;
  public $uid;
  public $lastName;
  public $firstName;
  public $sex;
  public $birth;
  public $active;
  public $cdata1;
  public $livingWill;
  public $poa;
  public $gestWeeks;
  public $middleName;
  public $race;
  public $ethnicity;
  public $deceased;
  public $language;
  public $familyRelease;
  public $primaryPhys;
  public $huid;
  public /*Address_Ci*/$Address;
  //
  static function create($ugid, $addr) {
    $me = new static();
    $me->userGroupId = $ugid;
    $me->uid = PatientEntry::getNextUid();
    $me->active = true;
    $me->Address = $addr;
    return $me;
  }
  static function fetch($cid, $addrJoin = null) {
    $c = new static($cid);
    $c->Address = CriteriaJoin::optional($addrJoin);
    $me = static::fetchOneBy($c);
    if (get($me, 'Address') == null) {
      $me->Address = $addrJoin;
    }
    return $me;
  }
  //
  public function saveDemo() {
    if ($this->clientId == null)
      $this->throwIfDupeNameBirth();
    parent::save();
    $this->Address->save($this->clientId);
    return $this;
  } 
}
class Address_Ci extends AddressRec {
  //
  public $addressId;
  public $tableCode;
  public $tableId;
  public $type;
  public $addr1;
  public $addr2;
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
  static function create($cid = null) {
    $me = new static();
    $me->setKey($cid);
    return $me;    
  }
  //
  public function save($cid = null) {
    if ($cid)
      $this->setKey($cid);
    return parent::save();
  }
  public function setKey($cid) {
    $this->tableCode = static::TABLE_CLIENTS;
    $this->tableId = $cid;
    $this->type = static::TYPE_SHIP;
  }
}
class Diagnosis_Ci extends Diagnosis {
  //
  public $dataDiagnosesId;
  public $userGroupId;
  public $clientId;
  public $sessionId;
  public $date;
  public $parUid;
  public $text;
  public $parDesc;
  public $icd;
  public $icd10;
  public $active;
  public $dateUpdated;
  public $dateClosed;
  public $status;
  public $snomed;
  public $dateRecon;
  public $reconBy;
  //
  static function create($ugid, $cid, $icd, $date, $text, $status, $snomed) {
    $me = new static();
    $me->userGroupId = $ugid;
    $me->clientId = $cid;
    $me->text = $text ?: 'UNKNOWN';
    if ($me->clientId) {
      $dupe = $me->fetchDupe($date);
      if ($dupe)
        return $dupe;
    }
    $me->date = $date;
    $me->snomed = $snomed;
    $me->icd = $icd;
    $me->status = $status;
    $me->active = $me->isActiveStatus($me->status);
    return $me;
  }
  //
  public function fetchDupe($date) {
    $c = new static($this);
    $c->sessionId = CriteriaValue::isNull();
    $c->Hd_Date = Hdata_DiagnosisDate::join(CriteriaValue::equals($date));
    logit_r($c, 'diag dupe fetch');
    $dupe = static::fetchOneBy($c);
    return $dupe;
  }
}
class Allergy_Ci extends Allergy {
//
  public $dataAllergyId;
  public $userGroupId;
  public $clientId;
  public $sessionId;
  public $date;  
	public $agent;
	public $reactions;
	public $active;   
	public $source;
  //
  static function create($ugid, $cid, $agent, $reactions, $active) {
    $me = new static();
    $me->userGroupId = $ugid;
    $me->clientId = $cid;
    $me->agent = $agent;
    if ($me->clientId)
      $me = $me->fetchDupe(); /*look for existing record after assigning enough fields to identify*/
    $me->reactions = $reactions;
    $me->active = $active; 
    return $me;
  }
  //
  public function fetchDupe() {
    $c = new static($this);
    $c->sessionId = CriteriaValue::isNull();
    $dupe = static::fetchOneBy($c);
    return $dupe ?: $this; 
  }
}
class Med_Ci extends Med {
  //
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
    //
  static function create($ugid, $cid, $date, $name, $text, $active, $amt = null, $freq = null, $route = null) {
    $me = new static();
    $me->userGroupId = $ugid;
    $me->clientId = $cid;
    $me->name = $name;
    if ($me->clientId) {
      $dupe = $me->fetchDupe($date);
      if ($dupe)
        return $dupe;
    }
    $me->date = $date;
    $me->amt = $amt;
    $me->freq = $freq;
    $me->route = $route;
    $me->text = $text ?: $me->formatSig();
    $me->active = $active;
    return $me;
  }
  //
  public function fetchDupe($date) {
    $c = new static($this);
    $c->sessionId = CriteriaValue::isNull();
    // $c->Hd_Date = Hdata_MedDate::join(CriteriaValue::equals($date));
    logit_r($c, 'fetch dupe med');
    $dupe = static::fetchOneBy($c);
    return $dupe; 
  }
} 
class Immun_Ci extends ImmunRec {
  //
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
  public $dose;
  public $route;
  public $site;
  public $adminBy;
  public $comment;
  //
  static function create($ugid, $cid, $dateGiven, $name) {
    $me = new static();
    $me->userGroupId = $ugid;
    $me->clientId = $cid;
    $me->dateGiven = $dateGiven ?: formatAsUnknownDate();
    $me->name = $name;
    if ($me->clientId)
      $me = $me->fetchDupe(); /*look for existing record after assigning enough fields to identify*/
    return $me;
  }
  //
  public function fetchDupe() {
    $c = new static($this);
    $dupe = static::fetchOneBy($c);
    return $dupe ?: $this; 
  }
}
class Ipc_Ci extends Ipc {
  //
  public $ipc;
  public $userGroupId;
  public $name;
  public $cat;
  //
  static function fetchOrCreate($ugid, $name, $cat) {
    if ($name) {
      $me = static::fetchCustomByName($ugid, $name);
      if ($me == null) 
        $me = static::saveAsNewCustom($ugid, $name, $cat);
      return $me;
    } else {
      throw new ClinicalImportException('Name required for IPC');
    }
  } 
}
class Proc_Ci extends ProcRec {
  //
  public $procId;
  public $userGroupId;
  public $clientId;
  public $date;  
  public $ipc;
  public $location;
  public $providerId;
  public $addrFacility;
  public $comments;
  //
  static function create($ugid, $cid, $date, $ipc, $comments = null) {
    $me = new static();
    $me->userGroupId = $ugid;
    $me->clientId = $cid;
    $me->date = $date ?: formatAsUnknownDate();
    $me->ipc = $ipc;
    if ($me->clientId)
      $me = $me->fetchDupe(); /*look for existing record after assigning enough fields to identify*/
    $me->comments = $comments;
    return $me;
  }
  //
  public function fetchDupe() {
    $c = new static($this);
    $dupe = static::fetchOneBy($c);
    return $dupe ?: $this; 
  }
}
class Proc_Ci_Results extends Proc_Ci {
  //
  public $procId;
  public $userGroupId;
  public $clientId;
  public $date;  
  public $ipc;
  public $location;
  public $providerId;
  public $addrFacility;
  public $comments;
  public /*ProcResult_Ci*/$Results;
  //
  static function create($ugid, $cid, $date, $ipc, $results) {
    $me = parent::create($ugid, $cid, $date, $ipc);
    $me->Results = $results;
    return $me;
  }
  //
  public function save() {
    if ($this->procId)
      Dao::query("DELETE FROM proc_results WHERE proc_id=$this->procId");
    parent::save();
    $seq = 0;
    foreach ($this->Results as $result) 
      $result->save($this->procId, ++$seq);
  }  
}
class Result_Ci extends ProcResultRec {
  //
  static $INTERPRET_MAP = array(
    'BETTER' => self::IC_BETTER,
    'DECREASED' => self::IC_DECREASED,
    'INCREASED' => self::IC_INCREASED,
    'WORSE' => self::IC_WORSE,
    'NORMAL' => self::IC_NORMAL,
    'INTERMEDIATE' => self::IC_INTERMEDIATE,
    'RESISTANT' => self::IC_RESISTANT,
    'SUSCEPTIBLE' => self::IC_SUSCEPTIBLE,
    'ABNORMAL' => self::IC_ABNORMAL,
    'ABNORMAL ALERT' => self::IC_ABNORMAL_ALERT,
  	'HIGH ALERT' => self::IC_HIGH_ALERT,
    'LOW ALERT' => self::IC_LOW_ALERT,
    'HIGH' => self::IC_HIGH,
    'ABNORMAL HIGH' => self::IC_HIGH,
    'LOW' => self::IC_LOW,
    'ABNORMAL LOW' => self::IC_LOW);
  //
  public $procResultId;
  public $clientId;
  public $procId;
  public $seq;
  public $ipc;
  public $value;
  public $valueUnit;
  public $range;
  public $interpretCode;
  public $comments;
  //
  static function create($cid, $ipc, $value, $valueUnit, $comments = null, $interpretCode = null) {
    $me = new static();
    $me->clientId = $cid;
    $me->ipc = $ipc;
    $me->value = $value;
    $me->valueUnit = $valueUnit;
    $me->comments = $comments;
    $me->interpretCode = $interpretCode;
    return $me;
  }
  //
  public function save($procId, $seq) {
    $this->procId = $procId;
    $this->seq = $seq;
    return parent::save();
  }
}
//
class ClinicalImportException extends DisplayableException {} 