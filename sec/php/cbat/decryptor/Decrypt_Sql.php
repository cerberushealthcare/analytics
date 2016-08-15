<?php
require_once 'php/data/rec/sql/_ClientRec.php';
require_once 'php/data/rec/sql/_ICardRec.php';
require_once 'php/data/rec/sql/_AddressRec.php';
require_once 'php/data/rec/sql/_AuditMruRec.php';
require_once 'php/data/rec/sql/_Hl7InboxRec.php';
require_once 'php/data/rec/sql/_MessagingRecs.php';
require_once 'php/data/rec/sql/_VisitSummaryRec.php';
require_once 'php/data/rec/sql/_PortalUserRec.php';
require_once 'php/data/rec/sql/_TrackItemRec.php';
require_once 'php/data/rec/sql/_SessionRec.php';
require_once 'php/data/rec/sql/_ImmunRec.php';
require_once 'php/data/rec/sql/_SchedRec.php';
//
class Client_D extends ClientRec implements ReadOnly {
  //
  public $clientId;
  public $userGroupId;
  public $uid;
  public $lastName;
  public $firstName;
  public $birth;
  public $cdata1;
  public $cdata2;
  public $cdata3;
  public $notes;
  public $familyRelease;
  public $release;
  public /*VisitSum_D[]*/ $VisitSums;
  public /*Address_D*/ $AddressHome;
  public /*Address_D*/ $AddressEmer;
  public /*Address_D*/ $AddressSpouse;
  public /*Address_D*/ $AddressFather;
  public /*Address_D*/ $AddressMother;
  public /*Address_D*/ $AddressRx;
  public /*Address_D*/ $AddressBirth;
  public /*ICard_D*/ $ICard1;
  public /*ICard_D*/ $ICard2;
  public /*AuditMru*/ $AuditMru;
  //
  static function fetchSet($ugid, $startPk = null, $limit = 500) {
    $c = new static();
    $c->userGroupId = $ugid;
    $c->VisitSums = VisitSum_D::asJoin();
    $c->AddressHome = Address_D::asJoinClient(Address_D::TYPE_SHIP);
    $c->AddressEmer = Address_D::asJoinClient(Address_D::TYPE_EMER);
    $c->AddressSpouse = Address_D::asJoinClient(Address_D::TYPE_SPOUSE);
    $c->AddressFather = Address_D::asJoinClient(Address_D::TYPE_FATHER);
    $c->AddressMother = Address_D::asJoinClient(Address_D::TYPE_MOTHER);
    $c->AddressRx = Address_D::asJoinClient(Address_D::TYPE_RX);
    $c->AddressBirth = Address_D::asJoinClient(Address_D::TYPE_BIRTH);
    $c->ICard1 = ICard_D::asJoin(1);
    $c->ICard2 = ICard_D::asJoin(2);
    $c->AuditMru = AuditMru_D::asJoin();
    if ($startPk)
      $c->setPkValue(CriteriaValue::greaterThanOrEqualsNumeric($startPk));
    $set = LimitedSet::fetch($c, $limit);
    return $set;
  }
  protected function getSqlValue($fid, $efids) {
    // don't encrypt out
    return parent::getSqlValue($fid, null);
  }
}
class Address_D extends AddressRec implements ReadOnly, AutoEncrypt {
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
  public $phone1;
  public $phone2;
  public $phone3;
  public $email1;
  public $email2;
  public $name;
  //
  static function asJoinClient($type) {
    $c = new static();
    $c->tableCode = static::TABLE_CLIENTS;
    $c->type = $type;
    return CriteriaJoin::optional($c, 'tableId');
  }
  protected function getSqlValue($fid, $efids) {
    // don't encrypt out
    return parent::getSqlValue($fid, null);
  }  
}
class ICard_D extends ICardRec implements ReadOnly {
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
  //
  static function asJoin($seq) {
    $c = new static();
    $c->seq = $seq;
    return CriteriaJoin::optional($c);
  }
  protected function getSqlValue($fid, $efids) {
    // don't encrypt out
    return parent::getSqlValue($fid, null);
  }
}
class AuditMru_D extends AuditMruRec implements ReadOnly {
  //
  public $clientId;
  public $date;
  public $label;
  //
  static function asJoin() {
    return CriteriaJoin::optional(new static());
  }
  protected function getSqlValue($fid, $efids) {
    // don't encrypt out
    return parent::getSqlValue($fid, null);
  }
}
class VisitSum_D extends VisitSummaryRec implements ReadOnly {
  //
  public $clientId;
  public $finalId;  
  public $dos;
  public $sessionId;
  public $finalHead;
  public $finalBody;
  public $finalizedBy;
  public $diagnoses;
  public $iols;
  public $instructs;
  public $vitals;
  public $meds;
  //
  static function asJoin() {
    return CriteriaJoin::optionalAsArray(new static());
  }
  protected function getSqlValue($fid, $efids) {
    // don't encrypt out
    return parent::getSqlValue($fid, null);
  }
}
class AuditRec_D extends SqlRec implements ReadOnly, AutoEncrypt {
  //
  public $auditRecId;
  public $userGroupId;
  public $label;
  public $before;
  public $after;
  //
  public function getSqlTable() {
    return 'audit_recs';
  }
  public function getEncryptedFids() {
    return array('label','before','after');
  }
  //
  static function fetchSet($ugid, $startPk = null, $limit = 500) {
    $c = new static();
    $c->userGroupId = $ugid;
    if ($startPk)
      $c->setPkValue(CriteriaValue::greaterThanOrEqualsNumeric($startPk));
    return LimitedSet::fetch($c, $limit);
  }
  protected function getSqlValue($fid, $efids) {
    // don't encrypt out
    return parent::getSqlValue($fid, null);
  }
}
class DataSync_D extends SqlRec implements ReadOnly, AutoEncrypt {
  //
  public $dataSyncId;
  public $userGroupId;
  public $value;
  //
  public function getSqlTable() {
    return 'data_syncs';
  }
  public function getEncryptedFids() {
    return array('value');
  }
  //
  static function fetchSet($ugid, $startPk = null, $limit = 500) {
    $c = new static();
    $c->userGroupId = $ugid;
    if ($startPk)
      $c->setPkValue(CriteriaValue::greaterThanOrEqualsNumeric($startPk));
    return LimitedSet::fetch($c, $limit);
  }
  protected function getSqlValue($fid, $efids) {
    // don't encrypt out
    return parent::getSqlValue($fid, null);
  }
}
class Hl7Inbox_D extends Hl7InboxRec implements ReadOnly {
  //
  public $hl7InboxId;
  public $userGroupId;
  public $patientName;
  public $dateReceived;
  public $data;
  //
  static function fetchSet($ugid, $startPk = null, $limit = 500) {
    $c = new static();
    $c->userGroupId = $ugid;
    if ($startPk)
      $c->setPkValue(CriteriaValue::greaterThanOrEqualsNumeric($startPk));
    return LimitedSet::fetch($c, $limit);
  }
  protected function getSqlValue($fid, $efids) {
    // don't encrypt out
    return parent::getSqlValue($fid, null);
  }
}
class MsgThread_D extends MsgThreadRec implements ReadOnly {
  //
  public $threadId;
  public $userGroupId;
  public $subject;
  public /*MsgPost_D[]*/$Posts;
  //
  static function fetchSet($ugid, $startPk = null, $limit = 500) {
    $c = new static();
    $c->userGroupId = $ugid;
    $c->Posts = MsgPost_D::asJoin();
    if ($startPk)
      $c->setPkValue(CriteriaValue::greaterThanOrEqualsNumeric($startPk));
    return LimitedSet::fetch($c, $limit);
  }
  protected function getSqlValue($fid, $efids) {
    // don't encrypt out
    return parent::getSqlValue($fid, null);
  }
}
class MsgPost_D extends MsgPostRec implements ReadOnly {
  //
  public $postId;
  public $threadId;
  public $body;
  public $data;
  //
  static function asJoin() {
    return CriteriaJoin::optionalAsArray(new static());
  }
  protected function getSqlValue($fid, $efids) {
    // don't encrypt out
    return parent::getSqlValue($fid, null);
  }
}
class PortalUser_D extends PortalUserRec implements ReadOnly {
  //
  public $portalUserId;
  public $userGroupId;
  public $email;
  public $lastName;
  public $zipCode;
  public $ca1;
  public $ca2;
  public $ca3;
  public /*PortalLogin_D[]*/$Logins;
  //
  static function fetchSet($ugid, $startPk = null, $limit = 500) {
    $c = new static();
    $c->userGroupId = $ugid;
    $c->Logins = PortalLogin_D::asJoin();
    if ($startPk)
      $c->setPkValue(CriteriaValue::greaterThanOrEqualsNumeric($startPk));
    return LimitedSet::fetch($c, $limit);
  }
  protected function getSqlValue($fid, $efids) {
    // don't encrypt out
    return parent::getSqlValue($fid, null);
  }
}
class PortalLogin_D extends PortalLoginRec implements ReadOnly {
  //
  public $logId;
  public $logIp;
  public $portalUserId;
  //
  static function asJoin() {
    return CriteriaJoin::optionalAsArray(new static());
  }
  protected function getSqlValue($fid, $efids) {
    // don't encrypt out
    return parent::getSqlValue($fid, null);
  }
}
class Proc_D extends SqlRec implements AutoEncrypt, ReadOnly {
  //
  public $procId;
  public $userGroupId;
  public $date;  
  public $comments;
  public /*ProcResult_D[]*/$Results;
  public /*Hd_PDate_D*/ $Hd_Date;
  //
  public function getSqlTable() {
    return 'procedures';
  }
  public function getEncryptedFids() {
    return array('date','comments');
  }
  //
  static function fetchSet($ugid, $startPk = null, $limit = 500) {
    $c = new static();
    $c->userGroupId = $ugid;
    $c->Results = ProcResult_D::asJoin();
    if ($startPk)
      $c->setPkValue(CriteriaValue::greaterThanOrEqualsNumeric($startPk));
    $set = LimitedSet::fetch($c, $limit);
    return $set;
  }  
  protected function getSqlValue($fid, $efids) {
    // don't encrypt out
    return parent::getSqlValue($fid, null);
  }
}
class ProcResult_D extends SqlRec implements AutoEncrypt, ReadOnly {
  //
  public $procResultId;
  public $procId;
  public $comments;
  //
  public function getSqlTable() {
    return 'proc_results';
  }
  public function getEncryptedFids() {
    return array('comments');
  }
  //
  static function asJoin() {
    return CriteriaJoin::optionalAsArray(new static());
  }
  protected function getSqlValue($fid, $efids) {
    // don't encrypt out
    return parent::getSqlValue($fid, null);
  }
}
class Sched_D extends SqlRec implements AutoEncrypt, ReadOnly {
  //
  public $schedId;
  public $userGroupId;
  public $comment;
  public $schedEventId;
  public /*SchedEvent_D[]*/$Event;
  //
  public function getSqlTable() {
    return 'scheds';
  }
  public function getEncryptedFids() {
    return array('comment');
  }
  //
  static function fetchSet($ugid, $startPk = null, $limit = 500) {
    $c = new static();
    $c->userGroupId = $ugid;
    $c->Event = SchedEvent_D::asJoin();
    if ($startPk)
      $c->setPkValue(CriteriaValue::greaterThanOrEqualsNumeric($startPk));
    return LimitedSet::fetch($c, $limit);
  }  
  protected function getSqlValue($fid, $efids) {
    // don't encrypt out
    return parent::getSqlValue($fid, null);
  }
}
class SchedEvent_D extends SqlRec implements AutoEncrypt, ReadOnly {
  //
  public $schedEventId;
  public $comment;
  //
  public function getSqlTable() {
    return 'sched_events';
  }
  public function getEncryptedFids() {
    return array('comment');
  }
  //
  static function asJoin() {
    return CriteriaJoin::optional(new static());
  }
  protected function getSqlValue($fid, $efids) {
    // don't encrypt out
    return parent::getSqlValue($fid, null);
  }
}
class TrackItem_D extends TrackItemRec implements ReadOnly {
  //
  public $trackItemId;
  public $userGroupId;
  public $trackDesc;
  public $orderDate;
  public $orderNotes;
  public $schedDate;
  public $diagnosis;
  public $schedLoc;
  public $schedNotes;
  public $closedNotes;
  //
  static function fetchSet($ugid, $startPk = null, $limit = 500) {
    $c = new static();
    $c->userGroupId = $ugid;
    if ($startPk)
      $c->setPkValue(CriteriaValue::greaterThanOrEqualsNumeric($startPk));
    return LimitedSet::fetch($c, $limit);
  }
  protected function getSqlValue($fid, $efids) {
    // don't encrypt out
    return parent::getSqlValue($fid, null);
  }
}
class Session_D extends SessionRec implements ReadOnly {
  //
  public $sessionId;
  public $userGroupId;
  public $data;
  public $html;
  public $dateCreated;
  public $dateService;
  public $dateClosed;
  public $noteDate;
  public $dateUpdated;
  public $title;
  //
  static function fetchSet($ugid, $startPk = null, $limit = 500) {
    $c = new static();
    $c->userGroupId = $ugid;
    if ($startPk)
      $c->setPkValue(CriteriaValue::greaterThanOrEqualsNumeric($startPk));
    return LimitedSet::fetch($c, $limit);
  }
  protected function getSqlValue($fid, $efids) {
    // don't encrypt out
    return parent::getSqlValue($fid, null);
  }
}
class Vitals_D extends SqlRec implements ReadOnly, AutoEncrypt {
  //
  public $dataVitalsId;
  public $userGroupId;
  public $date;  
  //
  public function getSqlTable() {
    return 'data_vitals';
  }
  public function getEncryptedFids() {
    return array('date');
  }
  //
  static function fetchSet($ugid, $startPk = null, $limit = 500) {
    $c = new static();
    $c->userGroupId = $ugid;
    if ($startPk)
      $c->setPkValue(CriteriaValue::greaterThanOrEqualsNumeric($startPk));
    return LimitedSet::fetch($c, $limit);
  }
  protected function getSqlValue($fid, $efids) {
    // don't encrypt out
    return parent::getSqlValue($fid, null);
  }
}
class Med_D extends SqlRec implements ReadOnly, AutoEncrypt {
  //
  public $dataMedId;
  public $userGroupId;
  public $date;  
	//
	public function getSqlTable() {
    return 'data_meds';
  }
  public function getEncryptedFids() {
    return array('date');
  }
  //
  static function fetchSet($ugid, $startPk = null, $limit = 500) {
    $c = new static();
    $c->userGroupId = $ugid;
    if ($startPk)
      $c->setPkValue(CriteriaValue::greaterThanOrEqualsNumeric($startPk));
    return LimitedSet::fetch($c, $limit);
  }
  protected function getSqlValue($fid, $efids) {
    // don't encrypt out
    return parent::getSqlValue($fid, null);
  }
}
class Allergy_D extends SqlRec implements ReadOnly, AutoEncrypt {
  //
  public $dataAllergyId;
  public $userGroupId;
  public $date;  
	//
	public function getSqlTable() {
    return 'data_allergies';
  }
  public function getEncryptedFids() {
    return array('date');
  }
  //
  static function fetchSet($ugid, $startPk = null, $limit = 500) {
    $c = new static();
    $c->userGroupId = $ugid;
    if ($startPk)
      $c->setPkValue(CriteriaValue::greaterThanOrEqualsNumeric($startPk));
    return LimitedSet::fetch($c, $limit);
  }
  protected function getSqlValue($fid, $efids) {
    // don't encrypt out
    return parent::getSqlValue($fid, null);
  }
}
class Diagnosis_D extends SqlRec implements ReadOnly, AutoEncrypt {
  //
  public $dataDiagnosesId;
  public $userGroupId;
  public $date;  
  public $dateClosed;
  //
  public function getSqlTable() {
    return 'data_diagnoses';
  }
  public function getEncryptedFids() {
    return array('date','dateClosed');
  }
  //
  static function fetchSet($ugid, $startPk = null, $limit = 500) {
    $c = new static();
    $c->userGroupId = $ugid;
    if ($startPk)
      $c->setPkValue(CriteriaValue::greaterThanOrEqualsNumeric($startPk));
    return LimitedSet::fetch($c, $limit);
  }
  protected function getSqlValue($fid, $efids) {
    // don't encrypt out
    return parent::getSqlValue($fid, null);
  }
}
class Immun_D extends ImmunRec implements ReadOnly {
  //
  public $dataImmunId;
  public $userGroupId;
  public $dateGiven;
  public $comment;  
  //
  static function fetchSet($ugid, $startPk = null, $limit = 500) {
    $c = new static();
    $c->userGroupId = $ugid;
    if ($startPk)
      $c->setPkValue(CriteriaValue::greaterThanOrEqualsNumeric($startPk));
    return LimitedSet::fetch($c, $limit);
  }
  protected function getSqlValue($fid, $efids) {
    // don't encrypt out
    return parent::getSqlValue($fid, null);
  }
}
class ScanIndex_D extends SqlRec implements ReadOnly, AutoEncrypt {
  //
  public $scanIndexId; 
  public $userGroupId;
  public $datePerformed;
  //
  public function getSqlTable() {
    return 'scan_index';
  }
  public function getEncryptedFids() {
    return array('datePerformed');
  }
  //
  static function fetchSet($ugid, $startPk = null, $limit = 500) {
    $c = new static();
    $c->userGroupId = $ugid;
    if ($startPk)
      $c->setPkValue(CriteriaValue::greaterThanOrEqualsNumeric($startPk));
    return LimitedSet::fetch($c, $limit);
  }
  protected function getSqlValue($fid, $efids) {
    // don't encrypt out
    return parent::getSqlValue($fid, null);
  }
}
