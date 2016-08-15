<?php
require_once 'php/data/pdf/_PdfHtmlRec.php';
//
class ScanIndex_Pdf extends PdfHtmlRec {
  //
  public function getPdfFilename() {
    return static::makeFilename('SI', $this->clientId, $this->scanIndexId);
  }
  public function getPdfTitle() {
    return $this->_rec->getLabel();
  }
  public function getPdfHeader_client() {
    return $this->Client;
  }
  public function getPdfHeader_dos() {
    return $this->datePerformed;
  }
  public function getPdfBody() {
    $h = new Html();
    foreach ($this->ScanFiles as $file) 
      $h->p_()->img($this->getImgSrc($file))->_();
    return $h->out();
  }
  protected function getImgSrc($file) {
    global $login;
    $url = MyEnv::url($file->getSrc() . '&sess=' . $login->sessionId);
    return $url;
  }
}
