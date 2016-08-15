<?php
p_i('Reporting');
require_once 'php/data/rec/sql/_SqlLevelRec.php';
require_once 'php/data/rec/sql/Immuns.php';
require_once 'php/data/rec/sql/Procedures_Admin.php';
require_once 'php/data/rec/sql/OrderEntry.php';
require_once 'Reporting_Metrics.php';
/**
 * Reporting DAO
 * @author Warren Hornsby
 */
class Reporting {
  //
  static function /*ReportCriteria*/newReport($type = ReportCriteria::TYPE_PATIENT) {
    $rc = ReportCriteria::asNew($type);
    return $rc;
  }
  static function test() {
    $rc = jsondecode("{\"userGroupId\":\"3\",\"reportId\":\"111\",\"name\":\"Test\",\"type\":\"1\",\"comment\":null,\"refs\":null,\"countBy\":\"1\",\"Rec\":{\"uid\":{},\"lastName\":{},\"firstName\":{},\"sex\":{},\"age\":{},\"birth\":{},\"deceased\":{},\"race\":{},\"ethnicity\":{},\"language\":{},\"livingWill\":{},\"poa\":{},\"active\":{},\"Joins\":[],\"table_\":\"0\"},\"RecDenom\":null,\"IpcHm\":null,\"recs\":[],\"recsDenom\":[],\"app\":false}");
    $report = static::generate($rc);
    p_r($report);
  }
  static function /*ReportCriteria*/save(/*obj*/$rc) {
    global $login;
    $rec = Report::fromReportCriteria($rc);
    $ugid = ($login->admin && $rc->app) ? Report::APP_LEVEL_UGID : $login->userGroupId; 
    $rec = $rec->save($ugid, $login->userId);
    return self::getReport($rec->reportId);
  }
  static function /*ReportCriteria*/getReport($reportId) {
    $rec = Report::fetch($reportId);
    if ($rec)
      return ReportCriteria::fromReport($rec);
    else
      return self::newReport();
  }
  static function /*Audit_Rep[]*/fetchImmunAudits($cid, $immunId) {
    global $login;
    $ugid = $login->userGroupId;
    $rc = ReportCriteria_QuickAudit::forImmun($ugid, $cid, $immunId)->load();
    return $rc->recs;
  }
  static function /*ReportCriteria*/generate(/*obj*/$rc) {
    global $login;
    $rc = ReportCriteria::revive($login->userGroupId, $rc);
    $rc->load();
    if ($login->super && $rc->isHistable()) 
      ReportHist::saveFrom($rc);
    return $rc;
  }
  static function /*ReportCriteria*/generateForDownload(/*obj*/$rc, /*bool*/$numerator, /*bool*/$noncomps, /*bool*/$duenow) {
    global $login;
    logit_r($rc, 'generatefordownload');
    $criteria = ReportCriteria::revive($login->userGroupId, $rc);
    logit_r($criteria, 'criteria');
    $critRec = ($numerator || $duenow) ? $criteria->Rec : $criteria->RecDenom;
    logit_r($critRec, 'critRec');
    if ($criteria->isPatient() || $criteria->isMu())
      if (! $critRec->isJoinedTo(RepCritRec::T_ADDRESS))
        $critRec->addJoin(RepCritJoin::asAddress());
    if ($noncomps) {
      $nc = static::generateNoncomps($criteria);
      return $nc;
    } else {
      $criteria->Rec = $critRec;
      $criteria->RecDenom = null;
      $rc = Reporting::generate($criteria);
      logit_r($rc);
      return $rc;
    }
  }
  static function /*int*/deleteReport($reportId) {
    $rec = Report::fetch($reportId);
    if ($rec) {
      Report::delete($rec);
      return $reportId;
    }
  }
  static function /*ReportStub[]*/getStubs() {
    global $login;
    $recs = ReportStub::fetchAll($login->userGroupId);
    return Rec::sort($recs, new RecSort('type', 'name'));
  }
  static function /*RepCritJoin*/getJoin(/*string(name)*/$table) {
    return RepCritJoin::forTable($table); 
  }
  //
  protected static function generateNonComps($criteria) {
    $rc = Reporting::generate($criteria);
    $recs = array();
    $map = static::getIdMap($rc->recs);
    foreach ($rc->recsDenom as $den) {
      if (! isset($map[$den->getPkValue()]))
        $recs[] = $den;
    }
    $rc->recs = $recs;
    $rc->recsDenom = null;
    return $rc;
  }
  protected static function getIdMap($recs) {
    $map = array();
    foreach ($recs as $rec)
      $map[$rec->getPkValue()] = $rec;
    return $map;
  }
}
//
class Report extends SqlLevelRec {
  //
  public $reportId;
  public $userGroupId;
  public $name;
  public $type;
  public $tableId;
  public $jsonRec/*serialized RepCritRec*/;  
  public $jsonRecDenom/*serialized RepCritRec*/;  
  public $comment;
  public $createdBy;
  public $dateCreated;
  public $countBy;
  public $refs;
  public $jsonSort/*serialized RepSort[]*/;
  public /*IpcHm_Cds*/$IpcHm;
  //
  public function getPkFieldCount() {
    return 1;  // reportId is auto-inc
  }
  public function getAuditLabel() {
    return $this->name;
  }
  public function validate(&$rv) {
    $rv->requires('name');
  }
  public function getSqlTable() {
    return 'reports';
  }
  public function save($ugid, $userId) {
    if ($this->reportId == null) { 
      $this->createdBy = $userId;
      $this->dateCreated = nowNoQuotes();
    }
    $this->userGroupId = $ugid;
    $rec = parent::save();
    if ($this->type == ReportCriteria::TYPE_CDS) 
      $rec->IpcHm = $this->saveIpchm($rec->reportId, $ugid, $this->IpcHm);
    return $rec;
  }
  protected function saveIpcHm($reportId, $ugid, $o) {
    $ipcHm = IpcHm_Cds::revive($reportId, $ugid, $o);
    if ($ipcHm) {
      return $ipcHm->save();
    } else {
      $me = self::fetch($reportId);
      if ($me->IpcHm)
        return $me->IpcHm->save_detached();
    }
  }
  //
  static function fetch($id) {
    $c = new self($id);
    $c->IpcHm = IpcHm_Cds::asOptionalJoin();
    return self::fetchOneBy($c);
  }
  static function delete(&$rec) {
    Dao::begin();
    try {
      $type = $rec->type;
      $id = $rec->reportId;
      parent::delete($rec);
      if ($type == ReportCriteria::TYPE_CDS) { 
        require_once 'php/c/health-maint/HealthMaint_Recs.php';
        IpcHm::deleteFor($id);
      }
      Dao::commit();
    } catch (Exception $e) {
      Dao::rollback();
      throw $e;
    }
  }
  /**
   * @param ReportCriteria $reportCriteria
   * @return Report
   */
  static function fromReportCriteria($reportCriteria) {
    $rec = new self();
    $rec->reportId = get($reportCriteria, 'reportId');
    $rec->name = $reportCriteria->name;
    $rec->type = $reportCriteria->type;
    $rec->tableId = $reportCriteria->Rec->table_;
    $rec->comment = get($reportCriteria, 'comment');
    $rec->refs = get($reportCriteria, 'refs');
    $rec->jsonRec = jsonencode($reportCriteria->Rec);
    $rec->jsonRecDenom = ($reportCriteria->RecDenom) ? jsonencode($reportCriteria->RecDenom) : null;
    $rec->IpcHm = get($reportCriteria, 'IpcHm');
    if ($rec->IpcHm) {
      if (! $rec->IpcHm->auto)
        $rec->IpcHm->every = 0;
    }
    $rec->countBy = get($reportCriteria, 'countBy', '0');
    return $rec;
  }
}
class ReportStub extends SqlLevelRec implements ReadOnly {
  //
  public $reportId;
  public $userGroupId;
  public $name;
  public $type;
  public $comment;
  public $refs;
  public /*IpcHm_Cds*/$IpcHm;
  public /*ReportHist*/$Hist;
  //
  public function getSqlTable() {
    return 'reports';
  }
  public function toJsonObject(&$o) {
    $o->lookup('type', ReportCriteria::$TYPES);
  }
  public function fetchAll($ugid) {
    $c = self::asCriteria($ugid);
    $c->IpcHm = IpcHm_Cds::asOptionalJoin();
    global $login;
    if ($login->super)
      $c->Hist = ReportHist::asJoinCurrent();
    return self::fetchAllBy($c, null, 2000);
  }
}
/**
 * ReportCriteria
 */
