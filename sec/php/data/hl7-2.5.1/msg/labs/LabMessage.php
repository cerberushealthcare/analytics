<?php
require_once 'LabMessage_Sql.php';
require_once 'php/data/hl7-2.5.1/msg/ORUMessage.php';
require_once 'php/data/_BasicRec.php';
//
/**
 * LabMessage v.2.5.1
 * Segments include: 
 * - associated SqlRecs for reconciliation (e.g. $Observation->Proc_)
 * - error if recon needs manual entry (e.g. $Observation->error_ = 'Test/Procedure required')
 * @author Warren Hornsby
 */
abstract class LabMessage extends ORUMessage {
  //
  public $Software = 'SFT';
  public $PatientId = 'PID_Lab';
  //
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
   * @param stdClass $json LabMessage with UI updates (e.g. Proc_, ProcResult_, TrackItem_)
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
  static function /*LabMessage[]*/fromFtpFile(/*FtpFile*/$file, /*Lab*/$lab) {
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
    $class = "ORU_$uid";
    $path = "php/data/hl7-2.5.1/msg/labs/$uid/$class.php";
    @include_once $path;
    if (! class_exists($class, false))
      throw new HL7ClassNotFoundEx($path);
    return new $class($header);
  }
}
/**
 * Segments 
 */
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
    logit_r($cid, 'reconcile');
    if ($cid == null) { 
      $match = $this->fetchMatch($ugid);
      if ($match)
        $cid = $match->clientId;
    }
    if ($cid) {
      if (! isset($this->_applied))  
        $this->Client_ = Client_Recon::fetch($cid);
      $valid = OBR_Lab::reconcileAll($this->getObsRequests(), $this->Client_, $map) && $valid;
    } else {
      $this->error_ = 'Patient required';
      $valid = false;
    }
    return $valid;
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
  //
  protected function /*PatientStub*/fetchMatch($ugid) {
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
  public $Comment = 'NTE[]';
  public $TimingQty = 'TQ1';
  public $Observation = 'OBX_Lab[]';
  public $Specimen = 'SPM';
  //
  public /*Proc_Recon*/ $Proc_;
  public /*TrackItem_Recon*/ $TrackItem_;
  public $error_;  
  //
  public function reconcile($client, $map) {
    $this->error_ = null;
    $valid = true;
    if (! isset($this->_applied)) {
      $this->Proc_ = $this->asProc($client, $map);
      if ($this->Proc_->ipc) 
        $this->TrackItem_ = TrackItem_Recon::find($client->TrackItems, $this->Proc_->ipc);
    }
    if (empty($this->Proc_) || $this->Proc_->ipc == null) {
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
    return Proc_Recon::from($this, $client, $map);
  }
  protected function /*string*/getProcXrefOrder() {
    // override to provide order number to associate with generated proc, to allow a subsequent msg to supercede (e.g. correction)
  }
  //
  static function reconcileAll(/*OBR[]*/$recs, $client, $map) {  // returns true if no errors
    $valid = true;
    foreach ($recs as &$rec)
      $valid = $rec->reconcile($client, $map) && $valid;
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
    foreach ($recs as &$rec)
      $procs[] = $rec->save($notes, $reconciler, $inboxId, $map);
    return $procs;
  }
}
//
class OBX_Lab extends OBX {
  //
  public $Comment = 'NTE[]';
  public /*ProcResult_Recon*/ $ProcResult_;
  public $error_;  
  //
  public function reconcile($proc, $index, $map) {
    $this->error_ = null;
    $valid = true;
    if (! isset($this->_applied))
      $this->ProcResult_ = $this->asProcResult($proc, $index, $map);
    if (empty($this->ProcResult_) || $this->ProcResult_->ipc == null) {
      $this->error_ = 'Test/Procedure required';
      $valid = false;
    }
    return $valid;
  }
  public function sanitize() {
    $encoding = ST_EncodingChars::asStandard();
    $this->value = $encoding->unencode($this->value);
    return parent::sanitize();
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
    $this->save($obx->obsId->id, $obx->obsId->text, $obx->obsId->$fid, $ipc);
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
