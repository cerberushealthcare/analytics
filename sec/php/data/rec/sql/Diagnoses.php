<?php
require_once 'php/data/rec/sql/_FsDataRec.php';
require_once 'php/data/rec/sql/_HdataRec.php';
require_once 'php/data/rec/sql/Snomeds.php';
require_once 'php/data/rec/sql/IcdCodes.php';
require_once 'php/data/rec/sql/IcdCodes10.php';
//
/**
 * Diagnoses DAO
 * - FaceDiagnosis (sid=null): Diagnosis record (by date)
 * - SessionDiagnosis (sid>0): Built from closed note (generates a FaceDiagnosis)
 * @author Warren Hornsby
 */
class Diagnoses {
  /**
   * Build facesheet records from unprocessed session history and old facesheet records
   * @param int $cid
   */
  static function rebuild($cid) {
    Diagnosis::buildFacesFromSessions($cid);
  }
  /**
   * Get active facesheet records
   * @param int $cid
   * @return array(FaceDiagnosis,..)
   */
  static function getActive($cid) {
    global $login;
    self::rebuild($cid);
    $recs = FaceDiagnosis::fetchAllActive($cid);
    if ($login->userGroupId == 3022)
      Rec::sort($recs, new RecSort('dataDiagnosesId'));
    else
      Rec::sort($recs, new RecSort('-date', 'text'));
    return $recs;
  }
  /**
   * Mark actives as reconciled
   */
  static function reconcile($cid, $diags) {
    logit_r($diags, 'reconcilediags');
    global $login;
    if ($cid) {
      $map = FaceDiagnosis::fetchAllActiveMap($cid);
      foreach ($diags as $i => $diag) {
        if (isset($map[get($diag, 'dataDiagnosesId')])) {
          unset($map[$diag->dataDiagnosesId]);
          unset($diags[$i]);
        }
      }
      FaceDiagnosis::deactivateMany($map);
      FaceDiagnosis::activateMany($cid, $login->userGroupId, $diags);
      FaceDiagnosis::reconcileActives($cid, $login->userId);
    }
  }
  /**
   * Get all facesheet records
   * @param int $cid
   * @return array(FaceDiagnosis,..)
   */
  static function getAll($cid) {
    self::rebuild($cid);
    $recs = FaceDiagnosis::fetchAll($cid);
    global $login;
    if ($login->userGroupId == 3022)
      Rec::sort($recs, new RecSort('dataDiagnosesId'));
    else
      Rec::sort($recs, new RecSort('icd', 'text', '-date'));
    return $recs;
  }
  /**
   * Get session records by date
   * @param int $cid
   * @return array(SessionDiagnosis,..)
   */
  static function getHistory($cid) {
    $recs = SessionDiagnosis::fetchAll($cid);
    Rec::sort($recs, new RecSort('-date', 'sessionId', 'text'));
    return $recs;
  }
  /**
   * Save record from UI
   * @param stdClass $o JSON object
   * @return FaceDiagnosis
   */
  static function save($o) {
    global $login;
    return FaceDiagnosis::saveFromUi($o, $login->userGroupId);
  }
  /**
   * @param int $id
   * @return int client ID
   */
  public static function delete($id) {
    $rec = FaceDiagnosis::fetch($id);
    if ($rec) {
      $cid = $rec->clientId;
      FaceDiagnosis::delete($rec);
      return $cid;
    }
  }
  /**
   * Deactivate record from UI
   * @param int $id
   * @return FaceDiagnosis
   */
  static function deactivate($id) {
    $face = FaceDiagnosis::fetch($id);
    if ($face) {
      $face->deactivate();
      return $face;
    }
  }
  /**
   * Deactivate all client diagnoses
   * @param int $cid
   */
  static function deactivateAll($cid) {
    $faces = FaceDiagnosis::fetchAllActive($cid);
    foreach ($faces as $face)
      $face->deactivate();
  }
  /**
   * Deactivate all and assign 'none active' record
   * @param int $cid
   */
  static function setNoneActive($cid) {
    global $login;
    self::deactivateAll($cid);
    NoneActiveDiagnosis::add($login->userGroupId, $cid);
  }
  /**
   * Copy diagnosis to past medical HX
   * @param string $name
   */
  static function copyToMedHx($name) {
    // TODO
  }
}
//
/**
 * Diagnosis
 */