class ReportCriteria extends Rec implements SerializeNulls {
  //
  public $userGroupId;
  public $reportId;
  public $name;
  public $type;
  public $comment;
  public $refs;
  public $countBy;
  public /*RepCritRec*/$Rec;
  public /*RepCritRec*/$RecDenom;
  public /*IpcHm*/$IpcHm;
  public /*RepSort[]*/$Sorts;
  //
  const TYPE_PATIENT = '1';
  const TYPE_AUDIT = '2';
  const TYPE_CDS = '3';
  const TYPE_MU = '4';
  const TYPE_CQM = '5';
  const TYPE_MU2 = '6';
  static $TYPES = array(
    self::TYPE_PATIENT => 'Patient Reports',
    self::TYPE_AUDIT => 'Audit Logs',
    self::TYPE_CDS => 'Clinical Decision Support',
    self::TYPE_MU => 'Meaningful Use (Stage 1)',
    self::TYPE_MU2 => 'Meaningful Use (Stage 2)',
    self::TYPE_CQM => 'Clinical Quality Measures'); 
  //
  const COUNT_BY_PATIENT = '0';
  const COUNT_BY_JOIN = '1';
  static $COUNT_BYS = array(
    self::COUNT_BY_PATIENT => 'Patient records',
    self::COUNT_BY_JOIN => 'Patient criteria records');
  //
  public function toJsonObject(&$o) {
    $o->_tableName = $this->Rec->getTableName();
  }
  public function isAudit() {
    return $this->type == self::TYPE_AUDIT;
  }
  public function isPatient() {
    return $this->type == self::TYPE_PATIENT;
  }
  public function isHistable() {
    return $this->RecDenom && ($this->type == self::TYPE_PATIENT || $this->type == self::TYPE_MU || $this->type == self::TYPE_MU2); 
  }
  public function isCds() {
    return $this->type == self::TYPE_CDS;
  }
  public function isMu() {
    return $this->type == self::TYPE_MU || $this->type == self::TYPE_MU2;
  }
  protected function getGroupBy() {
    if ($this->countBy == static::COUNT_BY_PATIENT) {
      switch ($this->type) {
        case static::TYPE_PATIENT:
        case static::TYPE_CDS:
        case static::TYPE_MU:
        case static::TYPE_MU2:
          return 'T0.client_id';
        default:
          return null;
      }
    }
  }
  //
  public function load() {
    $groupBy = $this->getGroupBy();
    if ($this->Rec)
      $this->recs = $this->Rec->fetchAll($this->userGroupId, $groupBy);
    if ($this->RecDenom) 
      $this->recsDenom = $this->RecDenom->fetchAll($this->userGroupId, $groupBy);
    return $this;
  }
  //
  static function asNew($type) {
    $tableId = self::getTableFromType($type);
    $class = RepCritRec::getClassFromTable($tableId);
    $rec = new static();
    $rec->name = self::$TYPES[$type];
    $rec->Rec = new $class();
    $rec->type = $type;
    if ($type == self::TYPE_CDS)
      $rec->IpcHm = null;
    return $rec;
  }
  static function fromReport($report) {
    $rec = new self($report->userGroupId, $report->reportId, $report->name, $report->type, $report->comment, $report->refs);
    $class = RepCritRec::getClassFromTable($report->tableId);
    $rec->Rec = RepCritRec::revive(jsondecode($report->jsonRec));
    if ($report->jsonRecDenom)
      $rec->RecDenom = RepCritRec::revive(jsondecode($report->jsonRecDenom));
    $rec->IpcHm = $report->IpcHm;
    $rec->countBy = $report->countBy;
    return $rec;
  }
  static function revive($ugid, $o) {
    if ($o->type == static::TYPE_CDS)
      return ReportCriteria_Cds::fromUi($ugid, $o);
    else
      return static::fromUi($ugid, $o);
  }
  protected static function fromUi($ugid, $o) {
    logit_r($o, 'fromUi');
    $rec = new static($ugid, $o->reportId, $o->name, $o->type, get($o, 'comment'), get($o, 'refs'), get($o, 'countBy'));
    logit_r('revive');
    $rec->Rec = RepCritRec::revive($o->Rec);
    if (isset($o->RecDenom))
      $rec->RecDenom = RepCritRec::revive($o->RecDenom);
    return $rec;
  }
  static function getTableFromType($type) {
    switch ($type) {
      case self::TYPE_AUDIT:
        return RepCritRec::T_AUDITS;
      default:
        return RepCritRec::T_CLIENTS;
    }
  }
}
class ReportCriteria_QuickAudit extends ReportCriteria {
  //
  static function from($ugid) {
    $me = new static();
    $me->userGroupId = $ugid;
    $me->name = 'Audit Report';
    $me->type = self::TYPE_AUDIT;
    $me->countBy = self::COUNT_BY_PATIENT;
    return $me;
  }
  static function forImmun($ugid, $cid, $immunId) {
    $me = static::from($ugid);
    $me->Rec = new RepCrit_Audit();
    $me->Rec->clientId = RepCritValue::asEquals($cid);
    $me->Rec->recName = RepCritValue::asEquals('Immun');
    $me->Rec->recId = RepCritValue::asEquals($immunId); 
    return $me;
  }
}
class ReportCriteria_Cds extends ReportCriteria {
  //
  public function load() {
    if ($this->IpcHm == null)
      return parent::load();
    require_once 'php/c/health-maint/HealthMaint_Recs.php';
    $sort = $this->Rec->getRecSort();
    $this->recs = Rec::sort(IpcHm::fetchAllApplicableClients($this->IpcHm->ipc, $this->userGroupId), $sort);
    $this->recsDenom = Rec::sort(IpcHm::fetchAllDueNowClients($this->IpcHm->ipc, $this->userGroupId), $sort);
  }
  protected static function fromUi($ugid, $o) {
    $rec = parent::fromUi($ugid, $o);
    $rec->IpcHm = get($o, 'IpcHm');
    return $rec;
  }
}
class IpcHm_Cds extends IpcHmRec implements NoAudit {
  //
  public $ipc;
  public $reportId;
  public $userGroupId;
  public $clientId;
  public $every;
  public $interval;
  public /*Ipc*/ $Ipc;
  //
  public function save_detached() {
    //$this->reportId = '0';
    //return $this->save();
    IpcHm_Cds::delete($this);
  }
  //
  static function revive($reportId, $ugid, $o) {
    if ($o == null)
      return null;
    $me = new static();
    $me->ipc = $o->ipc;
    $me->userGroupId = $ugid;
    $me->clientId = self::GROUP_LEVEL_CID;
    $me->reportId = $reportId;
    $me->every = $o->every;
    $me->interval = $o->interval;
    return $me;
  }
  static function fetch($ipc) {
    $c = self::asAppLevelCriteria();
    $c->ipc = $ipc;
    return self::fetchAppLevel($id);
  }
  static function fetchForReport($reportId) {
    $c = self::asCriteria();
    $c->reportId = $reportId;
    return self::fetchOneBy($c);
  }
  static function asOptionalJoin() {
    return CriteriaJoin::optional(self::asCriteria(), 'reportId');
  }
  static function asAppLevelCriteria() {
    $c = parent::asAppLevelCriteria();
    $c->Ipc = Ipc::asOptionalJoin();
    return $c;
  }
  static function asCriteria() {
    $c = new self();
    $c->clientId = self::GROUP_LEVEL_CID;
    $c->Ipc = Ipc::asOptionalJoin();
    return $c;
  }
}
/** 
 * RepCritRec
 */
