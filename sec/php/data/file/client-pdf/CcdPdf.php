<?php
require_once '_ClientPdfFile.php';
//
class CcdPdf extends ClientPdfFile {
  //
  public function setHeader(/*Client*/$client, $groupName = null) {
    $title = 'Clinical Care Document';
    return parent::setHeader($client, $title, null, $groupName); 
  }
  public function setBody(/*Client*/$client, /*ClinicalXml*/$xml, $demoOnly = false, $encounterLimit = 3) {
    $html = $xml->asHtml($client, $encounterLimit, $demoOnly);
    logit_r($html, 'html');  
    return parent::setBody($html);
  }
  public function setFilename(/*Client*/$client) {
    $filename = static::makeFilename_from($client);
    return parent::setFilename($filename);
  }
  //
  static function create(/*Client*/$client, /*ClinicalXml*/$xml, $groupName = null, $demoOnly = false) {
    $me = parent::create()
      ->setHeader($client, $groupName)
      ->setBody($client, $xml, $demoOnly)
      ->setFilename($client);
    return $me;
  }
  static function makeFilename_from($client) {
    return static::makeFilename('CCD', $client->clientId);
  }
  protected function getCss() {
    return <<<eos
H2 {margin-top:1em;margin-bottom:0.5em;}
H3 {margin-top:1em;margin-bottom:0.5em;}
CAPTION {margin-top:1em;margin-bottom:0.5em;text-align:left;font-size:16pt;font-weight:bold;}
TH {text-align:left;padding-left:0.2em;padding-right:0.2em;}
TD {text-align:left;padding-left:0.2em;padding-right:0.2em;}
eos;
  }
}
  
