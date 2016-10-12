<?php
require_once 'php/data/rec/sql/_ClientRec.php';
require_once 'php/data/rec/sql/_ICardRec.php';
require_once 'php/data/rec/sql/_AddressRec.php';
require_once 'php/data/rec/sql/Procedures_Admin.php';
require_once 'php/c/patient-list/PatientList.php';
//
/**
 * Patient Entry
 * @author Warren Hornsby
 */
class PatientEntry {
  //
  /** Increment next available UID */
  static function /*string*/getNextUid() {
	if (isset($_POST['IS_BATCH'])) {
		$uid = $_POST['userGroupId'];
	}
	else {
		global $login;
		$uid = P_Uid::makeUid($login->userGroupId);
		}
    return $uid;
  }
  /** Add patient @throws Dupe, RecValidator */
  static function /*Patient_Add*/add($o, $dupeNameOk = false) { 
    global $login;
    $rec = Patient_Add::from($login->userGroupId, $o, $dupeNameOk)->save();
    static::updateCerberus($rec->clientId);
    return $rec;
  }
  /** Update existing patient */
  static function /*Patient_Pe*/update($o) {
    global $login;
    $rec = Patient_Pe::from($login->userGroupId, $o)->save();
    static::updateCerberus($rec->clientId);
    return $rec;
  }
  /** Save patient address */
  static function /*Address_Pe*/saveAddress($cid, $o) {
    $rec = Address_Pe::from($cid, $o)->save();
    static::updateCerberus($cid);
    return $rec;
  }
  /** Save patient insurance */
  static function /*ICard_Pe*/saveICard($cid, $o) {
    $rec = ICard_Pe::from($cid, $o)->save();
    return $rec;
  }
  /** Save patient note field */
  static function saveNotes($cid, /*string*/$notes) {
    $rec = Patient_Pe::fetch($cid);
    $rec && $rec->saveNotes($notes);
  }
  /** Remove patient photo */
  static function removeImage($cid) {
    $rec = Patient_Pe::fetch($cid);
    $rec && $rec->saveImage(null);
  }
  /** Save patient photo */
  static function uploadImage($cid) {
    require_once 'php/data/rec/group-folder/GroupFolder_Faces.php';
    $rec = Patient_Pe::fetch($cid);
    if ($rec) {
      $filename = GroupFolder_Faces::open()->upload($cid);
      $rec->saveImage($filename);
    }
  }
  /** Get ISO languages */
  static function /*IsoLang[]*/getLangs() {
    $recs = IsoLang::fetchAll();
    return $recs;
  }
  //
  protected static function updateCerberus($cid) {
    // TODO - not working
    return;
    
    global $login;
    if ($login->cerberus) {
      require_once 'php/c/patient-billing/CerberusBilling.php';
      CerberusBilling::updateClient($cid);
    }
  }
}
//
class P_Uid extends ClientRec implements ReadOnly {
  //
  public $userGroupId;
  //
  static function makeUid($ugid) {
    $c = new static();
    $c->userGroupId = $ugid;
    $index = static::count($c) + 20;
    return 'A' . str_pad($index, 5, '0', STR_PAD_LEFT);
  }
}
class Patient_Add extends ClientRec {
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
  public $cdata1;
  public $primaryPhys;
  public $deceased;
  public $active;
  public $inactiveCode;
  public $emrId;
  public $huid;
  //
  static function from($ugid, /*stdClass*/$o, $dupeNameOk = false) {
    $me = new static($o);
    $me->clientId = null;
    $me->userGroupId = $ugid;
    $me->active = true;
    if (! $dupeNameOk)
      $me->throwIfDupeNameBirth();
    return $me;
  }
  //
  public function getJsonFilters() {
    return array(
    	'birth' => JsonFilter::editableDate(),
      'deceased' => JsonFilter::editableDate(),
      'active' => JsonFilter::boolean(),
    	'livingWill' => JsonFilter::boolean(),
      'poa' => JsonFilter::boolean());
  }
  public function getAuditLabel() {
    return $this->getFullName();
  }
  public function validate(&$rv) {
    $rv->requires('uid', 'lastName', 'firstName', 'birth');
  }
  public function fetchDupe() {
    $rec = PStub_Search::searchForExact($this->userGroupId, $this->lastName, $this->firstName, $this->birth);
    return $rec;
  }
  //
  protected function throwIfDupeNameBirth() {
    $rec = $this->fetchDupe();
    if ($rec)
      throw new DuplicateNameBirth($rec);
  }
}
class Patient_Pe extends Patient_Add {
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
  //
  static function from($ugid, /*stdClass*/$o) {
    $me = new static($o);
    $me->userGroupId = $ugid;
    return $me;
  }
  //
  public function getJsonFilters() {
    return array(
    	'birth' => JsonFilter::editableDate(),
      'userRestricts' => JsonFilter::serializedObject(),
      'deceased' => JsonFilter::editableDate(),
      'active' => JsonFilter::boolean(),
    	'livingWill' => JsonFilter::boolean(),
      'poa' => JsonFilter::boolean());
  }
  public function saveImage($img) {
    $this->img = $img;
    return SqlRec::save();
  }
  public function saveNotes($notes) {
    $this->notes = $notes;
    return SqlRec::save();
  }
  //
  protected function saveRefs() {
    $this->saveHdatas();
    $this->saveAdminIpcs();
  }
  protected function saveAdminIpcs() {
    if ($this->livingWill || $this->poa)
      Proc_LivingWillPoa::record($this->clientId);
  }
}
class Address_Pe extends ClientAddress { 
  //
  static function from($cid, /*stdClass*/$o) {
    $me = new static($o);
    $me->addressId = null;
    $me->tableId = $cid;
    $dupe = static::fetch($cid, $me->type);
    $me->addressId = $dupe ? $dupe->addressId : null;
    return $me;
  }
  static function fetch($cid, $type) {
    $c = new static();
    $c->tableCode = static::TABLE_CLIENTS;
    $c->tableId = $cid;
    $c->type = $type;
    return static::fetchOneBy($c);
  }
  //
  public function save() {
    parent::save($this->tableId);
  }
}
class ICard_Pe extends ICardRec {
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
  static function from($cid, /*stdClass*/$o) {
    $me = new static($o);
    $me->clientId = $cid;
    return $me;
  }
  //
  public function getJsonFilters() {
    return array(
      'dateEffective' => JsonFilter::editableDate());
  }
}
//
class DupeException extends DisplayableException {}
class DuplicateUid extends DupeException {
  public function __construct($dupe) {
  
	ob_start();
	debug_print_backtrace();
	$trace = ob_get_contents();
	ob_end_clean(); 
	
		
    $html = "This record cannot be created because a patient with that ID already exists:<br/><br/>ID: <b>$dupe->uid</b><br/>Name: <b>" . $dupe->getFullName() . "</b><br/>DOB: <b>" . formatDate($dupe->birth) . "</b>" . '. Trace: ' . $trace;
    $this->message = $html;
  }
}
class DuplicateNameBirth extends DupeException {
  public function __construct($dupe) {
    $html = "This record matches a patient with the same last name and birth:<br/><br/>ID: <b>$dupe->uid</b><br/>Name: <b>" . $dupe->getFullName() . "</b><br/>DOB: <b>" . formatDate($dupe->birth) . "</b><br><br>Continue with save?";
    $this->message = $html;
    $this->data = $dupe;
  }
}
