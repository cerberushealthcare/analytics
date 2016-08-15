<?php
require_once 'php/data/pdf/_PdfHtmlRec.php';
require_once 'php/data/rec/sql/Scanning.php';
//
class Pdf_ScanIndex extends PdfHtmlRec {
  //
  static function fetch($id) {
    $rec = ScanIndex_Pdf::fetch($id);
    return static::from($rec);
  }
  static function from($rec) {
    $me = static::fromRec($rec);
    return $me;
  }
  //
  public function getFilename($rec) {
    return static::makeFilename('SI', $rec->clientId, $rec->scanIndexId);
  }
  public function getTitle($rec) {
    return $rec->getLabel();
  }
  public function getHeader_dos($rec) {
    return $rec->datePerformed;
  }
  public function getBody($rec) {
    return $rec->getHtmlBody();
  }
}
class ScanIndex_Pdf extends ScanIndex {
  //
  public function getHtmlBody() {
    $h = new Html();
    foreach ($this->ScanFiles as $file)
    $h->img(array('src'=>$this->getImgSrc($file), 'height'=>830, 'width'=>643));
    return $h->out();
  }
  protected function getImgSrc($file) {
    global $login;
    $url = MyEnv::$PDF_URL . $file->getSrc() . '&sess=' . $login->sessionId; 
    return $url;
  }
  //
  static function fetch($id) {
  $c = static::asCriteria();
  $c->scanIndexId = $id;
    return static::fetchOneBy($c);
  }
  static function asCriteria() {
  $c = new static();
  $c->Client = new ClientStub();
  $c->Ipc = Ipc::asOptionalJoin();
  $c->Provider = Provider::asOptionalJoin();
  $c->Facility = FacilityAddress::asOptionalJoin();
  $c->ScanFiles = ScanFile::asOptionalJoin();
    return $c;
  }
}
  