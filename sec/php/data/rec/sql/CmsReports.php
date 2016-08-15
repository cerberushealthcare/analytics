<?php
require_once 'php/data/rec/sql/_ClientRec.php';
require_once 'php/data/rec/sql/UserGroups.php';
//
/**
 * CMS Clinical Quality Measures Reports
 * @author Warren Hornsby
 */
class CmsReports {
  
}
class Client_Cms extends SqlRec implements ReadOnly {
  public $clientId;
  public $userGroupId;
  public $birth;
  public $sex;
  //
  public function getSqlTable() {
    return 'clients';
  }
}
class Vital_Cms extends SqlRec implements ReadOnly {
  public $dataVitalsId;
  public $bpSystolic;
  public $bpDiastolic;
  public $bmi;
  //
  public function getSqlTable() {
    return 'data_vitals';
  }
}
class Proc_Cms extends SqlRec implements ReadOnly {
  public $procId;
  public $userGroupId;
  public $clientId;
  public $date;  
  public $ipc;
  //
  public function getSqlTable() {
    return 'procedures';
  }
  static function from($ipc) {
    $c = new static();
    $c->ipc = $ipc;
    return $c;
  }
}
class Diag_Cms extends SqlRec implements ReadOnly {
  public $dataDiagnosesId;
  public $userGroupId;
  public $clientId;
  //
  public function getSqlTable() {
    return 'data_diagnoses';
  }
}
abstract class CmsReport {
  //
  public $measureNumber;
  public $from;
  public $to;
  public /*CmsScorecard*/ $scorecard;
  public /*UserGroup*/ $UserGroup;
  //
  abstract public function calculate($ugid, $from, $to);
  //
  static function from($from, $to) {
    $me = new static();
    $me->from = $from;
    $me->to = $to;
    $me->UserGroup = UserGroups::getMine();
    $me->calculate($me->UserGroup->userGroupId, $from, $to);
    return $me; 
  }
}
/**
 * NQF 0013
 */
class CmsReport_NQF0013 extends CmsReport {
  //
  public $measureNumber = 'NQF 00013';
  //
  public function calculate($ugid, $from, $to) {
    $pops = $this->fetchPop($ugid, $from, $to);
    $nums = $this->fetchNum($ugid, $from, $to);
    $excs = $this->fetchExc($ugid, $from, $to);
    $this->scorecard = CmsScorecard::from($pops, $nums, $excs);
  }
  public function fetchPop($ugid, $from, $to) { 
    $c = Client_NQF0013::asPop($ugid, $from, $to);
    return SqlRec::fetchAllBy($c);
  }
  public function fetchNum($ugid, $from, $to) {
    $c = Client_NQF0013::asPop($ugid, $from, $to);
    $c->Vital = CriteriaJoin::requires(Vital_NQF0013::asNum($from, $to));
    return SqlRec::fetchAllBy($c);
  }
  public function fetchExc($ugid, $from, $to) {
    return null;
  }
}
class Client_NQF0013 extends Client_Cms {
  static function asPop($ugid, $from, $to) {
    $c = new static();
    $c->userGroupId = $ugid;
    $c->birth = CriteriaValue::betweenAge(array(0, 18), $from, $to);
    $c->Diag = CriteriaJoin::requires(Diag_NQF0013::asPop($from, $to));
    $c->Proc = CriteriaJoin::requiresCountGreaterThan(Proc_NQF0013::asPop($from, $to), 2);
    return $c;
  }
}
class Diag_NQF0013 extends Diag_Cms {
  static function asPop($from, $to) {
    $c = new static();
    $c->icd = CriteriaValue::in(array('401.0','401.1','401.9','402.00','402.01','402.10','402.11','402.90','402.91','403.00','403.01','403.10','403.11','403.90','403.91','404.00','404.01','404.02','404.03','404.10','404.11','404.12','404.13','404.90','404.91','404.92','404.93'));
    return $c;
  }
}
class Proc_NQF0013 extends Proc_Cms {
  static function asPop($from, $to) {
    $c = new static();
    $c->ipc = CriteriaValue::in(array('600186','600187'));
    $c->date = CriteriaValue::betweenDates(array($from, $to));
    return $c;
  }
} 
class Vital_NQF0013 extends Vital_Cms {
  static function asNum($from, $to) {
    $c = new static();
    $c->bpSystolic = CriteriaValue::greaterThanNumeric(0);
    $c->bpDiastolic = CriteriaValue::greaterThanNumeric(0);
    return $c;
  }
}
class CmsScorecard {
  public $eligibles;  // [cid,..]
  public $meets;  // [cid,..]
  public $exclusions;  // [cid,..]
  //
  const REPORTING_RATE = 100;
  //
  public function getNum() {
    return count($this->meets);
  }
  public function getDenom() {
    return count($this->eligibles) - count($this->exclusions);
  }
  public function getNotMeets() {
    return count($this->eligible) - count($this->meets) - count($this->exclusions);
  }
  public function getPerformanceRate() {
    if ($this->getDenom())
      return round($this->getNum() / $this->getDenom() * self::REPORTING_RATE, 2);
    else
      return 0;
  }
  //
  static function from($pops, $nums, $excs) {
    $pops = static::getIds($pops);
    $nums = static::getIds($nums);
    $excs = static::getIds($excs);
    $excs = array_diff($excs, $nums);  // omit exlusions already in numerator
    $pops = array_diff($pops, $excs);  // remove exclusions from population
    $me = new static();
    $me->eligibles = $pops;
    $me->meets = $nums;
    $me->exclusions = $excs;
    return $me;
  }
  //
  private static function getIds($clients) {
    $recs = array();
    if ($clients)
      foreach ($clients as $client)
        $recs[$client->clientId] = 1;
    return $recs;
  }
}
