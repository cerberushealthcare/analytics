<?php
require_once 'php/data/rec/sql/_VisitSummaryRec.php';
require_once 'php/c/facesheets/Facesheets.php';
require_once 'php/c/visit-summaries/VisitSummaryPdf.php';
//
/**
 * Patient Visit Summaries
 * @author Warren Hornsby
 */
class VisitSummaries {
  //
  static function /*VisitSummary*/getPending($cid) {
    $rec = VisitSummary::fetchPending($cid);
    return $rec;
  }
  static function /*string[]*/getInstructs($cid) {
    $rec = static::getPending($cid);
    if ($rec)
      return $rec->getInstructsArray();
  }
  static function /*VisitSummary*/finalize($o) {
    global $login;
    $rec = VisitSummary::revive($o);
    $fs = Facesheets::asVisitSummary($rec->clientId, $rec->dos);
    $rec->finalize($fs, $login->userId);
    return $rec->save();
  }
  static function /*VisitSummary*/getFinal($cid, $fid) {
    $rec = VisitSummary::fetchFinal($cid, $fid);
    return $rec;
  }
  static function printPdf(/*VisitSummary*/$rec) {
    /*
    $days = daysBetween($rec->dos, nowNoQuotes());
    if ($days > 3)
      Proc_VisitSummary3d::record($rec->clientId);
    else
      Proc_VisitSummary::record($rec->clientId);
    */
    VisitSummaryPdf::create($rec)->download();
  }
  static function reprintPdf($cid, $fid) {
    $rec = static::getFinal($cid, $fid);
    VisitSummaryPdf::create_asReprint($rec)->download();
  }
}
//
class VisitSummary extends VisitSummaryRec {
  //
  public $clientId;
  public $finalId; /*0 for pending, otherwise Unix timestamp when produced*/ 
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
  public function toJsonObject(&$o) {
    $o->instructs = $this->getInstructsArray();
  }
  public function getInstructsArray() {
    return jsondecode($this->instructs);
  }
  public function finalize($fs, $userId) {
    $this->finalId = strtotimeAdjusted(nowNoQuotes());
    $this->setFinalHtml($fs);
    $this->finalizedBy = $userId;
    $this->diagnoses = null;
    $this->iols = null;
    $this->instructs = null;
    $this->vitals = null;
  }
  //
  static function revive($o) {
    $me = new static();
    $me->clientId = $o->clientId;
    $me->finalId = $o->finalId;
    $me->dos = $o->dos;
    $me->sessionId = $o->sessionId;
    $me->diagnoses = $o->diagnoses;
    $me->iols = $o->iols;
    $me->instructs = jsonencode($o->instructs);
    $me->vitals = $o->vitals;
    $me->meds = $o->meds;
    return $me;
  }
  static function fetchFinal($cid, $fid) {
    $c = new static();
    $c->clientId = $cid;
    $c->finalId = $fid;
    return static::fetchOneBy($c); 
  }
  static function fetchPending($cid) {
    $c = new static();
    $c->clientId = $cid;
    $c->finalId = static::FINAL_ID_PENDING;  // '0'
    return static::fetchOneBy($c); 
  }
  //
  protected function setFinalHtml($fs) {
    $this->finalHead = $this->makeHead($fs);
    $this->finalBody = $this->makeBody($fs);
  }
  protected function makeHead($fs) {
    $h = new Html();
    $h->br($fs->Client->getFullName())
      ->br('Visit Summary')
      ->br(formatLongDate($this->dos))
      ->br($fs->UserGroup->name)
      ->br('Date Produced: ' . $this->getDateProduced());
    return $h->out();
  }
  protected function makeBody($fs) {
    $secs = array();
    $secs[] = $this->makeVitalsSec($fs);
    $secs[] = $this->makeDiagSec();
    $secs[] = $this->makeAllerSec($fs);
    $secs[] = $this->makeLabsSec($fs);
    $secs[] = $this->makePlanSec();
    $secs[] = $this->makeOrderSec($fs);
    $secs[] = $this->makeImmunSec($fs);
    $secs[] = $this->makeMedChangeSec($fs);
    $secs[] = $this->makeMedSec($fs);
    $secs[] = $this->makeNextApptSec($fs);
    logit_r($secs);
    return implode('', $secs);
  }
  protected function makeVitalsSec($fs) {
    $vital = $fs->Vitals;
    if (empty($vitals))
      $vital = Vital_Vs::from($this);
    if (! empty($vital)) 
      return $this->makeSecList('Vital Signs', $vital->getAllFriendlyValues());
  }
  protected function makeDiagSec() {
    $diags = jsondecode($this->diagnoses);
    if (! empty($diags)) 
      return $this->makeSecList("Today's Diagnoses", $diags);
  }
  protected function makeAllerSec($fs) {
    if (! empty($fs->Allergies)) {
      foreach ($fs->Allergies as &$rec) 
        $rec = $rec->getDesc();
        return $this->makeSecList('Allergies', $fs->Allergies);
    }
  }
  protected function makePlanSec() {
    $plan = jsondecode($this->instructs);
    if (! empty($plan)) 
      return $this->makeSecList('Your Plan', $plan);
  }
  protected function makeOrderSec($fs) {
    if (! empty($fs->TrackItems)) {
      foreach ($fs->TrackItems as &$rec) 
        $rec = $rec->trackDesc;
      return $this->makeSecList('Ordered Today', $fs->TrackItems);
    }
  }
  protected function makeLabsSec() {
    if (! empty($this->iols)) 
      return $this->makeSec("Today's Results", static::extractUl($this->iols));
  }
  protected function makeImmunSec($fs) {
    if (! empty($fs->Immuns)) {
      foreach ($fs->Immuns as &$rec) 
        $rec = $rec->name;
      return $this->makeSecList('Immunizations Given', $fs->Immuns);
    }
  }
  protected function makeMedSec($fs) {
    if (! empty($fs->Meds)) {
      foreach ($fs->Meds as &$rec) 
        $rec = $rec->name . ' (' . Med::friendlySig($rec->text) . ')';
      return $this->makeSecList('Current Medication List', $fs->Meds);
    }
  }
  /*
  protected function makeMedSec($fs) {
    if (! empty($fs->Meds)) {
      $h = new Html();
      $h->table_(array('border'=>'1', 'cellpadding'=>'3'));
      foreach ($fs->Meds as &$rec)
        $h->tr(array($rec->name, Med::friendlySig($rec->text)));
      $h->_()->p_()->nbsp()->_();
      return $this->makeSec('Current Medication List', $h->out());
    }
  }
  */
  protected function makeMedChangeSec($fs) {
    $meds = jsondecode($this->meds);
    $adds = $this->getMeds($meds, '@addMed', 'Add');
    $dcs = $this->getMeds($meds, '@dcMed', 'Discontinue');
    $list = array_merge($adds, $dcs);
    if (! empty($list))
      return $this->makeSecList('Medication Changes', $list);
  }
  protected function getMeds($meds, $action, $text) {
    $a = array();
    foreach ($meds as $med)   
      if ($med->action == $action)
        $a[] = $text . ': ' . $med->name . ' ' . Med::friendlySig($med->text); 
      return $a;
  }
  protected function makeNextApptSec($fs) {
    if ($fs->Sched) {
      $list = array($fs->Sched->getDesc());
      return $this->makeSecList('Your Next Appointment', $list);
    }
  }
  protected function makeSec($head, $html) {
    $h = new Html();
    $h->p_()->h3($head)->add($html)->_();
    return $h->out();
  }
  protected function makeList($list) {
    $h = new Html();
    $h->ul($list);
    return $h->out();
  }
  protected function makeSecList($head, $list) {
    return static::makeSec($head, $this->makeList($list));
  }
  protected static function extractUl($s) {
    $s = str_replace("<ul>", "", $s);
    $s = str_replace("<li>", "", $s);
    $s = str_replace("</li>", "", $s);
    $s = str_replace("</ul>", "", $s);
    return $s;
  }
}
class Vital_Vs extends Vital {
  //
  static function from($rec) {
    $o = jsondecode($rec->vitals);
    if ($o) {
      $me = new static();
      $me->pulse = get($o, 'pulse');
      $me->resp = get($o, 'resp');
      $me->bpSystolic = get($o, 'bp_systolic');
      $me->bpDiastolic = get($o, 'bp_diastolic');
      $me->bpLoc = get($o, 'bp_loc');
      $me->temp = get($o, 'temp');
      $me->tempRoute = get($o, 'temp_route');
      $me->wt = get($o, 'wt');
      $me->wtUnits = get($o, 'wt_units');
      $me->height = get($o, 'height');
      $me->hc = get($o, 'hc');
      $me->hcUnits = get($o, 'hc_units');
      $me->wc = get($o, 'wc');
      $me->wcUnits = get($o, 'wc_units');
      $me->o2Sat = get($o, 'o2_sat');
      $me->o2SatOn = get($o, 'o2_sat_on');
      $me->bmi = get($o, 'bmi');
      $me->htUnits = get($o, 'ht_units');
      return $me;
    }
  }
}
