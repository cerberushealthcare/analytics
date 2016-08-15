<?php
require_once 'php/data/LoginSession.php';
require_once 'php/data/hl7/msg/_HL7Message.php';
require_once 'php/data/hl7/msg/labs/ORU_Lab.php';
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
    global $login;
    $folder = GroupFolder_Labs::open();
    $file = $folder->upload();
    $msg = HL7Message::fromHL7($file->readContents());
    if ($msg)
      return self::import($msg, $login->userGroupId);
  }
  /** Import from single SFTP file */
  static function import_fromFtpFile(/*FtpFile*/$file) {
    $msgs = ORU_Lab::fromFtpFile($file, $file->Lab);
    blog($msgs, 'msgs');
    foreach ($msgs as $msg)
      static::import_fromFtpMsg($file, $msg);
  }
  /** Import from web service */
  static function import_fromWebService(/*LabServicesPost*/$post) {
    $lab = Lab::fetch_byWebService($post);
    $msg = ORU_Lab::fromHL7($post->msg, $lab);
    if ($msg) {
      $ugid = $msg->getUgid();
      $login = LoginSession::loginBatch($ugid, __CLASS__);
      self::import($lab, $msg, $ugid);
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
  protected static function import(/*Lab*/$lab, /*ORU_Lab*/$msg, $ugid, $filename = null) {
    $valid = $msg->reconcile();
    $inbox = HL7Inbox::add($lab->labId, $msg, $msg->Header->getSource(), $ugid, $filename);
    /*
    if ($valid) {
      $inbox->cid = $msg->getClientId();
      static::saveIntoChart($msg, $inbox);
    } 
    */
  }
  /** Save reconciliation, return LabRecon if still errors */
  static function /*LabRecon*/saveLabRecon($inboxId, /*ORU_Lab with updates, e.g. Proc_, ProcResult_, TrackItem_*/$obj) {
    global $login;
    $inbox = HL7Inbox::fetch($inboxId)->applyRecon($obj);
    $msg = $inbox->getMessage();
    $valid = $msg->reconcile_afterUpdates($inbox->cid, $obj);
    if (! $valid) 
      return LabRecon::from($inbox, $msg);
    else 
      static::saveIntoChart($msg, $inbox, $login->userId);
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
    $c->sendMethod = CriteriaValue::in(array(static::SEND_METHOD_SFTP, static::SEND_METHOD_SFTP_PULL));
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
  public /*Lab*/ $Lab;
  //
  public function getJsonFilters() {
    return array(
      'dateReceived' => JsonFilter::informalDateTime());
  }
  public function getMessage() {
    if ($this->labId) {
      $this->Lab = Lab::fetch($this->labId);
      $msg = ORU_Lab::fromHL7($this->data, $this->Lab);
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
  //
  static function add($labId, $msg, $source, $ugid, $filename = null) {
    $rec = self::from($labId, $msg, $source, $ugid, $filename);
    if ($rec) 
      $rec->save($ugid);
    return $rec;
  }
  static function from($labId, $msg, $source, $ugid, $filename = null) {
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
class LabRecon extends Rec {
  //
  public /*HL7Inbox*/ $Inbox;
  public /*ORU_Lab*/ $Msg;
  //
  public function toJsonObject(&$o) {
    logit_r($o, 'toJsonObject');
    if (isset($o->Msg))
      $o->Msg = $o->Msg->sanitize();
  }
  //
  /**
   * @param HL7Inbox $inbox
   * @param ORU_Lab $msg (optional) 
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
