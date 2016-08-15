<?php
require_once 'php/data/file/_PdfHtmlFile.php';
require_once 'php/data/rec/sql/Documentation.php';
//
class VisitSummaryPdf extends PdfHtmlFile {
  //
  public function setHeader(/*VisitSum*/$rec, $addPrintDate = false) {
    $html = $rec->finalHead;
    if ($addPrintDate)
      $html .= "Date Printed: " . formatNowTimestamp() . "<br>";
    return parent::setHeader($html);
  }
  public function setBody(/*VisitSum*/$rec) {
    $html = DocVisitSum::getHtmlBody($rec);
    return parent::setBody($html);
  }
  public function setFilename(/*VisitSum*/$rec) {
    $filename = static::makeFilename('Summary', $rec->clientId, $rec->finalId);
    return parent::setFilename($filename);
  }
  //
  static function create(/*VisitSum*/$rec, $addPrintDate = false) {
    $me = parent::create()
      ->withPaging()
      ->setHeader($rec, $addPrintDate)
      ->setBody($rec)
      ->setFilename($rec);
    return $me;
  }
  static function create_asReprint($rec) {
    return static::create($rec, true);
  }
}
