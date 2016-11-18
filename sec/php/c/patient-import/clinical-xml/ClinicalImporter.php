<?php
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
  
	/*Open the clinical-import folder in the root (?) and upload the xml file.
	
	$file will be a GroupFile object (defined in data/rec/group-folder/GroupFolder.php:195)
	*/
  
    $file = GroupFolder_ClinicalImport::open()->upload(); 
	
	
	/*
		If we're gonna call import, we need to have $file be a valid GroupFile object with the folder and filename properties defined.
		
		This should work:
		
		global $login; //defined in GroupFolder_ClinicalImport.php
		$file = GroupFile::from(GroupFolder::open($login->userGroupId, 'path/to/xml'), 'path/to/xml/file/file.xml');
		return static::import($file);
		
		'path/to/xml' is by default defined as 'clinical-import' in our script call.
	*/
    return static::import($file); //Import the file to the database.
  }
  /** Import previous upload to existing patient */
  static function /*ClinicalImport*/import_asUpdate($filename, $cid) {
    $file = GroupFolder_ClinicalImport::open()->getFile($filename);
    return static::import($file, $cid);
  }
  protected static function import($file, $cid = null, $uploadId = null) {
    $ci = ClinicalImport::from($file);
    try {
      $cid = $ci->import($cid, $uploadId);
      Scanning::saveClinicalXml($cid, $file->filename);
      Proc_CcdImported::record($cid);
    } catch (DuplicateNameBirth $e) {
      $ci->Client = $e->data;
      throw new DupeImportPatient($ci);
    }
    return $ci;
  }
  
   static function importFromFile($file, $cid = null, $uploadId = null) {
	//Logger::debug('clinicalImporter::importFromFile: Got file ' . var_dump($file));
    $ci = ClinicalImport::fromFile($file);
    try {
      $cid = $ci->import($cid, $uploadId);
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
  
  static function fromFile(/*ClinicalFile*/$file, $cid = null) {
    $xml = $file->getContent();
    $filename = $file->getFilename();
    $type = ClinicalXml::getDocType($xml);
    switch ($type) {
      case ClinicalXml::TYPE_CCD:
        return ClinicalImport_Ccd::create(706, $filename, $xml); //706 = group ID of the user that we defined that will do the import. We need this to match. McKinley
      case ClinicalXml::TYPE_CCR:
        return ClinicalImport_Ccr::create(1, $filename, $xml);
    }
    if ($type == ClinicalXml::TYPE_CCR)
      return ClinicalImport_Ccr::create(1, $filename, $xml);
    else
      throw new XmlParseException('Clinical document type not recognized.');
  }
  //
  public function import($cid = null, $uploadId = null) {
    $client = static::getClient($cid);
    //throw new RuntimeException('Something terrible happened in php/c/patient-import/clinicalXML/ClinicalImporter.php. This is a test.');
    
    if ($uploadId) $client->uploadId = $uploadId;
    $this->Client = $client;
    //$this->Address->tableId = $this->Client->lastName . ' TEST';
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
