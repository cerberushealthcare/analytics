<?php
require_once 'php/data/rec/_Rec.php';
//
class Facesheet extends Rec {
  //
  public $cid;
  public /*Client*/ $Client;
  public /*UserGroup->Doctors[userId]*/ $UserGroup;
  //
  static function from($cid) {
    $client = static::getClient($cid);
    if ($client) {
      $me = new static();
      $me->cid = $cid;
      $me->Client = $client;
      $me->UserGroup = static::getUserGroup($cid);
      return $me;
    }
  }
  //
  protected static function getClient($cid) {
    return Clients::get($cid);
  }
  protected static function getUserGroup($cid) {
    return UserGroups::getMineWithAddress();
  }
  protected static function getActiveDiagnoses($cid) {
    return Diagnoses::getActive($cid); 
  }
  protected static function getActiveMeds($cid) {
    return Meds::getActive($cid);
  }
  protected static function getActiveAllergies($cid) {
    return Allergies::getActive($cid);
  }
  protected static function getActiveOrders($cid) {
    return OrderEntry::getActiveItems($cid);
  }
  protected static function getProcedures($cid) {
    return Procedures::getAllProcOnly($cid);
  }
  protected static function getResults($cid) {
    return Procedures::getAllDiagnostic($cid);
  }
  protected static function getImmuns($cid) {
    return Immuns::getActive($cid); 
  }
  protected static function getSessions($cid) {
    return Sessions::getStubs($cid);
  }
  protected static function getVitals($cid) {
    return Vitals::getActive($cid);
  }
  protected static function getMedHxs($cid) {
    return MedHx::fetchAll($cid);
  }
  protected static function getFamHxs($cid) {
    return FamHx::fetchAll($cid);
  }
  protected static function getSocHxs($cid) {
    return SocHx::fetchAll($cid);
  }
}
class Facesheet_Hl7Ccd extends Facesheet {
  //
  public $cid;
  public /*Client*/ $Client;
  public /*UserGroup*/ $UserGroup;
  public /*ErxUser*/ $ErxUser;
  public /*Allergy[]*/ $Allergies;
  public /*Immun[]*/ $Immuns;
  public /*Session[]*/ $Sessions;
  public /*Med[]*/ $Meds;
  public /*Vital[]*/ $Vitals;
  public /*Diagnosis[]*/ $Diagnoses;
  public /*Proc[]*/ $Procs;
  //
  private $sids;
  //
  public function attachEncounterSid() {
    if (! empty($this->Sessions)) {
      $this->sids = static::buildSidMap($this->Sessions);
      $this->attachSidTo($this->Meds);
      $this->attachSidTo($this->Diagnoses);      
      $this->attachSidTo($this->Procs);
      $this->attachSidTo($this->Results);
      $this->attachSidTo($this->Vitals);
    }
  }
  protected function attachSidTo(&$recs, $fid = 'date') {
    if (! empty($recs)) {
      foreach ($recs as &$rec) {
        $date = substr($rec->$fid, 0, 10);
        if (isset($this->sids[$date]))
          $rec->encounterSid = $this->sids[$date];
      }
    }
  } 
  //
  static function from($cid) {
    $me = parent::from($cid);
    if ($me) {
      $me->ErxUser = ErxUsers::getMe();
      $me->Meds = static::getActiveMeds($cid);
      $me->Allergies = static::getActiveAllergies($cid);
      $me->Diagnoses = static::getActiveDiagnoses($cid);
      $me->Results = static::getResults($cid);
      $me->Procs = static::getProcedures($cid);
      $me->Immuns = static::getImmuns($cid);
      $me->Sessions = static::getSessions($cid);
      $me->Vitals = static::getVitals($cid);
      $me->Medhx = static::getMedhxs($cid);
      $me->attachEncounterSid();
    }
    return $me;
  }
  //
  protected static function buildSidMap($sessions) {
    $map = array();
    foreach ($sessions as $sess) {
      $date = substr($sess->dateService, 0, 10);
      $map[$date] = $sess->sessionId;
    }
    return $map;
  }
  protected static function extractDos($recs, $dos, $fid = 'date') {
    if ($recs)
      return array_filter($recs, function($rec) use ($fid, $dos) {
        return substr($rec->$fid, 0, 10) == substr($dos, 0, 10);
      });
  }
}
class Facesheet_Complete extends Facesheet {
  //
  public $cid;
  public /*Client*/ $Client;
  public /*UserGroup*/ $UserGroup;
  public /*ErxUser*/ $ErxUser;
  public /*Med[]*/ $Meds;
  public /*Allergy[]*/ $Allergies;
  public /*Vital[]*/ $Vitals;
  public /*Diagnosis[]*/ $Diagnoses;
  public /*Proc[]*/ $Procs;
  public /*Immun[]*/ $Immuns;
  public /*Session[]*/ $Sessions;
  public /*MedHx[]*/ $MedHxs;
  public /*FamHx[]*/ $FamHxs;
  public /*SocHx[]*/ $SocHxs;
  //
  static function fetch($cid) {
    $me = parent::from($cid);
    if ($me) {
      $me->ErxUser = ErxUsers::getMe();
      $me->Meds = static::getActiveMeds($cid);
      $me->Allergies = static::getActiveAllergies($cid);
      $me->Diagnoses = static::getActiveDiagnoses($cid);
      $me->Results = static::getResults($cid);
      $me->Procs = static::getProcedures($cid);
      $me->Immuns = static::getImmuns($cid);
      $me->Sessions = static::getSessions($cid);
      $me->Vitals = static::getVitals($cid);
      $me->MedHxs = static::getMedHxs($cid);
      $me->FamHxs = static::getFamHxs($cid);
      $me->SocHxs = static::getSocHxs($cid);
    }
    return $me;
  }
}
class Facesheet_Merge extends Facesheet {
  static function fetch($cid) {
    $me = parent::from($cid);
    if ($me) {
      $me->Diagnoses = static::getActiveDiagnoses($cid);
      $me->Procs = static::getProcedures($cid);
      $me->Immuns = static::getImmuns($cid);
      $me->Sessions = static::getSessions($cid);
      $me->Vitals = static::getVitals($cid);
      $me->Orders = static::getActiveOrders($cid);
      $me->Client = PatientStub::fetch($cid);
      $me->Client_ = $me->Client;
      unset($me->UserGroup);
    }
    return $me;
  }
}
class Facesheet_Visit extends Facesheet {
  //
  static function from($cid, $dos = null) {
    if ($dos == null) 
      $dos = nowNoQuotes();
    $me = parent::from($cid);
    if ($me) {
      $me->Diagnoses = static::getActiveDiagnoses($cid);
      $me->Meds = static::getActiveMeds($cid);
      $me->Allergies = static::getActiveAllergies($cid);
      $me->Immuns = static::extractDos(static::getImmuns($cid), $dos, 'dateGiven');
      $me->TrackItems = static::extractDos(static::getActiveOrders($cid), $dos, 'orderDate');
      $me->Sched = Sched_Face::fetchNext($cid, $dos);
      $me->Vitals = static::extractDos(static::getVitals($cid), $dos);
    }
    return $me;
  }
}
class Facesheet_Hl7Immun extends Facesheet {
  //
  public $cid;
  public /*Client*/ $Client;
  public /*UserGroup*/ $UserGroup;
  public /*Immun[]*/ $Immuns;
  public /*Immun_HL7Codes*/ $Immun_HL7;
  //
  static function from($cid) {
    $me = parent::from($cid);
    if ($me) {
      $me->Immuns = static::getImmuns($cid);
      $me->Immun_HL7 = Immuns::getHL7Codes($cid);
    }
    return $me;
  }
}
class Facesheet_Hl7Syndrome extends Facesheet {
  //
  public $cid;
  public /*Client*/$Client;
  public /*UserGroup*/$UserGroup;
  public /*Session_SS*/$Session;
  //
  static function from($cid, $npi, $session) {
    $me = parent::from($cid);
    $me->Session = Session_SS::from($session);
    $me->Client->Address_Home->fetchCountyCode();
    $me->UserGroup->npi = $npi;
    return $me;
  }
  //
  public function getData($fid) {
    return getr($this->Session, "Data.$fid");
  }
  public function getDataCode($fid) {
    $data = $this->getData($fid);
    if ($data)
      return $data->code;
  }
}
class Session_SS extends Rec {
  //
  public $sessionId;
  public $date;
  public $dateCreated;
  public /*Diagnosis_SS[]*/$Diagnoses;
  public /*DataSync_SS*/$Data;
  //
  static function from($o) {
    $me = new static();
    $me->sessionId = $o->sessionId;
    $me->date = $o->date;
    $me->dateCreated = $o->dateCreated;
    $me->Diagnoses = Diagnosis_SS::all($o->diagnoses);
    $me->Data = DataSync_SS::from($o->dsyncs);
    return $me;
  }
}
class Diagnosis_SS extends Rec {
  //
  public $icd;
  public $text;
  //
  static function all($diagnoses) {
    $us = array();
    foreach ($diagnoses as $d) {
      $us[] = static::from($d);
    }
    return $us;
  }
  static function from($d) {
    $me = new static();
    $me->icd = $d->icd;
    $me->text = $d->text;
    return $me;
  }
} 
class DataSync_SS extends Rec {
  //
  public $chiefComplaint;
  public $diagnosisType;
  public $dischargeDisposition;
  public $facilityVisitType;
  public $identifierType;
  public $observationResultStatus;
  public $onsetDate;
  public $patientClass;
  public $temp;
  //
  static function from($dsyncs) {
    $me = new static();
    foreach ($dsyncs as $fid => $data) {
      if (substr($fid, 0, 3) == 'ss.') {
        $fid = substr($fid, 3);
        $me->$fid = new Data_SS($data);
      }
    }
    return $me;
  }
}
class Data_SS extends Rec {
  //
  public $text;
  public $code;
}
class Facesheet_Hl7Adt extends Facesheet {
  //
  public $cid;
  public /*Client*/ $Client;
  public /*UserGroup*/ $UserGroup;
  public /*Diagnosis[]*/ $Diagnoses;
  public /*Session*/ $Session_Mr;
  public /*UserStub*/ $Doctor_Mr;
  //
  static function from($cid) {
    $me = parent::from($cid);
    if ($me) {
      $me->Diagnoses = static::getActiveDiagnoses($cid);
      $me->Session_Mr = static::getSession_Mr($cid);
      $me->Doctor_Mr = static::getDoctor_Mr($me->Session_Mr);
    }
    return $me;
  }
  //
  protected static function getSession_Mr($cid) {
    return current(parent::getSessions($cid));
  }
  protected static function getDoctor_Mr($session) {
    $users = UserGroups::getUserMap();
    return $users[$session->closedBy || $session->assignedTo];
  }
}
class Facesheet_Hl7Adt_PubHealth extends Facesheet_Hl7Adt {
  //
}
class Facesheet_Hl7Adt_Papyrus extends Facesheet_Hl7Adt {
  //
}
//
class Sched_Face extends SqlRec {
  //
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
  public $schedEventId;
  //
  public function getSqlTable() {
    return 'scheds';
  }
  public function getDesc() {
    $time = new Military($this->timeStart);
    return formatLongDate($this->date) . ' at ' . $time->formatted();
  }
  //
  static function fetchNext($cid, $fromDate = null) {
    if ($fromDate == null)
      $fromDate = nowNoQuotes();
    $c = new static();
    $c->clientId = $cid;
    $c->date = CriteriaValue::greaterThan($fromDate);
    return static::fetchOneBy($c);
  }
}
//
require_once 'php/data/rec/sql/Clients.php';
require_once 'php/data/rec/sql/Diagnoses.php';
require_once 'php/data/rec/sql/Immuns.php';
require_once 'php/c/sessions/Sessions.php';
require_once 'php/data/rec/sql/Vitals.php';
require_once 'php/data/rec/sql/UserGroups.php';
require_once 'php/data/rec/sql/ErxUsers.php';
require_once 'php/data/rec/sql/Meds.php';
require_once 'php/data/rec/sql/Allergies.php';
require_once 'php/data/rec/sql/Procedures.php';
require_once 'php/data/rec/sql/OrderEntry.php';
require_once 'php/data/rec/sql/_DataHxRecs.php';
