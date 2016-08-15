<?php
require_once 'Scheduling_Recs.php';
//
/**
 * Scheduling
 * @author Warren Hornsby
 */
class Scheduling {
  //
  /** Get appointments */
  static function /*Appt[]*/getAppts($date = null/*today*/, $providerId = null/*mine*/, $days/*to include*/ = 1) {
    global $login;
    if ($providerId == null) 
      $providerId = $login->docId;
    if ($login->cerberus)
      $recs = Appt_Cerberus::fetchAll($login->userGroupId, $date, $days, $providerId, $login->cerberus);
    else
      $recs = Appt::fetchAll($login->userGroupId, $date, $days, $providerId);
    $recs = Appt::addPasts($recs);
    return RecSort::sort($recs, 'date', 'timeStart');
  }
  static function getAppts_2weeks($date, $providerId) {
    return static::getAppts($date, $providerId, 14);
  }
  /** Get appt history */
  static function /*Appt[]*/getHistory($cid) {
    return Appt::fetchHistory($cid);
  }
  /** Get next appt */
  static function /*Appt_Next*/getNextAppt($cid) {
    return Appt_Next::fetch($cid);
  }
  /** Get for edit UI */
  static function /*Appt_Edit*/get($id) {
    $appt = Appt_Edit::fetch($id);
    if ($appt->Client)
      $appt->Client->Appts = Scheduling::getHistory($appt->clientId);
    return $appt;
  }
  /** Save appt/event from UI */
  static function /*Appt_Edit*/save($ui) {
    global $login;
    $appt = Appt_Edit::from($ui, $login->userGroupId);
    $appt = $appt->save();
    $maxRepeatDate = $appt->_maxRepeatDate;
    $appt = static::get($appt->schedId);
    if ($maxRepeatDate) 
      $appt->Event->_max = $maxRepeatDate;
    return $appt;
  }
  /** Delete appt/event(s) */
  static function delete($schedId, $withFutureRepeats = false) {
    Appt_Edit::delete($schedId, $withFutureRepeats);
  }
} 