abstract class RepCritRec extends Rec implements SerializeNulls {
  //
  const T_CLIENTS = '0';
  const T_ADDRESS = '1';
  const T_DIAGNOSES = '2';
  const T_MEDS = '3';
  const T_ALLERGIES = '4';
  const T_PROCS = '5';
  const T_RESULTS = '6';
  const T_IMMUNS = '7';
  const T_VITALS = '8';
  const T_AUDITS = '9';
  const T_SESSIONS = '10';
  const T_MEDHIST = '11';
  const T_SOCTOB = '12';
  const T_OFFICEVISIT = '13';
  const T_ICARDS = '14'; 
  const T_ADMINIPC = '15';
  static $TABLES = array(
    self::T_CLIENTS => 'Patients',
    self::T_ADDRESS => 'Address',
    self::T_DIAGNOSES => 'Diagnoses',
    self::T_MEDS => 'Medications',
    self::T_MEDHIST => 'Med History',
    self::T_ALLERGIES => 'Allergies',
    self::T_PROCS => 'Procedures',
    self::T_RESULTS => 'Results',
    self::T_IMMUNS => 'Immunizations',
    self::T_VITALS => 'Vitals',
    self::T_SESSIONS => 'Documents',
    self::T_SOCTOB => 'Social: Tobacco',
    self::T_OFFICEVISIT => 'Encounter',
    self::T_AUDITS => 'Audits',
    self::T_ICARDS => 'Insurance',
    self::T_ADMINIPC => 'MU Event');
  //
  public /*RepCritJoin[]*/ $Joins;
  public $table_;
  public $pid_;
  public $case_;
  //
  abstract public function getSqlClass();
  public function getRecSort() {
    return null;
  }
  public function getTemplatePid() {
    return null;
  } 
  public function isJoinedTo($table) {
    if (! empty($this->Joins)) 
      foreach ($this->Joins as $join)
        if ($join->isJoinedTo($table))
          return true;
  }
  public function addJoin($join) {
    $this->Joins[] = $join;
  }
  //
  protected function getClassFromJsonField($fid) {
    if ($fid == 'Joins')
      return 'RepCritJoins';
    else
      return 'RepCritValue';
  }
  public function toJsonObject(&$o) {
    $o->table_ = $this->getTable(); 
    if ($this->getTemplatePid()) 
      $o->pid_ = $this->getTemplatePid();
  }
  public function getTable() {
    if ($this instanceof RepCrit_Address)
      return self::T_ADDRESS;
    if ($this instanceof RepCrit_Client)
      return self::T_CLIENTS;
    if ($this instanceof RepCrit_Diagnosis)
      return self::T_DIAGNOSES;
    if ($this instanceof RepCrit_Session)
      return self::T_SESSIONS;
    if ($this instanceof RepCrit_Med)
      return self::T_MEDS;
    if ($this instanceof RepCrit_MedHist)
      return self::T_MEDHIST;
    if ($this instanceof RepCrit_SocTob)
      return self::T_SOCTOB;
    if ($this instanceof RepCrit_OfficeVisit)
      return self::T_OFFICEVISIT;
    if ($this instanceof RepCrit_AdminIpc)
      return self::T_ADMINIPC;
    if ($this instanceof RepCrit_Allergy)
      return self::T_ALLERGIES;
    if ($this instanceof RepCrit_Proc)
      return self::T_PROCS;
    if ($this instanceof RepCrit_ProcResult)
      return self::T_RESULTS;
    if ($this instanceof RepCrit_Immun)
      return self::T_IMMUNS;
    if ($this instanceof RepCrit_Vital)
      return self::T_VITALS;
    if ($this instanceof RepCrit_Audit) 
      return self::T_AUDITS;
    if ($this instanceof RepCrit_ICard) 
      return self::T_ICARDS;
  }
  public function getTableName() {
    return self::$TABLES[$this->getTable()];
  }
  public function getJoinFid($i = null) {
    if ($i === null) {
      $i = -1;
      foreach ($this->Joins as $join)
        $i += count($join->asSqlJoins());
    }
    return "Join$i";
  }
  /**
   * @return array(SqlRec_Rep,..)  
   */
  public function fetchAll($ugid, $groupBy = null) {
    $sqlClass = $this->getSqlClass();
    logit_r($sqlClass, 'sqlClass');
    $criteria = self::asSqlCriteria($this, $ugid);
    logit_r($criteria, 'criteria!');
    $recs = $sqlClass::fetchAllBy($criteria, null, 20000, null, null, null, $groupBy);
    //logit_r($recs, 'REPORT FETCHALL');
    //logit_r($this, 'THIS FETCHALL');
    $sort = $this->getRecSort();
    if ($sort) {
      Rec::sort($recs, $sort);
    }
    // Sort joins of each record
    foreach ($recs as &$rec) {
      if ($this->Joins) {
        foreach ($this->Joins as $i => $join) {
          if (count($join->Recs) > 0) {
            $jsort = $join->Recs[0]->getRecSort();
            if ($jsort) {
              $jfid = $this->getJoinFid($i);
              $jval = getr($rec, $jfid);
              if (is_array($jval)) {
                $rec->$jfid = Rec::sort($rec->$jfid, $jsort);
              }
            }
          }
        }
      }
    }
    return $recs; 
  }
  protected function assignSqlCriteriaValue(&$criteria, $fid, $value) {
    $cv = RepCritValue::asSqlCriteriaValue($criteria, $fid, $value);
    if ($cv) 
      $criteria->$fid = CriteriaValues::_and(get($criteria, $fid), $cv);
  }
  //
  /**
   * @param RepCritRec $rec
   */
  static function revive($rec) {
    logit_r($rec, 'revive445');
    logit_r($rec->table_, 'rectable445');
    $class = static::getClassFromTable($rec->table_);
    $me = new $class();
    $me->reviveValues($rec);
    return $me;
  }
  protected function reviveValues($rec) {
    if (! empty($rec)) {
      foreach ($rec as $fid => $value) {
        if ($fid == 'Joins')
          $this->Joins = RepCritJoin::reviveAll($value);
        else if (substr($fid, -1, 1) != '_')
          $this->$fid = new RepCritValue($value);
        else 
          $this->$fid = $value;
      }
    }
  }
  static function reviveAll($recs) {
    if (! empty($recs)) {
      $us = array();
      foreach($recs as $rec) 
        $us[] = static::revive($rec);
      return $us;
    }
  }
  /**
   * Modify existing RepCritValues to convert this to a SqlRec criteria object
   * @param RepCrit $rec
   * @param int $ugid (optional, for base critiera) 
   */
  static function asSqlCriteria($rec, $ugid = null) {
    $class = $rec->getSqlClass();
    $c = $class::asCriteria($ugid);
    $c->_case = get($rec, 'case_');
    $c->table_ = get($rec, 'table_');
    foreach ($rec as $fid => $value) 
      if ($value instanceof RepCritValue) 
        $rec->assignSqlCriteriaValue($c, $fid, $value);
    if (isset($rec->Joins)) {
      $cjoins = array();
      //logit_r($rec, 'look for rec->Joins');
      foreach ($rec->Joins as $join) 
        $cjoins = array_merge($cjoins, $join->asSqlJoins());
      $i = 0;
      foreach ($cjoins as $cjoin) {
        //logit_r($cjoin, 'cjoin');
        $fid = $rec->getJoinFid($i++);
        //logit_r($fid, 'fid');
        if (method_exists($cjoin->rec, 'getClientFk'))
          $fid .= '_' . $cjoin->rec->getClientFk();
        //logit_r($fid, 'fid2');
        $c->$fid = $cjoin;
      }
    }
    return $c;
  }
  static function asSqlCriterias($recs) {
    $a = array();
    foreach ($recs as $rec)
      $a[] = static::asSqlCriteria($rec);
    return $a;
  }
  static function getTableFromName($name) {
    static $a;
    if ($a == null) 
      $a = array_flip(self::$TABLES);
    return strval($a[$name]);
  }
  static function getClassFromTable($table) {
    switch ($table) {
      case self::T_CLIENTS:
        return 'RepCrit_Client';
      case self::T_ADDRESS:
        return 'RepCrit_Address';
      case self::T_DIAGNOSES:
        return 'RepCrit_Diagnosis';
      case self::T_SESSIONS:
        return 'RepCrit_Session';
      case self::T_MEDS:
        return 'RepCrit_Med';
      case self::T_MEDHIST:
        return 'RepCrit_MedHist';
      case self::T_SOCTOB:
        return 'RepCrit_SocTob';
      case self::T_OFFICEVISIT:
        return 'RepCrit_OfficeVisit';
      case self::T_ADMINIPC:
        return 'RepCrit_AdminIpc';
      case self::T_ALLERGIES:
        return 'RepCrit_Allergy';
      case self::T_PROCS:
        return 'RepCrit_Proc';
      case self::T_RESULTS:
        return 'RepCrit_ProcResult';
      case self::T_IMMUNS:
        return 'RepCrit_Immun';
      case self::T_VITALS:
        return 'RepCrit_Vital';
      case self::T_AUDITS:
        return 'RepCrit_Audit';
      case self::T_ICARDS:
        return 'RepCrit_ICard';
    }
  }
  static function getJoinSqlIndex($table) {
    switch ($table) {
      case self::T_DIAGNOSES:
      case self::T_MEDS:
      case self::T_ALLERGIES:
      case self::T_PROCS:
      case self::T_RESULTS:
      case self::T_IMMUNS: 
      case self::T_VITALS:
      case self::T_MEDHIST:
      case self::T_OFFICEVISIT:
      case self::T_ADMINIPC:
        return 'client_id';
      case self::T_SESSIONS:
        return 'client';
      case self::T_ICARDS:
        return null;
    }
  }
}
class RepCritJoin extends Rec {
  //
  public $jt;
  public $ct;
  public $table;
  public /*RepCrit*/ $Recs;
  //
  const JT_HAVE = '1';
  const JT_NOT_HAVE = '2';
  const JT_HAVE_CT = '3';
  const JT_HAVE_CT_LT = '4';
  const JT_HAVE_CT_GT = '5';
  const JT_HAVE_ONE = '10';
  const JT_HAVE_ALL = '11';
  const JT_NOT_HAVE_ANY = '12';
  const JT_NOT_HAVE_ALL = '13';
  const JT_OPTIONAL = '99';
  static $JTS = array(
    self::JT_HAVE => 'having',
    self::JT_HAVE_CT => 'having exactly',
    self::JT_HAVE_CT_LT => 'having less than',
    self::JT_HAVE_CT_GT => 'having at least',
    self::JT_NOT_HAVE => 'not having',
    self::JT_HAVE_ONE => 'having at least one of',
    self::JT_HAVE_ALL => 'having all of',
    self::JT_NOT_HAVE_ANY => 'not having any of',
    self::JT_NOT_HAVE_ALL => 'not having all of'
  );
  //
  public function getClassFromJsonField($fid) {
    // TODO : not using this->table any longer
    return RepCritRec::getClassFromTable($this->table);
  }
  public function isJoinedTo($table) {
    $rec = current($this->Recs);
    if ($rec && $rec->getTable() == $table)
      return true;
  }
  protected function getSingular($fid) {
    return $fid;
  }
  public function asSqlJoins() {
    static $orct = 1;
    if ($this->hasData()) {
      $joins = array();
      //logit_r($this->Recs, 'recs prior');
      $recs = RepCritRec::asSqlCriterias($this->Recs);
      $rec = current($recs);
      $sqlIndex = RepCritRec::getJoinSqlIndex($rec->table_);
      switch ($this->jt) {
        case self::JT_HAVE:
          $joins[] = CriteriaJoin::requiresAsArray($rec)->usingSqlIndex($sqlIndex);
          break;
        case self::JT_NOT_HAVE:
          $joins[] = CriteriaJoin::notExists($rec)->usingSqlIndex($sqlIndex);
          break;
        case self::JT_HAVE_CT:
          $joins[] = CriteriaJoin::requiresCountEquals($rec, $this->ct)->usingSqlIndex($sqlIndex);
          break;
        case self::JT_HAVE_CT_LT:
          $joins[] = CriteriaJoin::requiresCountLessThan($rec, $this->ct)->usingSqlIndex($sqlIndex);
          break;
        case self::JT_HAVE_CT_GT:
          $joins[] = CriteriaJoin::requiresCountAtLeast($rec, $this->ct)->usingSqlIndex($sqlIndex);
          break;
        case self::JT_HAVE_ONE:
          $casect = 1;
          $or = 'or' . $orct++;
          foreach ($recs as $rec) {
            if ($rec->_case)
              $case = 'case' . $casect++;
            else
              $case = null;
            $joins[] = CriteriaJoin::requiresAnyOf($rec, null, $or, $case)->usingSqlIndex($sqlIndex);
          }
          break;
        case self::JT_HAVE_ALL:
          foreach ($recs as $rec) {
            $sqlIndex = RepCritRec::getJoinSqlIndex($rec->table_); 
            $joins[] = CriteriaJoin::requiresAsArray($rec)->usingSqlIndex($sqlIndex);
          }
          break;
        case self::JT_NOT_HAVE_ANY:
          foreach ($recs as $rec) {
            $sqlIndex = RepCritRec::getJoinSqlIndex($rec->table_);
            $joins[] = CriteriaJoin::notExists($rec)->usingSqlIndex($sqlIndex);
          }
          break;
        case self::JT_NOT_HAVE_ALL:
          $or = 'or' . $orct++;
          foreach ($recs as $rec) { 
            $sqlIndex = RepCritRec::getJoinSqlIndex($rec->table_);
            $joins[] = CriteriaJoin::notExists($rec, null, $or)->usingSqlIndex($sqlIndex);
          }
          break;
        case self::JT_OPTIONAL:
          $joins[] = CriteriaJoin::optionalAsArray($rec)->usingSqlIndex($sqlIndex);
          break;
      }
      return $joins;
    }
  }
  public function hasData() {
    return ($this->jt && count($this->Recs) > 0);
  }
  //
  /**
   * @param string|int 
   * @return RepCritJoin
   */
  static function forTable($table, $joinType = null) {
    $rec = new self();
    $rec->jt = $joinType ?: static::JT_HAVE;
    $rec->table = (is_numeric($table)) ? $table : RepCritRec::getTableFromName($table);
    $class = $rec->getClassFromJsonField(null);
    $jrec = new $class();
    $jrec->table_ = $rec->table;
    $rec->Recs = array($jrec);
    return $rec;
  }
  static function asAddress() {
    $me = self::forTable(RepCritRec::T_ADDRESS, static::JT_OPTIONAL);
    return $me;
  }
  static function reviveAll($joins) {
    $us = array();
    if (! empty($joins)) {
      foreach ($joins as $join)
        $us[] = static::revive($join);
    }
    return $us;
  }
  static function revive($join) {
    $me = new static();
    $me->jt = $join->jt;
    $me->ct = get($join, 'ct');
    $me->Recs = RepCritRec::reviveAll($join->Recs);
    return $me; 
  }
}
/**
 * RepSort
 */
