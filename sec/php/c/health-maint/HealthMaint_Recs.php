<?php
require_once 'php/data/rec/sql/_IpcRec.php';
require_once 'php/c/reporting/Reporting_Recs.php';
//
class IpcHm extends IpcHmRec {
  //
  public $ipc;
  public $reportId;
  public $userGroupId;
  public $clientId;
  public $every;
  public $interval;
  public $active;
  public /*Ipc*/ $Ipc;
  public /*Report*/ $Report;
  //
  public function getJsonFilters() {
    return array(
      'active' => JsonFilter::boolean());
  }
  public function hasInterval() {
    return $this->every > 0;
  }
  public function getCriteriaObject() {
    return RepCrit_Hm::from($this);
  }
  public function setFetchCriteria() {
    $this->Ipc = Ipc::asRequiredJoin();
    $this->Report = CriteriaJoin::optional(new Report());
    return $this;
  }
  //
  /**
   * @param int $ipc
   * @param int $ugid
   * @return array(Client_Rep,..) 
   */
  static function fetchAllApplicableClients($ipc, $ugid) {
    $ipchm = self::fetchTopLevel($ipc, $ugid);
    $recs = array();
    if ($ipchm) {
      $recsGroupLevel = self::fetchGroupApplicable($ipchm, $ugid);
      $recsClientLevel = self::fetchClientsApplicable($ipchm, $ugid);
      $recs = array_merge($recsGroupLevel, $recsClientLevel);
    }
    return $recs;
  }
  /**
   * @param int $ipc
   * @param int $ugid
   * @return array(Client_Rep,..)
   */
  static function fetchAllDueNowClients($ipc, $ugid) {
    $ipchm = self::fetchTopLevel($ipc, $ugid);
    $recs = array();
    $recsGroupLevel = ($ipchm) ? self::fetchGroupDueNow($ipchm, $ugid) : array();
    $recsClientLevel = self::fetchClientsDueNow($ipc, $ugid);
    $recs = array_merge($recsGroupLevel, $recsClientLevel);
    foreach ($recs as &$rec) 
      $rec->_dueNow = 1;
    return $recs;
  }
  static function asClientLevelCriteria($ugid) {
    $c = parent::asClientLevelCriteria($ugid);
    $c->active = true;
    return $c;
  }
  static function deleteFor($reportId) {
    if ($reportId) {
      $c = new static();
      $c->reportId = $reportId;
      $c->clientId = 0; /* just delete the ugid record; any client records already assigned will remain since they don't use the report for criteria anyway */
      $me = static::fetchOneBy($c);
      static::delete($me);
    }
  }
  static function revive($o, $ugid) {
    $rec = new self($o);
    $rec->userGroupId = $ugid;
    if ($rec->reportId == null)
      $rec->reportId = '0';
    if (! isset($rec->clientId))
      $rec->clientId = self::GROUP_LEVEL_CID;
    return $rec;
  }
  //
  private static function fetchGroupApplicable($ipchm, $ugid) {
    $recs = array();
    if (! $ipchm->isClientLevel()) {
      $crit = RepCrit_Hm::asGroupApplicable($ipchm);
      $recs = $crit->fetchAll($ugid);
    }
    return $recs;
  }
  private static function fetchGroupDueNow($ipchm, $ugid) {
    $recs = array();
    if (! $ipchm->isClientLevel()) {
      $crit = RepCrit_Hm::asGroupDueNow($ipchm);
      $recs = $crit->fetchAll($ugid);
    }
    return $recs;
  }
  static function fetchClientsApplicable($ipchm, $ugid) {
    $crit = RepCrit_Hm::asClientLevelApplicable($ipchm->ipc);
    $recs = $crit->fetchAll($ugid);
    foreach ($recs as &$rec) 
      $rec->_clientLevel = 1;
    return $recs;
  }
  static function fetchClientsDueNow($ipc, $ugid) {
    $crit = RepCrit_Hm::asClientLevelDueNow($ipc);
    return $crit->fetchAll($ugid);
  }
}  
class Ipc_Hm extends IpcRec {
  //
  public $ipc;
  public $userGroupId;
  public $name;
  public $desc;
  public $cat;
  //
  static function fetchAll($ugid) {
    $ipcs = static::fetchAllIpcs($ugid);
    $c = new static();
    $c->setUserGroupCriteria($ugid);
    $c->ipc = CriteriaValue::in($ipcs);
    return static::fetchAllBy($c);
  }
  protected static function fetchAllIpcs($ugid) {
    $sql = "SELECT DISTINCT ipc FROM ipc_hm WHERE user_group_id IN (0, $ugid)";
    return Dao::fetchValues($sql);
  }
}
class IpcHm_Client extends IpcHm {
  //
  public $ipc;
  public $reportId;
  public $userGroupId;
  public $clientId;
  public $every;
  public $interval;
  public $active;
  public /*Ipc*/ $Ipc;
  public /*Report*/ $Report;
  public $_name;
  public $_overdue;
  //
  public function toJsonObject(&$o) {
    unset($o->Report);
  }
  //
  static function fetchAll($ugid, $cid) {
    $recs = self::fetchTopLevels($ugid, $cid);
    //logit_r($recs, 'HM fetchTopLevels');
    $recs = self::extractApplicables($recs, $cid, $ugid);
    //logit_r($recs, 'HM extractApplicables');
    $recs =  Rec::sort($recs, new RecSort('_sort', '_name'));
    return $recs; 
  }
  static function fetchOne($ugid, $cid, $ipc) {
    $recs = self::fetchAll($ugid, $cid);
    foreach ($recs as $rec) 
      if ($rec->ipc == $ipc)
        return $rec;
  }
  /**
   * @param IpcHm $hms
   * @param int $cid
   * @param int $ugid
   * @return array(IpcHm_Client,..)
   */
  static function extractApplicables($hms, $cid, $ugid) {
    $recs = array();
    foreach ($hms as $hm) {
      $crit = RepCrit_Hm::asClientApplicable($hm, $cid);
      $fid = $crit->ProcJoinFid;
      $client = $crit->fetchOne($ugid);
      if ($client) {
        $hasProc = is_array(get($client, $fid));
        $hm->setHelpers($hasProc);
        $recs[] = $hm;
      }
    }
    return $recs;
  }
  //
  private function setHelpers($hasProc) {
    $this->_overdue = ($this->hasInterval() && ! $hasProc);
    if (isset($this->Report)) {
      $this->_name = $this->Report->name;
      $this->_comment = $this->Report->comment;
    } else { 
      $this->_name = $this->Ipc->name;
    }
    if (! $this->active) 
      $this->_sort = 9;
    else if ($this->_overdue) 
      $this->_sort = 1;
    else if (! $this->hasInterval())
      $this->_sort = 2;
    else 
      $this->_sort = 3;
  }
}
/**
 * Clinical Decision 
 */
