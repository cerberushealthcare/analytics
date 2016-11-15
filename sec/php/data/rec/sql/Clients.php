<?php
require_once 'php/data/rec/sql/_ClientRec.php';
require_once 'php/data/rec/sql/_AddressRec.php';
require_once 'php/data/rec/sql/_ICardRec.php';
require_once 'php/data/rec/sql/UserGroups.php';
require_once 'php/csys/alerts/Alerts.php';
require_once 'php/c/patient-entry/PatientEntry.php';
/**
 * Clients DAO
 * @author Warren Hornsby
 */
class Clients {
  /**
   * 
   * @param int $cid
   * @return Client(+Addresses,+ICards)
   */
  static function get($cid) {
    global $login;
    $rec = Client::fetchWithDemo($cid);
    if ($rec && $rec->isRestrictedFor($login->userId)) 
      if (! $login->isGlassBroken($cid))
        throw new RestrictedChartException($rec->clientId);
    return $rec;
  }
  /**
   * @param int $cid
   */
  static function breakGlass($cid) {
    global $login;
    $login->breakGlass($cid);
    Alerts::sendGlassBroken($cid);
    Auditing::logRec(AuditRec::ACTION_BREAKGLASS, Client::fetchWithDemo($cid));
  }
  /**
   * @param int $cid
   */
  static function uploadImage($cid) {
    PatientEntry::uploadImage($cid);
    /*
    global $login;
    $rec = Client::fetch($cid);
    if ($rec) {
      $folder = GroupFolder_Faces::open();
      $filename = $folder->upload($cid);
      $rec->img = $filename;
      $rec->save($login->userGroupId);
    }*/
  }
  /**
   * @param int $cid
   * @return Client
   */
  static function removeImage($cid) {
    PatientEntry::removeImage($cid);
    /*
    global $login;
    $rec = Client::fetch($cid);
    $rec->img = null;
    $rec->save($login->userGroupId); 
    return Clients::get($rec->clientId);*/
  }
  /**
   * @param stdClass $object Client JSON
   * @return Client
   */
  static function save($object, $dupeOk = false) {
    $rec = PatientEntry::update($object);
    /*
    global $login;
    $rec = new Client($object);
    $rec->save($login->userGroupId, $dupeOk);
    if ($rec->livingWill || $rec->poa)
      Proc_LivingWillPoa::record($rec->clientId);
    static::updateCerberus($rec->clientId);*/
    return Clients::get($rec->clientId);
  }
  /*
  static function save_dupeOk($object) {
    return static::save($object, true);
  }*/
  /**
   * @param stdClass $object Address JSON
   * @param int cid 
   * @return Client(+Addresses,+ICards) 
   */
  static function updateAddress($object, $cid) {
    $rec = PatientEntry::saveAddress($cid, $object);
    /*
    $rec = new ClientAddress($object);
    $rec->addressId = null;
    if (empty($rec->addressId)) {  // UI considers an add
      $dupe = ClientAddress::fetch($cid, $rec->type);
      if (! empty($dupe))  // rec exists, however; turn UI request into update 
        $rec->addressId = $dupe->addressId;
    }
    $rec->save($cid);
    static::updateCerberus($cid);*/
    return Clients::get($cid);
  }
  /**
   * @param string $notes
   * @param int $cid
   */
  static function updateNotes($notes, $cid) {
    PatientEntry::saveNotes($cid, $notes);
    /*
    global $login;
    $rec = Client::fetch($cid);
    $rec->notes = $notes;
    $rec->save($login->userGroupId);*/
  }
  /**
   * @param stdClass $object ICard JSON
   * @param int cid 
   * @return Client(+Addresses,+ICards) 
   */
  static function updateICard($object, $cid) {
    PatientEntry::saveICard($cid, $object);
    /*
    $icard = new ICard($object);
    if ($icard->clientId != $cid) 
      throw new SecurityException("Invalid icard save, obj $icard->clientId/cid $cid");
    $icard->save();*/
    return Clients::get($cid);
  }
  /**
   * Search for potential matches 
   * @param string $last
   * @param string $first
   * @param(opt) string $dob
   * @param(opt) string $sex
   * @return array($cid=>Client,..)
   */
  static function search($last, $first, $dob = null, $sex = null) {
    $recs = PatientList::search($last, $first, null, $dob);
    return $recs;
    /*
    global $login;
    $birth = dateToString($dob);
    $matches = array();
    $rec = new Client();
    $rec->active = true;
    $rec->userGroupId = $login->userGroupId;
    $rec->lastName = $last;
    $rec->firstName = $first;
    $rec->sex = $sex;
    $rec->birth = $birth;
    if (Clients::_search($matches, $rec) > 0)  // last, first, dob, sex
      return $matches;
    $rec->firstName = CriteriaValue::startsWith(substr($first, 0, 1));
    Clients::_search($matches, $rec);  // last, first1%, dob, sex
    $rec->firstName = null;
    if (Clients::_search($matches, $rec) > 0)  // last, dob, sex
      return $matches;
    $rec->lastName = null;
    if ($rec->birth) {
      $rec->birth = null;
      $rec->lastName = $last;
      $rec->firstName = $first;
      if (Clients::_search($matches, $rec) > 0)  // last, first, sex
        return $matches;
      $rec->firstName = null;
      $rec->lastName = null;
      $rec->birth = $birth;
      Clients::_search($matches, $rec);  // dob, sex
      $rec->birth = null;
      $rec->lastName = $last;
      $rec->firstName = CriteriaValue::startsWith(substr($first, 0, 1));
      Clients::_search($matches, $rec);  // last, first1%, sex
      $rec->firstName = null;
      if (Clients::_search($matches, $rec) > 0)  // last, sex
        return $matches;
    }
    $rec->lastName = CriteriaValue::startsWith(substr($last, 0, 4));
    $rec->firstName = $first;
    Clients::_search($matches, $rec);  // last4%, first, sex
    $rec->firstName = null;
    Clients::_search($matches, $rec);  // last4%, sex
    if (count($matches) == 0) {
      $rec->firstName = $first;
      $rec->lastName = CriteriaValue::startsWith(substr($last, 0, 1));
      Clients::_search($matches, $rec);  // last1%, first, sex
      $rec->lastName = null;
      $rec->birth = null;
      Clients::_search($matches, $rec);  // first, sex
    }
    return empty($matches) ? null : $matches;
    */
  }
  //
  private static function _search(&$matches, $rec) {
    $recs = SqlRec::fetchMapBy($rec, 'clientId', 10);
    $matches = $matches + $recs;
    return count($recs);
  }
}
class Client extends ClientRec {
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
  public $emrId;
  public $inactiveCode;
  public $nickName;
  public $dnr;
  public $immRegReminders;
  public $immRegRefuse;
  public $uploadId;
  public /*Address*/ $Address_Home;
  public /*Address*/ $Address_Emergency;
  public /*Address*/ $Address_Spouse;
  public /*Address*/ $Address_Father;
  public /*Address*/ $Address_Mother;
  public /*Address*/ $Address_Rx;
  public /*[ICard]*/ $ICards;
  public /*UserStub*/ $User_primaryPhys;
  public $age;  // '1y 2m';
  public $ageYears;
  //
  public function toJsonObject(&$o) {
    parent::toJsonObject($o);
    $o->lookup('sex', self::$SEXES);
//    $o->lookup('language', self::$LANGUAGES); /* now in Language->engName */
//    $o->lookup('race', self::$RACES);
    $o->lookup('ethnicity', self::$ETHNICITIES);
    if (isset($this->User_primaryPhys)) 
      $o->_primaryPhys = $this->User_primaryPhys->name;
    $o->lookup('releasePref', self::$RELEASE_PREFS);
    if ($this->releasePref > self::RELEASE_LETTER)
      $o->_releasePref .= " $this->release";
    if (isset($o->userRestricts))
      $o->_userRestricts = self::lookupUserRestricts($o->userRestricts);
    $o->_immReg = $this->immRegReminders;
    if ($this->immRegRefuse) 
      $o->_immReg .= ',' . $this->immRegRefuse;
    switch ($this->dnr) {
      case 'DNR':
        $o->_dnrc = 'D';
        $o->_dnr = static::$DNR_CODES[$o->_dnrc];
        $o->_dnr2 = $o->_dnr;
        break;
      case 'FC':
        $o->_dnrc = 'F';
        $o->_dnr = static::$DNR_CODES[$o->_dnrc];
        $o->_dnr2 = $o->_dnr;
        break;
      default:
        if (! empty($this->dnr)) {
          $o->_dnrc = 'S';
          $dnri = array();
          for ($i = 1; $i <= 5; $i++) {
            $c = strval($i);
            $fid = '_dnr' . $c;
            if (strpos($this->dnr, $c) !== false) {
              $o->$fid = 1;
              $dnri[] = static::$DNR_INTS[$i];
            }
          }
          if (! empty($dnri)) {
            $o->_dnr = 'Allow:<br>' . implode('<br>', $dnri);
            $o->_dnr2 = 'Allow: ' . implode(', ', $dnri);
          }
        }
    }
  }
  public function getJsonFilters() {
    return array(
    	'birth' => JsonFilter::editableDate(),
      'userRestricts' => JsonFilter::serializedObject(),
      'deceased' => JsonFilter::editableDate(),
      'active' => JsonFilter::boolean(),
    	'livingWill' => JsonFilter::boolean(),
      'poa' => JsonFilter::boolean(),
      'User_primaryPhys' => JsonFilter::oneWay());
  }
  public function getAuditLabel() {
    return $this->getFullName();
  }
  public function validate(&$rv) {
    $rv->requires('uid', 'lastName', 'firstName', 'birth');
  }
  public function save($ugid, $dupeOk = false) {
    if ($this->clientId == null) 
      $this->saveAsNew($ugid, $dupeOk);
    else
      parent::save($ugid);
  }
  protected function saveAsNew($ugid, $dupeOk) {
    $dupe = ClientStub::fetchByUid($ugid, $this->uid);
    if ($dupe) 
      throw new DupePatientException($dupe);
    if (! $dupeOk) {
      $dupe = ClientStub::fetchByNameBirth($ugid, $this->lastName, $this->birth);
      if ($dupe) 
        throw new PossibleDupePatientException($dupe);
    }
    $this->active = true;
    $this->deceased = null;
    parent::save($ugid);
    $this->emrId = $this->clientId;
    parent::save($ugid);
  }
  //
  public function formatBirthplace() {
    if ($this->Address_Birth) {
      $a = nonNulls($Address_Birth->city, $Address_Birth->state);
      return implode(', ', $a);
    }
  }
  //
  /**
   * @param int $cid
   * @return Client
   */
  static function fetch($cid) {
    if ($cid) 
      return parent::fetch($cid);
    else
      return null;
  }
  /**
   * @param int $cid
   * @return Client(+Addresses,Icards)
   */
  static function fetchWithDemo($cid, $addressClass = 'ClientAddress', $icardClass = 'Icard') {
    $c = new static();
    $c->clientId = $cid;
    $c->User_primaryPhys = new User_Doctor();
    $c->Language = IsoLang::asJoin();
    $rec = self::fetchOneBy($c);
    if ($rec) {
      $rec->Address_Home = ClientAddress::fetchHome($cid, true, $addressClass); 
      $rec->Address_Emergency = ClientAddress::fetchEmergency($cid, true, $addressClass); 
      $rec->Address_Spouse = ClientAddress::fetchSpouse($cid, true, $addressClass); 
      $rec->Address_Father = ClientAddress::fetchFather($cid, true, $addressClass);
      $rec->Address_Mother = ClientAddress::fetchMother($cid, true, $addressClass);
      $rec->Address_Rx = ClientAddress::fetchRx($cid, true, $addressClass);
      $rec->Address_Birth = ClientAddress::fetchBirth($cid, false, $addressClass);
      $rec->ICards = ICard::fetchAllByClient($cid, $icardClass);
      $rec->setChronAge();
      if ($rec->language == -1)
        $rec->Language = IsoLang::asDeclined();
    }
    return $rec;
  }
  static function makeUid($ugid) {
    $c = new static();
    $c->userGroupId = $ugid;
    $index = static::count($c) + 1;
    return 'A' . str_pad($index, 5, '0', STR_PAD_LEFT);
  }
  //
  private static function lookupUserRestricts($ids) {
    $names = UserGroups::lookupUsers($ids);
    return implode(', ', $names);
  }
}
/**
 * Client Insurance Card
 */
