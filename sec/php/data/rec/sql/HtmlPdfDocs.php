<?php
require_once 'php/data/rec/sql/_SqlRec.php';
//
/**
 * HtmlPdf Documents
 * @author Warren Hornsby
 */
class HtmlPdfDocs {
  //
  /**
   * @param int $cid
   * @param string $dos
   * @param stdClass $out console.pendingOut (optional, to generate from console prior to signing)
   */
  static function createVisit($cid, $dos, $out) {
    
  }
}
class HtmlPdfDoc extends SqlRec {
  //
  public $docId;
  public $userGroupId;
  public $clientId;
  public $date;
  public $type;
  public $author;
  public $title;
  public $filename;
  public $head;
  public $body;
  public $creator;
  public $dateCreated;
  //
  public function getSqlTable() {
    return 'html_pdf_docs';
  }
  public function setHeader($lines) {
    $this->head = self::_toHtml($lines);
  }
  public function setBody($lines) {
    $this->body = self::_toHtml($lines);
  }
  //
  static function fetchFor($ugid, $cid) {
    $c = new static();
    $c->userGroupId = $ugid;
    $c->clientId = $cid;
    return self::fetchAllBy($c);
  }
  //
  private static function _toHtml($lines) {
    return implode('<br/>', $lines);
  }
}
class HtmlPdfDoc_Visit extends HtmlPdfDoc {
  //
  //static function from()
}