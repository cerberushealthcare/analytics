<?php
require_once 'php/data/xml/ccd/ClinicalDocument.php';
require_once 'php/c/facesheets/Facesheets.php';
//
/**
 * HL7 CCD Accessor
 * @author Warren Hornsby
 */
class HL7_ClinicalDocuments {
  //
  static function /*ClinicalDocument*/buildFull(/*int*/$cid) {
    $fs = Facesheet_Hl7Ccd::from($cid);
    if ($fs)
      return static::build($fs);
  }
  /*
  static function buildDos($cid, $dos) {
    $fs = Facesheet_Hl7Ccd_Dos::from($cid, $dos);
    return static::build($fs);
  }
  */
  //
  protected static function build($fs) {
    $ugid = $fs->UserGroup->userGroupId;
    if ($ugid == 1 || $ugid == 3 || 
      $ugid == 3013/*salutopia1*/ || $ugid == 3017/*salutopia2*/ || $ugid == 3018/*salutopia3*/) {
      $fs->Client->ssn = $fs->Client->cdata1;
    } 
    $doc = new ClinicalDocument($fs->Client, $fs->UserGroup, $fs->ErxUser);
    $doc->setHeader('db734647-fc99-424c-a864-7e3cda82e703', 'Continuity of Care Document', null, null);
    if ($fs->Allergies)
      $doc->setAlertsSection($fs->Allergies);
    if ($fs->Immuns)
      $doc->setImmunsSection($fs->Immuns);
    if ($fs->Sessions)
      $doc->setEncountersSection($fs->Sessions, $fs->UserGroup);
    if ($fs->Meds)
      $doc->setMedsSection($fs->Meds);
    if ($fs->Diagnoses)
      $doc->setProblemsSection($fs->Diagnoses);
    if ($fs->Procs)
      $doc->setProcsSection($fs->Procs);
    if ($fs->Results)
      $doc->setResultsSection($fs->Results);
    if ($fs->Vitals)
      $doc->setVitalsSection($fs->Vitals);
    return $doc;
  } 
}