class ICard extends ICardRec {
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
  public function getAuditRecId() {
    return "$this->clientId,$this->seq"; 
  }
  public function getJsonFilters() {
    return array(
      'dateEffective' => JsonFilter::editableDate());
  }
  //
  /**
   * @param int $cid
   * @return array(Icard,ICard)
   */
  static function fetchAllByClient($cid, $class = __CLASS__) {
    return array(
      self::fetchByClientSeq($cid, ICard::SEQ_PRIMARY, $class),
      self::fetchByClientSeq($cid, ICard::SEQ_SECONDARY, $class));
  }
  //
  private static function fetchByClientSeq($cid, $seq, $class = __CLASS__) {
    $c = new $class($cid, $seq);
    $rec = self::fetchOneBy($c);
    if ($rec == null) {
      $rec = new $class($cid, $seq);
      $rec->_empty = true;
    }
    return $rec;
  }
}
/**
 * Exceptions
 */
class DupePatientException extends DisplayableException {
  public function __construct($dupe) {
    $html = "This record cannot be created because a patient with that ID already exists:<br/><br/>ID: <b>$dupe->uid</b><br/>Name: <b>" . $dupe->getFullName() . "</b><br/>DOB: <b>" . formatDate($dupe->birth) . "</b>";
    $this->message = $html;
  }
}
class PossibleDupePatientException extends DisplayableException {
  public function __construct($dupe) {
    $html = "This record matches a patient with the same last name and birth:<br/><br/>ID: <b>$dupe->uid</b><br/>Name: <b>" . $dupe->getFullName() . "</b><br/>DOB: <b>" . formatDate($dupe->birth) . "</b><br><br>Continue with save?";
    $this->message = $html;
    $this->data = $dupe;
  }
}
class RestrictedChartException extends DisplayableException {
  public function __construct($cid) {
    $this->message = $cid;
  }
}
