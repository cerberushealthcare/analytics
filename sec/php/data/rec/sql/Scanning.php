<?php
require_once 'php/data/bat/Bat.php';
require_once 'php/data/rec/group-folder/GroupFolder_Scanning.php';
require_once 'php/data/rec/sql/Clients.php';
require_once 'php/data/rec/sql/Providers.php';
require_once 'php/data/rec/sql/Procedures_Admin.php';
require_once 'php/data/rec/sql/IProcCodes.php';
//
/**
 * Scanning DAO
 * @author Warren Hornsby
 */
class Scanning {
  //
  /**
   * @throws UploadFileException
   */
  static function upload($uploads = null) {
    global $login;
    $ugid = $login->userGroupId;
    if ($uploads == null)
      $uploads = GroupUpload_Scanning::asUploads();
    $fileIndex = ScanFile::nextFileIndex($ugid);
    $folder = GroupFolder_Scanning::open($ugid);
    $uploads = $folder->uploadAll($uploads, $fileIndex);
    ScanFile::saveUploads($uploads, $ugid);
  }
  /**
   * @return string filename 'file.pdf'
   */
  static function uploadBatch() {
    $folder = GroupFolder_Batch::open();
    $file = $folder->upload();
    return $file->filename;
  }
  /**
   * @param string filename
   */
  static function splitBatch($filename) {
    $folder = GroupFolder_Batch::open();
    $file = GroupFile::from($folder, $filename);
    $bat = Bat_Split::run($file);
    $uploads = GroupUpload_Split::getAllFor($file);
    logit_r($uploads, 'uploads');
    sleep(1);  // http://www.php.net/manual/en/function.rename.php#102274
    static::upload($uploads);
    $folder->delete($file);
  }
  static function saveClinicalXml($cid, $filename) {
    global $login;
    $index = ScanIndex_Xml::from($filename, $cid);
	
	if ($_POST['IS_BATCH']) {
		$groupId = $_POST['userGroupId'];
	}
	else {
		$groupId = $login->userGroupId;
	}
	
    $index->save($groupId);
  }
  /**
   * @deprecated
   */
  static function uploadXml() {
    require_once 'php/newcrop/data/_DomData.php';
    global $login;
    $ugid = $login->userGroupId;
    $upload = GroupUpload_ScanningXml::asUpload();
    $fileIndex = ScanFile::nextFileIndex($ugid);
    $folder = GroupFolder_Scanning::open($ugid);
    $upload = $folder->upload($upload, $fileIndex);
    $file = GroupFile::from($folder, $upload->newName);
    $password = geta($_POST, 'pw');
    $contents = $file->readContents($password);
    $file->save($contents);
    $xml = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $contents);
    try {
      $dom = @DomData::parse($xml);
      ScanFile::saveUploads(array($upload), $ugid);
    } catch (DomParseException $e) {
      throw new GroupUploadException(null, 'The file could not be read as XML data.');
    }
  }
  /**
   * @return array(ScanFile,..)
   */
  static function getUnindexedFiles($userId) {
    global $login;
    $recs = ScanFile::fetchAllUnindexed($login->userGroupId, $userId);
    return Rec::sort($recs, new RecSort('-_date', 'scanFileId'));
  }
  /**
   * @return array(ScanIndex+ClientStub,..)
   */
  static function getIndexedToday() {
    global $login;
    $recs = ScanIndex::fetchAllByDate($login->userGroupId, nowShortNoQuotes());
    return Rec::sort($recs, new RecSort('-dateUpdated'));
  }
  /**
   * @param int $sfid
   * @return ScanFile
   */
  static function getFile($sfid) {
    return ScanFile::fetch($sfid);
  }
  /**
   * @param int $sfid
   * @return ScanFile
   */
  static function rotate($sfid) {
    $file = static::getFile($sfid);
    $file->rotate();
    $file->save();
    return $file;
  }
  /**
   * @param stdClass $oScanIndex criteria
   * @return array(ScanIndex,..)
   */
  static function getAllIndexesBy($oScanIndex) {
    $scanIndex = new ScanIndex($oScanIndex);
    $recs = ScanIndex::fetchAllBy($scanIndex);
    return $recs;
  }
  /**
   * @param stdClass $oScanIndex 
   * @param int[] $sfids ScanFile id array to index
   * @return ScanIndex+ScanFiles
   */
  static function saveIndex($oScanIndex, $sfids) {
    global $login;
    $scanIndex = ScanIndex::revive($oScanIndex);
    $scanIndex->ScanFiles = $sfids;
    $id = $scanIndex->scanIndexId;
    Dao::begin();
    try {
      $old = null;
      if ($oScanIndex->scanIndexId)
        $old = ScanIndex::fetch($oScanIndex->scanIndexId);
      if ($old)
        static::deleteRelations($old, $scanIndex->isResult());
      $scanIndex->save($login->userGroupId);
      $scanIndex->index($sfids);
      require_once 'php/data/rec/sql/Messaging_DocStubReview.php';
      if ($scanIndex->isResult()) { 
        $proc = Procedures::saveProc(Proc_Scan::from($scanIndex, $oScanIndex));
        logit_r($proc, 'proc saved');
        if ($scanIndex->needsReview())
          Messaging_DocStubReview::createThread_asProc($proc->procId, $scanIndex->recipient);
      } else {
        if ($scanIndex->needsReview())  
          Messaging_DocStubReview::createThread_asScan($scanIndex->scanIndexId, $scanIndex->recipient);
      }
      if (isset($oScanIndex->Order)) 
        OrderEntry::receivedByScan($oScanIndex->Order->trackItemId, $scanIndex->scanIndexId);
      if ($scanIndex->isProgressNote()) 
        Proc_OfficeVisit::record($scanIndex->clientId, $scanIndex->datePerformed, $scanIndex->recipient);
      Dao::commit();
    } catch (Exception $e) {
      Dao::rollback();
      throw $e;
    }
    //return static::getIndex($id);
  }
  protected static function deleteRelations($scanIndex, $keepProc = false) {  
    $proc = get($scanIndex, 'Proc');
    if ($proc) {
      $scanIndex->Thread = ReviewThread_Scan::fetchByProc($proc->procId, $scanIndex->userGroupId);
      if (! $keepProc)
        SqlRec::delete($proc);
    }
    $thread = get($scanIndex, 'Thread');
    require_once 'php/data/rec/sql/Messaging_DocStubReview.php';
    if ($thread)
      Messaging_DocStubReview::deleteThread($thread);
  }
  /**
   * @param int $sxid
   * @return int sxid
   */
  static function deleteIndex($sxid) {
    $rec = ScanIndex::fetch($sxid);
    if ($rec) {
      $rec->dropIndex();
      static::deleteRelations($rec);
      SqlRec::delete($rec);
    }
    return $sxid;
  }
  /**
   * @param int $sxid
   * @return ScanIndex+ScanFiles
   */
  static function getIndex($sxid) {
    $scanIndex = ScanIndex::fetch($sxid);
    return $scanIndex;
  }
  /**
   * @param int $sxid
   * @return ScanIndex+ScanFiles
   */
  /*
  static function saveAsReviewed($sxid) {
    global $login;
    $scanIndex = ScanIndex::fetch($sxid);
    return $scanIndex->saveAsReviewed($login->userId);
  }
  */
  /**
   * @param int $sfid
   */
  static function output($sfid, $maxh = 0, $maxw = 0) {
    global $login;
    $file = static::getFile($sfid);
    $folder = GroupFolder_Scanning::open($login->userGroupId);
    $folder->output($file, $maxh, $maxw);
  }
  /**
   * @param int $sfid
   * @return int sfid
   */
  static function deleteFile($sfid) {
    global $login;
    $file = static::getFile($sfid);
    $folder = GroupFolder_Scanning::open($login->userGroupId);
    $folder->delete($file);
    ScanFile::delete($file);
    return $sfid;
  }
}
/**
 * Scan Index 
 */
