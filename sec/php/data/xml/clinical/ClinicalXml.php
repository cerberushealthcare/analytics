<?php
require_once 'php/data/xml/_XmlRec.php';
//
abstract class ClinicalXml extends XmlRec {
  //
  static $TYPE;
  const TYPE_CCD = 1;
  const TYPE_CCR = 2;
  //
  abstract public function getDocId();
  //
  static function getDocType($xml) {
    if (strpos($xml, 'ContinuityOfCareRecord'))
      return static::TYPE_CCR;
    if (strpos($xml, 'ClinicalDocument'))
      return static::TYPE_CCD;
  }
}