class Diagnosis extends FsDataRec implements AutoEncrypt {
  //
  public $dataDiagnosesId;
  public $userGroupId;
  public $clientId;
  public $sessionId;
  public $date;
  public $parUid;
  public $text;
  public $parDesc;
  public $icd;
  public $icd10;
  public $active;
  public $dateUpdated;
  public $dateClosed;
  public $status;
  public $snomed;
  public $dateRecon;
  public $reconBy;
  //
  const STATUS_ACTIVE = '1';
  const STATUS_CHRONIC = '2';
  const STATUS_INTERMITTENT = '3';
  const STATUS_RECURRENT = '4';
  const STATUS_RULE_OUT = '5';
  const STATUS_INACTIVE = '10';
  const STATUS_RULED_OUT = '11';
  const STATUS_RESOLVED = '20';
  static $STATUSES = array(
    self::STATUS_ACTIVE => 'Active',
    self::STATUS_CHRONIC => 'Chronic',
    self::STATUS_INTERMITTENT => 'Intermittent',
    self::STATUS_RECURRENT => 'Recurrent',
    self::STATUS_RULE_OUT => 'Rule Out',
    self::STATUS_RESOLVED => 'Resolved',
    self::STATUS_INACTIVE => 'Inactive',
    self::STATUS_RULED_OUT => 'Ruled Out');
  static $ACTIVES = array(
    self::STATUS_ACTIVE => 1,
    self::STATUS_CHRONIC => 1,
    self::STATUS_INTERMITTENT => 1,
    self::STATUS_RECURRENT => 1,
    self::STATUS_RULE_OUT => 1);
  //
  public function getSqlTable() {
    return 'data_diagnoses';
  }
  public function getEncryptedFids() {
    return array('date','dateClosed');
  }
  public function getKey() {
    return 'text';
  }
  public function getFaceClass() {
    return 'FaceDiagnosis';
  }
  public function getJsonFilters() {
    return array(
      'active' => JsonFilter::boolean(),
      'date' => JsonFilter::editableDateTime(),
      'dateUpdated' => JsonFilter::informalDateTime(),
      'dateRecon' => JsonFilter::informalDateTime(),
      'dateClosed' => JsonFilter::editableDateTime());
  }
  public function getAuditRecName() {
    return 'Diagnosis';
  }
  public function toJsonObject(&$o) {
    $o->_name = $this->formatName();
    $o->lookup('status', self::$STATUSES);
  }
  public function formatName() {
    $name = $this->text;
    if ($this->status == self::STATUS_RULE_OUT)
      $name = 'R/O: ' . $name;
    $c = array();
    if ($this->icd)
      $c[] = $this->icd;
    if ($this->icd10)
      $c[] = $this->icd10;
    if (count($c) > 0) 
      $name .= " (" . implode(', ', $c) . ")";
    return $name;
  }
  public function save() {
    $this->active = $this->isActiveStatus($this->status);
    parent::save();
    Hdata_DiagnosisDate::from($this)->save();
    Hdata_DiagnosisDateClosed::from($this)->save();
  }
  public function isActiveStatus() {
    return isset(self::$ACTIVES[$this->status]);
  }
  //
  /**
   * @param object $o JSON
   * @param int $ugid
   * @return Diagnosis
   */
  static function fromUi($o, $ugid) {
    $rec = new Diagnosis($o);
    $rec->userGroupId = $ugid;
    return $rec;
  }
  /**
   * Build new face records from session history
   * @param int cid
   */
  static function buildFacesFromSessions($cid) {
    $sessions = SessionDiagnosis::fetchAllUnbuilt($cid);
    logit_r($sessions, 'fetch all unbuilt');
    if ($sessions) {
      $faces = FaceDiagnosis::fetchActiveMap($cid);
      parent::_buildFacesFromSessions($sessions, $faces);
    }
  }
  /*
   * @return CriteriaValue
   */
  static function getActiveCriteriaValue() {
    return CriteriaValue::lessThanNumeric(self::STATUS_INACTIVE);
  }
}
/**
 * Diagnosis Face Record
 */
