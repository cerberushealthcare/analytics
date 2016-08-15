<?php
require_once 'php/data/file/_File.php';
//
class ClinicalFile0 extends File {
  //
  static function fetch($filename) {
    $me = parent::fetch($filename);
    $xml = $me->getContent();
    return $xml;
  }
}
class CcdFile extends ClinicalFile0 {
  //
  static function fetch() {
    return parent::fetch('ccd.xml');
  }
}
class CcrFile extends ClinicalFile0 {
  //
  static function fetch() {
    return parent::fetch('ccr.xml');
  }
}
class CcdaFile extends ClinicalFile0 {
  //
  static function fetch() {
    return parent::fetch('CCDA_Ambulatory.xml');
  }
}