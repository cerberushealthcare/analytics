<?php
require_once 'php/data/rec/sql/Reporting.php';
require_once 'php/data/rec/sql/Clients.php';
require_once 'php/data/rec/sql/Allergies.php';
require_once 'php/data/rec/sql/Meds.php';
require_once 'php/data/rec/sql/Diagnoses.php';
require_once 'php/data/rec/sql/Procedures.php';
require_once 'php/data/rec/sql/Vitals.php';
require_once 'php/data/rec/sql/Auditing.php';
require_once 'php/data/rec/sql/DrugClasses.php';
require_once 'php/c/sessions/Sessions.php';
//
interface ReportRec extends ReadOnly {
  //
  static function asCriteria($ugid);
}
interface ReportRec_CsvJoinable extends ReportRec {
  //
  public function formatLabel();
  public function getTableName();
}
/**
 * RepCrit Subclasses
 */
class RepCrit_Audit extends RepCritRec {
  //
  public $clientId;
  public $userId;
  public $date;
  public $action;
  public $recName;
  public $label;
  //
  static $JOINS_TO = array();
  //
  public function getSqlClass() {
    return 'Audit_Rep';
  }
  public function getRecSort() {
    return new RecSort('-date');
  }
}
class Audit_Rep extends AuditRec implements ReportRec_CsvJoinable {
  //
  public function toJsonObject(&$o) {
    $o->_table = $this->getTableName();
    $o->_label = $this->formatLabel();
    $o->_action = self::$ACTIONS[$this->action];
    $o->_by = $this->User->name;
    $o->after = $this->decorateSnapshot($this->after);
    $o->before = $this->decorateSnapshot($this->before);
  }
  private function decorateSnapshot($rec) {
    require_once 'php/data/rec/sql/OrderEntry.php';
    require_once 'php/data/rec/sql/HL7_Labs.php';
    require_once 'php/data/rec/sql/Procedures_Admin.php';
    require_once 'php/data/rec/sql/Procedures_Hm.php';
    if ($rec) {
      $class = $this->getRecClass();
      $nfr = jsondecode($rec);
      if (! is_object($nfr) || ! class_exists($class))
        return $nfr;
      $nfr->_noFilterIn = true;
      $rec = new $class($nfr);
      $pk = $rec->getPkFid();
      if ($pk)
        unset($rec->$pk);
      return $rec;
    }
  }
  private function getRecClass() {
    switch ($this->recName) {
      case 'Session':
        return 'SessionNote';
      default:
        $this->recName;
    }
  }
  public function getJsonFilters() {
    return array(
    	'date' => JsonFilter::reportDateTime());
  }
  public function getTableName() {
    return 'Audit';
  }
  public function formatLabel() {
    $a = array();
    $a[] = self::$ACTIONS[$this->action];
    $a[] = $this->recName;
    if ($this->label)
      $a[] = ': ' . $this->label;
    return join(' ' , $a);
  }
  //
  static function asCriteria($ugid) {
    $c = new self();
    $c->userGroupId = $ugid;
    $c->Client = CriteriaJoin::requires(new Client_Rep());
    $c->User = CriteriaJoin::requires(new UserStub());
    return $c;
  }
}
class RepCritRec_Client extends RepCritRec {
  //
  public function getSqlClass() {
    return 'Client_Rep';
  }
  protected function assignSqlCriteriaValue(&$criteria, $fid, $value) {
    global $login;
    if ($fid == 'age')
      $fid = 'birth';
    $cv = RepCritValue::asSqlCriteriaValue($criteria, $fid, $value);
    if ($cv) {
      switch ($fid) {
        case 'birth':
          if ($login->super)
            $criteria->Hd_dob = Hdata_ClientDob::create()->setCriteriaValue($cv)->asMultiJoin(array(1, 2, 74, 75, 76));
          else
            $criteria->Hd_dob = Hdata_ClientDob::create()->setCriteriaValue($cv)->asJoin();
          break;
        default:
          $criteria->$fid = CriteriaValues::_and(get($criteria, $fid), $cv);
      }
    }
  }
}
class RepCrit_Client extends RepCritRec_Client {
  //
  public $userGroupId;
  public $uid;
  public $lastName;
  public $firstName;
  public $sex;
  public $age;
  public $birth;
  public $deceased;
  public $race;
  public $ethnicity;
  public $language;
  public $livingWill;
  public $poa;
  public $active;
  public $prompted_;
  //
  static $JOINS_TO = array(
    self::T_CLIENTS,
    self::T_ADDRESS,
    self::T_ICARDS,
    self::T_DIAGNOSES,
    self::T_MEDS,
    self::T_MEDHIST,
    self::T_ALLERGIES,
    self::T_PROCS,
    self::T_RESULTS,
    self::T_SOCTOB,
    self::T_IMMUNS,
    self::T_VITALS,
    self::T_SESSIONS,
    self::T_OFFICEVISIT);
  //
  public function getRecSort() {
    global $login;
    if ($login->super)
      return new RecSort('UserGroup.name', 'lastName', 'firstName');
    else
      return new RecSort('lastName', 'firstName');
  }
  public function toJsonObject(&$o) {
    global $login;
    if (! $login->super)
      unset($o->userGroupId);
    parent::toJsonObject($o);
  }
}
class Client_Rep extends ClientRec implements ReportRec, Revivable, NoAuthenticate {
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
  //
  public function getJsonFilters() {
    return array(
      'userRestricts' => JsonFilter::serializedObject(),
      'deceased' => JsonFilter::boolean(),
      'birth' => JsonFilter::reportDate(),
      'livingWill' => JsonFilter::boolean(),
      'poa' => JsonFilter::boolean(),
      'active' => JsonFilter::boolean());
  }
  //
  static function asCriteria($ugid) {
    global $login;
    $c = new self();
    if ($login->super) {
      $ug = new UserGroup();
      $ug->parentId = $ugid;
      $c->UserGroup = CriteriaJoin::requires($ug);
    } else {
      $c->userGroupId = $ugid;
    }
    return $c;
  }
}
class RepCrit_Med extends RepCritRec {
  //
  public $name;
  public $active;
  public $drugSubclass;
  //
  public function getSqlClass() {
    return 'Med_Rep';
  }
  protected function assignSqlCriteriaValue(&$criteria, $fid, $value) {
    if ($fid == 'drugSubclass') {
      $fid = 'name';
      if (is_numeric($value->value))
        $value->value = DrugName::asRegexpValue($value->value);
    }
    parent::assignSqlCriteriaValue($criteria, $fid, $value);
  }
}
class Med_Rep extends Med implements ReportRec_CsvJoinable {
  //
  public function toJsonObject(&$o) {
    $o->_label = $this->formatLabel();
    $o->_table = $this->getTableName();
  }
  public function formatLabel() {
    return $this->name;
  }
  public function getTableName() {
    return 'Medications';
  }
  //
  static function asCriteria($ugid) {
    $c = new self();
    $c->sessionId = CriteriaValue::isNull();
    return $c;
  }
}
class RepCrit_MedHist extends RepCritRec {
  //
  public $name;
  public $date;
  public $ncOrigrxGuid;  // DeaClassCode
  public $ncOrderGuid;   // FinalDestinationType
  public $ncExtPhysId;
  public $prompted_;
  //
  public function getSqlClass() {
    return 'MedHist_Rep';
  }
  protected function assignSqlCriteriaValue(&$criteria, $fid, $value) {
    global $login;
    $cv = RepCritValue::asSqlCriteriaValue($criteria, $fid, $value);
    if ($cv) {
      switch ($fid) {
        case 'date':
          if ($login->super)
            $criteria->Hd_date = Hdata_MedDate::create()->setCriteriaValue($cv)->asMultiJoin($login->getChildrenUgids());
          else
            $criteria->Hd_date = Hdata_MedDate::create()->setCriteriaValue($cv)->asJoin();
          break;
        default:
          $criteria->$fid = CriteriaValues::_and(get($criteria, $fid), $cv);
      }
    }
  }
}
class MedHist_Rep extends SessionMedNc implements ReportRec_CsvJoinable {
  //
  public function toJsonObject(&$o) {
    $o->_label = $this->formatLabel();
    $o->_table = $this->getTableName();
  }
  public function formatLabel() {
    return $this->getQuidText() . ': ' . $this->name;
  }
  public function getTableName() {
    return 'Med History';
  }
  public function getJsonFilters() {
    return array(
      'date' => JsonFilter::editableDate());
  }
  //
  static function asCriteria($ugid) {
    $c = new self();
    $c->sessionId = self::NEW_CROP_SID;
    return $c;
  }
}
class RepCrit_SocTob extends RepCritRec {
  //
  public $value;
  //
  public function getSqlClass() {
    return 'SocTob_Rep';
  }
}
class SocTob_Rep extends DataSync_Rep implements ReportRec_CsvJoinable {
  //
  public function toJsonObject(&$o) {
    $o->_label = $this->formatLabel();
    $o->_table = $this->getTableName();
  }
  public function stripValue() {
    return substr($this->value, 2, -2);
  }
  public function formatLabel() {
    return $this->stripValue();
  }
  public function getTableName() {
    return 'Social: Tobacco';
  }
  //
  static function asCriteria($ugid) {
    $c = new self();
    $c->dsyncId = 'sochx.tob.recode';
    $c->active = true;
    return $c;
  }
}
class RepCrit_Address extends RepCritRec {
  //
  public $addr1;
  public $addr2;
  public $city;
  public $state;
  public $zip;
  public $phone1;
  public $email1;
  //
  public function getSqlClass() {
    return 'Address_Rep';
  }
}
class Address_Rep extends Address implements ReportRec_CsvJoinable, Revivable {
  //
  public function getClientFk() {
    return 'tableId';
  }
  public function toJsonObject(&$o) {
    $o->_label = $this->formatLabel();
    $o->_table = $this->getTableName();
  }
  public function formatLabel() {
    $a = array();
    pushIfNotNull($a, $this->addr1);
    pushIfNotNull($a, $this->addr2);
    pushIfNotNull($a, self::formatCsz($this));
    return join(' ', $a);
  }
  public function getTableName() {
    return 'Address';
  }
  //
  static function asCriteria($ugid) {
    $c = new self();
    $c->tableCode = Address::TABLE_CLIENTS;
    $c->type = Address::TYPE_SHIP;
    return $c;
  }
}
class RepCrit_ICard extends RepCritRec {
  //
  public $planName;
  public $groupNo;
  public $subscriberNo;
  public $subscriberName;
  public $dateEffective;
  //
  public function getSqlClass() {
    return 'ICard_Rep';
  }
}
class ICard_Rep extends ICardRec implements ReportRec_CsvJoinable {
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
  public function toJsonObject(&$o) {
    $o->_label = $this->formatLabel();
    $o->_table = $this->getTableName();
  }
  public function formatLabel() {
    return $this->planName;
  }
  public function getTableName() {
    return 'Insurance';
  }
  //
  static function asCriteria($ugid) {
    $c = new static();
    return $c;
  }
}
class RepCrit_Diagnosis extends RepCritRec {
  //
  public $icd;
  public $text;
  public $date;
  public $dateClosed;
  public $active;
  public $status;
  //
  public function getSqlClass() {
    return 'Diagnosis_Rep';
  }
  protected function assignSqlCriteriaValue(&$criteria, $fid, $value) {
    global $login;
    $cv = RepCritValue::asSqlCriteriaValue($criteria, $fid, $value);
    if ($cv) {
      switch ($fid) {
        case 'date':
          if ($login->super)
            $criteria->Hd_date = Hdata_DiagnosisDate::create()->setCriteriaValue($cv)->asMultiJoin($login->getChildrenUgids());
          else
            $criteria->Hd_date = Hdata_DiagnosisDate::create()->setCriteriaValue($cv)->asJoin();
          break;
        case 'dateClosed':
          if ($login->super)
            $criteria->Hd_date = Hdata_DiagnosisDateClosed::create()->setCriteriaValue($cv)->asMultiJoin($login->getChildrenUgids());
          else
            $criteria->Hd_date = Hdata_DiagnosisDateClosed::create()->setCriteriaValue($cv)->asJoin();
          break;
        default:
          $criteria->$fid = CriteriaValues::_and(get($criteria, $fid), $cv);
      }
    }
  }
}
require_once 'php/data/rec/sql/Diagnoses.php';
class Diagnosis_Rep extends FaceDiagnosis implements ReportRec_CsvJoinable {
  //
  public function toJsonObject(&$o) {
    $o->_label = $this->formatLabel();
    $o->_table = $this->getTableName();
  }
  public function formatLabel() {
    return $this->formatName();
  }
  public function getTableName() {
    return 'Diagnoses';
  }
  //
  static function asCriteria($ugid) {
    $c = new self();
    $c->sessionId = CriteriaValue::isNull();
    return $c;
  }
}
class RepCrit_Session extends RepCritRec {
  //
  public $templateId;
  public $dateService;
  public $title;
  public $createdBy;
  public $closedBy;
  //
  public function getSqlClass() {
    return 'Session_Rep';
  }
  protected function assignSqlCriteriaValue(&$criteria, $fid, $value) {
    global $login;
    $cv = RepCritValue::asSqlCriteriaValue($criteria, $fid, $value);
    if ($cv) {
      switch ($fid) {
        case 'dateService':
          if ($login->super)
            $criteria->Hd_date = Hdata_SessionDos::create()->setCriteriaValue($cv)->asMultiJoin($login->getChildrenUgids());
          else
            $criteria->Hd_date = Hdata_SessionDos::create()->setCriteriaValue($cv)->asJoin();
          break;
        default:
          $criteria->$fid = CriteriaValues::_and(get($criteria, $fid), $cv);
      }
    }
  }
}
class Session_Rep extends SessionNote implements ReportRec_CsvJoinable {
  //
  public function getJsonFilters() {
    return array(
      'dateService' => JsonFilter::reportDate());
  }
  public function toJsonObject(&$o) {
    $o->_label = $this->formatLabel();
    $o->_table = $this->getTableName();
  }
  public function formatLabel() {
    return dateToString($this->dateService) . ': ' . $this->formatTitle();
  }
  public function getTableName() {
    return 'Sessions';
  }
  //
  static function asCriteria($ugid) {
    $c = new static();
    return $c;
  }
}
class RepCrit_Allergy extends RepCritRec {
  //
  public $agent;
  public $active;
  //
  public function getSqlClass() {
    return 'Allergy_Rep';
  }
}
class Allergy_Rep extends Allergy implements ReportRec_CsvJoinable {
  //
  public function toJsonObject(&$o) {
    $o->_label = $this->formatLabel();
    $o->_table = $this->getTableName();
  }
  public function formatLabel() {
    return $this->agent;
  }
  public function getTableName() {
    return 'Allergies';
  }
  //
  static function asCriteria($ugid) {
    $c = new self();
    $c->sessionId = CriteriaValue::isNull();
    return $c;
  }
}
class RepCrit_OfficeVisit extends RepCritRec {
  //
  public $date;
  public $userId;
  public $userGroupId;
  public $prompted_ = 'OV';
  //
  public function getSqlClass() {
    return 'ProcOfficeVisit_Rep';
  }
  public function toJsonObject(&$o) {
    global $login;
    if (! $login->super)
      unset($o->userGroupId);
    parent::toJsonObject($o);
  }
  protected function assignSqlCriteriaValue(&$criteria, $fid, $value) {
    global $login;
    $cv = RepCritValue::asSqlCriteriaValue($criteria, $fid, $value);
    if ($cv) {
      switch ($fid) {
        case 'date':
          if ($login->super)
            $criteria->Hd_date = Hdata_ProcDate::create()->setCriteriaValue($cv)->asMultiJoin($login->getChildrenUgids());
          else
            $criteria->Hd_date = Hdata_ProcDate::create()->setCriteriaValue($cv)->asJoin();
          break;
        default:
          $criteria->$fid = CriteriaValues::_and(get($criteria, $fid), $cv);
      }
    }
  }
}
class ProcOfficeVisit_Rep extends Proc implements ReportRec_CsvJoinable {
  //
  public function toJsonObject(&$o) {
    $o->_label = $this->formatLabel();
    $o->_table = $this->getTableName();
  }
  public function formatLabel() {
    return formatDate($this->date) . ': ' . (($this->User) ? $this->User->name : '');
  }
  public function getTableName() {
    return 'Encounter';
  }
  //
  static function asCriteria($ugid) {
    $c = new self();
    $c->ipc = Proc_OfficeVisit::$IPC;
    $c->User = CriteriaJoin::requires(new UserStub());
    return $c;
  }
}
class RepCrit_Proc extends RepCritRec {
  //
  public $ipc;
  public $date;
  public $cat;
  public $providerId;
  public $addrFacility;
  public $location;
  public $value;
  public $userId;
  public $userGroupId;
  public $prompted_;
  //
  public function getSqlClass() {
    return 'Proc_Rep';
  }
  protected function assignSqlCriteriaValue(&$criteria, $fid, $value) {
    global $login;
    $cv = RepCritValue::asSqlCriteriaValue($criteria, $fid, $value);
    if ($cv) {
      switch ($fid) {
        case 'cat':
          $criteria->Ipc = Ipc::asRequiredJoin(null, $cv);
          break;
        case 'date':
          if ($login->super)
            $criteria->Hd_date = Hdata_ProcDate::create()->setCriteriaValue($cv)->asMultiJoin(array(1, 2, 74, 75, 76));
          else
            $criteria->Hd_date = Hdata_ProcDate::create()->setCriteriaValue($cv)->asJoin();
          break;
        case 'value':
          $rec = new ProcResult();
          $rec->value = CriteriaValue::regexp("'(positive)|(negative)|(pos)|(neg)|(^(-|\\\\+){0,1}([0-9]+\\\\.[0-9]*|[0-9]*\\\\.[0-9]+|[0-9]+)$)'");
          $criteria->ProcResults = CriteriaJoin::requiresCountAtLeast($rec, 1);
          break;
        default:
          $criteria->$fid = CriteriaValues::_and(get($criteria, $fid), $cv);
      }
    }
  }
}
class Proc_Rep extends Proc implements ReportRec_CsvJoinable {
  //
  public function toJsonObject(&$o) {
    $o->_label = $this->formatLabel();
    $o->_table = $this->getTableName();
  }
  public function formatLabel() {
    return formatApproxDate($this->date) . ': ' . (($this->Ipc) ? $this->Ipc->name : '');
  }
  public function getTableName() {
    return 'Procedures';
  }
  protected function assignSqlCriteriaValue(&$criteria, $fid, $value) {
    global $login;
    $cv = RepCritValue::asSqlCriteriaValue($criteria, $fid, $value);
    if ($cv) {
      switch ($fid) {
        case 'date':
          if ($login->super)
            $criteria->Hd_date = Hdata_ProcDate::create()->setCriteriaValue($cv)->asMultiJoin($login->getChildrenUgids());
          else
            $criteria->Hd_date = Hdata_ProcDate::create()->setCriteriaValue($cv)->asJoin();
          break;
        default:
          $criteria->$fid = CriteriaValues::_and(get($criteria, $fid), $cv);
      }
    }
  }
  //
  static function asCriteria($ugid) {
    //$c = parent::asCriteria();  // TODO: could modify to exclude joins if arg passed in indicating for COUNT(*)
    $c = new self();
    $c->Ipc = Ipc::asRequiredJoin();
    return $c;
  }
}
class RepCrit_ProcResult extends RepCritRec {
  //
  public $ipc;
  public $date;
  public $value;
  public $valueUnit;
  public $interpretCode;
  public $comments;
  public $orderBy;
  public $prompted_;
  //
  public function getSqlClass() {
    return 'ProcResult_Rep';
  }
  protected function assignSqlCriteriaValue(&$criteria, $fid, $value) {
    global $login;
    $cv = RepCritValue::asSqlCriteriaValue($criteria, $fid, $value);
    if ($cv) {
      switch ($fid) {
        case 'orderBy':
          $rec = new TrackItem();
          $rec->orderBy = $cv;
          $criteria->TrackItem = CriteriaJoin::requires($rec, 'procId');
          break;
        case 'date':
          if ($login->super)
            $criteria->Hd_date = Hdata_ProcDate::create()->setCriteriaValue($cv)->asMultiJoin(array(1, 2, 74, 75, 76));
          else
            $criteria->Hd_date = Hdata_ProcDate::create()->setCriteriaValue($cv)->asJoin();
          break;
        default:
          $criteria->$fid = CriteriaValues::_and(get($criteria, $fid), $cv);
      }
    }
  }
}
class ProcResult_Rep extends ProcResult implements ReportRec_CsvJoinable {
  //
  public function toJsonObject(&$o) {
    $o->_label = $this->formatLabel();
    $o->_table = $this->getTableName();
  }
  /* Commented out because didn't work for joining in patient reports... why was this done?
   * SQL generated was
   * JOIN proc_results T1 ON T0.`client_id`=T1.`proc_id`
   */
  //public function getClientFk() {
  //  return 'procId';
  //}
  public function formatLabel() {
    return formatApproxDate($this->Proc->date) . ': ' . self::summarizeResult(null, $this);
  }
  public function getTableName() {
    return 'Results';
  }
  //
  static function asCriteria($ugid) {
    $c = new static();
    $c->Ipc = Ipc::asRequiredJoin();
    $c->Proc = Proc::asRequiredJoin();
    return $c;
  }
}
class RepCrit_Immun extends RepCritRec {
  //
  public $name;
  public $tradeName;
  public $dateGiven;
  public $manufac;
  public $lot;
  public $dose;
  public $route;
  public $site;
  public $adminBy;
  //
  public function getSqlClass() {
    return 'Immun_Rep';
  }
  protected function assignSqlCriteriaValue(&$criteria, $fid, $value) {
    global $login;
    $cv = RepCritValue::asSqlCriteriaValue($criteria, $fid, $value);
    if ($cv) {
      switch ($fid) {
        case 'date':
          if ($login->super)
            $criteria->Hd_date = Hdata_ImmunDate::create()->setCriteriaValue($cv)->asMultiJoin($login->getChildrenUgids());
          else
            $criteria->Hd_date = Hdata_ImmunDate::create()->setCriteriaValue($cv)->asJoin();
          break;
        default:
          $criteria->$fid = CriteriaValues::_and(get($criteria, $fid), $cv);
      }
    }
  }
  public function getTemplatePid() {
    static $pid;
    if ($pid == null)
      $pid = Immuns::getPid();
    return $pid;
  }
}
class Immun_Rep extends Immun implements ReportRec_CsvJoinable {
  //
  public function toJsonObject(&$o) {
    $o->_label = $this->formatLabel();
    $o->_table = $this->getTableName();
  }
  public function formatLabel() {
    return formatApproxDate($this->dateGiven) . ': ' . $this->name;
  }
  public function getTableName() {
    return 'Immunizations';
  }
  //
  static function asCriteria($ugid) {
    $c = new self();
    $c->sessionId = CriteriaValue::isNull();
    return $c;
  }
}
class RepCrit_Vital extends RepCritRec {
  //
  public $date;
  public $pulse;
  public $resp;
  public $bpSystolic;
  public $bpDiastolic;
  public $temp;
  public $wt;
  public $height;
  public $bmi;
  public $hc;
  public $wc;
  public $prompted_;
  //
  public function getSqlClass() {
    return 'Vital_Rep';
  }
  protected function assignSqlCriteriaValue(&$criteria, $fid, $value) {
    global $login;
    $cv = RepCritValue::asSqlCriteriaValue($criteria, $fid, $value);
    if ($cv) {
      switch ($fid) {
        case 'date':
          if ($login->super)
            $criteria->Hd_date = Hdata_VitalsDate::create()->setCriteriaValue($cv)->asMultiJoin($login->getChildrenUgids());
          else
            $criteria->Hd_date = Hdata_VitalsDate::create()->setCriteriaValue($cv)->asJoin();
          break;
        default:
          $criteria->$fid = CriteriaValues::_and(get($criteria, $fid), $cv);
      }
    }
  }
}
class Vital_Rep extends Vital implements ReportRec_CsvJoinable {
  //
  public function toJsonObject(&$o) {
    $o->_label = $this->formatLabel();
    $o->_table = $this->getTableName();
  }
  public function getTableName() {
    return 'Vitals';
  }
  public function formatLabel() {
    return formatMDY($this->date) . ': ' . join(', ', $this->getAllValues());
  }
  //
  static function asCriteria($ugid) {
    $c = new self();
    $c->sessionId = CriteriaValue::isNull();
    return $c;
  }
}
class DataSync_Rep extends SqlRec {
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