class FaceDiagnosis extends Diagnosis {
  //
  public function deactivate() {
    $this->status = self::STATUS_INACTIVE;
    parent::_deactivate($this);
  }
  public function saveAsReconciled($date, $userId) {
    $this->dateRecon = $date;
    $this->reconBy = $userId;
    $this->save();
  }
  //
  /**
   * @param int $id
   * @return FaceDiagnosis
   */
  static function fetch($id) {
    return parent::_fetchFace($id, __CLASS__);
  }
  /**
   * @param int $cid
   * @return array(FaceDiagnosis,..)
   */
  static function fetchAll($cid) {
    $recs = parent::_fetchFaces($cid, __CLASS__);
    $recs = array_merge($recs, NoneActiveDiagnosis::fetchAll($cid));
    return $recs;
  }
  /**
   * @param int $cid
   * @return array(FaceDiagnosis,..)
   */
  static function fetchAllActive($cid) {
    $recs = parent::_fetchActiveFaces($cid, __CLASS__);
    if (empty($recs))
      $recs = NoneActiveDiagnosis::fetchAll($cid);
    return $recs;
  }
  static function fetchAllActiveMap($cid) {
    $map = array();
    $recs = static::fetchAllActive($cid);
    foreach ($recs as $rec) {
      $map[$rec->dataDiagnosesId] = $rec;
    }
    return $map;
  }
  static function fetchAllActive_forPortal($cid, $class) {
    $recs = parent::_fetchActiveFaces($cid, $class);
    return $recs;
  }
  /**
   * @param int $cid
   * @return array(text=>FaceDiagnosis,..)
   */
  static function fetchActiveMap($cid) {
    $c = self::asActiveCriteria($cid);
    return self::fetchMapBy($c, $c->getKey());
  }
  static function deactivateMany($recs) {
    foreach ($recs as $rec) {
      $rec->deactivate();
      $rec->save();
    }
  }
  static function activateMany($cid, $ugid, $recs) {
    foreach ($recs as $rec) {
      $rec->clientId = $cid;
      static::saveFromUi($rec, $ugid);
    }
  }
  /**
   * @param object $o JSON
   * @param int $ugid
   * @return FaceDiagnosis saved
   */
  static function saveFromUi($o, $ugid) {
    $diagnosis = Diagnosis::fromUi($o, $ugid);
    $face = $diagnosis->asFace(true);
    NoneActiveDiagnosis::remove($face->clientId);
    if (empty($face->snomed))
      $face->snomed = Snomeds::getCidFromIcd9($face->icd);
    if (empty($face->icd)) 
      $face->icd = Snomeds::getIcd9FromCid($face->snomed);
    if (empty($face->text)) {
      if (! empty($face->icd))
        $face->text = IcdCodes::getDesc($face->icd);
      else if (! empty($face->icd10))
        $face->text = IcdCodes10::getDesc($face->icd10);
      else if (! empty($face->snomed))
        $face->text = Snomeds::getShortDesc($face->snomed);
      if (empty($face->text)) 
        throw new DisplayableException('Description is required.');
    }
    return parent::_saveFromUi($face);
  }
  static function reconcileActives($cid, $userId) {
    $recs = static::fetchAllActive($cid);
    $now = nowNoQuotes();
    foreach ($recs as $rec) {
      $rec->saveAsReconciled($now, $userId);
    }
  }
  /**
   * @param int $cid
   * @return FaceDiagnosis
   */
  static function asCriteria($cid) {
    $c = parent::_asFaceCriteria($cid, __CLASS__);
    $c->text = CriteriaValue::notEquals(NoneActiveDiagnosis::NONE_ACTIVE);
    return $c;
  }
  static function asActiveCriteria($cid) {
    return parent::_asActiveFaceCriteria($cid, __CLASS__);
  }
  static function asGroupCriteria($ugid) {
    $c = self::asCriteria(null);
    $c->userGroupId = $ugid;
    return $c;
  }
}
class NoneActiveDiagnosis extends FaceDiagnosis {
  //
  const NONE_ACTIVE = 'None Active';
  //
  public function save() {
    $this->active = true;
    SqlRec::save();
  }
  public function toJsonObject(&$o) {
    $o->_name = $this->text;
    $o->_status = 'N/A';
    $o->_none = true;
  }
  /**
   * @param int $cid
   * @return NoneActiveDiagnosis
   */
  static function fetchAll($cid) {
    $c = self::asCriteria($cid);
    return self::fetchAllBy($c);
  }
  /**
   * @param int $ugid
   * @param int $cid
   */
  static function add($ugid, $cid) {
    $c = self::from($ugid, $cid);
    $c->save();
  }
  /*
   * @param int cid
   */
  static function remove($cid) {
    if ($cid) {
      $recs = self::fetchAll($cid);
      foreach ($recs as $rec)
        self::delete($rec);
    }
  }
  //
  /**
   * @param int $ugid
   * @param int $cid
   * @return NoneActiveDiagnosis
   */
  static function from($ugid, $cid) {
    $rec = self::asCriteria($cid);
    $rec->userGroupId = $ugid;
    $rec->active = true;
    return $rec;
  }
  //
  static function asCriteria($cid) {
    $rec = new self();
    $rec->clientId = $cid;
    $rec->text = self::NONE_ACTIVE;
    return $rec;
  }
}
//
/**
 * Diagnosis Session Record
 */