class RepSort extends Rec {
  //
  public $table;
  public $field;
  public /*bool*/$desc;
}
/**
 * RepCritValue
 */
class RepCritValue extends Rec {
  //
  public $op;
  public $value;  // single value e.g. 'Singulair' or comma-delimited e.g. '1,10'
  public $text_;  // picker text, e.g. 'Colonoscopy'
  //
  const OP_EQ = '1';
  const OP_NEQ = '2';
  const OP_START = '3';
  const OP_CONTAIN = '4';
  const OP_NULL = '6';
  const OP_NOT_NULL = '7';
  const OP_LTN = '11';
  const OP_GTN = '12';
  const OP_GTEN = '14';
  const OP_BETWEEN = '13';
  const OP_NUMERIC = '15';
  const OP_OLDER = '20';
  const OP_YOUNGER = '21';
  const OP_AGERANGE = '22';
  const OP_BEFORE = '23';
  const OP_AFTER = '24';
  const OP_ON = '25';
  const OP_NOT_ON = '26';
  const OP_WITHIN = '27';
  const OP_OVER = '28';
  const OP_BETWEEN_DATES = '29';
  const OP_IS = '30';
  const OP_IS_NOT = '31';
  const OP_IN = '32';
  const OP_SPLITAGERANGE = '33';
  const OP_REGEX = '40';
  const OP_NOT_REGEX = '41';
  static $OPS = array(
    self::OP_NULL => 'is empty',
    self::OP_NOT_NULL => 'has a value',
    /* strings/numerics */
    self::OP_EQ => 'equals',
    self::OP_NEQ => 'not equals',
    self::OP_START => 'starts with',
    self::OP_CONTAIN => 'contains',
    self::OP_LTN => 'less than',
    self::OP_GTN => 'greater than',
    self::OP_GTEN => 'greater than or equals',
    self::OP_BETWEEN => 'between',
    self::OP_NUMERIC => 'is numeric',
    self::OP_IN => 'is one of',
    /* date as calendar */
    self::OP_ON => 'on',
    self::OP_NOT_ON => 'not on',
    self::OP_BEFORE => 'before',
    self::OP_AFTER => 'after',
    self::OP_BETWEEN_DATES => 'between',
    /* date as age */
    self::OP_WITHIN => 'within past',
    self::OP_OVER => 'over',
    /* patient age */
    self::OP_OLDER => 'at least',
    self::OP_YOUNGER => 'younger than',
    self::OP_AGERANGE => 'from/up to',
    self::OP_SPLITAGERANGE => 'younger than/at least',
    /* fixed list */
    self::OP_IS => 'is',
    self::OP_IS_NOT => 'is not',
    //self::OP_IN => 'is one of',
    /* regex */
    self::OP_REGEX => 'is',
    self::OP_NOT_REGEX => 'is not');
  //
  public function hasData() {
    if ($this->op && ! is_null($this->value))
      return true;
    if ($this->op == self::OP_NOT_NULL || $this->op == self::OP_NULL || $this->op == self::OP_NUMERIC)
      return true;
  }
  public function isIs() {
    return $this->op == self::OP_IS;
  }
  //
  static function asEquals($value) {
    $rec = new static();
    $rec->op = self::OP_EQ;
    $rec->value = $value;
    $rec->text_ = $value;
    return $rec;
  }
  /**
   * @param SqlRec criteria
   * @param string fid
   * @param RepCritValue cv
   */
  static function asSqlCriteriaValue($criteria, $fid, $cv) {
    if ($cv->hasData()) {
      switch ($cv->op) {
        case self::OP_EQ:
        case self::OP_IS:
          return CriteriaValue::equals(self::asFilteredValue($criteria, $fid, $cv->value));
        case self::OP_NEQ:
        case self::OP_IS_NOT:
        case self::OP_NOT_ON:
          return CriteriaValue::notEquals(self::asFilteredValue($criteria, $fid, $cv->value));
        case self::OP_START:
          return CriteriaValue::startsWith(self::asFilteredValue($criteria, $fid, $cv->value));
        case self::OP_CONTAIN:
          return CriteriaValue::contains(self::asFilteredValue($criteria, $fid, $cv->value));
        case self::OP_NULL:
          return CriteriaValue::isNull();
        case self::OP_NOT_NULL:
          return CriteriaValue::isNotNull();
        case self::OP_LTN:
          return CriteriaValue::lessThanNumeric(self::asFilteredValue($criteria, $fid, $cv->value));
        case self::OP_GTN:
          return CriteriaValue::greaterThanNumeric(self::asFilteredValue($criteria, $fid, $cv->value));
        case self::OP_GTEN:
          return CriteriaValue::greaterThanOrEqualsNumeric($cv->value);
        case self::OP_BEFORE:
          return CriteriaValue::lessThan(self::asFilteredValue($criteria, $fid, $cv->value));
        case self::OP_AFTER:
          return CriteriaValue::greaterThan(self::asFilteredValue($criteria, $fid, $cv->value));
        case self::OP_BETWEEN_DATES:
          return CriteriaValue::betweenDates(self::asFilteredValueArray($criteria, $fid, $cv->value));
        case self::OP_ON:
          return CriteriaValue::startsWith(self::asFilteredValue($criteria, $fid, $cv->value));
        case self::OP_IN:
          return CriteriaValue::in(self::asFilteredValueArray($criteria, $fid, $cv->value));
        case self::OP_BETWEEN:
          return CriteriaValue::betweenNumeric(self::asFilteredValueArray($criteria, $fid, $cv->value));
        case self::OP_NUMERIC:
          return CriteriaValue::isNumeric();
        case self::OP_OLDER:
          return CriteriaValue::olderThan($cv->value);
        case self::OP_YOUNGER:
          return CriteriaValue::betweenAge(array(0, $cv->value));
        case self::OP_AGERANGE:
          return CriteriaValue::betweenAge(self::asValueArray($cv->value));
        case self::OP_SPLITAGERANGE:
          return CriteriaValue::splitAgeRange(self::asValueArray($cv->value));
        case self::OP_WITHIN:
          return CriteriaValue::withinPast(self::asValueArray($cv->value));
        case self::OP_OVER:
          return CriteriaValue::over(self::asValueArray($cv->value));
        case self::OP_REGEX:
          return CriteriaValue::regexp($cv->value);
        case self::OP_NOT_REGEX:
          return CriteriaValue::notRegexp($cv->value);
      }
    }
  }
  protected static function asValueArray($value) {
    return explode(',', $value);
  }
  protected static function asFilteredValueArray($criteria, $fid, $value) {
    $values = self::asValueArray($value);
    foreach ($values as &$value) 
      $value = self::asFilteredValue($criteria, $fid, $value);
    return $values;
  }
  protected static function asFilteredValue($criteria, $fid, $value) {
    return RecJson::filterIncomingValue($criteria, $fid, $value);
  }
}
require_once 'Reporting_Recs.php';
p_i('/Reporting');
