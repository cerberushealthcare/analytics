<?php
require_once 'php/data/file/File.php';
require_once 'php/pdf/PdfM.php';
require_once 'php/data/Html.php';
//
abstract class PdfFile extends FileSpec {
  //
  static $FILENAME;
  static $BASEPATH;  // leave null to default to same folder as class
  //
  public /*string(html)*/$header;  
  public /*string(html)*/$body;  
  public /*bool*/$paging;
  //
  public function setHeader($header) {  // if used, must be prior to setBody()
    $this->header = $header;
    return $this;
  }
  public function setBody($body) {
    $this->body = $body;
    return $this;
  }
  public function withPaging() {
    $this->paging = true;
    return $this;
  }
  public function save() {
    $pdf = $this->buildPdf();
    $pdf->save(static::getFilename());
    return $this;
  }
  public function download() {
    $pdf = $this->buildPdf();
    $pdf->download(static::$FILENAME);
  }
  protected function buildPdf() {
    $pdf = PdfM_Factory::createMine();
    if ($this->header) 
      $pdf->setHeader($this->header);
    $pdf->setBody($this->body);
    if ($this->paging)
      $pdf->withPaging();
    return $pdf;
  }
  //
  static function create($body = null, $header = null) {
    $me = new static();
    if ($header) 
      $me->setHeader($header);
    if ($body)
      $me->setBody($body);
    return $me;
  }
  protected static function makeFilename() {
    $args = $args = func_get_args();
    $filename = implode('_', $args) . '.pdf';
    return $filename;
  }
}
