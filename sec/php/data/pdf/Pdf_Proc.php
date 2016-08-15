<?php
require_once 'php/data/pdf/_PdfHtmlRec.php';
require_once 'php/data/pdf/Pdf_ScanIndex.php';
//
class Pdf_Proc extends PdfHtmlRec {
  //
  /**
   * @param Proc_Pdf $rec
   */
  static function from($rec) {  
    $me = static::fromRec($rec);
    return $me;
  }
  static function fetch($id) {
    global $login;
    $rec = Proc_Pdf::fetch($id, $login->userGroupId);
    return static::from($rec);
  }
  //
  public function getFilename($rec) {
    return static::makeFilename('R', $rec->clientId, $rec->scanIndexId);
  }
  public function getTitle($rec) {
    return $rec->formatName();
  }
  public function getHeader_dos($rec) {
    return $rec->date;
  }
  public function getBody($rec) {
    return $rec->getHtmlBody();
  }
}
//
class Proc_Pdf extends Proc {
  //
  public $procId;
  public $userGroupId;
  public $clientId;
  public $date;  
  public $ipc;
  public $priority;
  public $location;
  public $providerId;
  public $addrFacility;
  public $recipient;
  public $scanIndexId;
  public $hl7InboxId;
  public $userId;
  public $comments;
  public /*Ipc*/ $Ipc;
  public /*ProcResult[]*/ $ProcResults;
  public /*Provider*/ $Provider;
  public /*FacilityAddress*/ $Facility;
  public /*ClientStub*/ $Client;
  public /*ScanIndex_Pdf*/ $ScanIndex;
  public /*HL7InboxStub*/ $LabInbox;
  // 
  public function getHtmlBody() {
    $h = new Html();
    if ($this->ScanIndex) {
      $h->add($this->ScanIndex->getHtmlBody());
    }
    $this->htmlProcHead($h);
    $this->htmlResultsTable($h);
    return $h->out();
  }
  protected function htmlProcHead(&$h) {
    $h->h3($this->Ipc->name)
      ->b('Date Performed: ')->br(formatDate($this->date));
    if ($this->Provider)
      $h->b('Provider: ')->br($this->Provider->formatName());
    if ($this->Facility)
      $h->b('Facility: ')->br($this->Facility->formatName());
    if ($this->LabInbox) {
      $h->b('Source: ')->br($this->LabInbox->source)
        ->b('Report Received: ')->br(formatDate($this->LabInbox->dateReceived));
      if (! empty($this->comments))
        $h->br($this->comments);
    }
    $h->br();
  }
  protected function htmlResultsTable(&$h) {
    $h->table_(array('border'=>'1', 'cellpadding'=>'3'));
    $h->tr_('style="font-weight:bold"')->th(array(
    	'','Value', 'Range', 'Interpret', 'Comments'))->_();
    foreach ($this->ProcResults as $rec)
      $h->tr_()->td(array(
        $rec->Ipc->name, $rec->getResult(), $rec->range, $rec->getInterpret(), $rec->comments))->_();
    $h->_();
  }
  //
  static function fetch($id, $ugid) {
    $c = static::asCriteria($ugid);
    $c->procId = $id;
    return static::fetchOneBy($c);
  }
  static function asCriteria($ugid) {
    require_once 'php/data/rec/sql/Documentation.php';
    $c = new static();
    $c->Ipc = Ipc::asRequiredJoin($ugid); 
    $c->ProcResults = ProcResult::asOptionalJoin();
    $c->Provider = Provider::asOptionalJoin();
    $c->Facility = FacilityAddress::asOptionalJoin();
    $c->Client = new ClientStub();
    $c->ScanIndex = ScanIndex_Pdf::asOptionalJoin();
    $c->LabInbox = HL7InboxStub::asOptionalJoin();
    return $c;
  }
}