<?php
//error_reporting(E_ALL); ini_set('display_errors', '1');
require_once 'ClinicalImport_Sql.php';
require_once 'ClinicalImport_Ccd.php';
require_once 'ClinicalImport_Ccr.php';
require_once 'php/data/rec/group-folder/GroupFolder_ClinicalImport.php';
require_once 'php/data/xml/clinical/ccd/ContinuityCareDocument.php';
require_once 'php/data/xml/clinical/ccr/ContinuityCareRecord.php';
require_once 'php/data/rec/sql/Scanning.php';
require_once 'php/data/rec/sql/Procedures_Admin.php';
//
/**
 * Clinical Data Import
 * @author Warren Hornsby
 */
class ClinicalImporter {
  //
  /** Import new patient from uploaded XML */
  static function /*ClinicalImport*/import_asUpload() {
    $file = GroupFolder_ClinicalImport::open()->upload();
    return static::import($file);
  }
  /** Import previous upload to existing patient */
  static function /*ClinicalImport*/import_asUpdate($filename, $cid) {
    $file = GroupFolder_ClinicalImport::open()->getFile($filename);
    return static::import($file, $cid);
  }
  protected static function import($file, $cid = null) {
    $ci = ClinicalImport::from($file);
    try {
      $cid = $ci->import($cid);
      Scanning::saveClinicalXml($cid, $file->filename);
      Proc_CcdImported::record($cid);
    } catch (DuplicateNameBirth $e) {
      $ci->Client = $e->data;
      throw new DupeImportPatient($ci);
    }
    return $ci;
  }
}
//
class ClinicalImportPreview extends Rec {
  //
  public $filename;
  public $html/*XSLT-transformation*/;
  //
  static function from($file) {

  }
}//
class ClinicalImport extends BasicRec {
  //
  public $ugid;
  public $filename;
  public $type;
  public /*ClinicalXml*/$Xml;
  public /*Client_Ci*/$Client;
  //
  static function from(/*ClinicalFile*/$file, $cid = null) {
    global $login;
    $xml = $file->getContent();
    $filename = $file->getFilename();
    $type = ClinicalXml::getDocType($xml);
    switch ($type) {
      case ClinicalXml::TYPE_CCD:
        return ClinicalImport_Ccd::create($login->userGroupId, $filename, $xml);
      case ClinicalXml::TYPE_CCR:
        return ClinicalImport_Ccr::create($login->userGroupId, $filename, $xml);
    }
    if ($type == ClinicalXml::TYPE_CCR)
      return ClinicalImport_Ccr::create($login->userGroupId, $filename, $xml);
    else
      throw new XmlParseException('Clinical document type not recognized.');
  }
  //
  public function import($cid = null) {
    $client = static::getClient($cid);
    $this->Client = $client;
    $client->saveDemo($this->Xml);
    $client->import($this->Xml);
    return $client->clientId;
  }
  public function getDiags() {
    $client = static::getClient();
    $recs = $client->getDiags($this->Xml);
    return Rec::sort($recs, new RecSort('text'));
  }
  public function getMeds() {
    $client = static::getClient();
    $recs = $client->getMeds($this->Xml);
    return Rec::sort($recs, new RecSort('name'));
  }
  public function getAllergies() {
    $client = static::getClient();
    $recs = $client->getAllergies($this->Xml);
    return Rec::sort($recs, new RecSort('agent'));
  }
  protected static function create($ugid, $filename, $type, $Xml) {
    $me = new static();
    $me->ugid = $ugid;
    $me->filename = $filename;
    $me->type = $type;
    $me->Xml = $Xml;
    return $me;
  }
}
class ClinicalImport_Ccd extends ClinicalImport {
  //
  public function getClient($cid = null) {
    return $cid ? Client_Ci_Ccd::fetch($cid) : Client_Ci_Ccd::create($this->ugid);
  }
  //
  protected static function create($ugid, $filename, $xml) {
    $ccd = ContinuityCareDocument::fromXml($xml);
    return parent::create($ugid, $filename, ClinicalXml::TYPE_CCD, $ccd);
  }
}
class ClinicalImport_Ccr extends ClinicalImport {
  //
  public function getClient($cid = null) {
    return $cid ? Client_Ci_Ccr::fetch($cid) : Client_Ci_Ccr::create($this->ugid);
  }
  //
  protected static function create($ugid, $filename, $xml) {
    return parent::create($ugid, $filename, ClinicalXml::TYPE_CCR, ContinuityCareRecord::fromXml($xml));
  }
}
class DupeImportPatient extends DupeException {
  public function __construct($ci) {
    $dupe = $ci->Client;
    $html = "This document matches a patient with the same last name and birth:<br/><br/>ID: <b>$dupe->uid</b><br/>Name: <b>" . $dupe->getFullName() . "</b><br/>DOB: <b>" . formatDate($dupe->birth) . "</b><br><br>Continue with import?";
    $this->message = $html;
    $this->data = $ci;
  }
}
