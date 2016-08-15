<?php
require_once 'php/data/rec/sql/_AuditMruRec.php';
//
/**
 * Auditing
 * DAO for AuditRecs
 * @author Warren Hornsby
 */
class Auditing {
  /*
   * Composite records
   */
  const REC_FACESHEET = 'Facesheet';
  const REC_POP = 'FacesheetTable';
  const REC_VITALS_CHART = 'VitalsChart';
  const REC_RX = 'RX';
  //
  static function getClientUpdateTimestamp($cid) {
    $sql = "SELECT date FROM audit_mrus WHERE client_id=$cid";
    return Dao::fetchValue($sql);
  }
  /**
   * @param string $action AuditRec::ACTION_
   * @param int $clientId
   * @param string $recName
   * @param string $recId (optional)
   * @param string $label (optional)
   * @param AuditImage $before (optional)
   * @param AuditImage $after (optional)
   */
  static function log($action, $clientId, $recName, $recId = null, $label = null, $before = null, $after = null) {
    global $login;
    $audit = new AuditRec();
    $audit->userGroupId = $login->userGroupId;
    $audit->userId = $login->userId;
    $audit->clientId = $clientId;
    $audit->date = nowNoQuotes();
    $audit->action = $action;
    $audit->recName = $recName;
    $audit->recId = ($recId) ? $recId : AuditRec::NO_REC_ID;
    $audit->source = currentUrl();
    $audit->sessId = session_id();
    $audit->label = $label;
    if ($before)
      $audit->before = $before->toString();
    if ($after) 
      $audit->after = $after->toString();
    $audit->hrec = $audit->hash();
    $audit->save();
    AuditMru::saveAudit($audit);
  }
  static function logRec($action, $rec, $before = null, $after = null) {
    Auditing::log($action, $rec->getAuditClientId(), $rec->getAuditRecName(), $rec->getAuditRecId(), $rec->getAuditLabel(), $before, $after);
  }
  /**
   * @param SqlRec $rec
   * @return SqlRec 
   */
  static function logCreateRec($rec) {
    $after = AuditImage::from($rec);
    Auditing::logRec(AuditRec::ACTION_CREATE, $rec, null, $after);
    return $after->rec;
  }
  /**
   * @param SqlRec $rec
   * @return SqlRec 
   */
  static function logDupeUpdateRec($rec, $before) {
    if ($before) {
      $after = AuditImage::asAfterChange($rec, $before);
      if ($after) {
        Auditing::logRec(AuditRec::ACTION_CREATE_DUPE_UPDATE, $rec, $before, $after);
        $rec = $after->rec;
      }
    } else {
      $after = AuditImage::from($rec);
      Auditing::logRec(AuditRec::ACTION_CREATE, $rec, null, $after);
      $rec = $after->rec;
    }
    return $rec;
  }
  /**
   * @param SqlRec $rec
   * @param AuditImage $before
   * @return SqlRec 
   */
  static function logUpdateRec($rec, $before, $action = null) {
    if ($action == null)
      $action = AuditRec::ACTION_UPDATE;
    $after = AuditImage::asAfterChange($rec, $before);
    if ($after) {
      Auditing::logRec($action, $rec, $before, $after);
      $rec = $after->rec;
    }
    return $rec;
  }
  /**
   * @param SqlRec $rec
   * @param AuditImage $before
   */
  static function logDeleteRec($rec, $before) {
    Auditing::logRec(AuditRec::ACTION_DELETE, $rec, $before, null);
  }
  static function logPrintRec($rec) {
    Auditing::logRec(AuditRec::ACTION_PRINT, $rec);
  }
  static function logReviewRec($rec) {
    Auditing::logRec(AuditRec::ACTION_REVIEW, $rec);
  }
  static function logReviewFacesheet($client, $ipchms) {
    $after = null;
    global $login;
    if ($login->Role->Patient->cds) {
      $after = AuditImage::fromHms($ipchms);
    }
    Auditing::log(AuditRec::ACTION_REVIEW, $client->clientId, Auditing::REC_FACESHEET, null, $client->getAuditLabel(), null, $after);
  }
  static function logBreakGlass($client) {
    Auditing::logRec(AuditRec::ACTION_BREAKGLASS, $client->clientId, Auditing::REC_FACESHEET, null, $client->getAuditLabel());
  }
  static function logPrintFacesheet($cid, $name) {
    Auditing::log(AuditRec::ACTION_PRINT, $cid, Auditing::REC_FACESHEET, null, $name);
  }
  static function logPrintRx($med) {
    Auditing::log(AuditRec::ACTION_PRINT, $med->clientId, Auditing::REC_RX, $med->dataMedId, $med->getAuditLabel());
  }
  static function logPrintPop($cid, $tableId, $title) {
    Auditing::log(AuditRec::ACTION_PRINT, $cid, Auditing::REC_POP, $tableId, $title);
  }
  static function logPrintVitalsChart($cid, $chartId, $title) {
    Auditing::log(AuditRec::ACTION_PRINT, $cid, Auditing::REC_VITALS_CHART, $chartId, $title);
  }
  static function logPrint($cid, $type, $id, $title) {
    Auditing::log(AuditRec::ACTION_PRINT, $cid, $type, $id, $title);    
  }
}
class AuditImage {
  //
  public $rec;
  public $fids;
  //
  public function toString() {
    return jsonencode($this->fids);
  }
  //
  /**
   * @param SqlRec $rec
   * @return AuditImage
   */
  static function from($rec) {
    $image = new self();
    $image->rec = $rec->fetchForAudit();
    if ($image->rec == null)  
      return null;  // rec doesn't exist
    $image->fids = $image->rec->getAuditFields();
    return $image;
  }
  static function fromHms($ipchms) {
    $me = new static();
    $a = array();
    $i = 0;
    if ($ipchms) {
      foreach ($ipchms as $hm) {
        $i++;
        $a['ipc' . $i] = $hm->Ipc->ipc;
        $a['name' . $i] = $hm->Ipc->name;
        $a['active' . $i] = $hm->active;
        $a['overdue' . $i] = $hm->_overdue;
      }
    }
    $me->fids = $a;
    return $me;
  }
  /**
   * @param SqlRec $rec after changes
   * @param AuditImage $before
   * @return AuditImage including only fids that have changed
   * @return null if no fids have changed
   */
  static function asAfterChange($rec, &$before) {
    $after = self::from($rec);
    $afterFids = array_diff_assoc($after->fids, $before->fids);
    $beforeFids = array_diff_assoc($before->fids, $after->fids);
    $after->fids = $afterFids;
    $before->fids = $beforeFids; 
    return (empty($after->fids)) ? null : $after;
  }
}
//
/**
 * Audit Record
 */
