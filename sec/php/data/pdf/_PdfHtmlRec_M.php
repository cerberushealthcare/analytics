<?php
require_once 'php/pdf/PdfM.php';
require_once 'php/data/Html.php';
//
/**
 * PDF wrapper for SqlRec
 * @author Warren Hornsby
 */
abstract class PdfHtmlRec {
  //
  public $Rec;
  //
  static function fromRec($rec) {
    $me = new static();
    $me->Rec = $rec;
    return $me;
  }
  //
  public function getFilename($rec) {
    return static::makeFilename('File', 'Name');
  }
  public function getTitle($rec) {
    return 'PDF Title';  // @abstract
  }
  public function getBody($rec) {
    return '<p>Body</p>';  // @abstract
  }
  public function getHeader($rec) {
    $h = new Html();
    $client = $this->getHeader_client($rec);
    if ($client)
      $h->br($client->getFullName());
    $h->br($this->getTitle_withDos());
    $h->br($this->getHeader_groupName($rec));
    $h->br('Date Printed: ' . formatNowTimestamp());
    return $h->out();
  }
  public function getHeader_client($rec) {
    return get($rec, 'Client');  
  }
  public function getHeader_dos($rec) {
    return null;
  }
  public function getHeader_groupName($rec) {
    global $login;
    return $login->User->UserGroup->name;
  }
  public function download() {
    PdfM::create()
      ->withPaging()
      ->setHeader($this->getHeader($this->Rec))
      ->setBody($this->getBody($this->Rec))
      ->download($this->getFilename($this->Rec));
  }
  //
  protected function getTitle_withDos() {
    if ($this->getHeader_dos($this->Rec)) 
      return $this->getTitle($this->Rec) . ' (' . formatFullDate($this->getHeader_dos($this->Rec)) . ')';
    else
      return $this->getTitle($this->Rec);
  }
  protected static function makeFilename() {
    $args = $args = func_get_args();
    return implode('_', $args) . '.pdf';
  }
}