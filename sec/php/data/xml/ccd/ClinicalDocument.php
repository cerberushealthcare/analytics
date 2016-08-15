<?php
require_once 'php/data/xml/_XmlRec.php';
require_once 'php/data/xml/ccd/POCD_MT000040.php';
require_once 'php/data/xml/ccd/datatypes_base.php';
require_once 'php/data/xml/ccd/Section_Types.php';
require_once 'php/c/facesheets/Facesheets.php';
//
/**
 * Clinical Document 
 * @author Warren Hornsby 
 */
class ClinicalDocument extends XmlRec {
  //
  public /*NullFlavor*/ $_nullFlavor;
  public /*ActClinicalDocument*/ $_classCode;
  public /*ActMood*/ $_moodCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId; //REQ
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*II*/ $id; //REQ
  public /*CE*/ $code; //REQ
  public /*ST*/ $title;
  public /*TS*/ $effectiveTime; //REQ
  public /*CE*/ $confidentialityCode; //REQ
  public /*CS*/ $languageCode;
  public /*II*/ $setId;
  public /*INT*/ $versionNumber;
  public /*TS*/ $copyTime;
  public /*RecordTarget[]*/ $recordTarget; //REQ
  public /*Author[]*/ $author; //REQ
  public /*DataEnterer*/ $dataEnterer;
  public /*Informant12[]*/ $informant;
  public /*Custodian*/ $custodian; //REQ
  public /*InformationRecipient[]*/ $informationRecipient;
  public /*LegalAuthenticator*/ $legalAuthenticator;
  public /*Authenticator[]*/ $authenticator;
  public /*Participant1[]*/ $participant;
  public /*InFulfillmentOf[]*/ $inFulfillmentOf;
  public /*DocumentationOf[]*/ $documentationOf;
  public /*RelatedDocument[]*/ $relatedDocument;
  public /*Authorization[]*/ $authorization;
  public /*Component1*/ $componentOf;
  public /*Component2*/ $component; //REQ
  //
  protected $client;
  protected $userGroup;
  protected $user;
  //
  static /*CustMap*/$CustMap;
  //
  static function fetch($cid, $asVisitSummary = false, $custmap = null) {
    $fs = Facesheet_Ccda::fetch($cid, $asVisitSummary);
    $ccda = $asVisitSummary ? static::asCCDA_VisitSummary($fs, $custmap) : static::asCCDA($fs);
    //logit_r($ccda, 'ccda');
    return $ccda;
  }
  static function fetch_asVisitSummary($cid, $custmap = null) {
    return static::fetch($cid, true, $custmap);
  }
  static function asCCDA(/*Facesheet_Ccda*/$fs) {
    //logit_r('asCCDA');
    static::loadIds($fs);
    static::$CustMap = CustMap::create();
    //logit_r(static::$CustMap, 'custmap');
    $me = new static($fs->Client, $fs->UserGroup, $fs->ErxUser);
    $me->setHeader($fs, 'Summary of Care');
    $me->setAlertsSection($fs->Allergies);
    if (get($fs, 'ReasonForReferral'))
      $me->setReasonForReferralSection($fs->ReasonForReferral);
    $me->setImmunsSection($fs->Immuns);
    $me->setEncountersSection($fs);
    $me->setMedsSection($fs->Meds);
    $me->setProblemsSection($fs->Diagnoses);
    $me->setProcsSection($fs->Procs, $fs->UserGroup->userGroupId);
    $me->setResultsSection($fs->Results);
    $me->setVitalsSection($fs->Vitals);
    $me->setPlanOfCareSection($fs);
    $me->setSocialSection($fs->UserGroup->userGroupId, $fs->Smoking);
    $me->setFunctionalSection($fs->FuncStatus);
    $me->setInstructsSection($fs->Instructs, $fs->InstructsPda);
    return $me;
  }
  static function asCCDA_VisitSummary(/*Facesheet_Ccda*/$fs, $custmap = null) {
    //logit_r('asVisitSummary');
    static::loadIds($fs);
    static::$CustMap = CustMap::create($custmap);
    $me = new static($fs->Client, $fs->UserGroup, $fs->ErxUser);
    $me->setHeader($fs, 'Visit Summary');
    $me->setAlertsSection($fs->Allergies);
    if (get($fs, 'ReasonForVisit'))
      $me->setReasonForVisitSection($fs->ReasonForVisit);
    $me->setImmunsSection($fs->Immuns);
    $me->setEncountersSection($fs);
    $me->setMedsSection($fs->Meds);
    $me->setMedsAdminSection($fs->MedsAdmin);
    $me->setProblemsSection($fs->Diagnoses);
    $me->setProcsSection($fs->Procs, $fs->UserGroup->userGroupId);
    $me->setResultsSection($fs->Results);
    $me->setVitalsSection($fs->Vitals);
    $me->setPlanOfCareSection($fs);
    $me->setSocialSection($fs->UserGroup->userGroupId, $fs->Smoking);
    $me->setFunctionalSection($fs->FuncStatus);
    $me->setInstructsSection($fs->Instructs, $fs->InstructsPda);
    return $me;
  }
  static function loadIds($fs) {
    if ($fs->Immun_HL7) {
      HL70292::loadIds($fs->Immun_HL7->CVX);
      HL70292_byName::loadIds($fs->Immun_HL7->CVXI);
      HL70227::loadIds($fs->Immun_HL7->MVX);
      NIP001::loadIds($fs->Immun_HL7->source);
      NIP002::loadIds($fs->Immun_HL7->refusal);
    }
  }
  /**
   * Construct with required defaults
   * @param Client $client
   * @param UserGroup $userGroup
   * @param ErxUser $user
   */
  public function __construct($client, $userGroup, $user) {
    $this->client = $client;
    $this->userGroup = $userGroup;
    $this->user = $user;
    $this->setNamespaces();
    $this->typeId = InfrastructureRoot_typeId::fromExtension('POCD_HD000040');
    $this->templateId = InfrastructureRoot_templateId::asCCDA();
    $this->code = new CE_LOINC('34133-9', 'Summarization of Episode Note');
    $this->confidentialityCode = new CE_HL7_Confidentiality('N');
    $this->languageCode = CS_LanguageType::asUsEnglish(); 
    $this->effectiveTime = TS_EffectiveTime::fromNow();
    $this->component = Component2::asStructuredBody();
  }
  public function getClient() {
    return $this->client;
  }
  /**
   * @return POCD.Patient 
   */
  public function getPatient() {
    return get_recursive($this, 'recordTarget.patientRole.patient');
  }
  /**
   * @return array(templateId => Section,..)
   */
  public function getSections() {
    static $sections;
    if ($sections == null) {
      $sections = array();
      $components = arrayify(get_recursive($this, 'component.structuredBody.component'));
      if ($components) {
        foreach ($components as $component) {
          $section = get($component, 'section');
          if ($section) {
            $id = $section->getTemplateId();
            $sections[$id] = $section;
          }
        }
      }
    }
    return $sections;
  }
  /**
   * @return {templateId:{title:'Section Title',text:Text_Section},..} 
   */
  public function getSectionTexts() {
    $sections = $this->getSections();
    $a = array();
    foreach ($sections as $tid => $section) {
      $s = new stdClass();
      $s->title = $section->title->_;
      $s->text = $section->text;
      $a[$tid] = $s;
    }
    return $a;
  }
  public function getSection($templateId) {
    $sections = $this->getSections();
    return geta($sections, $templateId->_root);
  }
  public function getSectionVitals() {
    return $this->getSection(InfrastructureRoot_templateId::asSectionVitals());
  }
  public function getSectionProcedures() {
    return $this->getSection(InfrastructureRoot_templateId::asSectionProcedures());
  }
  public function getSectionProblems() {
    return $this->getSection(InfrastructureRoot_templateId::asSectionProblems());
  }
  public function getSectionEncounters() {
    return $this->getSection(InfrastructureRoot_templateId::asSectionEncounters());
  }
  public function getSectionAlerts() {
    return $this->getSection(InfrastructureRoot_templateId::asSectionAlerts());
  }
  public function getSectionMeds() {
    return $this->getSection(InfrastructureRoot_templateId::asSectionMeds());
  }
  public function setHeader($fs, $title) {
    $this->id = II::from(Guid::get());
    $this->realmCode = CD::from('US', null); 
    $this->title = ST::asText($title);
    $this->recordTarget = RecordTarget::from($this->client, $this->userGroup);
    $this->author = Author::from($this->user, $this->userGroup);
    $this->custodian = Custodian::from($this->userGroup);
    $this->documentationOf = DocumentationOf::from($fs);  // $this->client, $this->user, $this->userGroup, $dateFrom, $dateTo);
  }
  /**
   * Add a participant to header
   * @param AssociatedEntity $entity e.g. AssociatedEntity::asGuardian()
   */
  public function addParticipant($entity) {
    $this->set('participant', Participant1::asIndirectTarget($entity));
  }
  /**
   * Medication section
   * @param Med[] $meds 
   */
  public function setMedsSection($meds) {
    $meds = static::$CustMap->filterMeds($meds);
    if (! empty($meds)) {
      $this->component->structuredBody->add(new Section_Meds($meds));
    }
  }
  /**
   * Medication section
   * @param Med[] $meds 
   */
  public function setMedsAdminSection($meds) {
    $meds = static::$CustMap->filterMedsAdmin($meds);
    if (! empty($meds)) {
      $this->component->structuredBody->add(new Section_MedsAdmin($meds));
    }
  }
  /**
   * Alerts section
   * @param Allergy[] $allers 
   */
  public function setAlertsSection($allers) {
    $allers = static::$CustMap->filterAllers($allers);
    if (! empty($allers)) {
      $this->component->structuredBody->add(new Section_Alerts($allers, $custom));
    }
  }
  /**
   * Vitals section
   * @param Vital[] $vitals
   */
  public function setVitalsSection($vitals) {
    $vitals = static::$CustMap->filterVitals($vitals);
    if (! empty($vitals)) 
      $this->component->structuredBody->add(new Section_Vitals($vitals));
  }
  /**
   * Immunizations section
   * @param Immun[] $immuns
   */
  public function setImmunsSection($immuns) {
    $immuns = static::$CustMap->filterImmuns($immuns);
    if (! empty($immuns)) 
      $this->component->structuredBody->add(new Section_Immuns($immuns));
  }
  /**
   * Procedures section
   * @param Proc[] $procs
   */
  public function setProcsSection($procs, $ugid) {
    $procs = static::$CustMap->filterProcs($procs);
    $this->component->structuredBody->add(new Section_Procedures($procs, $ugid));
  }
  //
  public function setPlanOfCareSection($fs) {
    static::$CustMap->filterPlanOfCare($fs);
    if ($fs->hasPlanOfCare())
      $this->component->structuredBody->add(new Section_PlanOfCare($fs));
  }
  public function setReasonForVisitSection($proc) {
    $proc = static::$CustMap->filterReasonForVisit($proc);
    if ($proc) 
      $this->component->structuredBody->add(new Section_ReasonForVisit($proc));
  }
  public function setReasonForReferralSection($trackItem) {
    if ($trackItem) 
      $this->component->structuredBody->add(new Section_ReasonForReferral($trackItem));
  }
  public function setFunctionalSection($procs) {
    $procs = static::$CustMap->filterFunctional($procs);
    if (! empty($procs)) 
      $this->component->structuredBody->add(new Section_FunctionalStatus($procs));
  }
  public function setSocialSection($ugid, $smoking) {
    $smoking = static::$CustMap->filterSocial($smoking);
    if (! empty($smoking)) 
      $this->component->structuredBody->add(new Section_Social($ugid, $smoking));
  }
  public function setInstructsSection($instructs, $pda) {
    //$instructs = static::$CustMap->filterInstructs($instructs);
    if (! empty($instructs) || ! empty($pda))
      $this->component->structuredBody->add(new Section_Instructions($instructs, $pda));
  }
  /**
   * Procedures section
   * @param Proc[] $procs
   */
  public function setResultsSection($procs) {
    $procs = static::$CustMap->filterResults($procs);    
    if (! empty($procs)) 
      $this->component->structuredBody->add(new Section_Results($procs));
  }
  /**
   * Encounters section
   */
  public function setEncountersSection($fs) {
    $procs = static::$CustMap->filterEncounters($fs->Encounters);
    if (! empty($procs)) 
      $this->component->structuredBody->add(new Section_Encounters($procs, $fs->UserGroup));
  }
  /**
   * Problems section
   * @param Diagnosis[] $diags 
   * @param CE $code (optional) @see CE_LOINC_ProblemContent
   */
  public function setProblemsSection($diags, $code = null) {
    $diags = static::$CustMap->filterProblems($diags);
    if (! empty($diags)) 
      $this->component->structuredBody->add(new Section_Problems($diags, $code));
  }
  //
  public function setNamespaces() {
    $this->_xmlns = 'urn:hl7-org:v3';
    $this->_xmlns_xsi = 'http://www.w3.org/2001/XMLSchema-instance';
  }
  public function toXml($formatted = false, $rootTagName = null, $includeEmpties = false, $noVersion = false, $noHyphenate = false) {
    $noVersion = true;
    return parent::toXml($formatted, $rootTagName, $includeEmpties, $noVersion, $noHyphenate);  
  }
}
class InfrastructureRoot_templateId extends II {
  public /*uid*/ $_root;
  public /*xs:boolean*/ $_unsorted;
  //
  static function asCCDA() {
    return array(
      new static('2.16.840.1.113883.10.20.22.1.1'),
      new static('2.16.840.1.113883.10.20.22.1.2'));   
  }
  static function asCCD() {
    return array(
      new static('2.16.840.1.113883.3.88.11.32.1'),
      new static('2.16.840.1.113883.10.20.1'),
      new static('1.3.6.1.4.1.19376.1.5.3.1.1.6'),
      new static('1.3.6.1.4.1.19376.1.5.3.1.1.2'),
      new static('1.3.6.1.4.1.19376.1.5.3.1.1.1'),
      new static('2.16.840.1.113883.10.20.3'));
  }
  static function asSectionAlerts() {
    return new static('2.16.840.1.113883.10.20.22.2.6.1');
  }
  static function asSectionResults() {
    return new static('2.16.840.1.113883.10.20.22.2.3.1');
  }
  static function asSectionSocial() {
    return new static('2.16.840.1.113883.10.20.22.2.17');
  }
  static function asSectionInstructions() {
    return new static('2.16.840.1.113883.10.20.22.2.45');
  }
  static function asSectionProblems() {
    return new static('2.16.840.1.113883.10.20.22.2.5.1');
  }
  static function asSectionEncounters() {
    return new static('2.16.840.1.113883.10.20.22.2.22');
  }
  static function asSectionImmuns() {
    return new static('2.16.840.1.113883.10.20.22.2.2.1');
  }
  static function asSectionMeds() {
    return new static('2.16.840.1.113883.10.20.22.2.1.1');
  }
  static function asSectionMedsAdmin() {
    return new static('2.16.840.1.113883.10.20.22.2.38');
  }
  static function asSectionProcedures() {
    return new static('2.16.840.1.113883.10.20.22.2.7.1');
  }
  static function asSectionPlanOfCare() {
    return new static('2.16.840.1.113883.10.20.22.2.10');
  }
  static function asSectionReasonForVisit() {
    return new static('2.16.840.1.113883.10.20.22.2.12');
  }
  static function asSectionReasonForReferral() {
    return new static('1.3.6.1.4.1.19376.1.5.3.1.3.1');
  }
  static function asSectionFunctionalStatus() {
    return new static('2.16.840.1.113883.10.20.22.2.14');
  }
  static function asSectionVitals() {
    return new static('2.16.840.1.113883.10.20.22.2.4');
  }
  static function asAlertObservation() {
    return new static('2.16.840.1.113883.10.20.22.4.7');
  }
  static function asCoverageActivity() {
    return new static('2.16.840.1.113883.10.20.1.20'); 
  }
  static function asEncounterActivity() {
    return new static('2.16.840.1.113883.10.20.1.21'); 
  }
  static function asMedActivity() {
    return new static('2.16.840.1.113883.10.20.22.4.16');
  }
  static function asImmun() {
    return new static('2.16.840.1.113883.10.20.22.4.52');
  }
  static function asProblemAct() {
    return new static('2.16.840.1.113883.10.20.22.4.3');
  }
  static function asProblemAlertAct() {
    return new static('2.16.840.1.113883.10.20.22.4.30');
  }
  static function asProblemObservation() {
    return new static('2.16.840.1.113883.10.20.22.4.4');
  }
  static function asPlanObservation() {
    return new static('2.16.840.1.113883.10.20.22.4.44');
  }
  static function asPlanProcedure() {
    return new static('2.16.840.1.113883.10.20.22.4.41');
  }
  static function asPlanEncounter() {
    return new static('2.16.840.1.113883.10.20.22.4.40');
  }
  static function asFunctional() {
    return new static('2.16.840.1.113883.10.20.22.4.68');
  }
  static function asSocial() {
    return new static('2.16.840.1.113883.10.20.22.4.38');
  }
  static function asSmoke() {
    return new static('2.16.840.1.113883.10.20.22.4.78');
  }
  static function asProcedureActivity() {
    return new static('2.16.840.1.113883.10.20.22.4.14');
  }
  static function asPurposeActivity() {
    return new static('2.16.840.1.113883.10.20.1.30'); 
  }
  static function asResultObservation() {
    return new static('2.16.840.1.113883.10.20.22.4.2');
  }
  static function asResultOrganizer() {
    return new static('2.16.840.1.113883.10.20.22.4.1');
  }
  static function asVitalSignsOrganizer() {
    return new static('2.16.840.1.113883.10.20.1.35');
  }
  static function asAlertStatusObservation() {
    return new static('2.16.840.1.113883.10.20.1.39');
  }
  static function asProblemStatusObservation() {
    return new static('2.16.840.1.113883.10.20.1.50');
  }
  static function asMedStatusObservation() {
    return new static('2.16.840.1.113883.10.20.1.47');
  }
  static function asProduct() {
    return new static('2.16.840.1.113883.10.20.22.4.23');
  }
  static function asProductImmun() {
    return new static('2.16.840.1.113883.10.20.22.4.54');
  }
  static function asReactionObservation() {
    return new static('2.16.840.1.113883.10.20.1.54');
  }
  static function asLanguageComm() {
    return array(
      new static('2.16.840.1.113883.3.88.11.32.2'),
      new static('2.16.840.1.113883.3.88.11.83.2'),
      new static('1.3.6.1.4.1.19376.1.5.3.1.2.1'));
  }
  static function from(/*oid,..*/) {
    $oids = func_get_args();
    foreach ($oids as &$oid) {
      $oid = new static($oid);
    }
    return count($oids) == 1 ? reset($oids) : $oids;
  }
}
class InfrastructureRoot_typeId extends II {
  public /*uid*/ $_root = "2.16.840.1.113883.1.3"; //REQ
}
class InFulfillmentOf extends XmlRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*ActRelationshipFulfills*/ $_typeCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*Order*/ $order; //REQ
}
//
class CustMap {
  public $map;
  //
  static function create($custmap = null) {
    $me = new static();
    if ($custmap) {
      $me->map = jsondecode($custmap);
    }
    return $me;
  }
  //
  public function filterAllers($recs) {
    return $this->filter(InfrastructureRoot_templateId::asSectionAlerts(), $recs);
  }
  public function filterMeds($recs) {
    return $this->filter(InfrastructureRoot_templateId::asSectionMeds(), $recs);
  }
  public function filterMedsAdmin($recs) {
    return $this->filter(InfrastructureRoot_templateId::asSectionMedsAdmin(), $recs);
  }
  public function filterProcs($recs) {
    return $this->filter(InfrastructureRoot_templateId::asSectionProcedures(), $recs);
  }
  public function filterInstructs($recs) {
    return $this->filter(InfrastructureRoot_templateId::asSectionInstructions(), $recs);
  }
  public function filterFunctional($recs) {
    return $this->filter(InfrastructureRoot_templateId::asSectionFunctionalStatus(), $recs);
  }
  public function filterProblems($recs) {
    return $this->filter(InfrastructureRoot_templateId::asSectionProblems(), $recs);
  }
  public function filterVitals($recs) {
    return $this->filter(InfrastructureRoot_templateId::asSectionVitals(), $recs);
  }
  public function filterImmuns($recs) {
    return $this->filter(InfrastructureRoot_templateId::asSectionImmuns(), $recs);
  }
  public function filterEncounters($recs) {
    return $this->filter(InfrastructureRoot_templateId::asSectionEncounters(), $recs);
  }
  public function filterReasonForVisit($proc) {
    $recs = $this->filter(InfrastructureRoot_templateId::asSectionReasonForVisit(), array($proc));
    return current($recs);
  }
  public function filterSocial($smoking) {
    $recs = $this->filter(InfrastructureRoot_templateId::asSectionSocial(), array($smoking));
    return current($recs);
  }
  public function filterResults($procs) {
    $section = $this->get(InfrastructureRoot_templateId::asSectionResults());
    if ($section == null) {
      $a = $procs;
    } else if (! $section->checked) {
      $a = null;
    } else {
      $checks = $this->tbodyChecks($section->text->table);
      $a = array();
      $i = -1;
      foreach ($procs as $proc) {
        $i++;
        $pchecked = $checks[$i];
        $r = array();
        if (count($proc->ProcResults) == 1) {
          $i--;
        }
        foreach ($proc->ProcResults as $result) {
          $i++;
          if ($pchecked && $checks[$i]) {
            $r[] = $result;
          }
        }
        if ($pchecked) {
          $proc->ProcResults = $r;
          $a[] = $proc;
        }
      }
    }
    return $a;
  }
  public function filterPlanOfCare(&$fs) {
    $section = $this->get(InfrastructureRoot_templateId::asSectionPlanOfCare());
    if ($section == null) {
      return;
    } else if (! $section->checked) {
      $fs->PlanOfCare = null;
      $fs->PendingTests = null;
      $fs->FutureTests = null;
      $fs->Followups = null;
      $fs->Referrals = null;
      return;
    }
    foreach ($section->text->list as $list) {
      $checks = $this->tbodyChecks($list->item->table);
      switch (get($list, 'caption')) {
        case 'Future Scheduled Tests':
          $fs->FutureTests = $this->filterChecks($checks, $fs->FutureTests);
        case 'Future Appointments':
          $fs->Followups = $this->filterChecks($checks, $fs->Followups);
          break;
        case 'Diagnostic Tests Pending':
          $fs->PendingTests = $this->filterChecks($checks, $fs->PendingTests);
          break;
        default:
          $fs->PlanOfCare = $this->filterChecks($checks, $fs->PlanOfCare);
      }
    }
  }
  //
  protected function filter($ii, $recs) {
    $section = $this->get($ii);
    if ($section == null) {
      $a = $recs;
    } else if (! $section->checked) {
      $a = null;
    } else {
      $checks = $this->tbodyChecks($section->text->table);
      $a = $this->filterChecks($checks, $recs);
    }
    return $a;
  }
  protected function filterChecks($checks, $recs) {
    $a = array();
    foreach ($recs as $i => $rec) {
      if ($checks[$i])
        $a[] = $rec;
    }
    return $a;
  }
  protected function tbodyChecks($t) {
    if ($t) {
      $tbody = $t->tbody;
      $checks = array();
      foreach ($tbody->tr as $tr) {
        $checks[] = $tr->td[0]->checked;
      }
      return $checks;
    }
  }
  protected function get($ii) {
    if ($this->map) {
      return get($this->map, $ii->_root);
    }
  }
}