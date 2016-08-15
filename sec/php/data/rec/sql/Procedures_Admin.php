<?php
//
require_once 'php/data/rec/sql/Procedures.php';
require_once 'php/data/rec/sql/_ICardRec.php';
//
/**
 * Procedures (Administrative Category) 
 * @author Warren Hornsby
 * @example 
 *   Proc_ReviewedBmiChart::record($cid);  // server-side
 *   Ajax.Procedures.record(this.fs.client.clientId, 600179);  // client-side  
 */
//
/** One-per-day */
class Proc_Admin extends Proc implements NoAudit {
  static $IPC;
  //
  static function record($cid, $date = null, $userId = null, $ipc = null) {
    $me = static::from($cid, $date, $userId, $ipc);
    static::deleteOld($me);
    $me->save();
    return $me;
  }
  static function from($cid, $date = null, $userId = null, $ipc = null) {
    global $login;
    $me = new static();
    $me->userGroupId = $login->userGroupId;
    $me->clientId = $cid;
    $me->date = ($date) ? dateToString($date) : nowShortNoQuotes();
    $me->userId = $userId ?: $login->userId;
    $me->ipc = $ipc ?: static::$IPC;
    return $me;
  }
  protected static function deleteOld($me) {
    $recs = static::fetchDeletes($me);
    if ($recs)
      static::deleteAll($recs);
  }
  protected static function fetchDeletes($me) {
    $c = static::asDeleteCrit($me);
    if ($c)
      return static::fetchAllBy($c);
  }
  protected static function asDeleteCrit($me) {
    if ($me->clientId && $me->ipc && $me->date) {
      $c = new static();
      $c->clientId = $me->clientId;
      $c->ipc = $me->ipc;
      $c->setDateCriteria($me->date);
      return $c;
    }
  }
}
/** One-per-patient */
class Proc_AdminOneTime extends Proc_Admin implements NoAudit {
  //
  static function record($cid, $active, $ipc = null) {
    $ipc = $ipc ? : static::$IPC;
    if ($cid && $ipc) {
      static::deleteOld($cid, $ipc);
      if ($active) {
        $me = static::from($cid, null, null, $ipc);
        $me->save();
      }
    }
  }
  protected static function deleteOld($cid, $ipc) {
    $recs = static::fetchDeletes($cid, $ipc);
    static::deleteAll($recs);
  }
  protected static function fetchDeletes($cid, $ipc) {
    $c = static::asDeleteCrit($cid, $ipc);
    return static::fetchAllBy($c);
  }
  protected static function asDeleteCrit($cid, $ipc) {
    $me = new static();
    $me->clientId = $cid;
    $me->ipc = $ipc;
    return $me;
  }
}
/** Record without deleting old */
class Proc_AdminSaveAlways extends Proc_Admin implements NoAudit {
  //
  static function record($cid, $date = null, $userId = null, $ipc = null) {
    $me = static::from($cid, $date, $userId, $ipc);
    $me->save();
  }
}
/** Associated with tracking */
class Proc_AdminTracking extends Proc_Admin implements NoAudit {
  //
  static function record($cid, $trackItemId, $date = null) {
    if ($trackItemId) {
      $me = static::from($cid, $date);
      $me->trackItemId = $trackItemId;
      static::deleteOld($me);
      $me->save();
    } 
  }
  static function record_fromTrackItem(/*TrackItem*/$rec) {
    static::record($rec->clientId, $rec->trackItemId, $rec->orderDate);
  }
  protected static function asDeleteCrit($me) {
    if ($me->clientId && $me->ipc) {
      $c = new static();
      $c->clientId = $me->clientId;
      $c->ipc = $me->ipc;
      $c->trackItemId = $me->trackItemId;
      return $c;
    }
  }
}
/** Associated with tracking or scan */
class Proc_AdminTrackScan extends Proc_Admin implements NoAudit {
  //
  static function record($cid, $date, $trackItemId = null, $scanIndexId = null) {
    if ($trackItemId || $scanIndexId) {
      $me = static::from($cid, $date);
      $me->trackItemId = $trackItemId;
      $me->scanIndexId = $scanIndexId;
      static::deleteOld($me);
      $me->save();
    } 
  }
  static function record_fromTrackItem($track) {
    static::record($track->clientId, $track->orderDate, $track->trackItemId);
  }
  static function record_fromScanIndex($scan) {
    static::record($scan->clientId, $scan->datePerformed, null, $scan->scanIndexId);
  }
  protected static function asDeleteCrit($me) {
    if ($me->clientId && $me->ipc) {
      $c = new static();
      $c->clientId = $me->clientId;
      $c->ipc = $me->ipc;
      $c->trackItemId = $me->trackItemId;
      $c->scanIndexId = $me->scanIndexId;
      return $c;
    }
  }
}
//
/** ------------------------------ Admin procs ------------------------------ */
//
class Proc_OfficeVisit extends Proc_Admin {
  static $IPC = Ipcs::OfficeVisit;
  //
  static function record($cid, $date = null, $userId = null) {
    if (static::isFirstTime($cid)) {
      Proc_NewEncounter::record($cid, $date);
    }
    parent::record($cid, $date, $userId); 
  }
  static function isFirstTime($cid) {
    global $login;
    $c = new static();
    $c->userGroupId = $login->userGroupId;
    $c->ipc = static::$IPC;
    $c->clientId = $cid;
    return (static::fetchOneBy($c)) ? false : true;
  }
  static function getLast($cid) {
    global $login;
    $c = new static();
    $c->userGroupId = $login->userGroupId;
    $c->ipc = static::$IPC;
    $c->clientId = $cid;
    $recs = static::fetchAllBy($c, new RecSort('-date'));
    return reset($recs);
  }
}
class Proc_NewEncounter extends Proc_Admin {
  static $IPC = 602901;
  //
  static function record($cid, $date, $userId = null) {
    parent::record($cid, $date, $userId);
    Proc_EncSummaryCare::record($cid, $userId, $date);
  }
}
class Proc_RadioWithImage extends Proc_AdminTrackScan {
  static $IPC = 602826;
  //
  static function record($scan) {
    $me = static::from($scan->clientId, $scan->datePerformed);
    $me->scanIndexId = $scan->scanIndexId;
    static::deleteOld($me);
    if ($scan->withImage) {
      $me->save();
    }
  }
}
class Proc_SummaryDownloaded extends Proc_Admin {
  static $IPC = 604742;
  //
  static function record($cid, $date = null, $userId = null) {
    parent::record($cid, $trackItemId, $date, $userId);
    Proc_SumCareA::record($cid, $date);
  }
}
class Proc_CcdImported extends Proc_AdminSaveAlways {
  static $IPC = 604714;
}
class Proc_OrderLab extends Proc_AdminTrackScan {
  static $IPC = 603783;
}
class Proc_CpoeLab extends Proc_AdminTrackScan {
  static $IPC = 602290;
} 
class Proc_OrderRadio extends Proc_AdminTrackScan {
  static $IPC = 603784;
}
class Proc_CpoeRadio extends Proc_AdminTrackScan {
  static $IPC = 602291;
}
class Proc_Referral extends Proc_AdminTracking {
  static $IPC = 600215;
}
class Proc_FamHxRecorded extends Proc_AdminOneTime {
  static $IPC = 602827;
  //
  static function record_fromDsync($cid, $value) {
    $active = strpos($value, 'Father') || strpos($value, 'Mother') || strpos($value, 'Brother') || strpos($value, 'Sister') || strpos($value, 'Son') || strpos($value, 'Daughter');
    static::record($cid, $active);
  } 
}
class Proc_SmokingHxRecorded extends Proc_Admin {
  static $IPC = 600084;
} 
class Proc_ReviewedBmiChart extends Proc_Admin {
  static $IPC = 600209;
} 
class Proc_ImmunInfoProvided extends Proc_Admin {
  static $IPC = 600179;
}
class Proc_MedsReconciled extends Proc_Admin {
  static $IPC = 600174;
}
class Proc_Pneumovax extends Proc_Admin {
  static $IPC = 600211;
}
class Proc_Tetanus extends Proc_Admin {
  static $IPC = 600210;
}
class Proc_GenerateOrders extends Proc_Admin {
  static $IPC = 600175;
}
class Proc_LivingWillPoa extends Proc_Admin {
  static $IPC = 600173;
}
/*
class Proc_VisitSummary extends Proc_Admin {
  static $IPC = 600212;
}
class Proc_VisitSummary3d extends Proc_Admin {
  static $IPC = 600229;
}
*/
class Proc_VitalsRecorded extends Proc_Admin {
  static $IPC = 603786;
}
class Proc_InfoButton extends Proc_Admin {
  static $IPC = 602784;
}
class Proc_PatientSummary extends Proc_AdminTracking {
  static $IPC = 600216;
  //
  static function asOptionalJoin() {
    return parent::asOptionalJoin('trackItemId');
  }
  static function asJoinCriteria() {
    $c = new static();
    $c->ipc = static::$IPC;
    return $c;
  }
  static function record($cid, $trackItemId, $date = null) {
    parent::record($cid, $trackItemId, $date);
    Proc_SumCareA::record($cid, $date);
  }
}
class Proc_CareReminder extends Proc_Admin {
  static $IPC = 600213;
  //
  static function recordAll($cids, $name) {
    $us = array();
    foreach ($cids as $cid) 
      $us[] = static::from($cid, $name);
    static::insertAll($us, 100);
  }
  static function from($cid, $name) {
    $me = parent::from($cid);
    $me->comments = $name;
    return $me;
  }
}
class Proc_NewCropRefresh extends Proc_Admin {
  static $IPC = 600271;
}
class Proc_ACE extends Proc_AdminOneTime {
  static $IPC = 600416;
  //
  static function record($cid, $meds) {
    $has = DrugClasses::has_ACEInhibitor($meds);
    parent::record($cid, $has);
  }
}
class Proc_ACEOrARB extends Proc_AdminOneTime {
  static $IPC = 600417;
  //
  static function record($cid, $meds) {
    $has = DrugClasses::has_ACEInhibitor($meds) || DrugClasses::has_ARB($meds);  
    parent::record($cid, $has);
  }
}
class Proc_BetaBlocker extends Proc_AdminOneTime {
  static $IPC = 600418;
  //
  static function record($cid, $meds) {
    $has = DrugClasses::has_BetaBlocker($meds);
    parent::record($cid, $has);
  }
}
//
class Proc_AdminResult extends Proc_Admin {
  static function record($cid, $date, $value) {
    $me = parent::record($cid, $date);
    $result = new ProcResult();
    $result->procId = $me->procId;
    $result->clientId = $me->clientId;
    $result->seq = 0;
    $result->ipc = static::$IPC;
    $result->value = $value;
    $result->save();
  }
  protected static function asDeleteCrit($me) {
    $c = parent::asDeleteCrit($me);
    if ($c) {
      $c->ProcResult = new ProcResult();      
    }
    return $c;
  }
  static function delete(&$rec) {
    if ($rec->ProcResult) {
      ProcResult::delete($rec->ProcResult);
    }
    parent::delete($rec);
  }
}
class Proc_BMI extends Proc_AdminResult {
  static $IPC = 602473;
}
class Proc_Diastolic extends Proc_AdminResult {
  static $IPC = 602353;
} 
class Proc_Systolic extends Proc_AdminResult {
  static $IPC = 602354;
}
/* 
class Proc_ErxNonSched extends Proc_Admin {
  static $IPC = 604753;
}
class Proc_ErxNonSchedFormularyChecked extends Proc_Admin {
  static $IPC = 604754;
}
*/
class Proc_FormularyNotChecked extends Proc_Admin {
  static $IPC = 604744;
  //
  static function recordIfNoInsurance($cid, $date) {
    if (ICard_ProcAdmin::noneActive($cid)) {
      static::record($cid, $date);
      return true;
    }
  }
}
class ICard_ProcAdmin extends ICardRec {
  //
  static function noneActive($cid) {
    $c = static::asCriteria($cid);
    $me = static::fetchOneBy($c);
    if (empty($me)) {
      return true;
    }
  }
  static function asCriteria($cid) {
    $c = new static();
    $c->clientId = $cid;
    return $c;
  }
}
/* For MU reporting */
class Proc_SumCareA extends Proc_Admin {
  static $IPC = 604747;
}
class Proc_SumCareB extends Proc_Admin {
  static $IPC = 604748;
}
class Proc_TransCareMedRecon extends Proc_Admin {
  static $IPC = 604752;
}
class Proc_Admin_MostRecentEncounter extends Proc_Admin {
  //
  static function record($cid, $userId = null, $date = null) {
    if ($date == null) {
      $date = static::getMrEncounterDate($cid);
    }
    parent::record($cid, $date, $userId);
    return $date;
  }
  static function getMrEncounterDate($cid) {
    $recs = Proc::fetchEncounters($cid);
    if ($recs) {
      $rec = reset($recs);
      return $rec->date;
    }
  }
  static function fetch($cid, $date, $ipc = null) {
    $c = new static();
    $c->clientId = $cid;
    $c->ipc = $ipc ?: static::$IPC;
    $c->setDateCriteria($date);
    return static::fetchOneBy($c);
  }
}
class Proc_EncSummaryCare extends Proc_Admin_MostRecentEncounter {
  static $IPC = 604749;
  //
  static function record($cid, $userId = null, $date = null) {
    $date = parent::record($cid, $userId, $date);
    Proc_EncSummaryCareMedRecon::record($cid, $date, Proc_EncMedRecon::$IPC, $userId);
  }
} 
class Proc_EncMedRecon extends Proc_Admin_MostRecentEncounter {
  static $IPC = 604750;
  //
  static function record($cid, $userId = null) {
    $date = parent::record($cid, $userId);
    Proc_EncSummaryCareMedRecon::record($cid, $date, Proc_EncSummaryCare::$IPC, $userId);
  }
} 
class Proc_EncSummaryCareMedRecon extends Proc_Admin {
  static $IPC = 604751;
  //
  static function record($cid, $date, $otherIpc, $userId = null) {
    logit_r($cid.','.$date.','.$otherIpc, 'record escmd');
    $rec = Proc_Admin_MostRecentEncounter::fetch($cid, $date, $otherIpc);
    if ($rec) {
      parent::record($cid, $date, $userId);
    }
  }
}