class SessionDiagnosis extends Diagnosis implements ReadOnly {
  //
  protected function shouldReplace($face) {
    logit_r($face, 'shouldReplace face');
    logit_r($this, 'shouldReplace this');
    return ($this->isNewerThan($face) && ($this->icd != $face->icd || ($this->icd == $face->icd && $this->icd10 && $face->icd10 == null)));  // replace only if ICD changed
  }
  /*
   * @return FaceDiagnosis
   */
  public function asFace($replaceFace = null) {
    $face = parent::asFace($replaceFace);
    $face->parUid = null;
    $face->parDesc = null;
    if ($replaceFace)
      $face->date = $replaceFace->date;  // keep existing diagnosis onset date
    $face->active = true;
    $face->status = self::STATUS_ACTIVE;
    return $face;
  }
  //
  private function explodeText() {
    return array_filter(explode('<br>', $this->text));
  }
  //
  /**
   * @param int $cid
   * @return array(SessionDiagnosis,..)
   */
  static function fetchAll($cid) {
    $c = self::asCriteria($cid);
    return self::fetchAllBy($c);
  }
  /**
   * @param int $cid
   * @return array(SessionDiagnosis,..)
   */
  static function fetchAllUnbuilt($cid) {
    $c = self::asUnbuiltCriteria($cid);
    $recs = self::fetchAllBy($c);
    return self::addCloneTextRecs($recs);
  }
  /**
   * @param int $cid
   * @return SessionDiagnosis
   */
  static function asCriteria($cid) {
    return parent::_asSessCriteria($cid, __CLASS__);
  }
  /**
   * @param int $cid
   * @return SessionDiagnosis
   */
  static function asUnbuiltCriteria($cid) {
    return parent::_asUnbuiltSessCriteria($cid, __CLASS__);
  }
  //
  private static function addCloneTextRecs($recs) {  // make dupe session recs out of 'multi<br>diagnoses'
    $crecs = array();
    foreach ($recs as $rec)
      $crecs = array_merge($crecs, self::clonesByText($rec));
    return $crecs;
  }
  private static function clonesByText($rec) {
    $pk = $rec->getPkValue();
    $texts = $rec->explodeText();
    if (count($texts) == 1)
      return array($rec);
    $recs = array();
    foreach ($texts as $text) {
      $text = trim($text);
      if ($text) {
        $sess = clone $rec;
        $sess->setPkValue($pk);
        $sess->text = $text;
        $recs[] = $sess;
      }
    }
    return $recs;
  }
}
//
require_once "php/dao/DataDao.php";
