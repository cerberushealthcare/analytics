<?php
require_once 'php/data/rec/_Rec.php';
//
/**
 * Documentation DAO
 * @author Warren Hornsby
 */
class Documentation {
  //
  /**
   * @param int $cid
   * @return array(DocStub,..)
   */
  static function getAll($cid) {
    global $login;
    $recs = DocStub::fetchAllTypes($cid, $login->userId);
    return Rec::sort($recs, new RecSort('-date', '-timestamp', '_sort'));
  }
  /**
   * @return array(DocStub,..)
   */
  static function getUnreviewed() {
    global $login;
    $recs = DocStub::fetchAllUnreviewed($login->userGroupId, $login->userId);
    return Rec::sort($recs, new RecSort('Unreviewed.Client.lastName', 'cid'));
  }
  /**
   * @param DocStub rec
   * @return DocStub
   */
  static function refetch($rec) {
    $rec = DocStub::fetch_byStub($rec);
    return $rec;
  }
  /**
   * @param DocStub rec e.g. {'type':1,'id':3200}
   * @return DocStub_Preview
   */
  static function preview($rec) {
    global $login;
    $rec = DocStub_Preview::fetch($rec, $login->userId);
    return $rec;
  }
  /**
   * @param int threadId
   * @return DocStub
   */
  static function setReviewed($threadId) {
    $thread = Messaging_DocStubReview::postReviewed($threadId);
    return static::preview($thread->Stub);
  }
  /** Forward to another for review
   */ 
  static function /*DocStub_Preview*/forward($stubType, $stubId, $userId) {
    $thread = Messaging_DocStubReview::forward($stubType, $stubId, $userId);
    return static::preview($thread->Stub);
  }
}
class DocStub extends Rec {
  //
  public $type;
  public $id;
  public $cid;
  public $date;
  public $timestamp;
  public $name;
  public $desc;
  public $signed;  // '04-May-2012 3:55PM by Dr. Clicktate'
  public $provider;
  public $facility;
  public $areas;
  public /*MsgThread_Stub*/ $Unreviewed;
  public $_sort;
  //
  const TYPE_SESSION = 1;
  const TYPE_MSG = 2;
  const TYPE_APPT = 3;
  const TYPE_ORDER = 4;
  const TYPE_SCAN = 5;
  const TYPE_SCAN_XML = 7;
  const TYPE_RESULT = 6;
  const TYPE_VISITSUM = 8;
  const TYPE_CLIENTDOC = 10;
  static $TYPES = array(
    self::TYPE_SESSION => 'Document',
    self::TYPE_MSG => 'Message',
    self::TYPE_APPT => 'Appointment',
    self::TYPE_ORDER => 'Orders',
    self::TYPE_SCAN => 'Scan',
    self::TYPE_SCAN_XML => 'Electronic',
    self::TYPE_RESULT => 'Proc/Result',
    self::TYPE_VISITSUM => 'Visit Summary',
    self::TYPE_CLIENTDOC => 'Patient Document');
  //
  public function getJsonFilters() {
    return array(
      'date' => JsonFilter::editableDateApprox());
  }
  public function toJsonObject(&$o) {
    $o->lookup('type', static::$TYPES);
  }
  public function lookupType() {
    return static::$TYPES[$this->type];
  }
  public function setDate($date) {
    $this->date = $date;
  }
  public function setSigned($date, $by) {
    $this->signed = formatDateTime($date) . ' by ' . UserGroups::lookupUser($by);
  }
  public function setKey($type, $id) {
    $this->type = $type;
    $this->id = $id;
    $this->_sort = "$type.$id"; 
  }
  //
  static function from($rec) {
    if ($rec) {
      $me = new static();
      $me->setKey($rec::getStubType(), $rec->getAuditRecId());
      $rec->loadStub($me);
      $me->cid = get($rec, 'clientId');
      if ($me->timestamp == null)
        $me->timestamp = get($rec, 'dateCreated') ?: $me->date;
      if (get($rec, 'Provider')) 
        $me->provider = $rec->Provider->formatName();
      if (get($rec, 'Facility'))
        $me->facility = $rec->Facility->name;
      $thread = getr($rec, 'Unreviewed');
      if ($thread && $thread->Inbox)
        $me->Unreviewed = $thread; 
      if (getr($rec, 'Ipc.codeLoinc')) {
        $me->_loinc = $rec->Ipc->codeLoinc;
        //logit_r($me, 'wgh3');
      }        
      return $me;
    }
  }
  static function fromRecs($recs) {
    $mes = array();
    foreach ($recs as $rec)
      $mes[] = static::from($rec);
    return $mes;
  }
  static function fetch($type, $id) {
    if ($type && $id) {
      $rec = static::fetchRec($type, $id);
      return static::from($rec);
    }
  }
  protected static function fetchRec($type, $id) {
    $class = static::getRecClass($type);
    $c = static::getCriteria($class);
    $c->setPkValue($id);
    logit_r($c, 'doc fetchrec');
    logit_r($class, 'clazz');
    $rec = $class::fetchOneBy($c);
    return $rec;
  }
  static function fetch_byStub($stub) {
    $rec = static::fetch($stub->type, $stub->id);
    return $rec;
  }
  static function fetchAllTypes($cid, $userId) {
    return array_merge(
      static::fetchAllFor(static::TYPE_SESSION, $cid, $userId),
      static::fetchAllFor(static::TYPE_MSG, $cid, $userId),
      static::fetchAllFor(static::TYPE_APPT, $cid, $userId),
      DocStub_Order::fetchAll($cid),
      static::fetchAllFor(static::TYPE_SCAN, $cid, $userId),
      static::fetchAllFor(static::TYPE_SCAN_XML, $cid, $userId),
      static::fetchAllFor(static::TYPE_RESULT, $cid, $userId),
      static::fetchAllFor(static::TYPE_VISITSUM, $cid, $userId),
      static::fetchAllFor(static::TYPE_CLIENTDOC, $cid, $userId));
  }
  protected static function fetchAllFor($stubType, $cid, $userId) {
    $class = static::getRecClass($stubType);
    $c = $class::asStubCriteria($cid);
    if ($c == null)
      return array();
    if ($c instanceof PreviewRec_Reviewable)
      $c->Unreviewed = MsgThread_Stub::asJoin_optionalUnreviewed($userId, $stubType);
    $recs = $class::fetchAllBy($c, null, 2000);
    return static::fromRecs($recs);
  }
  static function fetchAllUnreviewed($ugid, $userId) {
    return array_merge(
      static::fetchUnreviewedFor(static::TYPE_SESSION, $ugid, $userId),
      static::fetchUnreviewedFor(static::TYPE_SCAN, $ugid, $userId),
      static::fetchUnreviewedFor(static::TYPE_RESULT, $ugid, $userId));
  }
  protected static function fetchUnreviewedFor($stubType, $ugid, $userId) {
    $class = static::getRecClass($stubType);
    $c = $class::asStubCriteria();
    $c->userGroupId = $ugid;
    $c->Unreviewed = MsgThread_Stub::asJoin_requiresUnreviewed($userId, $stubType);
    $recs = $class::fetchAllBy($c);
    return static::fromRecs($recs);
  }
  protected static function getCriteria($class) { 
    return $class::asStubCriteria();
  }
  protected static function getRecClass($type) {
    switch ($type) {
      case static::TYPE_SESSION:
        return 'DocSession';
      case static::TYPE_MSG:
        return 'DocMessage';
      case static::TYPE_APPT: 
        return 'DocAppt';
      case static::TYPE_ORDER:
        return 'DocOrder';
      case static::TYPE_SCAN:
        return 'DocScan';
      case static::TYPE_SCAN_XML:
        return 'DocScanXml';
      case static::TYPE_RESULT:
        return 'DocProc'; 
      case static::TYPE_VISITSUM:
        return 'DocVisitSum'; 
      case static::TYPE_CLIENTDOC:
        return 'DocClientDoc';
    }
  }
  static function getNextAppt($stubs) {
    $appt = null;
    if ($stubs)
      foreach ($stubs as $stub) {
        if ($stub->type == static::TYPE_APPT) {
          if (isTodayOrFuture($stub->date))
            $appt = $stub;
        }
      }
    return $appt;
  }
}
class DocStub_Preview extends DocStub {  
  //
  static function from($rec, $userId) {
    $me = parent::from($rec);
    $rec->_html = $rec::getHtmlBody($rec);
    $me->Preview = $rec;
    if ($me->cid)
      $me->Client = ClientStub::fetch($me->cid);
    if ($rec instanceof PreviewRec_Reviewable && $userId)
      $me->ReviewThread = MsgThread_Stub::fetchByStub($me, $rec->userGroupId, $userId);
    return $me;
  }
  static function fetch($stub, $userId = null) {
    $rec = static::fetchRec($stub->type, $stub->id);
    if ($rec)
      return static::from($rec, $userId);
  }
  //
  protected static function getCriteria($class) { 
    return $class::asPreviewCriteria();
  }
}
//
require_once 'php/data/rec/sql/Documentation_Recs.php';