class RepCrit_Hm extends RepCritRec_Client {
  //
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
  public $cdata5;
  public $cdata6;
  //
  static $JOINS_TO = array(
    self::T_ADDRESS, 
    self::T_DIAGNOSES,
    self::T_MEDS,
    self::T_ALLERGIES,
    self::T_PROCS,
    self::T_RESULTS,
    self::T_IMMUNS,
    self::T_VITALS,
    self::T_SESSIONS);
  //
  public function getTable() {
    return self::T_CLIENTS;
  }
  public function fetchOne($ugid) {
    $recs = $this->fetchAll($ugid);
    return reset($recs);
  }
  public function count($ugid) {
    $criteria = self::asSqlCriteria($this, $ugid);
    return Client_Rep::count($criteria);
  } 
  public function addNotHavingProcJoin($ipchm) {
    $this->Joins[] = RepCritJoin_Hm::asNotHavingProc($ipchm); 
  }
  public function addHavingProcJoin($ipchm) {
    $this->Joins[] = RepCritJoin_Hm::asHavingProc($ipchm);
    $this->ProcJoinFid = $this->getJoinFid();
  }
  public function addNotHavingProcCalcIntervalJoin($ipc) {
    $this->Joins[] = RepCritJoin_Hm::asNotHavingCalcIntervalProc($ipc); 
  }
  public function addNotHavingClientLevelJoin($ipc) {
    $this->Joins[] = RepCritJoin_Hm::asNotHavingClientLevel($ipc); 
  }
  public function addHavingClientLevelJoin($ipc) {
    $this->Joins[] = RepCritJoin_Hm::asHavingClientLevel($ipc); 
  }
  public function addCid($cid) {
    $this->clientId = RepCritValue_Hm::asCid($cid);
  }
  protected function getClassFromJsonField($fid) {
    if ($fid == 'Joins')
      return 'RepCritJoin_Hms';
    else
      return 'RepCritValue';
  }
  //
  static function revive($rec) {
    $me = new static();
    $me->reviveValues($rec);
    return $me;    
  }
  static function from($ipchm = null) {
    if ($ipchm && $ipchm->Report && ! $ipchm->isClientLevel()) {
      $j = jsondecode($ipchm->Report->jsonRec);
      $me = static::revive($j);
    } else {
      $me = new static();
    }
    $me->addJoin(RepCritJoin::asAddress());
    return $me;
  }
  /**
   * @param IpcHm ipchm group-level
   * @return RepCrit_Hm to return all patients applicable  
   */
  static function asGroupApplicable($ipchm) {
    $crit = static::from($ipchm);
    $crit->addNotHavingClientLevelJoin($ipchm->ipc);
    return $crit;
  }
  /**
   * @param IpcHm ipchm any level
   * @param RepCrit_Hm to return all patients with client-level IPC_HM
   */
  static function asClientLevelApplicable($ipc) {
    $crit = static::from();
    $crit->addHavingClientLevelJoin($ipc);
    return $crit;
  }
  /**
   * @param IpcHm ipchm group-level
   * @param int $cid (optional, to filter for specific patient)
   * @return RepCrit_Hm to return all patients applicable and are due now  
   */
  static function asGroupDueNow($ipchm, $cid = null) {
    $crit = static::from($ipchm);
    $crit->addNotHavingClientLevelJoin($ipchm->ipc);
    if ($cid) 
      $crit->addCid($cid);
    $crit->addNotHavingProcJoin($ipchm);
    return $crit;
  }
  /**
   * @param IpcHm ipchm any level
   * @param RepCrit_Hm to return all patients with client-level IPC_HM that are due now
   */
  static function asClientLevelDueNow($ipc) {
    $crit = static::from();
    $crit->addHavingClientLevelJoin($ipc);
    $crit->addNotHavingProcCalcIntervalJoin($ipc);
    logit_r($crit, 'asclientlevelduenow');
    return $crit;
  }
  /**
   * @param IpcHm $ipchm any level
   * @param int $cid
   * @return RepCrit_Hm to return a record if supplied client is applicable
   */
  static function asClientApplicable($ipchm, $cid) {
    $crit = static::from($ipchm);
    $crit->addCid($cid);
    $crit->addHavingProcJoin($ipchm);
    return $crit;
  }
  //
  protected function assignSqlCriteriaValue(&$criteria, $fid, $value) {
    if ($fid == 'age') 
      $fid = 'birth';
    parent::assignSqlCriteriaValue($criteria, $fid, $value);
  }
}
class RepCritJoin_Hm extends RepCritJoin {
  //
  static function asNotHavingProc($ipchm) {  // not having proc dated within group-level IpcHm interval
    $rec = new self();
    $rec->jt = self::JT_NOT_HAVE;
    $rec->table = RepCritRec::T_PROCS;
    $rec->Recs[] = RepCrit_HmProc::asProcWithinDate($ipchm);
    return $rec;
  }
  static function asHavingProc($ipchm) {  // having proc dated within group-level IpcHm interval
    $rec = new self();
    $rec->jt = self::JT_OPTIONAL;
    $rec->table = RepCritRec::T_PROCS;
    $rec->Recs[] = RepCrit_HmProc::asProcWithinDate($ipchm);
    return $rec;
  }
  static function asNotHavingCalcIntervalProc($ipc) {  // not having proc dated within client-level IpcHm interval
    $rec = new self();
    $rec->jt = self::JT_NOT_HAVE;
    $rec->table = RepCritRec::T_PROCS;
    $rec->Recs[] = RepCrit_HmProc::asProcWithinCalcInterval($ipc);
    return $rec;
  }
  static function asNotHavingClientLevel($ipc) {
    $rec = new self();
    $rec->jt = self::JT_NOT_HAVE;
    $rec->table = RepCrit_IpcHm::T_IPCHM;
    $rec->Recs[] = RepCrit_IpcHm::asIpc($ipc);
    return $rec;
  }
  static function asHavingClientLevel($ipc) {
    $rec = new self();
    $rec->jt = self::JT_HAVE_ONE;
    $rec->table = RepCrit_IpcHm::T_IPCHM;
    $rec->Recs[] = RepCrit_IpcHm::asActiveIpc($ipc);
    return $rec;
  }
}
class RepCrit_IpcHm extends RepCritRec {  
  //
  const T_IPCHM = '100';
  //
  public $clientId;
  public $active;
  //
  public function getSqlClass() {
    return 'IpcHm_Rep';
  }
  public function getTable() {
    return self::T_IPCHM;
  }
  //
  static function getClassFromTable() {
    return __CLASS__;
  }
  static function asIpc($ipc) {  // e.g. IPC_HM at client level
    $rec = new self();
    $rec->ipc = RepCritValue_Hm::asIpc($ipc);
    return $rec;
  }
  static function asActiveIpc($ipc) {  // e.g. active IPC_HM at client level
    $rec = new self();
    $rec->ipc = RepCritValue_Hm::asIpc($ipc);
    $rec->active = '1';
    return $rec;
  }
}
class IpcHm_Rep extends IpcHm implements ReadOnly {  
  //
  protected function getPkField() {
    return 'ipc';
  }
  //
  static function asCriteria($ugid) {
    return new static();
  }
}
class RepCrit_HmProc extends RepCrit_Proc {  
  //
  static function asProcWithinDate($ipchm) {  // e.g. proc performed within group-level interval
    $rec = new self();
    $rec->ipc = RepCritValue_Hm::asIpc($ipchm->ipc);
    $rec->date = RepCritValue_Hm::asDate($ipchm);
    return $rec;
  }
  static function asProcWithinCalcInterval($ipc) {  // e.g. proc performed within client-level interval
    $rec = new self();
    $rec->ipc = RepCritValue_Hm::asIpc($ipc);
    $rec->date_interval = RepCritValue_Hm::asDateWithinInterval();
    //$rec->IpcHm = RepCritValue_Hm::asIpc($ipc);/*to set up join, see parent::assignSqlCriteriaValue*/;
    return $rec;
  }
  protected function assignSqlCriteriaValue(/*SqlRec*/&$criteria, /*string*/$fid, /*RepCritValue*/$value) {
    if ($fid == 'date_interval') {
      $ipchm = new IpcHm();
      $ipchm->ipc = $this->ipc->value;
      $criteria->IpcHm = CriteriaJoin::requires($ipchm, 'clientId'); 
      $fid = 'date';
    }
    parent::assignSqlCriteriaValue($criteria, $fid, $value);
  }
}
class RepCritValue_Hm extends RepCritValue {
  //
  static $INTERVALS = array(
    IpcHm::INT_DAY => 'd',
    IpcHm::INT_WEEK => 'w',
    IpcHm::INT_MONTH => 'm', 
    IpcHm::INT_YEAR => 'y');
  //
  static function asIpc($ipc) {
    $rec = new self();
    $rec->op = self::OP_IS;
    $rec->value = $ipc;
    //$rec->value = $ipchm->ipc;
    //$rec->text_ = $ipchm->Ipc->name;
    return $rec;
  }
  static function asDate($ipchm) {
    if ($ipchm->every) {
      $rec = new self();
      $rec->op = self::OP_WITHIN;
      $rec->value = $ipchm->every . ',' . self::$INTERVALS[$ipchm->interval];
      $rec->text_ = $ipchm->every . ' ' . IpcHm::$INTERVALS[$ipchm->interval];
      return $rec;
    } else {
      return null;
    }
  }
  static function asCid($cid) {
    $rec = new self();
    $rec->op = self::OP_IS;
    $rec->value = $cid;
    return $rec;
  }
  static function asDateWithinInterval() {
    $rec = new self();
    $rec->op = self::OP_GTEN;
    $rec->value = 'CASE WHEN `interval`=1 THEN unix_timestamp(now()-interval `every` DAY) WHEN `interval`=2 THEN unix_timestamp(now()-interval `every` WEEK) WHEN `interval`=3 THEN unix_timestamp(now()-interval `every` MONTH) WHEN `interval`=4 THEN unix_timestamp(now()-interval `every` YEAR) END';
    $rec->text_ = 'interval start date';
    return $rec; 
  }
}