class ScanIndex extends SqlRec implements AutoEncrypt {
  //
  public $scanIndexId; 
  public $userGroupId;
  public $clientId;
  public $scanType;
  public $ipc;
  public $area1;
  public $area2;
  public $area3;
  public $providerId;
  public $addrFacility;
  public $datePerformed;
  public $dateUpdated;
  public $recipient;
  public $reviewed;
  public $tag1;
  public $tag2;
  public $tag3;
  public $withImage;
  public /*Ipc*/ $Ipc;
  public /*Provider*/ $Provider;
  public /*FacilityAddress*/ $Address_addrFacility;
  public /*ClientStub*/ $Client;
  public /*Proc*/ $Proc;
  public /*TrackItem*/ $TrackItem;
  public /*ScanFile[]*/ $ScanFiles;
  //
  const TYPE_RESULT = '1';
  const TYPE_LETTER = '2';
  const TYPE_OUTSIDE = '3';
  const TYPE_INSUR = '4';
  const TYPE_CARE = '5';
  const TYPE_RX = '6';
  const TYPE_DME = '7';
  const TYPE_PT = '8';
  const TYPE_PROGRESS = '9';
  const TYPE_HDS = '10';
  const TYPE_HHP = '11';
  const TYPE_CONS = '12';
  const TYPE_UC = '13';
  const TYPE_EMER = '14';
  const TYPE_ADMIN = '15';
  const TYPE_LAB_ORDER = '19';
  const TYPE_RADIO_ORDER = '20';
  const TYPE_XML = '99';
  public static $TYPES;  // see getStaticLists()
  /*
    self::TYPE_CARE => 'Care Supervision',
    self::TYPE_DME => 'DME/Supplies',
    self::TYPE_HDS => 'Hospital Discharge Summary',
    self::TYPE_HHP => 'Hospital H&P',
    self::TYPE_INSUR => 'Insurance',
    self::TYPE_LETTER => 'Letter/Note',
    self::TYPE_OUTSIDE => 'Outside Records',
    self::TYPE_RX => 'Pharmacy Communication',
    self::TYPE_PT => 'PT/OT',
    self::TYPE_PROGRESS => 'Progress Note',
    self::TYPE_RESULT => 'Test/Procedure Result',
    self::TYPE_CONS => 'Consultation',
    self::TYPE_UC => 'Urgent Care',
    self::TYPE_EMER => 'Emergency',
    self::TYPE_ADMIN => 'Administrative',
    self::TYPE_HRA => 'Health Risk Assessment');
  */
  //const TYPE_LEGAL_LW = 1000;  // legal docs scanned via facesheet  
  //const TYPE_LEGAL_POA = 1001;
  //
  static $FRIENDLY_NAMES = array(
    'clientId' => 'Patient',
    'scanType' => 'Type');
  //
  public function getSqlTable() {
    return 'scan_index';
  }
  public function getEncryptedFids() {
    return array('datePerformed');
  }
  public function save($ugid) {
    $triggerOrderLab = $this->scanIndexId == null && $this->scanType == static::TYPE_LAB_ORDER;
    $triggerOrderRadio = $this->scanIndexId == null && $this->scanType == static::TYPE_RADIO_ORDER;
    if ($this->withImage == null)
      $this->withImage = 0;
    parent::save($ugid);
    $this->saveTriggers();
    return $this;
  }
  protected function saveTriggers() {
    if ($this->scanType == static::TYPE_LAB_ORDER) {
      Proc_OrderLab::record_fromScanIndex($this);
    }
    if ($this->scanType == static::TYPE_RADIO_ORDER) {
      Proc_OrderRadio::record_fromScanIndex($this);
    }
    if ($this->scanType == static::TYPE_RESULT && $this->ipc) {
      $Ipc = Ipc::fetchTopLevel($this->ipc, $this->userGroupId);
      if ($Ipc->isRadiology()) {
        Proc_RadioWithImage::record($this);
      }
    }
  }
  public function toJsonObject(&$o) {
    $o->areas = $this->getAreas();
  }
  public function fromJsonObject($o) {
    $this->areas = get($o, 'areas');
    $this->setAreas($this->areas);
    if (empty($this->datePerformed))
      $this->datePerformed = nowNoQuotes();
  }
  public function getJsonFilters() {
    return array(
      'datePerformed' => JsonFilter::editableDate(),
    	'dateUpdated' => JsonFilter::informalDate(),
      'reviewed' => JsonFilter::boolean(),
      'withImage' => JsonFilter::boolean());
  }
  public function validate(&$rv) {
    $rv->requires('clientId', 'scanType');
    if (empty($this->ScanFiles))
      $rv->setRequired('ScanFiles', 'At least one file');
    if (isset($this->areas) && count($this->areas) > 3) 
      $rv->set('areas', ': No more than 3 may be selected', 'Area');
  }
  public function isResult() {
    return $this->scanType == static::TYPE_RESULT;
  }
  public function isProgressNote() {
    return $this->scanType == static::TYPE_PROGRESS;
  }
  public function isLabOrder() {
    return $this->scanType == static::TYPE_LAB_ORDER;
  }
  public function isRadioOrder() {
    return $this->scanType == static::TYPE_RADIO_ORDER;
  }
  public function needsReview() {
    return ! $this->reviewed && $this->recipient; 
  }
  public function getLabel() {
    $label = $this->getTypeName();
    if ($this->Ipc)
      $label .= ': ' . $this->Ipc->name;
    $s = Provider::formatProviderFacility($this);
    if (! empty($s))
      $label .= " ($s)";
    return $label;
  }
  public function getTypeName() {
    static::loadStaticLists();
    return static::$TYPES[$this->scanType];
  }
  public function getAreas() {
    return array_filter(array($this->area1, $this->area2, $this->area3));
  }
  public function setAreas($areas) {
    $arr = ($areas) ? $areas : array();
    $this->area1 = geta($arr, 0);
    $this->area2 = geta($arr, 1);
    $this->area3 = geta($arr, 2);
  }
  /**
   * Add files to this
   * @param int[] $sfids
   */
  public function index($sfids) {
    $this->dropIndex();
    $this->ScanFiles = ScanFile::indexAllTo($sfids, $this->scanIndexId, $this->userGroupId);
  }
  /**
   * Drop all files from this 
   */
  public function dropIndex() {
    ScanFile::dropIndexTo($this->scanIndexId, $this->userGroupId);
  }
  public function sortFiles() {
    $this->ScanFiles = ScanFile::sortAll($this->ScanFiles);
  }
  //
  static function revive($obj) {
    unset($obj->ScanFiles);
    unset($obj->Client);
    unset($obj->Thread);
    $me = new ScanIndex($obj);
    return $me;
  }
  static function fetch($sxid) {
    require_once 'php/data/rec/sql/Messaging_DocStubReview.php';
    global $login;
    $c = new static();
    $c->userGroupId = $login->userGroupId;
    $c->scanIndexId = $sxid;
    $c->Client = new ClientStub();
    $c->Provider = new Provider();
    $c->Ipc = Ipc::asOptionalJoin();
    $c->Address_addrFacility = new FacilityAddress();
    $c->Proc = Proc::asScanJoin();
    // (not used?) $c->TrackItem = TrackItem::asOptionalJoin();
    $c->Thread = MsgThread_Stub::asScanJoin();  // will not join "proc" type review threads; these are associated with proc, not scanindex
    $rec = static::fetchOneBy($c);
    $rec->ScanFiles = ScanFile::fetchAllIndexedTo($sxid, $login->userGroupId);
    return $rec;
  }
  static function fetchAllByDate($ugid, $date) {
    $c = new static();
    $c->userGroupId = $ugid;
    $c->dateUpdated = CriteriaValue::greaterThanOrEquals($date);
    $c->Client = new ClientStub();
    $c->Provider = new Provider();
    $c->Ipc = Ipc::asOptionalJoin();
    $c->Address_addrFacility = new FacilityAddress();
    return static::fetchAllBy($c);
  }
  static function loadStaticLists() {
    global $login;
    if (empty(static::$TYPES)) {
      static::$TYPES = ScanTypes::fetchList($login->userGroupId);
      static::$TYPES['99'] = 'Electronic';
    }
  }
  static function asOptionalJoin() {
    $c = new static();
    $c->ScanFiles = ScanFile::asOptionalJoin();
    return CriteriaJoin::optional($c);
  }
  //
  protected static function getStaticLists($rc) {
    static::loadStaticLists();
    return parent::getStaticLists($rc);
  } 
}
/**
 * Scan File
 */
