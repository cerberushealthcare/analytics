<?php
require_once 'php/data/hl7/msg/ORUMessage.php';
require_once 'php/data/rec/sql/Clients.php';
require_once 'php/data/rec/sql/Procedures.php';
require_once 'php/data/rec/sql/OrderEntry.php';
require_once 'php/data/rec/sql/_LabXrefRec.php';
require_once 'php/data/rec/sql/_Hl7ProcXrefRec.php';
require_once 'php/data/_BasicRec.php';
//
/**
 * ORU_Lab
 * Segments include: 
 * - associated SqlRecs for reconciliation (e.g. $Observation->Proc_)
 * - error if recon needs manual entry (e.g. $Observation->error_ = 'Test/Procedure required')
 * @author Warren Hornsby
 */
abstract class ORU_Lab extends ORUMessage {
  //
  public $PatientId = 'PID_Lab';
  public $_reconciled;
  //
  abstract public function getUgid();  // returns int
  //
  /**
   * Reconcile segments by creating new SqlRec properties (Client_, Proc_, ProcResult_, TrackItem_) 
   * @param int $cid
   * @return bool true if all valid (e.g. no manual entry required)
   */
  public function reconcile($cid = null) {  // returns bool true if all valid (e.g. no manual entry required)
    $this->_reconciled = $this->PatientId->reconcile($cid, $this->getUgid(), $this->getMap());
    return $this->_reconciled;
  }
  /**
   * Reconcile after applying UI updates 
   * @param int cid
   * @param stdClass $json ORU_Lab with UI updates (e.g. Proc_, ProcResult_, TrackItem_)
   * @return bool true if all valid (e.g. no manual entry required)
   */
  public function reconcile_afterUpdates($cid, $json) {
    $this->PatientId->apply($json, $this->getUgid());
    return $this->reconcile($cid);
  }
  /**
   * @param string $notes for order receipt
   * @param int $reconciler USER_ID or null if auto-reconciled
   * @param int $inboxId
   */
  public function saveIntoChart($notes, $reconciler, $inboxId) {
    if ($this->getClientId() == null)
      throw new HL7Exception('Cannot save message without a patient assignment');
    $this->PatientId->save($notes, $reconciler, $inboxId, $this->getMap());
  }
  /**
   * @return int
   */
  public function getClientId() {
    return getr($this, 'PatientId.Client_.clientId');
  }
  public function getPatientId() {
    return $this->get('PatientId');  
  }
  public function getOrder() {
    $pid = $this->getPatientId();
    return $pid->getOrder();
  }
  public function getObsRequests() {
    $pid = $this->getPatientId();
    return $pid->getObsRequests();
  }
  public function getDecodedPdf() {
    $pid = $this->getPatientId();
    return $pid->getDecodedPdf();
  }
  //
  protected function getMap() {
    static $map;
    if ($map == null)
      $map = LabXrefMap::byId($this->getUgid(), $this->_labId);
    return $map; 
  }
  //
  static function fromHL7(/*string*/$data, /*Lab*/$lab) {
    $buffer = HL7Buffer::fromHL7($data);
    return static::from($buffer, $lab, $data);
  }
  static function /*ORU_Lab[]*/fromFtpFile(/*FtpFile*/$file, /*Lab*/$lab) {
    $buffers = HL7Buffer::fromFtpFile($file);
    $us = array();
    foreach ($buffers as $buffer)
      $us[] = static::from($buffer, $lab, $buffer->toString());
    return $us;
  }
  //
  protected static function from($buffer, $lab, $data) {
    $header = MSH::fromBuffer($buffer);
    $me = static::fromHeader($header, $lab);
    $me->_data = $data;
    $me->_lab = $lab;
    $me->_labId = $lab->labId;
    $me->setSegments($buffer, $me->getEncoding());
    return $me;
  }
  protected static function fromHeader($header, $lab) {
    $uid = $lab->uid;
    $class = $header->msgType->type . "_$uid";
    $path = "php/data/hl7/msg/labs/$uid/$class.php";
    @include_once $path;
    if (! class_exists($class, false))
      throw new HL7ClassNotFoundEx($path);
    return new $class($header);
  }
}
class PID_Lab extends PID {
  // 
  public $CommonOrder = 'ORC_Lab';
  public $ObsRequest = 'OBR_Lab[]';
  //
  public /*Client_Recon*/ $Client_;
  public $error_;  
  //
  public function reconcile($cid, $ugid, $map) {  
    $valid = true;
    if ($cid == null) { 
      $match = $this->fetchMatch($ugid);
      if ($match)
        $cid = $match->clientId;
    }
    if ($cid) {
      if (! isset($this->_applied)) { 
        $provider = $this->getOrderingProvider();
        $name = null;
        if ($provider) {
          $a = explode(' ', $provider->familyName);
          $name = $a[0];
          if (strlen($name) < 3) 
            $name = null;
        }
        $this->Client_ = Client_Recon::fetch($cid, $name);
      }
      $valid = OBR_Lab::reconcileAll($this->getObsRequests(), $this->Client_, $map) && $valid;
    } else {
      $this->error_ = 'Patient required';
      $valid = false;
    }
    return $valid;
  }
  public function /*XCN*/getOrderingProvider() {
    $obrs = $this->getObsRequests();
    if ($obrs) {
      $obr = reset($obrs);
      logit_r($obr, 'obr');
      return $obr->orderProvider;
    }
  }
  public function /*OBR[]*/getObsRequests() {
    return arrayify($this->ObsRequest);
  }
  public function getOrder() {
    if (is_array($this->CommonOrder)) {
      return reset($this->CommonOrder)->getOrder();      
    } else if ($this->CommonOrder) {
      return $this->CommonOrder->getOrder();
    }
  }
  public function apply($json, $ugid) {
    $j_obrs = arrayify($json->PatientId->ObsRequest);
    $this->Client_ = $json->PatientId->Client_;
    $this->_applied = true;
    OBR_Lab::applyAll($this->getObsRequests(), $j_obrs, $ugid);
  }
  public function save($notes, $reconciler, $inboxId, $map) {
    $procs = OBR_Lab::saveAll($this->getObsRequests(), $notes, $reconciler, $inboxId, $map);
    $sendTo = $this->Client_->reviewer_;
    if ($sendTo) 
      Messaging_DocStubReview::createThreads_fromProcs($procs, $sendTo);
  }
  public function getDecodedPdf() {
    $obr = end($this->getObsRequests());
    if ($obr) {
      return $obr->getDecodedPdf();
    }
  }
  //
  protected function /*PatientStub*/fetchMatch($ugid) {
    if (empty($this->birthDate) || empty($this->gender)) 
      return null;
    $last = $this->name->last;
    $first = $this->name->first;
    $dob = $this->birthDate->asSqlDate();
    $sex = $this->gender;
    $client = PStub_Search::searchForExact($ugid, $last, $first, $dob, $sex);
    return $client;
  }
}
//
class ORC_Lab extends ORC {
  //
}
//
class OBR_Lab extends OBR {
  //
  public $Observation = 'OBX_Lab[]';
  //
  public /*Proc_Recon*/ $Proc_;
  public /*TrackItem_Recon*/ $TrackItem_;
  public $error_;  
  //
  public function isPdf() {
    $obx = current($this->getObservations());
    return $obx->isPdf();
  }
  public function getDecodedPdf() {
    $obx = current($this->getObservations());
    return $obx->getDecodedPdf();
  }
  public function reconcile($client, $map) {
    $valid = true;
    logit_r($this, 'reconcile222');
    if (! isset($this->_applied)) {
      $this->Proc_ = $this->asProc($client, $map);
      logit_r($this->Proc_, 'Proc_');
      if ($this->get('Proc_.ipc')) 
        $this->TrackItem_ = TrackItem_Recon::find($client->TrackItems, $this->Proc_->ipc);
    }
    if (! $this->isPdf() && (empty($this->Proc_) || $this->Proc_->ipc == null)) {
      logit_r('222 a');
      $this->error_ = 'Test/Procedure required';
      $valid = false;
    } 
    $valid = OBX_Lab::reconcileAll($this->getObservations(), $this->Proc_, $map) && $valid;
    return $valid;
  }
  public function apply($j_obr, $ugid) {
    $this->Proc_ = Proc_Recon::revive(get($j_obr, 'Proc_'), $ugid);
    $this->TrackItem_ = TrackItem_Recon::revive(get($j_obr, 'TrackItem_'));
    $this->_applied = true;
    $j_obxs = arrayify($j_obr->Observation);
    OBX_Lab::applyAll($this->getObservations(), $j_obxs, $ugid);  
  } 
  public function save($notes, $reconciler, $inboxId, $map) {
    if ($this->TrackItem_) 
      $this->TrackItem_->saveAsReceived($notes, $reconciler);
    if ($this->Proc_) {
      $this->Proc_->hl7InboxId = $inboxId;
      $this->Proc_->save();
      $map->save_fromObr($this, $this->Proc_->ipc);
      OBX_Lab::saveAll($this->getObservations(), $this->Proc_, $map);
      $orderNo = $this->getProcXrefOrder();
      if ($orderNo) 
        Hl7ProcXref::create($this->Proc_->clientId, $map->labId, $inboxId, $orderNo, $this->Proc_->ipc, $this->Proc_);
      return $this->Proc_;
    } else {
      throw new HL7Exception('Pending procedure missing from OBR segment');
    }
  }
  //
  protected function asProc($client, $map) {
    if (! $this->isPdf())
      return Proc_Recon::from($this, $client, $map);
  }
  protected function /*string*/getProcXrefOrder() {
    // override to provide order number to associate with generated proc, to allow a subsequent msg to supercede (e.g. correction)
  }
  //
  static function reconcileAll(/*OBR[]*/$recs, $client, $map) {  // returns true if no errors
    $valid = true;
    foreach ($recs as &$rec) {
      logit_r('reconcile obr, before valid=' . $valid);
      $valid = $rec->reconcile($client, $map) && $valid;
      logit_r('reconcile obr, after valid=' . $valid);
    }
    return $valid;
  }
  static function applyAll($recs, $j_obrs, $ugid) {
    reset($j_obrs);
    foreach ($recs as &$rec) {
      $rec->apply(current($j_obrs), $ugid);
      next($j_obrs);
    }
  }
  static function saveAll($recs, $notes, $reconciler, $inboxId, $map) {
    $procs = array();
    foreach ($recs as &$rec) {
      if (! $rec->isPdf())
        $procs[] = $rec->save($notes, $reconciler, $inboxId, $map);
    }
    return $procs;
  }
}
//
class OBX_Lab extends OBX {
  //
  public /*ProcResult_Recon*/ $ProcResult_;
  public $error_;
  public $pdf_;  
  //
  public function isPdf() {
    if ($this->get('valueType._value') == 'ED' && $this->value->subtype == 'PDF')
      return true;
  }
  public function getDecodedPdf() {
    if ($this->isPdf()) {
      return base64_decode($this->value->data);
    }
  }
  public function reconcile($proc, $index, $map) {
    logit_r('reconcile 123');
    $valid = true;
    if ($this->isPdf()) {
      logit_r('123 a');
      $this->pdf_ = true;
    } else {
      logit_r('123 b');
      if (! isset($this->_applied))
        $this->ProcResult_ = $this->asProcResult($proc, $index, $map);
      if (empty($this->ProcResult_) || $this->ProcResult_->ipc == null) {
        $this->error_ = 'Test/Procedure required';
        $valid = false;
      }
    }
    logit_r($this, '123 c');
    return $valid;
  }
  public function apply($j_obx, $ugid) {
    $this->ProcResult_ = ProcResult_Recon::revive(get($j_obx, 'ProcResult_'), $ugid);
    $this->_applied = true;
  }
  public function save($proc, $map) {
    if ($this->ProcResult_) {
      $this->ProcResult_->save($proc);
      $map->save_fromObx($this, $this->ProcResult_->ipc);
    } else {
      throw new HL7Exception('Pending procedure result missing from OBX segment');
    }
  }
  //
  protected function asProcResult($proc, $index, $map) {
    return ProcResult_Recon::from($this, $proc, $index, $map);
  }
  // 
  static function reconcileAll($recs, $proc, $map) {
    $valid = true;
    $recs = arrayify($recs);
    foreach ($recs as $index => &$rec) {
      if ($rec) 
        $valid = $rec->reconcile($proc, $index, $map) && $valid;
    }
    return $valid;
  }
  static function applyAll($recs, $j_obxs, $ugid) {
    reset($j_obxs);
    foreach ($recs as &$rec) {
      $rec->apply(current($j_obxs), $ugid);
      next($j_obxs);
    }
  }
  static function saveAll($recs, $proc, $map) {
    foreach ($recs as &$rec) 
      $rec->save($proc, $map);
  }
}
//
class LabXrefMap extends BasicRec {
  //
  public $ugid;
  public $labId;
  public /*LabXref[]*/ $xrefs;
  //
  public function get_fromObr($obr) {
    $fid = $this->getKeyFid();
    return $this->get($obr->serviceId->$fid);
  }
  public function get_fromObx($obs) {
    $fid = $this->getKeyFid();
    return $this->get($obs->obsId->$fid);
  }
  public function get($key) {  // return ipc
    $rec = geta($this->xrefs, $key);
    if ($rec)
      return $rec->toId;
  }
  //
  public function save_fromObr($obr, $ipc) {
    $fid = $this->getKeyFid();
    $this->save($obr->serviceId->id, $obr->serviceId->text, $obr->serviceId->$fid, $ipc);
  }
  public function save_fromObx($obx, $ipc) {
    $fid = $this->getKeyFid();
    if (! empty($obx->obsId->id)) {
      $this->save($obx->obsId->id, $obx->obsId->text, $obx->obsId->$fid, $ipc);
    }
  }
  public function save($id, $text, $key, $ipc) {   
    $rec = geta($this->xrefs, $key);
    if ($rec && $rec->toId == $ipc)  // no need to save if already there
      return;
    if ($rec == null)
      $rec = LabXref::asNew($this->ugid, $this->labId, $id, $text, $ipc);
    else  
      $rec->toId = $ipc;
    $rec->save();
    $this->xrefs[$key] = $rec;
  }
  //
  protected function getKeyFid() {
    return ($this->_byId) ? 'id' : 'text';
  }
  //
  static function byId($ugid, $labId) {
    $me = new static($ugid, $labId);
    $me->_byId = true;
    $me->xrefs = LabXref::fetchMap_byId($ugid, $labId);
    return $me;
  }
  static function byText($ugid, $labId) {
    $me = new static($ugid, $labId);
    $me->_byId = false;
    $me->xrefs = LabXref::fetchMap_byText($ugid, $labId);
    return $me;
  }
}
/*
 * SqlRecs
 */
