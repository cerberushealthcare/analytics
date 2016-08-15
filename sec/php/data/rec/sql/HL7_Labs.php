<?php
require_once 'php/data/LoginSession.php';
require_once 'php/data/rec/sql/Clients.php';
require_once 'php/data/rec/sql/_Hl7InboxRec.php';
require_once 'php/data/rec/sql/_LabRec.php';
require_once 'php/data/rec/sql/Procedures.php';
require_once 'php/data/rec/sql/OrderEntry.php';
require_once 'php/data/rec/group-folder/GroupFolder_Labs.php';
//
/**
 * HL7 Lab Interface
 * @author Warren Hornsby
 */
class HL7_Labs {
  //
  /** Import from single file upload */
  static function import_fromUpload() {
    static::setVersion('2.5.1');
    global $login;
    $folder = GroupFolder_Labs::open();
    $file = $folder->upload();
    $lab = Lab::fetch(8)/*NIST TEST*/;
    $msg = LabMessage::fromHL7($file->readContents(), $lab);
    logit_r($msg);
    if ($msg)
      self::import($lab, $msg, $login->userGroupId, $file->filename);
  }
  /** Import from single SFTP file */
  static function import_fromFtpFile(/*FtpFile*/$file) {
    static::setVersion('2.3.1');
    $msgs = LabMessage::fromFtpFile($file, $file->Lab);
    blog($msgs, 'msgs');
    foreach ($msgs as $msg)
      static::import_fromFtpMsg($file, $msg);
  }
  /** Import from web service */
  static function import_fromWebService(/*LabServicesPost*/$post) {
    static::setVersion('2.3.1');
    $lab = Lab::fetch_byWebService($post);
    $msg = LabMessage::fromHL7($post->msg, $lab);
    if ($msg) {
      $ugid = $msg->getUgid();
      $login = LoginSession::loginBatch($ugid, __CLASS__);
      self::import($lab, $msg, $ugid);
    }
  }
  static function setVersion($version) {
    switch ($version) {
      case '2.5.1':
        require_once 'php/data/hl7-2.5.1/msg/_HL7Message.php';
        require_once 'php/data/hl7-2.5.1/msg/labs/LabMessage.php';
        break;
      default:
        require_once 'php/data/hl7/msg/_HL7Message.php';
        require_once 'php/data/hl7/msg/labs/LabMessage.php';
        break;
    }
  }
  //
  protected static function import_fromFtpMsg($file, $msg) {
    if ($msg) {
      $ugid = $msg->getUgid();
      $login = LoginSession::loginBatch($ugid, __CLASS__);
      self::import($file->Lab, $msg, $ugid, $file->filename);
    } else {
      throw new LabMsgInvalidEx($file->getFilepath());
    }
  }
  protected static function import(/*Lab*/$lab, /*LabMessage*/$msg, $ugid, $filename = null) {
    $valid = $msg->reconcile();
    $pdf = $msg->getDecodedPdf();
    $inbox = HL7Inbox_New::add($lab->labId, $msg, $msg->Header->getSource(), $ugid, $filename, $pdf);
    /*
    if ($valid) {
      $inbox->cid = $msg->getClientId();
      static::saveIntoChart($msg, $inbox);
    } 
    */
  }
  /** Save reconciliation, return LabRecon if still errors */
  static function /*LabRecon*/saveLabRecon($inboxId, /*LabMessage with updates, e.g. Proc_, ProcResult_, TrackItem_*/$obj) {
    global $login;
    logit_r('saveLabRecon');
    $inbox = HL7Inbox::fetch($inboxId)->applyRecon($obj);
    logit_r('111');
    $msg = $inbox->getMessage();
    logit_r('222');
    $valid = $msg->reconcile_afterUpdates($inbox->cid, $obj);
    logit_r($valid, '333 valid');
    if (! $valid) {
      logit_r('444');
      return LabRecon::from($inbox, $msg);
    } else { 
      logit_r('555');
      static::saveIntoChart($msg, $inbox, $login->userId);
    }
  }
  protected static function saveIntoChart($msg, $inbox, $userId = null/*to auto-reconcile*/) {  
    Dao::begin();
    try {
      logit_r($inbox, 'inbox');
      $inbox = $inbox->saveAsReconciled($userId);
      $notes = $inbox->makeReconcileNotes();
      $msg->saveIntoChart($notes, $userId, $inbox->hl7InboxId);
      Dao::commit();
    } catch (Exception $e) {
      Dao::rollback();
      throw $e;
    }
  }
  static function /*HL7Inbox[]*/getInboxes() {
    global $login;
    $recs = HL7Inbox::fetchUnreconciled($login->userGroupId);
    return $recs;
  }
  static function removeInbox($inboxId) {
    $inbox = HL7Inbox::fetch($inboxId);
    SqlRec::delete($inbox);
  }
  static function /*int*/getInboxCt() {
    $recs = self::getInboxes();
    return (empty($recs)) ? 0 : count($recs);
  }
  static function /*OruMessage*/getInboxMessage($id) {
    $inbox = HL7Inbox::fetch($id);
    if ($inbox) 
      return $inbox->getMessage();
  }
  static function /*LabRecon*/getLabRecon($id) {
    $inbox = HL7Inbox::fetch($id);
    if ($inbox) {
      $rec = LabRecon::from($inbox);
      return $rec;
    }
  }
  static function /*LabRecon*/assignInboxToClient($inboxId, $cid) {
     $inbox = HL7Inbox::fetch($inboxId);
     if ($inbox) { 
       $inbox = $inbox->saveClient($cid);
       return LabRecon::from($inbox);
     }
  }
}
//
class Lab extends LabRec {
  //
  public $labId;
  public $uid;
  public $name;
  public $status;
  public $sendMethod;
  public $sftpFolder;
  public $id;
  public $pw;
  public $address;
  public $contact;
  //
  public function getLabel() {
    return $this->uid . ' ' . $this->name; 
  }
  public function toJsonObject(&$o) {
    unset($o->status);
    unset($o->sendMethod);
    unset($o->sftpFolder);
    unset($o->id);
    unset($o->pw);
  }
  //
  static function fetchByUid($uid) {
    $c = new static();
    $c->uid = $uid;
    $me = static::fetchOneBy($c);
    return $me;
  }
  static function fetch_byAuth($id, $pw) {
    $me = static::fetchByUid($id);
    if ($me && $me->pw == $pw) {
      return $me;
    }
  }
  static function fetch_byWebService(/*LabServicesPost*/$post) {
    $me = static::fetchByUid($post->uid);
    if ($me == null) 
      throw new LabUidNotFoundEx();
    if ($me->status == static::STATUS_INACTIVE)
      throw new LabInactiveEx();
    if ($me->pw != $post->pw)
      throw new LabPwInvalidEx();
    return $me;
  }
  static function fetchAll_forSftpPolling() {
    $c = new static();
    $c->status = static::STATUS_ACTIVE;
    //$c->sendMethod = CriteriaValue::in(array(static::SEND_METHOD_SFTP, static::SEND_METHOD_SFTP_PULL)); -- modified so that webservice dumps to in folder, so all methods should apply here
    $mes = static::fetchAllBy($c);
    return $mes;
  }
  static function fetchAll_forSftpPull() {
    $c = new static();
    $c->status = static::STATUS_ACTIVE;
    $c->sendMethod = static::SEND_METHOD_SFTP_PULL;
    $mes = static::fetchAllBy($c);
    return $mes;
  }
} 
class LabMsgInvalidEx extends Exception {}
class LabLoginInvalidEx extends Exception {}
class LabUidNotFoundEx extends LabLoginInvalidEx {}
class LabPwInvalidEx extends LabLoginInvalidEx {}
class LabInactiveEx extends LabLoginInvalidEx {}
class LabReconEx extends Exception {}
//
/**
 * SqlRec HL7Inbox
 */
