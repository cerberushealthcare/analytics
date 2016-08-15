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
    $client = static::fetchClient($cid);
    if ($client) {
      $me = new static();
      $me->cid = $cid;
      $me->Client = $client;
      $me->UserGroup = static::fetchUserGroup($cid);
      return $me;
    }
  }
  public function getEarliestDate() {
    if ($this->Encounters) {
      $e = end($this->Encounters);
      return $e->date;
    }
  }
  //
  protected static function fetchClient($cid) {
    return Clients::get($cid);
  }
  protected static function fetchUserGroup($cid) {
    return UserGroups::getMineWithAddress();
  }
  protected static function fetchAllDiagnoses($cid) {
    return Diagnoses::getAll($cid); 
  }
  protected static function fetchActiveDiagnoses($cid) {
    return Diagnoses::getActive($cid); 
  }
  protected static function fetchActiveMeds($cid) {
    return Meds::getActive($cid);
  }
  protected static function fetchActiveAllergies($cid) {
    return Allergies::getActive($cid);
  }
  protected static function fetchActiveOrders($cid) {
    return OrderEntry::getActiveItems($cid);
  }
  protected static function fetchProcedures($cid) {
    return Procedures::getAllProcOnly($cid);
  }
  protected static function fetchResults($cid) {
    return Procedures::getAllDiagnostic($cid);
  }
  protected static function fetchPlanOfCare($cid) {
    return Procedures::getAllPlanOfCare($cid);
  }
  protected static function fetchEncounters($cid, $lastOnly = false) {
    return Procedures::getEncounters($cid, $lastOnly);
  }
  protected static function fetchSmoking($cid) {
    return Procedures::getSmokingStatus($cid);
  }
  protected static function fetchFuncStatus($cid) {
    return Procedures::getAllFuncStatus($cid);
  }
  protected static function fetchImmuns($cid) {
    return Immuns::getActive($cid); 
  }
  protected static function fetchSessions($cid) {
    return Sessions::getStubs($cid);
  }
  protected static function fetchVitals($cid, $lastOnly = true) {
    $recs = Vitals::getActive($cid, $lastOnly);
    return $recs;
  }
  protected static function fetchMedHxs($cid) {
    return MedHx::fetchAll($cid);
  }
  protected static function fetchFamHxs($cid) {
    return FamHx::fetchAll($cid);
  }
  protected static function fetchSocHxs($cid) {
    return SocHx::fetchAll($cid);
  }
  protected static function extractDos($recs, $dos, $fid = 'date') {
    if ($recs) {
      return array_filter($recs, function($rec) use($fid, $dos) {
        return substr($rec->$fid, 0, 10) == substr($dos, 0, 10);
      });
    }
  }
  protected static function extractRange($recs, $from, $to, $fid = 'date') {
    if ($recs)
      return array_filter($recs, function($rec) use ($fid, $from, $to) {
        $date = substr($rec->$fid, 0, 10);
        return $date >= $from && $date < $to;
      });
  }
}
class Facesheet_Qrda extends Facesheet { /*may not need this*/
  //
  static function from($cid, $from, $to) {
    $me = parent::from($cid);
    if ($me) {
      $me->ErxUser = ErxUsers::getMe();
      $me->Encounters = static::extractRange(Procedures::getEncounters($cid), $from, $to);
      $me->Results = static::extractRange(Procedures::getAllDiagnostic($cid), $from, $to);
      $me->Diagnoses = Diagnoses::getAll($cid);
      $me->Procs = Procedures::getAllProcOnly($cid);
      $me->Medhx = MedHx::fetchAll($cid);
      return $me;
    }
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
      $me->Meds = static::fetchActiveMeds($cid);
      $me->Allergies = static::fetchActiveAllergies($cid);
      $me->Diagnoses = static::fetchAllDiagnoses($cid);
      $me->Results = static::fetchResults($cid);
      $me->Procs = static::fetchProcedures($cid);
      $me->Immuns = static::fetchImmuns($cid);
      $me->Sessions = static::fetchSessions($cid);
      $me->Vitals = static::fetchVitals($cid);
      $me->Medhx = static::fetchMedhxs($cid);
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
}
class Facesheet_Ccda extends Facesheet {
  //
  static function fetch($cid, $lastEncounterOnly/*to restrict certain elements*/ = false) {
    require_once 'php/c/visit-summaries/VisitSummaries.php';
    $me = parent::from($cid);
    if ($me) {
      $me->Client->ssn = $me->Client->cdata1;
      $me->ErxUser = ErxUsers::getMe();
      $me->setMeds(static::fetchActiveMeds($cid));
      $me->Allergies = static::fetchActiveAllergies($cid);
      $me->Diagnoses = static::fetchActiveDiagnoses($cid);
      $me->Results = static::fetchResults($cid);
      $me->Procs = static::fetchProcedures($cid);
      $me->PlanOfCare = static::fetchPlanOfCare($cid);
      $me->Smoking = static::fetchSmoking($cid);
      $me->FuncStatus = static::fetchFuncStatus($cid);
      $me->Immuns = static::fetchImmuns($cid);
      $me->Immun_HL7 = Immuns::getHL7Codes($cid);
      $me->Sessions = static::fetchSessions($cid);
      $me->Vitals = static::fetchVitals($cid);
      $me->MedHxs = static::fetchMedHxs($cid);
      $me->FamHxs = static::fetchFamHxs($cid);
      $me->SocHxs = static::fetchSocHxs($cid);
      $me->setInstructs(VisitSummaries::getInstructs($cid));
      $me->Encounters = static::fetchEncounters($cid, $lastEncounterOnly);
      $me->setEncounterDiagnoses();
      $me->Referrals = OrderEntry::getActiveReferrals($cid);
      $me->Followups = OrderEntry::getActiveFollowups($cid);
      $me->FutureTests = OrderEntry::getFutureTests($cid);
      $me->PendingTests = OrderEntry::getPendingTests($cid);
      if ($lastEncounterOnly) {
        $me->ReasonForVisit = Procedures::getReasonForVisit($cid);
      } else {
        $me->ReasonForReferral = end($me->Referrals);
      }
    }
    return $me;
  }
  //
  public function hasPlanOfCare() {
    $plan = get($this, 'PlanOfCare');
    $pending = get($this, 'PendingTests');
    $future = get($this, 'FutureTests'); 
    $followups = get($this, 'Followups');
    $referrals = get($this, 'Referrals');
    return (! empty($plan) || ! empty($pending) || ! empty($future) || ! empty($followups) || ! empty($referrals));
  }
  //
  protected function setInstructs($recs) {
    if (empty($recs)) {
      $this->Instructs = null;
      $this->InstructsPda = null;
    } else {
      $this->Instructs = array();
      $this->InstructsPda = array();
      foreach ($recs as $rec) {
        if (substr($rec, 0, 4) == 'PDA:')
          $this->InstructsPda[] = substr($rec, 5);
        else
          $this->Instructs[] = $rec;
      }
    }
  }
  protected function setEncounterDiagnoses() {
    if ($this->Encounters) {
      foreach ($this->Encounters as $encounter) {
        $dos = substr($encounter->date, 0, 10);
        $encounter->Diagnoses = array();
        foreach ($this->Diagnoses as $diag) {
          if (substr($diag->date, 0, 10) == $dos) {
            $encounter->Diagnoses[] = $diag;
          }
        }
      }
    }
  }
  protected function setMeds($meds) {
    $m = array();
    $a = array();
    foreach ($meds as $med) {
      if ($med->wasAdminInOffice()) {
        $a[] = $med;
      } else {
        $m[] = $med;
      }
    }
    $this->Meds = $m;
    $this->MedsAdmin = $a;
  }
}
class Facesheet_Visit extends Facesheet {
  //
  static function from($cid, $dos = null) {
    if ($dos == null) 
      $dos = nowNoQuotes();
    $me = parent::from($cid);
    if ($me) {
      $me->Diagnoses = static::fetchActiveDiagnoses($cid);
      $me->Meds = static::fetchActiveMeds($cid);
      $me->Allergies = static::fetchActiveAllergies($cid);
      $me->Immuns = static::extractDos(static::fetchImmuns($cid), $dos, 'dateGiven');
      $me->TrackItems = static::extractDos(static::fetchActiveOrders($cid), $dos, 'orderDate');
      $me->Sched = Sched_Face::fetchNext($cid);
      $me->Vitals = static::extractDos(static::fetchVitals($cid), $dos);
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
      $me->Immuns = static::fetchImmuns($cid);
      $me->Immun_HL7 = Immuns::getHL7Codes($cid);
    }
    return $me;
  }
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
      $me->Diagnoses = static::fetchActiveDiagnoses($cid);
      $me->Session_Mr = static::fetchSession_Mr($cid);
      $me->Doctor_Mr = static::fetchDoctor_Mr($me->Session_Mr);
    }
    return $me;
  }
  //
  protected static function fetchSession_Mr($cid) {
    return current(parent::getSessions($cid));
  }
  protected static function fetchDoctor_Mr($session) {
    $users = UserGroups::getUserMap();
    $id = ($session->closedBy) ? $session->closedBy : $session->assignedTo;
    return geta($users, $id);
  }
}
class Facesheet_Hl7Adt_PubHealth extends Facesheet_Hl7Adt {
  //
}
class Facesheet_Hl7Adt_Papyrus extends Facesheet_Hl7Adt {
  //
}
//
require_once 'php/data/rec/sql/_SchedRec.php';
class Sched_Face extends SchedRec {
  //
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
    $c->setDateCriteria(CriteriaValue::greaterThan($fromDate));
    return static::fetchOneBy($c);
  }
}
class DataSync_Face extends SqlRec {
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
  //
  static function deleteForSession($cid, $sid) {
    if ($cid == null || $sid == null)
      throw new SecurityException('All key values required'); 
    $c = new static();
    $sql = 'DELETE FROM ' . $c->getSqlTable() . " WHERE client_id=$cid AND session_id=$sid";
    Dao::query($sql);
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