class LabXref extends LabXrefRec {
  //
  public $labId;
  public $type;
  public $fromId;
  public $userGroupId;
  public $fromText;
  public $toId;  // ipc
  //
  static function asNew($ugid, $labId, $fromId, $fromText, $toId) {
    $me = new static();
    $me->labId = $labId;
    $me->type = static::TYPE_PROC;
    $me->fromId = $fromId;
    $me->userGroupId = $ugid;
    $me->fromText = $fromText;
    $me->toId = $toId;
    return $me;
  }
  static function fetchMap_byId($ugid, $labId) {
    return static::fetchAll($ugid, $labId, 'fromId');
  }
  static function fetchMap_byText($ugid, $labId) {
    return static::fetchAll($ugid, $labId, 'fromText');
  }
  protected static function fetchAll($ugid, $labId, $keyFid = null) {
    $c = new static();
    $c->labId = $labId;
    $c->type = static::TYPE_PROC;
    $c->setUserGroupCriteria($ugid);
    $recs = static::fetchTopLevelsBy($c);
    logit_r($recs, 'fetched top levels');
    if ($keyFid)
      $recs = array_keyify($recs, $keyFid);
    return $recs;
  }
}
class Client_Recon extends Client {
  //
  static function fetch($cid, $oruProvider = null/*last name of ordering provider*/) {
    logit_r($oruProvider, 'oru123');
    $me = parent::fetch($cid);
    $me->Address_Home = ClientAddress::fetchHome($cid);
    $ugid = $me->userGroupId;
    $me->TrackItems = TrackItem_Recon::fetchAllOpen($ugid, $cid);
    if ($oruProvider) { /*default reviewer to ORU provider, if possible*/
      $docs = UserGroups::getDocs();
      foreach ($docs as $doc) {
        if (strpos($doc->name, $oruProvider) !== false) {
          $me->reviewer_ = $doc->userId;
        }
      }
    }
    if (get($me, 'reviewer_') == null) {
      if ($me->primaryPhys) 
        $me->reviewer_ = $me->primaryPhys;
      else 
        $me->reviewer_ = UserGroups::getFirstDoc()->userId;  // TODO
    }
    return $me;
  }
}
class TrackItem_Recon extends TrackItem {
  //
  public function saveAsReceived($notes, $closedBy) {
    if ($this->trackItemId) {
      $rec = static::fetch($this->trackItemId);
      if ($rec) {
        $rec->status = self::STATUS_CLOSED;
        $rec->closedFor = self::CLOSED_FOR_RECEIVED;
        $rec->closedBy = $closedBy;
        $rec->closedDate = nowNoQuotes();
        $rec->closedNotes = $notes;
        $rec->save();
      }
    }
  } 
  //
  /**
   * @param TrackItem[] $recs
   * @param int $ipc
   * @return TrackItem if IPC found (and not already found on a prior call)
   */
  static function find(&$recs, $ipc) {
    if (! empty($recs) && $ipc) {
      foreach ($recs as &$rec) {
        if ($rec->cptCode == $ipc && ! isset($rec->_found)) {
          $rec->_found = true;
          return $rec;
        }
      }
    }
  }
  static function fetchAllOpen($ugid, $cid) {
    $c = new static();
    $c->userGroupId = $ugid;
    $c->clientId = $cid;
    $c->status = CriteriaValue::lessThanNumeric(self::STATUS_CLOSED);
    $recs = self::fetchAllBy($c);
    return $recs;
  }
  static function fetch($id) {
    $c = new static($id);
    return SqlRec::fetchOneBy($c);
  }
  static function revive($json) {
    if (! empty($json)) {
      return new static($json);
    }
  }
}
class Proc_Recon extends Proc {
  //
  static function from($obr, $client, $map) {
    $me = new static();
    $me->Ipc = Ipc_Recon::fromObr($obr, $client->userGroupId, $map);
    $me->ipc = get($me->Ipc, 'ipc');
    $me->userGroupId = $client->userGroupId;
    $me->clientId = $client->clientId;
    $me->date = $obr->obsDateTime->asSqlValue();
    $me->comments = self::makeComments($obr);
    return $me;
  }
  static function revive($json, $ugid) {
    if (! empty($json)) {
      $me = new static($json);
      if ($me->ipc) 
        $me->Ipc = Ipc::fetchTopLevel($me->ipc, $ugid);
      return $me;
    }
  }
  protected static function makeComments($obr) {
    return implode('<br>', $obr->getComments());
  }
}
class ProcResult_Recon extends ProcResult {
  //
  function save($proc) {
    $this->procId = $proc->procId;
    parent::save();
  }
  //
  static function from($obx, $proc, $index, $map) {
    $me = new static();
    $me->clientId = $proc->clientId;
    $me->seq = $index;
    //$me->date = $proc->date;
    $me->Ipc = Ipc_Recon::fromObx($obx, $proc->userGroupId, $map);
    $me->ipc = get($me->Ipc, 'ipc');
    $me->value = $obx->get('value');
    $me->valueUnit = $obx->get('units.id');
    $me->range = $obx->get('range');
    $me->interpretCode = self::makeInterpretCode($obx);
    $me->comments = self::makeComments($obx);
    return $me;
  }
  static function revive($json, $ugid) {
    if (! empty($json)) {
      $me = new static($json);
      if ($me->ipc) 
        $me->Ipc = Ipc::fetchTopLevel($me->ipc, $ugid);
      return $me;
    }
  }
  //
  protected static function makeInterpretCode($obx) {
    return $obx->get('abnormal');
  }
  protected static function makeComments($obx) {
    return implode('<br>', $obx->getComments());
  }
}
class Ipc_Recon extends Ipc {
  //
  static function fromObr($obr, $ugid, $map) {
    return static::from($map->get_fromObr($obr), $ugid, $obr->serviceId->text);
  } 
  static function fromObx($obx, $ugid, $map) {
    return static::from($map->get_fromObx($obx), $ugid, $obx->obsId->text);
  } 
  static function from($ipc, $ugid, $name) {
    logit_r('Ipc_Recon::from(' . $ipc . ',' . $ugid . ',' . $name . ')');
    if ($ipc)
      return static::fetchTopLevel($ipc, $ugid);
    else if ($name)
      return static::fetchByName($ugid, $name);
  }
}
class Hl7ProcXref extends Hl7ProcXrefRec implements NoAudit {
  //
  public $hpxId;
  public $clientId;
  public $labId;
  public $hl7InboxId; 
  public $orderNo;
  public $ipc;
  public $procId;
  public $supercededBy;
  public /*Proc_Xref*/$Proc;
  //
  static function /*Hl7ProcXref*/create($cid, $labId, $inboxId, $orderNo, $ipc, $proc) {
    $us = static::fetchAll($cid, $labId, $inboxId, $orderNo, $ipc);
    static::saveAllAsSuperceded($us, $proc);
    $me = new static(null, $cid, $labId, $inboxId, $orderNo, $ipc, $proc->procId, null);
    $me->save();
    return $me;
  }
  protected static function fetchAll($cid, $labId, $inboxId, $orderNo, $ipc) {
    $c = new static();
    $c->clientId = $cid;
    $c->labId = $labId;
    $c->hl7InboxId = CriteriaValue::lessThanNumeric($inboxId);
    $c->orderNo = $orderNo;
    $c->ipc = $ipc;
    $c->Proc = new Proc_Xref();
    return static::fetchAllBy($c);
  } 
  protected static function saveAllAsSuperceded($us, $proc) {
    foreach ($us as $me) {
      $me->supercededBy = $proc->procId;
      $me->save();
      $me->Proc->saveSupercededComment($proc);
    }
  }
}
class Proc_Xref extends Proc implements NoAudit {
  //
  public function saveSupercededComment($proc) {
    $now = nowNoQuotes();
    $c = "<B>NOTE: THIS RESULT WAS SUPERCEDED BY ANOTHER RESULT ON $now.</B>";
    if (! empty($this->comments))
      $this->comments = $c . '<br><br>' . $this->comments;
    else
      $this->comments = $c;
    return $this->save();
  }
}
