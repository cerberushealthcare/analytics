<?php
require_once 'php/data/rec/sql/ErxUsers.php';
require_once 'php/data/rec/sql/Clients.php';
require_once 'php/data/rec/sql/Procedures.php';
require_once 'php/data/rec/sql/Diagnoses.php';
require_once 'php/data/rec/sql/MedsNewCrop.php';
//
class Client_Cqm extends Client implements ReadOnly {
  //
  public function getClassType() {
    $a = explode('_', get_called_class());
    return $a[0];
  }
  public function getClassSubtype() {
    $a = explode('_', get_called_class());
    return $a[1];
  }
  public function getReportNum() {
    return substr($this->getClassType(), -3);
  }
  public function /*['y'=>y,'m'=>m,'d'=>d]*/cageAt($date) { 
    return chronAge($this->birth, $date);
  }
  public function ageAt($date) { 
    $cage = $this->cageAt($date);
    return $cage['y'];
  }
  public function fetchAddress() {
    $this->Address_Home = ClientAddress::fetchHome($this->clientId, true);
  }
  public function setMostRecent($setFid, $arrayFid, $recFid = 'date') {
    $this->$setFid = $this->filterMostRecent($arrayFid, $recFid);
    unset ($this->$arrayFid);
  }
  public function filterMostRecent($fid, $recFid = 'date') {
    $mr = null;
    $mrdate = '1970-01-01';
    $recs = $this->$fid;
    if (is_array($recs)) {
      foreach ($recs as $rec) {
        if ($rec->$recFid > $mrdate) {
          $mr = $rec;
          $mrdate = $mr->$recFid;
        }
      }
    }
    return $mr;
  }
  public function setFirst($setFid, $arrayFid, $recFid = 'date') {
    $this->$setFid = $this->first($arrayFid, $recFid);
    unset ($this->$arrayFid);
  }
  public function first($fid, $recFid = 'date') {
    $mr = null;
    $mrdate = nowNoQuotes();
    $recs = $this->$fid;
    if (is_array($recs)) {
      foreach ($recs as $rec) {
        if ($rec->$recFid < $mrdate) {
          $mr = $rec;
          $mrdate = $mr->$recFid;
        }
      }
    }
    return $mr;
  }
  public function filterClosedPrior($fid, $from) {
    $recs = array_filter(gets($this, $fid), function($rec) use($from) {
      if ($rec->dateClosed == null || $rec->dateClosed >= $from) 
        return true;
    });
    return (empty($recs)) ? null : reset($recs);
  }
  public function has($fid) {
    return ! empty($this->$fid);
  }
  public function hasAny(/*$fid,..*/) {
    $fids = func_get_args();
    foreach ($fids as $fid) {
      if ($this->has($fid))
        return true;
    }
  }
  //
  static function fetchAll($ugid, $from, $to, $uid) {
    $c = static::asCriteria($ugid, $from, $to, $uid);
    $recs = static::fetchAllBy($c);
    $recs = static::filter($recs, $from, $to);
    return $recs;
  } 
  static function fetchAllBy($c) {
    return parent::fetchAllBy($c, null, 2000, 'clientId');
  }
  static function filter($recs, $from = null, $to = null) {
    return $recs;
  }
  static function from($ugid, $dateFrom = null, $dateTo = null, $ageFrom = null, $ageTo = null) {
    $c = new static();
    $c->userGroupId = $ugid;
    if (! empty($ageFrom) || ! empty($ageTo)) {
      $c->Hd_birth = Hdata_ClientDob::join(CriteriaValue::betweenAge(array($ageFrom, $ageTo), $dateFrom, $dateTo));
    }
    return $c;
  }
  static function fetchMerge(/*$c,..*/) {
    $crits = func_get_args();
    $recs = array();
    foreach ($crits as $c) {
      $recs = array_merge($recs, static::fetchAllBy($c));
    }
    return $recs;
  }
  static function setMostRecents($recs, $setFid, $arrayFid, $recFid = 'date') {
    foreach ($recs as $rec) {
      $rec->setMostRecent($setFid, $arrayFid, $recFid);
    }
    return $recs;
  }
  static function setFirsts($recs, $setFid, $arrayFid, $recFid = 'date') {
    foreach ($recs as $rec) {
      $rec->setFirst($setFid, $arrayFid, $recFid);
    }
    return $recs;
  }
  static function mapByDemo($clients, $fid/*e.g. 'gender'*/) {
    $map = array();
    foreach ($clients as $client) {
      $value = $client->$fid;
      if ($value != null) {
        if (geta($map, $value) == null) {
          $map[$value] = array();
        }
        $map[$value][] = $client;
      }
      return $map;
    }
  }
}
class Proc_Cqm extends Proc implements ReadOnly {
  //
  const IPC_ENCOUNTER = '600186';
  //
  public function setUserId($userId) {
    if ($userId)
      $this->userId = CriteriaValues::_or(CriteriaValue::isNull(), CriteriaValue::equals($userId));
    return $this;
  }
  public function setDates($from, $to) {
    $this->Hd_date = Hdata_ProcDate::join(CriteriaValue::betweenDates(array($from, $to)));
    return $this;
  }
  public function ipc($ipc) {
    $this->ipc = $ipc;
    return $this;
  }
  public function ipcs(/*ipc,..*/) {
    $ipcs = func_get_args();
    $this->ipc = CriteriaValue::in($ipcs);
    return $this;
  }
  public function cid($cid) {
    $this->clientId = $cid;
    return $this;
  }
  public function asEncounter($from, $to, $uid) {
    return static::from($from, $to, $uid)->ipc(static::IPC_ENCOUNTER);
  }
  public function withIpc() {
    $this->Ipc = Ipc::asRequiredJoin();
    return $this;
  }
  public function withResult() {
    $this->Result = Result_Cqm::asOptionalJoin();
    return $this;
  }
  public function dateOnly() {
    $this->date = dateToString($this->date);
    return $this->date;
  }
  public function isPositive() {
    return get($this, 'Result') && $this->Result->ipc == Result_CQM::IPC_SNOMED_POSITIVE;
  }
  public function isNegative() {
    return get($this, 'Result') && $this->Result->ipc == Result_CQM::IPC_SNOMED_NEGATIVE;
  }
  public function isContraIndicated() {
    return get($this, 'Result') && $this->Result->ipc == Result_CQM::IPC_CONTRAINDICATED;
  }
  public function isRefused() {
    return get($this, 'Result') && $this->Result->ipc == Result_CQM::IPC_REFUSED;
  }
  public function isNotDone() {
    return get($this, 'Result') && ($this->Result->ipc == Result_CQM::IPC_PROC_NOT_DONE || $this->Result->ipc == Result_CQM::IPC_NOT_DONE_MED_RSN);
  }
  public function isRefusedOrNotDone() {
    return $this->isRefused() || $this->isNotDone();
  }
  public function isInRange($lo, $hi) {
    if (get($this, 'Result')) {
      $value = $this->Result->value;
      return ($value >= $lo && $value < $hi);
    }
  }
  public function isLower($lo) {
    if (get($this, 'Result')) {
      $value = $this->Result->value;
      return $value < $lo;
    }
  }
  public function getSdtcValueSet() {
    if ($this->Ipc) {
      $oid = '2.16.840.1.113883.3.';
      $a = explode($oid, $this->Ipc->desc);
      if (count($a) > 1) {
        $b = explode(';', $a[1]);
        return $oid . trim($b[0]);
      }
    }
  }
  //
  static function from($from = null, $to = null, $userId = null) {
    $c = new static();
    if ($from || $to)
      $c->setDates($from, $to);
    if ($userId) 
      $c->setUserId($userId);
    return $c->withIpc();
  }
}
class Result_Cqm extends ProcResult implements ReadOnly {
  //
  const IPC_SNOMED_POSITIVE = 602460;
  const IPC_SNOMED_NEGATIVE = 602461;
  const IPC_CONTRAINDICATED = 602453;
  const IPC_REFUSED = 604740;
  const IPC_PROC_NOT_DONE = 602454;
  const IPC_NOT_DONE_MED_RSN = 602309;
  //
  public function getSdtcValueSet() {
    if ($this->Ipc) {
      $oid = '2.16.840.1.113883.3.';
      $a = explode($oid, $this->Ipc->desc);
      if (count($a) > 1) {
        $b = explode(';', $a[1]);
        return $oid . trim($b[0]);
      }
    }
  }
  //
  static function asOptionalJoin() {
    $c = new static();
    $c->Ipc = Ipc::asRequiredJoin(); 
    return CriteriaJoin::optional($c);
  } 
}
class MedHist_Cqm extends SessionMedNc implements ReadOnly {
  //
  public function setDates($from, $to) {
    $this->Hd_date = Hdata_MedDate::join(CriteriaValue::betweenDates(array($from, $to)));
    return $this;
  }
  public function rxnorms(/*rxn,..*/) {
    $icds = func_get_args();
    $this->index = CriteriaValue::in($icds);
    return $this;
  }
  public function asOrdered() {
    $this->quid = static::QUID_NC_ADD;
    return $this;
  }
  public function dateOnly() {
    $this->date = dateToString($this->date);
    return $this->date;
  }
  //
  static function from($from = null, $to = null) {
    $c = new static();
    $c->sessionId = CriteriaValue::isNotNull();
    $c->name = CriteriaValue::isNotNull();
    if ($from || $to)
      $c->setDates($from, $to);
    return $c;
  }
}
class Diag_Cqm extends Diagnosis implements ReadOnly {
  //
  public function setDate($from, $to) {
    $this->Hd_date = Hdata_DiagnosisDate::join(CriteriaValue::betweenDates(array($from, $to)));
    return $this;
  }
  public function icds(/*icd,..*/) {
    $icds = func_get_args();
    $this->icd = CriteriaValue::in($icds);
    return $this;
  }
  public function snomeds(/*snomed,..*/) {
    $snomeds = func_get_args();
    $this->snomed = CriteriaValue::in($snomeds);
    return $this;
  }
  public function icdStartsWith(/*icd,..*/) {
    $icds = func_get_args();
    foreach ($icds as &$icd) 
      $icd = CriteriaValue::startsWith($icd);
    $this->icd = CriteriaValues::_orArray($icds);
    return $this;
  }
  public function closedBefore($date) {
    $closed = get($this, 'dateClosed');
    if ($closed) {
      if (compareDates($closed, $date) < 0)
        return true; 
    }
    return false;
  }
  //
  static function from($before = null) {
    $c = new static();
    if ($before)
      $c->setDate(null, $before);
    return $c;
  }
  static function asPregnant($before = null) {
    $c = static::from($before);
    $c->icd = CriteriaValues::_orArray(array_merge(array(
      CriteriaValue::in(array('V61.6','V61.7','V72.42')),
      CriteriaValue::in(array('V24','V24.0','V24.2','V25','V25.01','V25.02','V25.03','V25.09','V26.81','V28','V28.3','V28.81','V28.82','V72.4','V72.40','V72.41','V72.42'))),
      static::cvStarts('645','V22','633','V23','639','677','651','761','640','643','671','646','642','649','760')));
    return $c;
  }
  static function asCancer($before = null) {
    $c = static::from($before)->icdStartsWith(
      '141','142','143','144','145','146','147','148','149','150','151','152','153','154','160','161','162','163','164',
      '170','171','172','174','180','181','182','183','184','185','186','187','188','189','190','191','192','194','195',
      '196','197','200','201','202','203','204','205','206','207','208');
    return $c;
  }
  protected static function cvStarts(/*icd,..*/) {
    $icds = func_get_args();
    foreach ($icds as &$icd) 
      $icd = CriteriaValue::startsWith($icd);
    return $icds;
  }
} 