class AuditRec extends SqlRec implements NoAuthenticate, AutoEncrypt {
  //
  public $auditRecId;
  public $userGroupId;
  public $clientId;
  public $userId;
  public $date;
  public $action;
  public $recName;
  public $recId;
  public $txId;
  public $source;
  public $sessId;
  public $label;
  public $before;
  public $after;
  public $hrec;
  //
  const ACTION_CREATE = 'C';
  const ACTION_COPY = '+';
  const ACTION_CREATE_DUPE_UPDATE = 'K';
  const ACTION_REVIEW = 'R';
  const ACTION_UPDATE = 'U';
  const ACTION_DELETE = 'D';
  const ACTION_PRINT  = "P";
  const ACTION_SIGN = 'S';
  const ACTION_BREAKGLASS = '!';
  const ACTION_UNSIGN = '$';
  static $ACTIONS = array(
    self::ACTION_CREATE => 'Create',
    self::ACTION_COPY => 'Copy',
    self::ACTION_CREATE_DUPE_UPDATE => 'Write',
    self::ACTION_REVIEW => 'Review',
    self::ACTION_UPDATE => 'Update',
    self::ACTION_DELETE => 'Delete',
    self::ACTION_PRINT => "Print",
    self::ACTION_BREAKGLASS => 'Break Glass',
    self::ACTION_SIGN => 'Sign',
    self::ACTION_UNSIGN => 'Unsign');
  //
  const NO_CLIENT = 0;
  const NO_REC_ID = 0;
  //  
  public function getSqlTable() {
    return 'audit_recs';
  }
  public function getEncryptedFids() {
    return array('label','before','after');
  }
  public function hash() {
    $rec = $this->date 
      . $this->action
      . $this->recName
      . $this->recId
      . $this->source
      . $this->sessId
      . $this->before
      . $this->after;
    return MyCrypt_Auto::hash($rec); 
  }
}
/**
 * Audit Most Recent Update
 */
class AuditMru extends AuditMruRec implements CompositePk/*to force insert dupe update*/ {
  //
  public $clientId;
  public $userGroupId;
  public $userId;
  public $date;
  public $action;
  public $recName;
  public $recId;
  public $label;
  //
  /**
   * @param AuditRec $audit
   */
  static function saveAudit($audit) {
    $mru = AuditMru::fromAudit($audit);
    if ($mru) { 
      $mru->save();
      //ClientUpdate::save_mru($mru);
    }
  }
  //
  private static function fromAudit($audit) {
    if ($audit->clientId != AuditRec::NO_CLIENT) 
      if ($audit->action != AuditRec::ACTION_REVIEW)
        return new AuditMru(
          $audit->clientId,
          $audit->userGroupId,
          $audit->userId,
          $audit->date,
          $audit->action,
          $audit->recName,
          $audit->recId,
          $audit->label);
  } 
}
