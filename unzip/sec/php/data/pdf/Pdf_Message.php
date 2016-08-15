<?php
require_once 'php/data/pdf/_PdfHtmlRec.php';
require_once 'php/data/rec/sql/Documentation.php';
//
class Pdf_Message extends PdfHtmlRec {
  //
  static function fetch($id) {
    global $login;
    $rec = MsgThread_Pdf::fetch($id, $login->userGroupId);
    return static::from($rec);
  }
  static function from($rec) {  
    $me = static::fromRec($rec);
    return $me;
  }
  //
  public function getFilename($rec) {
    return static::makeFilename('M', $rec->clientId, $rec->threadId);
  }
  public function getTitle($rec) {
    return $rec->getLabel();
  }
  public function getBody($rec) {
    return $rec->getHtmlBody();
  }
}
//
class MsgThread_Pdf extends MsgThread {
  //
  static function fetch($id, $ugid) {
    $c = static::asCriteria($ugid);
    $c->threadId = $id;
    return static::fetchOneBy($c);
  }
  static function asCriteria($ugid) {
    $c = new static();
    $c->userGroupId = $ugid;
    $c->Client = new ClientStub();
    $c->MsgPosts = CriteriaJoin::optionalAsArray(new MsgPost());
    return $c;
  }
  //
  public function getHtmlBody() {
    $h = new Html();
    $h->h3($this->getLabel())
      ->add(DocMessage::getHtmlBody($this));
    return $h->out();
  }
  public function getLabel() {
    return "Subject: " . parent::getLabel();
  }
}