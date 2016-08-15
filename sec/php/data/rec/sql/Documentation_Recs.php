<?php
require_once 'php/data/rec/sql/_ClientDocRec.php';
require_once 'php/data/rec/sql/_ClientRec.php';
require_once 'php/data/rec/sql/_SchedRec.php';
require_once 'php/data/rec/sql/_SessionRec.php';
require_once 'php/data/rec/sql/_Hl7InboxRec.php';
require_once 'php/data/rec/sql/_VisitSummaryRec.php';
require_once 'php/data/rec/sql/Diagnoses.php';
require_once 'php/data/rec/sql/Messaging.php';
require_once 'php/data/rec/sql/Scanning.php';
require_once 'php/data/rec/sql/Procedures.php';
require_once 'php/data/rec/sql/OrderEntry.php';
require_once 'php/data/rec/sql/Messaging_DocStubReview.php';
require_once 'php/data/xml/ClinicalXmls.php';
//
interface PreviewRec extends ReadOnly {
  //
  static function getStubType();    
  static function asStubCriteria($cid = null);
  static function asPreviewCriteria();
  static function getHtmlBody($rec);  // may return null
  //
  public function loadStub(&$stub);
}
interface PreviewRec_Reviewable extends PreviewRec {}
//
class DocClientDoc extends ClientDocRec implements PreviewRec {
  //
  public $clientDocId;
  public $clientId;
  public $type;
  public $dateCreated;
  public $createdBy;
  public $html;
  //
  public function loadStub(&$rec) {
    $rec->date = $this->dateCreated;
    $rec->name = $this->getLabel();
    $rec->desc = $this->getLabel();
  }
  //
  static function getStubType() {
    return DocStub::TYPE_CLIENTDOC;
  }
  static function asStubCriteria($cid = null) {
    $c = new static();
    $c->clientId = $cid;
    return $c;
  }
  static function asPreviewCriteria() {
    return static::asStubCriteria();
  }
  static function getHtmlBody($rec) {
    return $rec->html;
  }
}
class DocSession extends SessionRec implements PreviewRec {
  //
  public $sessionId;
  public $userGroupId;
  public $clientId;
  public $templateId;
  public $dateCreated;
  public $dateUpdated;
  public $dateService;
  public $closed;
  public $closedBy;
  public $dateClosed;
  public $billed;
  public $schedId;
  public $data;
  public $createdBy;
  public $updatedBy;
  public $sendTo;
  public $assignedTo;
  public $html;
  public $title;
  public $noteDate;
  public $stubType;
  public $stubId;
  public $stubName;
  //
  public function toJsonObject(&$o) {
    parent::toJsonObject($o);
    $o->html = null;
  }
  public function loadStub(&$rec) {
    $rec->date = $this->dateService;
    $rec->name = $this->getLabel();
    $rec->desc = $this->formatDiagnoses();
    if ($this->closedBy)
      $rec->provider = UserGroups::lookupUser($this->closedBy); 
    if ($this->isClosed())
      $rec->setSigned($this->dateClosed, $this->closedBy); 
  }
  protected function formatDiagnoses() {
    if (isset($this->Diagnoses)) {
      $names = DocSession_Diagnosis::formatNames($this->Diagnoses);
      return implode(', ', $names);
    }
  }
  //
  static function getStubType() {
    return DocStub::TYPE_SESSION;
  }
  static function fetchAllBy($c) {
    return parent::fetchAllBy($c, null, 1000);
  }
  static function asStubCriteria($cid = null) {
    global $login;
    $c = new static();
    $c->clientId = $cid;
    $c->Diagnoses = array(new DocSession_Diagnosis());
    return $c;
  }
  static function asPreviewCriteria() {
    return static::asStubCriteria();
  }
  static function getHtmlBody($rec) {
    return $rec->getHtml();
  }
}
class DocSession_Diagnosis extends Diagnosis implements ReadOnly {
  //
  public function formatName() {
    $name = $this->text;
    if ($this->icd) 
      $name .= " ($this->icd)";
    return $name;
  }
  //
  static function formatNames($recs) {
    $names = array();
    foreach ($recs as $rec) 
      $names[] = $rec->formatName();
    return $names;
  }  
}
/*
class DocSuperbill extends ApiIdXref_Cerberus implements PreviewRec {
  //
  static function getStubType() {
    return DocStub::TYPE_SUPERBILL;
  }
  static function asStubCriteria($cid = null) {
    global $login;
    if ($login->cerberus) {
      $c = static::asEncounter($login->userGroupId);
      $session = new DocSession();
      $session->clientId = $cid;
      $c->Session = CriteriaJoin::requires($session, 'internalId');
      return $c;
    }
  }
  static function asPreviewCriteria() {
    return static::asStubCriteria();
  }
  static function getHtmlBody($rec) {
    return null;
  }
  //
  public function setPkValue($id) {
    $this->externalId = $id;
  }
  public function getAuditRecId() {
    return $this->externalId;
  }
  public function loadStub(&$rec) {
    $rec->_sort = DocStub::TYPE_SESSION . '.' . $this->internalId . 'SB'; 
    $rec->date = $this->Session->dateService;
    $rec->name = 'Superbill';
    $rec->desc = 'Encounter ID ' . $this->externalId;
  }
}
*/
class DocMessage extends MsgThread implements PreviewRec {
  //
  public function loadStub(&$rec) {
    $rec->date = $this->getDate();
    $rec->name = $this->getLabel();
    $rec->desc = $this->creator;
  }
  //
  static function getStubType() {
    return DocStub::TYPE_MSG;
  }
  static function asStubCriteria($cid = null) {
    $c = new self();
    $c->clientId = $cid;
    $c->type = CriteriaValue::notEqualsNumeric(MsgThread::TYPE_STUB_REVIEW);
    return $c;
  }
  static function asPreviewCriteria() {
    $c = static::asStubCriteria();
    //$c->MsgPosts = CriteriaJoin::optionalAsArray(new MsgPost());
    return $c;
  }
  static function getHtmlBody($rec) {
    $h = array();
    foreach ($rec->MsgPosts as $post) 
      $h[] = static::getHtmlPost($post);
    return implode($h);
  }
  static function fetchOneBy($c) {
    $me = parent::fetchOneBy($c);
    if ($me) {
      $me->MsgPosts = MsgPost::fetchByThread($me);
    }
    return $me;
  }
  protected static function getHtmlPost($post) {
    return Html::create()
      ->div_('posthead')
        ->b('From: ')->br($post->author)
        ->if_($post->sendTo)->b(' To: ')->br($post->sendTo)->_if()
        ->b('Date: ')->add($post->dateCreated)
      ->_()
      ->p($post->body)
      ->out();
  }
}
class DocAppt extends SchedRec implements PreviewRec {
  //
  public function loadStub(&$rec) {
    $rec->date = $this->date;
    $rec->name = $this->getLabel();
    $rec->desc = null;
  }
  //
  static function getStubType() {
    return DocStub::TYPE_APPT;
  }
  static function asStubCriteria($cid = null) {
    $c = new static();
    $c->clientId = $cid;
    $c->setDateCriteria(CriteriaValue::lessThanOrEquals(nowNoQuotes()));
    return $c;
  }
  static function asPreviewCriteria() {
    return static::asStubCriteria();
  }
  static function getHtmlBody($rec) {
    return null;
  }
}
class DocStub_Order extends DocStub {
  //
  static $TYPE = self::TYPE_ORDER;
  static $REC_CLASS = 'DocOrder';
  //
  static function fetchAll($cid) {
    $map = array();
    $class = static::$REC_CLASS;
    $recs = $class::fetchStubs($cid);
    foreach ($recs as $rec)
      static::append($map, $rec);
    return static::flatten($map);
  }
  static function append(&$map, $rec) {
    $date = substr($rec->orderDate, 0, 10);
    if (isset($map[$date]))
      $map[$date][] = $rec;
    else
      $map[$date] = array($rec);
  }
  static function flatten($map) {
    $us = array();
    foreach ($map as $date => $recs) 
      $us[] = static::from($date, $recs);
    return $us;
  }
  static function from($date, $recs) {
    $ids = array();
    $descs = array();
    foreach ($recs as $rec) {
      $ids[] = $rec->trackItemId;
      $descs[] = $rec->trackDesc;
      $cid = $rec->clientId;
    }
    $me = new static();
    $me->setKey(static::$TYPE, implode(',', $ids));
    $me->date = $date;
    $me->name = 'Orders';
    $me->desc = implode(', ', $descs);
    $me->cid = $cid;
    $me->timestamp = $date;
    $me->Orders = $recs;
    return $me;
  }
}
class DocOrder extends TrackItem implements PreviewRec {
  //
  public function loadStub(&$rec) {
    $rec->date = $this->orderDate;
    $rec->name = $this->trackDesc;
    $rec->desc = $this->orderNotes;
  }
  //
  static function getStubType() {
    return DocStub::TYPE_ORDER;
  }
  static function fetchStubs($cid) {
    $c = static::asStubCriteria($cid);
    return static::fetchAllBy($c);
  }
  static function asStubCriteria($cid = null) {
    $c = new static();
    $c->clientId = $cid;
    return $c;
  }
  static function asPreviewCriteria() {
    return static::asStubCriteria();
  }
  static function getHtmlBody($rec) {
    return null;
  }
}
class DocScan extends ScanIndex implements PreviewRec_Reviewable {
  //
  public function loadStub(&$rec) {
    $rec->date = $this->datePerformed;
    $rec->name = $this->formatName();
    $rec->desc = $this->formatDesc();
  }
  protected function formatName() {
    $label = $this->getTypeName();
    if ($this->Ipc)
      $label .= ': ' . $this->Ipc->name;
    return $label;
  }
  protected function formatDesc() {
    $d = array();
    if (isset($this->Ipc))
      $d[] = $this->Ipc->name;
    $p = Provider::formatProviderFacility($this);
    if (! empty($p)) {
      $d[] = $p;
    } else {
      if (isset($this->ScanFiles)) {
        $f = current($this->ScanFiles);
        $d[] = $f->origFilename; 
      }
    }
    return implode(' ', $d);
  }
  //
  static function getStubType() {
    return DocStub::TYPE_SCAN;
  }
  static function asStubCriteria($cid = null) {
    $c = new static();
    $c->scanType = CriteriaValue::notEqualsNumeric(ScanIndex::TYPE_XML);
    $c->clientId = $cid;
    $c->Ipc = Ipc::asOptionalJoin();
    $c->Provider = Provider::asOptionalJoin();
    $c->Facility = FacilityAddress::asOptionalJoin();
    $proc = new Proc();
    $proc->Ipc = Ipc::asRequired_noAdmin();
    $c->Proc = CriteriaJoin::notExists($proc);
    $c->ScanFiles = ScanFile::asOptionalJoin();
    return $c;
  }
  static function asPreviewCriteria() {
    $c = static::asStubCriteria();
    $c->ScanFiles = ScanFile::asOptionalJoin();
    return $c; 
  }
  static function getHtmlBody($rec) {
    return null;
  }
  static function fetchOneBy($c) {
    $me = parent::fetchOneBy($c);
    if ($me)
      $me->sortFiles();
    return $me;
  }
}
class DocScanXml extends ScanIndex_Xml implements PreviewRec {
  //
  public function loadStub(&$rec) {
    $rec->date = $this->datePerformed;
    $rec->name = $this->getTypeName();
    $rec->desc = Provider::formatProviderFacility($this);
  }
  //
  static function getStubType() {
    return DocStub::TYPE_SCAN_XML;
  }
  static function asStubCriteria($cid = null) {
    $c = new static();
    $c->scanType = ScanIndex::TYPE_XML;
    $c->clientId = $cid;
    $c->Provider = Provider::asOptionalJoin();
    $c->Facility = FacilityAddress::asOptionalJoin();
    return $c;
  }
  static function asPreviewCriteria() {
    $c = static::asStubCriteria();
    $c->ScanFile = ScanFile::asRequiredJoin();
    return $c;
  }
  static function getHtmlBody($rec) {
    $file = $rec->getGroupFile();
    logit_r($file, 'xml file');
    logit_r($file->getContent());
    $xml = ClinicalXmls::parse($file->getContent());
    return $xml->asHtml();
  }
}
class DocVisitSum extends VisitSummaryRec implements PreviewRec {
  //
  public $clientId;
  public $finalId;
  public $dos;
  public $sessionId;
  public $finalHead;
  public $finalBody;
  public $finalizedBy;
  public $diagnoses;
  public $iols;
  public $instructs;
  public $vitals;
  public $meds;
  //
  public function loadStub(&$rec) {
    $rec->date = formatFromDateTime($this->getDateProduced());
    $rec->name = $this->getLabel();
    $rec->desc = null;
  }
  public function setPkValue($value) {
    $values = explode(',', $value);
    $this->clientId = $values[0];
    $this->finalId = $values[1];
  }
  //
  static function getStubType() {
    return DocStub::TYPE_VISITSUM;
  }
  static function asStubCriteria($cid = null) {
    $c = new static();
    $c->clientId = $cid;
    $c->finalId = CriteriaValue::greaterThanNumeric(0);
    return $c;
  }
  static function asPreviewCriteria() {
    return static::asStubCriteria();
  }
  static function getHtmlBody($rec) {
    return $rec->finalBody . static::getDisclaimer();
  }
  protected static function getDisclaimer() {
    return "<p>&nbsp;</p><table border=1 cellpadding=5><tr><td>This document is provided as a summary of your office visit. If you have any worsening or change in your condition, or if your condition doesn't seem to be responding to therapy, please contact your provider immediately. If you notice any inaccuracies, errors or incomplete information on the summary, please notify your provider.</td></tr></table>"; 
  }
}
class DocProc extends Proc implements PreviewRec_Reviewable {
  //
  public function loadStub(&$rec) {
    $rec->date = $this->date;
    $rec->name = $this->formatName();
    $rec->desc = $this->formatDesc();
  }
  //
  static function getStubType() {
    return DocStub::TYPE_RESULT;
  }
  static function asStubCriteria($cid = null) {
    $c = new static();
    $c->clientId = $cid;
    $c->Ipc = Ipc::asRequired_noAdmin();
    $c->ProcResults = ProcResult::asOptionalJoin();
    $c->Provider = Provider::asOptionalJoin();
    $c->Facility = FacilityAddress::asOptionalJoin();
    return $c;
  }
  static function asPreviewCriteria() {
    $c = static::asStubCriteria();
    $c->LabInbox = HL7InboxStub::asOptionalJoin();
    $c->ScanIndex = ScanIndex::asOptionalJoin();
    return $c;
  }
  static function fetchOneBy($c) {
    $me = parent::fetchOneBy($c);
    if (isset($me->ScanIndex))
      $me->ScanIndex->sortFiles();
    return $me;
  }
  static function getHtmlBody($rec) {
    return null;
  }
} 
class HL7InboxStub extends Hl7InboxRec implements ReadOnly {
  //
  public $hl7InboxId;
  public $userGroupId;
  public $labId;
  public $msgType; 
  public $source;
  public $dateReceived;
  public $patientName;
  public $cid;
  public $status;
  public $reconciledBy;
  public $pdf; 
  //
  public function toJsonObject(&$o) {
    if ($this->pdf) {
      $o->pdf = 1;
    }
    return $o;
  }
  public function getJsonFilters() {
    return array(
      'dateReceived' => JsonFilter::reportDateTime());
  }
}
//
require_once 'php/data/rec/sql/LookupScheduling.php';
