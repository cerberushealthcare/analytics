<?php
require_once 'php/data/xml/ccd/ClinicalDocument.php';
require_once 'POCD_Qrda.php';
//
/**
 * @author Warren Hornsby
 */
class ClinicalDocument_Qrda extends ClinicalDocument {
  //
  protected $filename;
  protected $cat3;
  //
  static function asCategory1(/*CqmReport*/$cqm, /*Client*/$client) {
    $e = new static($cqm->UserGroup, $cqm->ErxUser, $client);
    $e->templateId = InfrastructureRoot_templateId::from(
      '2.16.840.1.113883.10.20.22.1.1', '2.16.840.1.113883.10.20.24.1.1', '2.16.840.1.113883.10.20.24.1.2');
    $e->code = new CE_LOINC('55182-0', 'Quality Measure Report');
    $e->setHeader('QRDA Category 1 Report');
    $e->recordTarget->patientRole->patient->raceCode = CD_Cqm_Supp::asRace($client->race);
    unset($e->recordTarget->patientRole->patient->raceCode->_xsi_type);
    $e->setMeasureSection($cqm);
    $e->setReportParamSection($cqm);
    $e->setPatientDataSection($cqm, $client);
    $e->setFilename_cat1($cqm, $client);
    return $e;
  }
  static function asCategory3(/*CqmReportSet*/$cqmset) {
    $e = new static($cqmset->getUserGroup(), $cqmset->getErxUser());
    $e->templateId = InfrastructureRoot_templateId::from(
      '2.16.840.1.113883.10.20.27.1.1');
    $e->code = new CE_LOINC('55184-6', '2.16.840.1.113883.6.1');
    $e->setHeader('QRDA Category 3 Calculated Summary Report');
    $e->setReportParamSection($cqmset);
    $e->setCat3MeasureSection($cqmset);
    $e->setFilename_cat3($cqmset);
    return $e;
  }
  public function __construct($group, $user, $client = null) {
    $this->client = $client;
    $this->userGroup = $group;
    $this->user = $user;
    $this->setNamespaces();
    $this->typeId = InfrastructureRoot_typeId::fromExtension('POCD_HD000040');
    $this->confidentialityCode = new CE_HL7_Confidentiality('N');
    $this->languageCode = CS_LanguageType::asUsEnglish();
    $this->effectiveTime = TS_EffectiveTime::fromNow();
    $this->component = Component2::asStructuredBody();
    if ($client == null) {
      $this->cat3 = true;
    }
  }
  public function getFilename() {
    return $this->filename;
  }
  protected function setFilename_cat1($cqm, $client) {
    $this->filename = $cqm::$CQM;
    $this->filename .= "-CAT1-" . $this->client->uid . ".xml";
  }  
  protected function setFilename_cat3($cqmset) {
    $this->filename = $cqmset->getTitle() . ".xml";
  }
  protected function isCat3() {
    return $this->cat3;
  }
  //
  public function setNamespaces() {
    $this->_xmlns = 'urn:hl7-org:v3';
    $this->_xmlns_xsi = 'http://www.w3.org/2001/XMLSchema-instance';
    $this->_xmlns_voc = 'urn:hl7-org:v3/voc';
    $this->_xmlns_sdtc = 'urn:hl7-org:sdtc';
  }
  public function setHeader($title) {
    $this->id = II::from(Guid::get());
    $this->realmCode = CD::from('US', null); 
    $this->title = ST::asText($title);
    $this->recordTarget = RecordTarget::from($this->client, $this->userGroup);
    $this->author = Author::from($this->user, $this->userGroup);
    $this->custodian = Custodian::from($this->userGroup);
    $this->legalAuthenticator = LegalAuthenticator::from($this->user, $this->userGroup);
  }
  public function setMeasureSection($cqm) {
    $this->component->structuredBody->add(new Section_QrdaMeasure($cqm));
  }
  public function setCat3MeasureSection($cqmset) {
    $this->component->structuredBody->add(new Section_QrdaCat3Measure($cqmset));
  }
  public function setReportParamSection($cqm) {
    $this->component->structuredBody->add(new Section_QrdaReportParams($cqm, $this->cat3));
  }
  public function setPatientDataSection($cqm, $client) {
    $cls = "Section_QrdaPatientData_" . $client->getReportNum();
    $this->component->structuredBody->add(new $cls($cqm, $client));
  }
  public function toXml($formatted = false) {
    return parent::toXml($formatted, 'ClinicalDocument');
  }
}
/** Sections_Qrda */
class Section_QrdaMeasure extends POCD_Section {
  //
  public function __construct($cqm) {
    $this->templateId = InfrastructureRoot_templateId::from(
      '2.16.840.1.113883.10.20.24.2.2', '2.16.840.1.113883.10.20.24.2.3');
    $this->code = new CE_LOINC('55186-1');
    $this->title = ST::asText('Measure Section');
    $this->text = Text_QrdaMeasure::from($cqm);
    $this->add(Entry_Qrda::asMeasure($cqm));
  }
}
class Section_QrdaCat3Measure extends POCD_Section {
  //
  public function __construct($cqmset) {
    $this->templateId = InfrastructureRoot_templateId::from(
      '2.16.840.1.113883.10.20.24.2.2', '2.16.840.1.113883.10.20.27.2.1');
    $this->code = new CE_LOINC('55186-1');
    $this->title = ST::asText('Measure Section');
    $this->text = Text_QrdaMeasure::fromSet($cqmset);
    foreach ($cqmset->cqms as $cqm) {
      $this->add(Entry_Qrda::asCat3Measure($cqm));
    }
  }
}
class Text_QrdaMeasure extends Text {
  //
  public function __construct() {
    $this->table = new TextTable(
      'eMeasure Title', 'Version Neutral Identifier', 'eMeasure Version Number', 'NQF eMeasure Number', 'Version Specific Identifier');
  }
  static function fromSet($cqmset) {
    $me = new static();
    foreach ($cqmset->cqms as $cqm) {
      $me->add($cqm);
    }
    return $me;
  }
  static function from($cqm) {
    $me = new static();
    $me->add($cqm);
    return $me;
  }
  protected function add($cqm) {
    $this->table->add($cqm->title, $cqm->setid, $cqm->version, $cqm->nqf, $cqm->id);
  }
}
class Section_QrdaReportParams extends POCD_Section {
  //
  public function __construct($cqm, $asCat3) {
    if ($asCat3) {
      $this->templateId = InfrastructureRoot_templateId::from(
        '2.16.840.1.113883.10.20.17.2.1', '2.16.840.1.113883.10.20.27.2.2');
    } else {
      $this->templateId = InfrastructureRoot_templateId::from(
        '2.16.840.1.113883.10.20.17.2.1');
    }
    $this->id = II::random();
    $this->code = new CE_LOINC('55187-9');
    $this->title = ST::asText('Reporting Parameters');
    $this->text = new Text_QrdaReportParams($cqm);
    $this->add(Entry_Qrda::asReportParams($cqm));
  }
}
class Text_QrdaReportParams extends Text {
  //
  public function __construct($cqm) {
    $from = formatDate($cqm->from);
    $to = formatDate($cqm->to);
    $this->list = TextSimpleList::create("Reporting period: $from - $to");
  }
}
class Section_QrdaPatientData extends POCD_Section {
  //
  public function __construct($cqm, $client) {
    $this->templateId = InfrastructureRoot_templateId::from(
      '2.16.840.1.113883.10.20.17.2.4', '2.16.840.1.113883.10.20.24.2.1');
    $this->code = new CE_LOINC('55188-7');
    $this->title = ST::asText('Patient Data');
    $this->text = new Text();
  }
}
/** Depression */
class Section_QrdaPatientData_002 extends Section_QrdaPatientData {
  //
  public function __construct($cqm, $client) {
    parent::__construct($cqm, $client);
    $this->add(Entry_Qrda_Data::asEncounterPerformed($client->Encounter, '2.16.840.1.113883.3.600.1916'));
    if ($client->has('Screen')) { 
      $this->add(Entry_Qrda_Data::asRiskCategoryAssessment($client->Screen));
    }
    foreach (gets($client, 'Interventions') as $proc) { 
      $this->add(Entry_Qrda_Data::asInterventionPerformed($proc));
    }  
    if ($client->has('Depression')) { 
      $this->add(Entry_Qrda_Data::asDiagnosisActive($client->Depression, '2.16.840.1.113883.3.600.145'));
    }
    if ($client->has('Bipolar')) { 
      $this->add(Entry_Qrda_Data::asDiagnosisActive($client->Bipolar, '2.16.840.1.113883.3.600.450'));
    }
  }
}
/** Specialist Report */
class Section_QrdaPatientData_050 extends Section_QrdaPatientData {
  //
  public function __construct($cqm, $client) {
    parent::__construct($cqm, $client);
    $this->add(Entry_Qrda_Data::asEncounterPerformed($client->Encounter));
    foreach ($client->Referrals as $proc) { 
      $this->add(Entry_Qrda_Data::asInterventionPerformed($proc));
    }  
    foreach (gets($client, 'Reports') as $proc) { 
      $this->add(Entry_Qrda_Data::asCommunication($proc));
    }  
  }
}
/** Current Meds */
class Section_QrdaPatientData_068 extends Section_QrdaPatientData {
  //
  public function __construct($cqm, $client) {
    parent::__construct($cqm, $client);
    foreach ($client->Encounters as $proc) {
      $this->add(Entry_Qrda_Data::asEncounterPerformed($proc, '2.16.840.1.113883.3.600.1.1834'));
    }
    foreach (gets($client, 'MedsDocs') as $proc) { 
      $this->add(Entry_Qrda_Data::asProcedurePerformed($proc));
    }  
  }
}
/** BMI */
class Section_QrdaPatientData_069 extends Section_QrdaPatientData {
  //
  public function __construct($cqm, $client) {
    parent::__construct($cqm, $client);
    foreach ($client->Encounters as $proc) {
      $this->add(Entry_Qrda_Data::asEncounterPerformed($proc, '2.16.840.1.113883.3.600.1.1751'));
    } 
    foreach (gets($client, 'Bmis') as $proc) {
      $this->add(Entry_Qrda_Data::asPhysicalExamPerformed($proc));
    }
    foreach (gets($client, 'AboveInts') as $proc) {
      $this->add(Entry_Qrda_Data::asProcedurePerformed($proc));
    }
    foreach (gets($client, 'BelowInts') as $proc) {
      $this->add(Entry_Qrda_Data::asProcedurePerformed($proc));
    }
    foreach (gets($client, 'AboveMeds') as $med) {
      $this->add(Entry_Qrda_Data::asMedicationOrder($med, '2.16.840.1.113883.3.600.1.1498'));
    }
    foreach (gets($client, 'BelowMeds') as $med) {
      $this->add(Entry_Qrda_Data::asMedicationOrder($med, '2.16.840.1.113883.3.600.1.1499'));
    }
    if ($client->has('Preg')) {
      $this->add(Entry_Qrda_Data::asDiagnosisActive($client->Preg, '2.16.840.1.113883.3.600.1.1623'));
    }
  }
}
/** Complex Chronic */
class Section_QrdaPatientData_090 extends Section_QrdaPatientData {
  //
  public function __construct($cqm, $client) {
    parent::__construct($cqm, $client);
    $this->add(Entry_Qrda_Data::asEncounterPerformed($client->Encounter1));
    $this->add(Entry_Qrda_Data::asEncounterPerformed($client->Encounter2));
    $this->add(Entry_Qrda_Data::asDiagnosisActive($client->HeartFailure, '2.16.840.1.113883.3.526.3.376'));
    foreach (gets($client, 'FuncStats') as $proc) {
      $this->add(Entry_Qrda_Data::asFunctionalStatusResult($proc));
    }
    foreach (gets($client, 'Dementia') as $diag) { 
      $this->add(Entry_Qrda_Data::asDiagnosisActive($diag, '2.16.840.1.113883.3.526.3.1025'));
    }  
    foreach (gets($client, 'Cancer') as $diag) { 
      $this->add(Entry_Qrda_Data::asDiagnosisActive($diag, '2.16.840.1.113883.3.464.1003.108.12.1011'));
    }  
  }
}
/** Tobacco */
class Section_QrdaPatientData_138 extends Section_QrdaPatientData {
  //
  public function __construct($cqm, $client) {
    parent::__construct($cqm, $client);
    foreach ($client->Encounters as $proc) {
      $this->add(Entry_Qrda_Data::asEncounterPerformed($proc));
    }
    foreach (gets($client, 'RiskAss') as $proc) {
      $this->add(Entry_Qrda_Data::asRiskCategoryAssessment($proc));
    }  
    foreach (gets($client, 'User') as $proc) {
      $this->add(Entry_Qrda_Data::asTobaccoUse($proc, '2.16.840.1.113883.3.526.3.1170'));
    }    
    foreach (gets($client, 'NonUser') as $proc) {
      $this->add(Entry_Qrda_Data::asTobaccoUse($proc, '2.16.840.1.113883.3.526.3.1189'));
    }    
    foreach (gets($client, 'CessInt') as $proc) { 
      $this->add(Entry_Qrda_Data::asInterventionPerformed($proc));
    }  
    foreach (gets($client, 'CessMed') as $med) { 
      $this->add(Entry_Qrda_Data::asMedicationOrder($med, '2.16.840.1.113883.3.526.3.1190'));
    }  
    foreach (gets($client, 'LimitLife') as $diag) { 
      $this->add(Entry_Qrda_Data::asDiagnosisActive($diag, '2.16.840.1.113883.3.526.3.1259'));
    }  
  }
}
/** High-Risk Meds */
class Section_QrdaPatientData_156 extends Section_QrdaPatientData {
  //
  public function __construct($cqm, $client) {
    parent::__construct($cqm, $client);
    $this->add(Entry_Qrda_Data::asEncounterPerformed($client->Encounter));
    foreach (gets($client, 'Meds') as $med) {
      $this->add(Entry_Qrda_Data::asMedicationOrder($med, '2.16.840.1.113883.3.464.1003.196.12.1253'));
    }
    foreach (gets($client, 'Meds90Days') as $med) {
      $this->add(Entry_Qrda_Data::asMedicationOrder($med, '2.16.840.1.113883.3.464.1003.196.12.1254'));
    }
  }
}
/** High BP */
class Section_QrdaPatientData_165 extends Section_QrdaPatientData {
  //
  public function __construct($cqm, $client) {
    parent::__construct($cqm, $client);
    foreach ($client->Encounters as $proc) {
      $this->add(Entry_Qrda_Data::asEncounterPerformed($proc));
    }
    $this->add(Entry_Qrda_Data::asDiagnosisActive($client->Hyper, '2.16.840.1.113883.3.464.1003.104.12.1011'));
    if ($client->has('Preg')) {
      $this->add(Entry_Qrda_Data::asDiagnosisActive($client->Preg, '2.16.840.1.113883.3.526.3.378'));
    }
    if ($client->has('Renal')) {
      $this->add(Entry_Qrda_Data::asDiagnosisActive($client->Renal, '2.16.840.1.113883.3.526.3.353'));
    }
    if ($client->has('Kidney')) {
      $this->add(Entry_Qrda_Data::asDiagnosisActive($client->Kidney, '2.16.840.1.113883.3.526.3.1002'));
    }
    if ($client->has('Diastolic')) {
      $this->add(Entry_Qrda_Data::asPhysicalExamPerformed($client->Diastolic));
    }
    if ($client->has('Systolic')) {
      $this->add(Entry_Qrda_Data::asPhysicalExamPerformed($client->Systolic));
    }
    foreach (gets($client, 'ProcExclu') as $proc) {
      $this->add(Entry_Qrda_Data::asProcedurePerformed($proc));
    }  
    foreach (gets($client, 'EncExclu') as $proc) {
      $this->add(Entry_Qrda_Data::asasEncounterPerformed($proc, '2.16.840.1.113883.3.464.1003.109.12.1014'));
    }  
    foreach (gets($client, 'IntExclu') as $proc) {
      $this->add(Entry_Qrda_Data::asInterventionPerformed($proc));
    }
  }
}
/** Imaging Back Pain*/
class Section_QrdaPatientData_166 extends Section_QrdaPatientData {
  //
  public function __construct($cqm, $client) {
    parent::__construct($cqm, $client);
    foreach ($client->Encounters as $proc) {
      $this->add(Entry_Qrda_Data::asEncounterPerformed($proc));
    }
    $this->add(Entry_Qrda_Data::asDiagnosisActive($client->BackPain, '2.16.840.1.113883.3.464.1003.113.12.1001'));
    foreach (gets($client, 'BackPains') as $diag) {
      $this->add(Entry_Qrda_Data::asDiagnosisActive($diag, '2.16.840.1.113883.3.464.1003.113.12.1001'));
    }  
    foreach (gets($client, 'Neuro') as $diag) {
      $this->add(Entry_Qrda_Data::asDiagnosisActive($diag, '2.16.840.1.113883.3.464.1003.114.12.1012'));
    }  
    foreach (gets($client, 'Trauama') as $diag) {
      $this->add(Entry_Qrda_Data::asDiagnosisActive($diag, '2.16.840.1.113883.3.464.1003.113.12.1036'));
    }  
    foreach (gets($client, 'Drug') as $diag) {
      $this->add(Entry_Qrda_Data::asDiagnosisActive($diag, '2.16.840.1.113883.3.464.1003.106.12.1003'));
    }  
    foreach (gets($client, 'Neuro') as $diag) {
      $this->add(Entry_Qrda_Data::asDiagnosisActive($diag, '2.16.840.1.113883.3.464.1003.114.12.1012'));
    }  
  }
}
