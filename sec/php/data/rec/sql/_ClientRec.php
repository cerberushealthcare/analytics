<?php
require_once 'php/data/rec/sql/_SqlRec.php';
require_once 'php/data/rec/cryptastic.php';
//
/**
 * Client Base Class
 * @author Warren Hornsby
 */
abstract class ClientRec extends SqlRec implements AutoEncrypt {
  /*  
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
  public $inactiveCode;
  public $huid;
  public $nickName;
  public $dnr;
  public $immRegReminders;
  public $immRegRefuse;
  */
  const SEX_MALE = 'M';
  const SEX_FEMALE = 'F';
  static $SEXES = array(
    self::SEX_FEMALE => "Female",
    self::SEX_MALE => 'Male');
  //
  const RELEASE_LETTER = '1';
  const RELEASE_PHONE = '2';
  const RELEASE_EMAIL = '3';
  static $RELEASE_PREFS = array(
    self::RELEASE_LETTER => 'Letter',
    self::RELEASE_PHONE => 'Phone',
    self::RELEASE_EMAIL => 'Email');
  //
  static $DNR_CODES = array(
    'D' => 'Do not resuscitate',
    'F' => 'Full code',
    'S' => 'Allow specific interventions');
  static $DNR_INTS = array(
    '1' => 'Chest compressions',
    '2' => 'Intubation',
    '3' => 'Mechanical ventilation',
    '4' => 'Resuscitation medications',
    '5' => 'Cardioversion');
  //
  static $IR_REMINDERS = array(
    '01' => 'No reminder/recall',
    '02' => 'Reminder/recall - any method',
    '03' => 'Reminder/recall - no calls',
    '04' => 'Reminder only - any method',
    '05' => 'Reminder only - no calls',
    '06' => 'Recall only - any method',
    '07' => 'Recall only - no calls',
    '08' => 'Reminder/recall - to provider',
    '09' => 'Reminder to provider',
    '10' => 'Only reminder/recall, no recall',
    '11' => 'Recall to provider',
    '12' => 'Only recall to provider, no reminder');
  static $IR_REFUSES = array(
    'Y' => 'Yes; exclude from registry reporting',
    'N' => 'No; may be included in registry reporting');
  //
  const RACE_DECLINED = -1;
  const RACE_OTHER = -99;
  const RACE_NATIVE_AMER_ALASKA = 1;
  const RACE_ASIAN = 2;
  const RACE_BLACK = 4;
  const RACE_WHITE = 8;
  const RACE_HAW_PAC_ISLAND = 16;
  static $RACES = array(
    '1' => 'American Indian or Alaska Native',
    '2' => 'Asian',
    '4' => 'Black or African-American',
    '8' => 'White',
    '16' => 'Native Hawaiian or Other Pacific Islander',
    '32' => 'Other',
    '-1' => '(Declined to Answer)');
  //
  const ETHN_HISPANIC = '1';
  const ETHN_NOT_HISPANIC = '2';
  static $ETHNICITIES = array(
    '1' => 'Hispanic or Latino',
    '2' => 'Not Hispanic or Latino',
    '-1' => '(Declined to Answer)');
  //
  /* Replaced by IsoLang table
  static $LANGUAGES = array(
    '3' => 'English',
    '2' => 'Spanish',
  	'1' => 'Mandarin',
  	'4' => 'Hindi-Urdu',
    '5' => 'Arabic',
    '6' => 'Bengali',
    '7' => 'Portuguese',
    '8' => 'Russian',
    '9' => 'Japanese',
    '10' => 'Punjabi',
    '11' => 'German',
    '12' => 'Javanese',
    '13' => 'Vietnamese',
    '14' => 'Marathi',
    '15' => 'French',
    '16' => 'Korean',
    '17' => 'Turkish',
    '18' => 'Pashto',
    '19' => 'Italian',
    '20' => 'Polish',
    '21' => 'Ukrainian',
    '22' => 'Thai',
    '23' => 'Romanian',
    '24' => 'Dutch',
    '25' => 'Greek',
    '26' => 'Hebrew');
  */
  //
  const INACTIVE_PATIENT_REQ = '1';
  const INACTIVE_DISCHARGED = '2';
  static $INACTIVE_CODES = array(
    self::INACTIVE_PATIENT_REQ => 'Patient request',
    self::INACTIVE_DISCHARGED => 'Discharged from practice');
  //
  static $FRIENDLY_NAMES = array(
    'uid' => 'Patient ID');
  //
  public function getSqlTable() {
    return 'clients';
  }
  public function getEncryptedFids() {
    return array(
    	'uid','lastName','firstName','middleName','birth','dateCreated','dateUpdated',
    	'cdata1','cdata2','cdata3','notes','familyRelease','release','nickName');
  }
  public function toJsonObject(&$o) {
    if (isset($o->clientId)) 
      $o->name = $this->getFullName();
    unset($o->huid);
  }
  public function save() {
    $this->setHuid();
    $this->throwIfDupeUid();
    Dao::begin();
    try {
      SqlRec::save();
      $this->saveRefs();
      Dao::commit();
    } catch (Exception $e) {
      Dao::rollback();
      throw $e;
    }
    return $this;
  }
  //
  public function /*int[]*/getRaces() {
    $a = array();
    $race = intval($this->race);
    if ($race == -1) {
      $a[] = $race;
    } else {
      foreach (static::$RACES as $id => $desc) {
        $i = intval($id);
        if ($i > -1) {
          if ($race & $i)
            $a[] = $i;
        }
      } 
    }
    return $a;
  }
  public function isInactive() {
    if (! $this->active)
      return true; 
  }
  public function isDeceased() {
    if ($this->deceased)
      return true;
  }
  public function isDischarged() {
    return $this->isInactive() && $this->inactiveCode == static::INACTIVE_DISCHARGED;
  }
  public function getFullName() {
    return self::formatName($this);
  }  
  public function getMiddleInitial() {
    if ($this->middleName) 
      return substr($this->middleName, 0, 1);
  }
  public function setBirth($date) {
    $this->birth = formatFromDate($date);
  }
  public function setHuid($uid = null) {
    if ($uid == null)
      $uid = $this->uid;
    $this->huid = MyCrypt_Auto::hash($this->userGroupId . $uid);
  }
  public function /*string[]*/getUserRestricts() {  // IDs of users not allowed
    if ($this->userRestricts) 
      return jsondecode($this->userRestricts);
  }
  public function isRestrictedFor($userId) {
    $ids = $this->getUserRestricts();
    if ($ids) 
      return in_array($userId, $ids);
  }
  public function getCidForNewCrop() {
    return ($this->emrId) ? $this->emrId : $this->clientId;
  }
  public function setChronAge() {
    $cage = chronAge($this->birth);
    $this->age = static::formatAge($cage);
    $this->ageYears = $cage['y']; 
  }
  protected function saveRefs() {
    $this->saveHdatas();
    $this->saveEmrId();
  }
  protected function saveHdatas() {
    HData_ClientDob::from($this)->save();
    HData_ClientName::from($this)->save();
    return $this;
  }
  protected function saveEmrId() {
    if ($this->emrId == null) {
      $this->emrId = $this->clientId;
      $this->save();
    }
    return $this;
  }
  protected function throwIfDupeUid() {
    $rec = PStub_Search::searchForUid($this->userGroupId, $this->uid, $this->clientId);
    if ($rec)
      throw new DuplicateUid($rec);
  }
  //
  static function formatName($rec) {
    $name = "$rec->lastName, $rec->firstName";
    if ($rec->middleName) 
      $name .= " $rec->middleName";
    if (get($rec, 'nickName'))
      $name .= " \"$rec->nickName\"";
    if ($rec->deceased) {
      $name .= " (DECEASED)";
    } else if (! $rec->active) {
      if ($rec->inactiveCode == statiC::INACTIVE_DISCHARGED)
        $name .= ' (DISCHARGED)';
      else
        $name .= ' (INACTIVE)';
    }
    return trim($name);
  }
  private static function formatAge($cage) {
    $y = $cage['y'];
    if ($y >= 3) {
      return $y;
    } else if ($y > 0) {
      return $y . 'y ' . $cage['m'] . 'm';
    } else {
      return $y . 'y ' . $cage['m'] . 'm ' . $cage['d'] . 'd';
    }
  }
}
class IsoLang extends SqlRec implements ReadOnly {
  //
  public $isolangId;
  public $engName;
  public $alpha3Code;
  //
  public function getSqlTable() {
    return 'isolangs';
  }
  //
  static function asDeclined() {
    $me = new static();
    $me->isolangId = -1;
    $me->engName = '(Declined to Answer)';
    return $me;
  }
  static function asJoin() {
    $c = new static();
    return CriteriaJoin::optional($c, 'language');
  }
  static function fetchAll() {
    $c = new static();
    $us = static::fetchAllBy($c);
    return RecSort::sort($us, 'engName');
  }
  static function fetchAllBy($c) {
    Dao::query('SET CHARACTER SET utf8');
    $recs = parent::fetchAllBy($c);
    $recs[] = static::asDeclined();
    return $recs;
  }
  static function /*IsoLang*/map($id) {
    static $map;
    if ($id) {
      if ($map == null) {
        $c = new static();
        $map = static::fetchAllBy($c, null, null, 'isolangId');
      }
      return geta($map, $id);
    }
  }
  static function getEngName($id) {
    $rec = static::map($id);
    if ($rec)
      return $rec->engName;
  }  
}
/**
 * Client Stub
 */