class ScanFile extends SqlRec {
  //
  public $scanFileId;
  public $userGroupId;
  public $filename;
  public $fileseq;
  public $origFilename;
  public $height;
  public $width;
  public $rotation;
  public $mime;
  public $scanIndexId;
  public $seq;
  public $dateCreated;
  public $createdBy;
  //
  public function getSqlTable() {
    return 'scan_files';
  }
  public function toJsonObject(&$o) {
    $o->src = $this->getSrc();
    if ($this->isPdf()) {
      $o->_pdf = 1;
      $o->pdfsrc = "scan-image.php?id=$this->scanFileId";
      $o->height = 80;
      $o->width = 80;
    } else if ($this->isXml()) {
      $o->_xml = 1;
      $o->xmlsrc = "scan-image.php?id=$this->scanFileId";
      $o->height = 80;
      $o->width = 80;
    }
    $o->_uploaded = $this->getUploadedText();
  }
  public function getSrc() {
    if ($this->isPdf()) 
      return 'img/adobe-pdf.png';
    else if ($this->isXml()) 
      return 'img/xml.png';
    else
      return "scan-image.php?id=$this->scanFileId&rot=$this->rotation";
  }
  public function getUploadedText() {
    return $this->origFilename;
//    $s = $this->origFilename . ' (uploaded ' . formatDateTime($this->dateCreated);
//    if ($this->createdBy) 
//      $s .= ' by ' . UserGroups::lookupUser($this->createdBy);
//    $s .= ')';
//    return $s;
  }
  public function setDateOnly() {
    $this->_date = substr($this->dateCreated, 0, 10);
  }
  public function resetIndex() {
    $this->scanIndexId = null;
    $this->seq = null;
  } 
  public function setIndex($sxid, $seq) {
    $this->scanIndexId = $sxid;
    $this->seq = $seq;
  } 
  public function swapDims() {
    $height = $this->height;
    $this->height = $this->width;
    $this->width = $height;
  }
  public function rotate() {
    switch ($this->rotation) {
      case 90:
        $this->setRotation(180);
        break;
      case 180:
        $this->setRotation(270);
        break;
      case 270:
        $this->setRotation(0);
        break;
      default:
        $this->setRotation(90);
        break;
    }
  }
  public function setRotation($value) {
    switch ($this->rotation) {
      case 90:
      case 270:
        if ($value == 0 || $value == 180)
          $this->swapDims();
        break;
      default:
        if ($value == 90 || $value == 270)
          $this->swapDims();
        break;
    }
    $this->rotation = $value;
  }
  public function isPdf() {
    return $this->mime == GroupFolder::MIME_PDF; 
  }
  public function isXml() {
    return $this->mime == GroupFolder::MIME_XML; 
  }
  //
  /**
   * @param int $ugid
   * @return int
   */
  static function nextFileIndex($ugid) {
    $sql = "SELECT MAX(fileseq) FROM scan_files WHERE user_group_id=$ugid";
    return intval(Dao::fetchValue($sql)) + 1;
  }
  /**
   * @param int $ugid
   * @return array(ScanFile,..)
   */
  static function fetchAllUnindexed($ugid, $userId = null) {
    $c = static::asCriteria($ugid);
    $c->scanIndexId = CriteriaValue::isNull();
    $c->createdBy = $userId;
    $recs = static::fetchAllBy($c);
    return static::setDateOnlyFor($recs);
  }
  /**
   * @param int $scanIndexId
   * @param int $ugid
   * @return array(ScanFile,..)
   */
  static function fetchAllIndexedTo($sxid, $ugid) {
    $c = static::asCriteria($ugid);
    $c->scanIndexId = $sxid;
    return static::fetchAllBy($c, new RecSort('seq'));
  }
  static function sortAll($recs) {
    return Rec::sort($recs, new RecSort('seq'));
  }
  /**
   * @param int $sfids
   * @param int $ugid
   * @return array(sfid=>ScanFile,..)
   */
  static function fetchAllIn($sfids, $ugid) {
    $c = static::asCriteria($ugid);
    $c->scanFileId = CriteriaValue::in($sfids);
    return static::fetchMapBy($c, 'scanFileId');    
  }
  /**
   * @param int $scanIndexId
   * @param int $ugid
   */
  static function dropIndexTo($sxid, $ugid) {
    $recs = static::fetchAllIndexedTo($sxid, $ugid);
    foreach ($recs as $rec) {
      $rec->resetIndex();
      $rec->save();
    }
  }
  /**
   * @param int[] $sfids 
   * @param int $scanIndexId
   * @param int $ugid
   * @return array(ScanFile,..)
   */
  static function indexAllTo($sfids, $sxid, $ugid) {
    $recs = static::fetchAllIn($sfids, $ugid);
    $seq = 0;
    $sorted = array();
    foreach ($sfids as $sfid) {
      $rec = $recs[$sfid];
      $rec->setIndex($sxid, $seq++);
      $rec->save();
      $sorted[] = $rec;
    }
    return $sorted;
  }
  /**
   * @param GroupUpload_Scanning[] $files
   * @param int $ugid
   */
  static function saveUploads($files, $ugid) {
    $recs = static::fromUploads($files, $ugid);
    static::saveAll($recs);
  }
  /**
   * @param GroupUpload_Scanning[] $files
   * @param int $ugid
   * @return array(ScanFile,..)
   */
  static function fromUploads($files, $ugid) {
    global $login;
    $recs = array();
    foreach ($files as $file)
      $recs[] = static::fromUpload($file, $ugid, $login->userId); 
    return $recs;
  }
  /**
   * @param GroupUpload_Scanning[] $files
   * @param int $ugid
   * @param int $userId
   * @return ScanFile
   */
  static function fromUpload($file, $ugid, $userId) {
    $rec = new static();
    $rec->userGroupId = $ugid;
    $rec->filename = $file->newName;
    $rec->fileseq = $file->fileseq;
    $rec->origFilename = $file->name;
    $rec->height = $file->height;
    $rec->width = $file->width;
    $rec->mime = $file->mime;
    $rec->dateCreated = nowNoQuotes();
    $rec->createdBy = $userId;
    logit_r($rec, 'rec fromupload');
    return $rec;
  }
  /**
   * @param int $ugid
   * @return ScanFile
   */
  static function asCriteria($ugid) {
    $c = new static();
    $c->userGroupId = $ugid;
    return $c;
  }
  static function asOptionalJoin() {
    return CriteriaJoin::optionalAsArray(new static());
  }
  //
  static function setDateOnlyFor($recs) {
    foreach ($recs as &$rec) 
      $rec->setDateOnly();
    return $recs;
  }
}
//
class Bat_Split extends Bat {
  //
  static $BAT_FILE = 'split.bat';
  //
  static function run($file) {
    $ugid = $file->folder->ugid;
    $filename = $file->filename;
    return new static($ugid, $filename);
  }
}
//
class Proc_Scan extends Rec {  
  //
  static function from($scanIndex, $obj) {
    $me = new static();
    if (isset($obj->Proc))
      $me->procId = $obj->Proc->procId;
    $me->userGroupId = $scanIndex->userGroupId;
    $me->clientId = $scanIndex->clientId;
    $me->date = $scanIndex->datePerformed;
    $me->ipc = $scanIndex->ipc;
    $me->providerId = $scanIndex->providerId;
    $me->addrFacility = $scanIndex->addrFacility;
    $me->scanIndexId = $scanIndex->scanIndexId;
    $me->hl7InboxId = null;
    $me->interpretCode = get($obj, 'interpretCode');
    $me->rcomments = get($obj, 'rcomments');
    $me->value = get($obj, 'value');
    $me->units = get($obj, 'units');
    logit_r($me, 'me');
    return $me;
  } 
}
class ReviewThread_Scan extends MsgThreadRec {
  //
  public $threadId;
  public $userGroupId;
  public $clientId;
  public $creatorId;
  public $creator;
  public $dateCreated;
  public $dateToSend;
  public $dateClosed;
  public $type;  // TYPE_STUB_REVIEW 
  public $status;
  public $priority;
  public $subject;
  public $stubType;
  public $stubId;
  public /*MsgPost_Stub[]*/ $Posts;
  public /*MsgInbox_Stub*/ $Inbox;
  //
  static function fetchByProc($procId, $ugid) {
    require_once 'php/data/rec/sql/Documentation.php';
    $c = new static();
    $c->userGroupId = $ugid;
    $c->stubType = DocStub::TYPE_RESULT;
    $c->stubId = $procId;
    $c->Posts = MsgPost_Stub::asJoin();
    $c->Inbox = MsgInbox_Stub::asUnreviewedJoin(null); 
    $rec = static::fetchOneBy($c);
    return $rec;
  }
}
//
class ScanTypes extends SqlGroupLevelRec {
  //
  public $scanTypeId;
  public $userGroupId;
  public $name;
  public $active;
  //
  public function getSqlTable() {
    return 'scan_types';
  }
}
class ScanIndex_Xml extends ScanIndex {
  //
  public function getGroupFile() {
    require_once 'php/data/rec/group-folder/GroupFolder_ClinicalImport.php';
    return GroupFolder_ClinicalImport::open()->getFile($this->ScanFile->filename);
  }
  public function save($ugid) {
    SqlRec::save($ugid);
    $file = reset($this->ScanFiles);
    $file->scanIndexId = $this->scanIndexId;
    $file->save($ugid);
  }
  //
  static function from($filename, $cid, $datePerformed = null, $recipient = null, $reviewed = null) {
    $me = new static();
    $me->clientId = $cid;
    $me->scanType = 99;
    $me->datePerformed = $datePerformed ?: nowNoQuotes();
    $me->recipient = $recipient;
    $me->reviewed = $reviewed ?: 0;
    $me->withImage = 0;
	
	if (MyEnv::$IS_ORACLE) {
		$me->ScanFiles = array(ScanFile_Xml::fromFormattedDate($filename));
	}
    else {
		$me->ScanFiles = array(ScanFile_Xml::from($filename));
	}
    return $me;
  }
  static function fetch($sxid) {
    $c = new static();
    $c->scanIndexId = $sxid;
    $c->ScanFile = new ScanFile;
    return static::fetchOneBy($c);
  }
}
class ScanFile_Xml extends ScanFile {
  //
  static function from($filename) {
    $me = new static();
    $me->filename = $filename;
    $me->origFilename = $filename;
    $me->mime = 'application/xml';
    $me->dateCreated = nowNoQuotes();
    return $me;
  } 
  
  //Sprout that uses the SAME code as above but fixes a bug where an incorrect date format (2016-10-01 6:03:44) was created. The correct format that the scan_files table wants is 10-OCT-16.
  static function fromFormattedDate($filename) {
	$me = new static();
	$me->filename = $filename;
	$me->origFilename = $filename;
	$me->mime = 'application/xml';
	$me->dateCreated = date('d-M-y');
	return $me;
  }
}
