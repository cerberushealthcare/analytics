<?php
require_once 'php/data/_BasicRec.php';
require_once 'PatientList_Sql.php';
require_once 'php/cbat/ccda-batch/CcdaBatch_Recs.php';
//
/**
 * Patient List/Search
 * @author Warren Hornsby
 */
class PatientList {
  //
  /** Get most recently used patients */
  static function /*PatientStub[]*/mru($activeOnly = true, $limit = 30) {
    global $login;
    $recs = PStub_Mru::fetchLimit($login->userGroupId, $limit, null, $activeOnly);
    return $recs;
  }
  /** Get page of most recently used patients */
  static function /*PatientPage*/page($page = 1, $activeOnly = true, $byMru = true, $limit = 100) {
    global $login;
    Logger::debug('c/patient-list/PatientList.php: Entered with ' . $login->userGroupId . ' and limit ' . $limit);
    $page = PatientPage::fetch($login->userGroupId, $limit, $page, $activeOnly, $byMru);
    return $page;
  }
  /** Search for partial matches */
  static function /*PatientStub[]*/search($last, $first, $uid = null, $birth = null, $activeOnly = true) {
    global $login;
    $recs = PStub_Search::search($login->userGroupId, $last, $first, $uid, $birth, $activeOnly);
    return $recs;
  }
  /** Search for exact match */
  static function /*PatientStub[]*/match($last, $first, $birth, $sex) {
    global $login;
    $recs = PStub_Search::searchForMatches($login->userGroupId, $last, $first, $birth, $sex);
    return $recs;
  }
  /** Get a patient stub */
  static function /*PatientStub*/get($cid) {
    if ($cid)
      return PatientStub::fetch($cid);
  }
  /** Get all active stubs */
  static function /*PatienStub[]*/getAllActive() {
    global $login;
    $recs = PatientStub::fetchAll($login->userGroupId);
    return $recs;
  }
}
class PatientPage extends Rec {
  //
  public $page;
  public /*PStub_Mru[]*/$recs;
  public /*bool*/$more;
  public /*CcdaBatch*/$Batch;
  //
  static function fetch($ugid, $limit, $page, $activeOnly) {
    global $login;
    if ($login->uid == 'llane')
      $recs = PStub_Mru::fetchLimit_Ayoub($ugid, $limit, $page, $activeOnly); 
    else
      $recs = PStub_Mru::fetchLimit($ugid, $limit, $page, $activeOnly);
    $Batch = CcdaBatch::fetchLast($ugid);
    $me = static::from($recs, $limit, $page, $Batch);
    return $me;
  }
  static function from($recs, $limit, $page, $Batch) {
    $me = new static();
    $me->page = $page;
    if (count($recs) > $limit) {
      array_pop($recs);
      $me->more = true;
    }
    $me->recs = $recs;
    $me->Batch = $Batch;
    return $me;
  }
}