class ClientStub extends ClientRec implements ReadOnly {
  //
  public $clientId;
  public $userGroupId;
  public $uid;
  public $lastName;
  public $firstName;
  public $middleName;
  public $nickName;
  public $sex;
  public $birth;
  public $primaryPhys;
  public $deceased;
  public $active;
  public $emrId;
  public $inactiveCode;
  //
  public function toJsonObject(&$o) {
    $o->name = $this->getFullName();
    $o->_birth = formatDate($this->birth);
  }
  //
  static function fetchByUid($ugid, $uid) {
    $c = new static();
    $c->userGroupId = $ugid;
    $c->uid = $uid;
    return static::fetchOneBy($c);
  }
  static function fetchByNameBirth($ugid, $lastName, $birth) {
    $c = new static();
    $c->userGroupId = $ugid;
    $c->lastName = $lastName;
    $c->setBirth($birth);
    return static::fetchOneBy($c);
  }
  static function fetchByEmrId($emrId) {
    global $login;
    $c = new static();
    $c->userGroupId = $login->userGroupId;
    $c->emrId = $emrId;
    return static::fetchOneBy($c);
  }
}
class PatientStub extends ClientRec implements ReadOnly {
  //
  public $clientId;
  public $userGroupId;
  public $uid;
  public $lastName;
  public $firstName;
  public $middleName;
  public $nickName;
  public $sex;
  public $birth;
  public $primaryPhys;
  public $deceased;
  public $active;
  public $inactiveCode;
  public $huid;
  /*
  public $age;  e.g. '18', '0y 9m 6d'
  public $ageYears;
   */
  public function toJsonObject(&$o) {
    parent::toJsonObject($o);
    $this->setChronAge();
    $o->birth = formatDate($this->birth) . ' (' . $this->age . ')'; 
  }
  public function setActiveOnly($b) {
    if ($b) {
      $this->active = true;
      $this->deceased = CriteriaValue::isNull();
    }
  }  
  //
  static function fetchAll($ugid) {
    $c = new static();
    $c->userGroupId = $ugid;
    $c->setActiveOnly(true);
    return static::fetchAllBy($c);
  }
}
require_once 'php/c/patient-list/PatientList_Sql.php';
