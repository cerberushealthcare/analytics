<?php
//
require_once 'php/data/rec/sql/Procedures_Admin.php';
require_once 'php/newcrop/data/soap/DailyMeaningfuluseReport.php';
//
class Proc_Admin_MU2 {
  //
  public function save($date, /*DailyMeaningfulUseReport*/$mus) {
    $map = Mu2DailyMap::fromDailyMUReport($date, $mus);
    $map->saveIpcs();
  }
}
class Mu2DailyMap {
  //
  public $date;
  public $docId;
  //
  /* mapped as array('cid'=>rec,..) */
  public $portalVdt;
  public $portalDsm;
  public $summaryToPatient;
  public $summaryFromDoctor;
  public $summaryToDoctor;
  public $medRecon;
  //
  public function saveIpcs() {
    ProcMu2_portalVdt::recordAll($this->portalVdt, $this->date, $this->docId);
    ProcMu2_portalDsm::recordAll($this->portalDsm, $this->date, $this->docId);
    ProcMu2_medRecon::recordAll($this->medRecon, $this->date, $this->docId);
    ProcMu2_summaryToDoctor::recordAll($this->summaryToDoctor, $this->date, $this->docId);
    ProcMu2_summaryFromDoctor::recordAll($this->summaryFromDoctor, $this->date, $this->docId);
    ProcMu2_summaryToPatient::recordAll($this->summaryToPatient, $this->date, $this->docId);
  }
  //
  protected function getCids($map) {
    return array_keys($map);
  }
  //
  static function fromDailyMUReport($date, /*DailyMeaningfulUseReport[]*/$mus) {
    $me = static::from($date);
    $mu = null;
    if ($mus) {
      foreach ($mus as $mu) {
        $cid = $mu->getClientId();
        if ($mu->isMu2_portalVdt()) 
          $me->portalVdt[$cid] = $mu;
        if ($mu->isMu2_portalDsm()) 
          $me->portalDsm[$cid] = $mu;
        if ($mu->isMu2_summaryToPatient()) 
          $me->summaryToPatient[$cid] = $mu;
        if ($mu->isMu2_summaryFromDoctor()) 
          $me->summaryFromDoctor[$cid] = $mu;
        if ($mu->isMu2_summaryToDoctor()) 
          $me->summaryToDoctor[$cid] = $mu;
        if ($mu->isMu2_medRecon()) 
          $me->medRecon[$cid] = $mu;
      }
    }
    if ($mu) 
      $me->docId = $mu->getDoctorId();
    return $me;
  }
  static function from($date) {
    $me = new static();
    $me->date = $date;
    $me->portalVdt = array();
    $me->portalDsm = array();
    $me->summaryToPatient = array();
    $me->summaryToDoctor = array();
    $me->summaryFromDoctor = array();
    $me->medRecon = array();
    return $me;
  }
}
//
class Proc_AdminMu2 extends Proc_AdminSaveAlways {
  //
  static function record($cid, $date = null, $userId = null, $timestamp = null) {
    logit_r('record timestamp='.$timestamp);
    $me = static::from($cid, $date, $userId, $timestamp);
    $recs = static::fetchDeletes($me);
    if (empty($recs)) {
      $rec = static::fetchByTimestamp($me);
      if (empty($rec)) {
        $me->save();
        return true;
      }
    }
  }
  static function recordAll($map, $date, $userId) {
    //$cids = array_keys($map);
    logit_r($map, 'recordAll 151');
    foreach ($map as $cid => $mu) {
      static::record($cid, $date, $userId, $mu->CreatedTimestamp);
    }
  }
  static function from($cid, $date, $userId, $ncTimestamp) {
    $me = parent::from($cid, $date, $userId);
    $me->ncTimestamp = $ncTimestamp;
    return $me;
  }
  static function fetchByTimestamp($me) {
    $c = new static();
    $c->clientId = $me->clientId;
    $c->ipc = $me->ipc;
    $c->ncTimestamp = $me->ncTimestamp;
    return static::fetchOneBy($c);
  }
}
class ProcMu2_portalVdt extends Proc_AdminMu2 {
  static $IPC = 603792;
}
class ProcMu2_portalDsm extends Proc_AdminMu2 {
  static $IPC = 602825;
}
class ProcMu2_summaryToDoctor extends Proc_AdminMu2 {
  static $IPC = 602820;
  //
  static function record($cid, $date = null, $userId = null, $timestamp = null) {
    $saved = parent::record($cid, $date, $userId, $timestamp);
    if ($saved) {
      Proc_SumCareA::record($cid, $date, $userId);
      Proc_SumCareB::record($cid, $date, $userId);
    }
  }
}
class ProcMu2_summaryFromDoctor extends Proc_AdminMu2 {
  static $IPC = 604714;
  //
  static function record($cid, $date = null, $userId = null, $timestamp = null) {
    $saved = parent::record($cid, $date, $userId, $timestamp);
    if ($saved) {
      Proc_EncSummaryCare::record($cid, $userId);
    }
  }
}
class ProcMu2_medRecon extends Proc_AdminMu2 {
  static $IPC = 600174;
  //
  static function record($cid, $date = null, $userId = null, $timestamp = null) {
    $saved = parent::record($cid, $date, $userId, $timestamp);
    if ($saved) {
      Proc_EncMedRecon::record($cid, $userId);
      $tdate = dateToString(static::getMrTransCareDate($cid));
      $days = getWorkingDays($tdate, $date, null);
      if ($days <= 14) {
        Proc_TransCareMedRecon::record($cid, $tdate);
      }
    }
  }
  static function getMrTransCareDate($cid) {
    $recs = Proc::fetchAllForIpc($cid, Ipcs::TransitionOfCare);
    if ($recs) {
      $rec = reset($recs);
      return $rec->date;
    }  
  }
}
class ProcMu2_summaryRefused extends Proc_AdminMu2 {
  static $IPC = 602078;
}
class ProcMu2_portalRefused extends Proc_AdminMu2 {
  static $IPC = 600233;
}
class ProcMu2_summaryToPatient extends Proc_AdminMu2 {
  static function record($cid, $date, $userId, $timestamp, $printed = 0) {
    $proc = Proc_OfficeVisit::getLast($cid);
    if ($proc) {
      $dos = dateToString($proc->date);
      if ($date == $dos) {
        ProcMu2_summaryToPatient_le1::record($cid, $date, $userId, $timestamp);
      }
      $days = getWorkingDays($dos, $date, null);
      if ($days <= 3) {
        ProcMu1_summaryToPatient_le3::record($cid, $date, $userId, $timestamp);
      }
      if ($printed == 0 && $days <= 4) { 
        ProcMu2_summaryToPatient_le4::record($cid, $date, $userId, $timestamp);
      }
      /*
      if ($date == $dos) {
        if ($printed)
          ProcMu2_summaryToPatient_printed_le1::record($cid, $date, $userId);
        else 
          ProcMu2_summaryToPatient_le1::record($cid, $date, $userId);
      } else {
        logit_r('9999');
        ProcMu2_summaryToPatient_gt1::record($cid, $date, $userId);
        if (getWorkingDays($dos, $date, null) <= 4) { 
          ProcMu2_summaryToPatient_le4::record($cid, $date, $userId);
        }
      }
      */
    }
  }
}
class ProcMu2_summaryToPatient_le1 extends Proc_AdminMu2 {
  static $IPC = 604746;
}
class ProcMu1_summaryToPatient_le3 extends Proc_AdminMu2 {
  static $IPC = 604743;
}
class ProcMu2_summaryToPatient_le4 extends Proc_AdminMu2 {
  static $IPC = 604731;
}
