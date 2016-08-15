<?php
require_once 'php/data/rec/sql/_FsDataRec.php';
//
/**
 * Health Maintenance DAO
 * - FaceHm (sid=0):       Proc history record (by proc ID + date) 
 * - SessionHm (sid>0):    Built from closed note (generates a FaceHm) 
 * - SummaryHm (sid=null): Proc summary record (last result, next due info)
 * @author Warren Hornsby
 */
class HmProcs {
  // TODO
}
//
/**
 * HM Procedure 
 */
class HmProc extends FsDataRec {
  //
  public $dataHmId;
  public $userGroupId;
  public $clientId;
  public $sessionId;
  public $type;
  public $procId;  
  public $proc;
  public $dateText;
  public $dateSort;
  public $results;
  public $nextTimestamp;
  public $active;
  public $dateUpdated;
  public $nextText;
  public $cint;
  public $cevery;  
  //
  public function getSqlTable() {
    return 'data_hm';
  }
  public function getKey() {
    return array('procId', 'dateSort');
  }
  public function getFaceClass() {
    return 'FaceHm';
  }
  public function getJsonFilters() {
    return array(
      'dateSort' => JsonFilter::editableDateApprox(),
      'dateUpdated' => JsonFilter::informalDateTime());
  }
  public function getAuditRecName() {
    return 'HmProc';
  }
  public static function asFace($replaceFace = null) {
    $face = parent::asFace();
    $face->type = FaceHm::TYPE;
    if ($face->sessionId == null) { 
      $face->sessionId = '0';
      $face->dateSort = formatFromLongApproxDate($face->dateText);
      $face->dateText = null;
    }
    return $face;
  }
  //
  /**
   * @param object $o JSON
   * @param int $ugid
   * @return HmProc 
   */
  public static function fromUi($o, $ugid) {
    $rec = new HmProc($o);
    $rec->userGroupId = $ugid;
    return $rec;
  }
  /**
   * Build new face records from session history
   * @param int cid
   */
  public static function buildFacesFromSessions($cid) {
    $sessions = SessionHm::fetchAllUnbuilt($cid);
    if ($sessions) {
      $faces = FaceHm::fetchMap($cid);
      parent::_buildFacesFromSessions($sessions, $faces);
    }
  }
  /**
   * Build new face records from old (active=null)
   * @param int $cid
   */
  public static function buildFacesFromOldFaces($cid) {
    $c = FaceHm::asCriteria($cid);
    $c->active = CriteriaValue::isNull();
    $oldFaces = parent::fetchAllBy($c);
    foreach ($oldFaces as $oldFace) {
      $face = $oldFace->asFace();
      if ($face)
        $face->save();
    }
  }
}
/**
 * HmProc Face Record
 */
class FaceHm extends HmProc {
  //
  const TYPE = 2;  // using as current version (for rebuilding old faces) 
  //
  public function deactivate() {
    parent::_deactivate($this);
  }
	/**
   * @param int $id
   * @return FaceHm
   */
  public static function fetch($id) {
    return parent::_fetchFace($id, __CLASS__);
  }
  /**
   * @param int $cid
   * @return array(key=>FaceHm,..) 
   */
  public static function fetchMap($cid) {
    $c = self::asCriteria($cid);
    $c->active = CriteriaValue::isNotNull();
    return self::fetchMapBy($c, $c->getKey());
  }
  /**
   * @param int $cid
   * @return array(FaceHm,..)
   */
  public static function fetchAllActive($cid) {
    return parent::_fetchActiveFaces($cid, __CLASS__);
  }
  /**
   * @param object $o JSON
   * @param int $ugid
   * @return FaceHm saved
   */
  public static function saveFromUi($o, $ugid) {
    $hm = HmProc::fromUi($o, $ugid);
    $face = $hm->asFace();
    return parent::_saveFromUi($face);
  }
  /**
   * @param int $cid
   * @return FaceHm 
   */
  public static function asCriteria($cid) {
    $c = new FaceHm();
    $c->clientId = $cid;
    $c->sessionId = '0';
    return $c;
  }  
}
//
/**
 * HmProc Session Record
 */
class SessionHm extends HmProc implements ReadOnly {
  //
  /**
   * @param int $cid
   * @return array(SessionHm,..)
   */
  public static function fetchAllUnbuilt($cid) {
    $c = self::asUnbuiltCriteria($cid);
    return self::fetchAllBy($c);
  }
  /**
   * @param int $cid
   * @return SessionHm
   */
  public static function asUnbuiltCriteria($cid) {
    $c = parent::_asUnbuiltSessCriteria($cid, __CLASS__);
    return $c;
  }
}
/**
 * HmProc Summary Record
 */
class SummaryHm extends HmProc implements NoAudit {
  //
  /**
   * @param FaceHm $face
   * @return SummaryHm
   */
  public static function fromFace($face) {
    $sum = new SummaryHm($face);
    $sum->sessionId = null;
    $sum->setPkValue(null);
    $sum->active = true;
    $sum->dateUpdated = null;
        
  }
} 
?>