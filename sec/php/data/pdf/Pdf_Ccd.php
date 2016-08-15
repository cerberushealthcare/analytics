<?php
require_once 'php/data/rec/sql/_ClientRec.php';
require_once 'php/data/pdf/_PdfHtmlRec.php';
require_once 'php/data/rec/group-folder/GroupFolder_Ccd.php';
require_once 'php/data/xml/ClinicalXmls.php';
//
class Pdf_Ccd extends PdfHtmlRec {
  //
  /**
   * @param string $filename
   * @param int $cid
   */
  static function from($filename, $cid) {
    $file = GroupFile_Ccd::from($filename);
    $xml = ClinicalXmls::parse($file->readContents());
    $rec = ClientStub::fetch($cid);  
    $rec->filename = $filename;
    $rec->xml = $xml;
    $me = parent::fromRec($rec);
    return $me;
  }
  //
  public function getFilename($rec) {
    return static::makeFilename('CCD', $rec->clientId);
  }
  public function getTitle($rec) {
    return 'Clinical Care Document';
  }
  public function getHeader_client($rec) {
    return $rec;
  }
  public function getBody($rec) {
    return $rec->xml->asHtml($rec->clientId);
  }
}