class HL7Inbox extends Hl7InboxRec {
  //
  public $hl7InboxId;
  public $userGroupId;
  public $labId;
  public $msgType; 
  public $source;
  public $filename;
  public $dateReceived;
  public $patientName;
  public $cid;
  public $status;
  public $reconciledBy;
  public $data;
  public $headerTimestamp;
  public $placerOrder;
  public /*Lab*/ $Lab;
  //
  public function getJsonFilters() {
    return array(
      'dateReceived' => JsonFilter::informalDateTime());
  }
  public function getMessage() {
    if ($this->labId) {
      $this->Lab = Lab::fetch($this->labId);
      HL7_Labs::setVersion('2.3.1');
      $msg = LabMessage::fromHL7($this->data, $this->Lab);
      return $msg;
    }
  }
  public function getReconciledMessage() {
    $msg = $this->getMessage();
    if ($msg) {
      $msg->reconcile($this->cid);
      return $msg;
    }
  }
  public function saveClient($cid) {
    logit('saveClient ' . $cid);
    $this->cid = $cid;
    $this->save();
    return $this;
  }
  public function saveAsReconciled($userId) {
    if (empty($this->cid))
      throw new LabReconEx('Patient not assigned to inbox ' . $this->hl7InboxId);
    $dupe = static::fetchByOrder($this->userGroupId, $this->labId, $this->placerOrder, $this->hl7InboxId);
    logit_r($this, 'saveAsRecon');
    logit_r($dupe, 'dupe');
    if ($dupe && $dupe->cid == $this->cid) {
      logit_r('deleting dupe procs');
      Procedures::deleteForHl7Inbox($dupe->hl7InboxId, $this->userGroupId, $this->cid);
      $dupe->saveAsCorrected();
    }
    $this->reconciledBy = $userId;
    $this->status = static::STATUS_RECONCILED;
    $this->save();
    return static::fetchWithReconciler($this->hl7InboxId);
  }
  public function makeReconcileNotes() {
    $a = array("Via Lab Recon: $this->source ($this->hl7InboxId) received");
    $a[] = formatDateTime($this->dateReceived);
    if (isset($this->User_reconciledBy))
      $a[] = "saved by " . $this->User_reconciledBy->name; 
    return implode(' ', $a);
  }
  public function applyRecon($obj) {
    $client = getr($obj, 'PatientId.Client_');
    if ($client && $client->clientId != $this->cid)
      $this->saveClient($client->clientId);
    return $this;
  }
  public function saveAsCorrected() {
    $this->status = static::STATUS_CORRECTED;
    $this->save();
  }
  //
  static function fetchByOrder($ugid, $labId, $order, $notId = null) {
    $c = new static();
    $c->userGroupId = $ugid;
    $c->status = CriteriaValue::notEquals(static::STATUS_CORRECTED);
    $c->placerOrder = $order;
    if ($notId) {
      $c->hl7InboxId = CriteriaValue::notEquals($notId);
    }
    return static::fetchOneBy($c);
  }
  static function fetchWithReconciler($id) {
    $c = new static($id);
    $c->User_reconciledBy = new UserStub();
    return self::fetchOneBy($c);
  }
  static function fetchUnreconciled($ugid) {
    $c = new static();
    $c->userGroupId = $ugid;
    $c->status = static::STATUS_UNRECONCILED;
    $recs = static::fetchAllBy($c);
    return $recs;
  }
}
class Hl7Inbox_New extends Hl7InboxRec {
  //
  public $hl7InboxId;
  public $userGroupId;
  public $labId;
  public $msgType; 
  public $source;
  public $filename;
  public $dateReceived;
  public $patientName;
  public $cid;
  public $status;
  public $reconciledBy;
  public $data;
  public $headerTimestamp;
  public $placerOrder;
  public $pdf;
  //
  static function add($labId, $msg, $source, $ugid, $filename = null, $pdf) {
    $order = $msg->getOrder();
    logit_r($order, 'order');
    $me = static::from($labId, $msg, $source, $ugid, $filename, $order, $pdf);
    logit_r($me, 'me');
    if ($me) {
      $dupe = Hl7Inbox::fetchByOrder($ugid, $labId, $order);
      if ($dupe) {
        $me->cid = $dupe->cid;
        if ($dupe->status == static::STATUS_UNRECONCILED) {
          Hl7Inbox::delete($dupe);
        }
      }
      $me->save($ugid);
    }
    return $me;
  }
  static function from($labId, /*LabMessage*/$msg, /*string*/$source, $ugid, $filename = null, $placerOrder = null, $pdf = null) {
    if ($msg) {
      $head = $msg->Header;
      $patient = $msg->getPatientId();
      $me = new static();
      $me->userGroupId = $ugid;
      $me->labId = $labId;
      $me->msgType = substr($head->msgType->_data, 0, 7);
      $me->source = ($source) ? $source : 'None';
      $me->filename = $filename;
      $me->dateReceived = nowNoQuotes();
      $me->status = static::STATUS_UNRECONCILED;
      $me->placerOrder = $placerOrder;
      $me->pdf = $pdf;
      if ($patient) 
        $me->patientName = $patient->name->makeFullName();
      if (isset($patient->Client_))
        $me->cid = $patient->Client_->clientId;
      $me->data = $msg->getData();
      if (! empty($head->timestamp))
        $me->headerTimestamp = $head->timestamp->asFormatted();
      return $me;
    }
  }
}
class LabRecon extends Rec {
  //
  public /*HL7Inbox*/$Inbox;
  public /*LabMessage*/$Msg;
  //
  public function toJsonObject(&$o) {
    //logit_r($o, 'toJsonObject');
    if (isset($o->Msg))
      $o->Msg = $o->Msg->sanitize();
  }
  //
  /**
   * @param HL7Inbox $inbox
   * @param LabMessage $msg (optional) 
   * @return LabRecon
   */
  static function from($inbox, $msg = null) {
    if ($msg == null)
      $msg = $inbox->getReconciledMessage();
    $me = new static();
    $me->Inbox = $inbox;
    $me->Msg = $msg;
    return $me;
  }
}
