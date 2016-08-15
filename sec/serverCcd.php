<?php
ini_set('memory_limit', '1024M');
require_once 'server.php';
require_once 'php/data/xml/ccd/ClinicalDocument.php';
require_once 'php/c/facesheets/Facesheets.php';
require_once 'php/data/rec/group-folder/GroupFolder_Ccd.php';
require_once 'php/data/rec/group-folder/GroupFolder_BatchCcda.php';
require_once 'php/data/file/client-pdf/CcdPdf.php';
require_once 'php/data/rec/sql/Procedures_Admin_MU2.php';
//
try { 
  LoginSession::verify_forServer()->requires($login->Role->Artifact->hl7);
  switch ($action) {
    case 'get':
      $id = $obj->id;
      $password = getr($obj, 'pw');
      $custmap = getr($obj, 'custmap');
      $visit = ($custmap) ? 1 : 0;
      $ccda = ClinicalDocument::fetch($id, $visit, $custmap);
      if ($ccda == null)
        throw new Exception("Unable to build CCD for patient $id"); 
      $folder = GroupFolder_Ccd::open();
      $file = $folder->save($ccda, $password);
      AjaxResponse::out($action, $file);
      exit;
    case 'getSectionTexts':
      $ccda = ClinicalDocument::fetch_asVisitSummary($id);
      $obj = $ccda->getSectionTexts();
      AjaxResponse::out($action, $obj);
      exit;
    case 'getDiags':
    case 'getMeds':
    case 'getAllergies':
      require_once 'php/data/rec/sql/Scanning.php';
      require_once 'php/c/patient-import/clinical-xml/ClinicalImporter.php';
      $scan = ScanIndex_Xml::fetch($id);
      $file = $scan->getGroupFile();
      $ci = ClinicalImport::from($file);
      if ($action == 'getDiags')
        $recs = $ci->getDiags();
      else if ($action == 'getMeds')
        $recs = $ci->getMeds();
      else 
        $recs = $ci->getAllergies();
      AjaxResponse::out($action, $recs);
      exit;
    case 'get_deprecated':
      $password = geta($_GET, 'pw');
      $ccd = HL7_ClinicalDocuments::buildFull($id);
      if ($ccd == null)
        throw new Exception("Unable to build CCD for patient $id"); 
      $folder = GroupFolder_Ccd::open();
      $file = $folder->save($ccd, $password);
      AjaxResponse::out($action, $file);
      exit;
    case 'clearBatch':
      require_once 'php/cbat/ccda-batch/CcdaBatcher.php';
      CcdaBatcher::clear();
      AjaxResponse::out($action, null);
      exit;
    case 'batchSync':
      require_once 'php/cbat/ccda-batch/CcdaBatcher.php';
      $batch = CcdaBatcher::start(true);
      AjaxResponse::out($action, $batch);
      exit;
    case 'batchDownload':
      $folder = GroupFolder_BatchCcda::open();
      $folder->download($id);
      exit;
    case 'download':
      $cid = $_GET['cid'];
      $visit = $_GET['visit'];
      $folder = GroupFolder_Ccd::open();
      $folder->download($id);
      if ($visit) {
        $date = nowShortNoQuotes();
        ProcMu2_summaryToPatient::record($cid, $date, null, null, 1);
        logit_r('555 record summary to patient');
      } else {
        Proc_SummaryDownloaded::record($cid);
      }
      exit;
    case 'refuse':
      ProcMu2_summaryRefused::record($id);
      exit;
    case 'print':
      require_once 'php/data/xml/ClinicalXmls.php';
      $visit = $obj->visit;
      $client = Client::fetchWithDemo($obj->cid);
      $file = GroupFile_Ccd::from($obj->filename);
      $xml = ClinicalXmls::parse($file->readContents());
      Auditing::logPrintFacesheet($obj->cid, $obj->filename);
      if ($visit) {
        logit_r('123 cid=' . $obj->cid);
        $date = nowShortNoQuotes();
        ProcMu2_summaryToPatient::record($obj->cid, $date, null, null, 1);
      }
      $ccd = CcdPdf::create($client, $xml, null, $obj->demoOnly);
      $ccd->download();
      exit;
    case 'printdebug':
      require_once 'php/data/xml/ClinicalXmls.php';
      $obj = new stdClass();
      $obj->cid = 15796;
      $obj->filename = "Hornsby_15796_CCD.xml";
      $client = Client::fetchWithDemo($obj->cid);
      $file = GroupFile_Ccd::from($obj->filename);
      $xml = ClinicalXmls::parse($file->readContents());
      $ccd = CcdPdf::create($client, $xml);
      $ccd->download();
      exit;
  }
} catch (Exception $e) {
  AjaxResponse::exception($e);
}
  