<?php
require_once 'php/data/xml/ccd/datatypes_base.php';
require_once 'php/data/xml/ccd/voc.php';
require_once 'php/data/xml/ccd/CodeSystems.php';
//
class Act extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*x_ActClassDocumentEntryAct*/ $_classCode;
  public /*x_DocumentActMood*/ $_moodCode; //REQ
  public /*bl*/ $_negationInd;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*II[]*/ $id;
  public /*CD*/ $code; //REQ
  public /*ED*/ $text;
  public /*CS*/ $statusCode;
  public /*IVL_TS*/ $effectiveTime;
  public /*CE*/ $priorityCode;
  public /*CS*/ $languageCode;
  public /*Subject*/ $subject;
  public /*Specimen[]*/ $specimen;
  public /*Performer2[]*/ $performer;
  public /*Author[]*/ $author;
  public /*Informant12[]*/ $informant;
  public /*Participant2[]*/ $participant;
  public /*EntryRelationship[]*/ $entryRelationship;
  public /*Reference[]*/ $reference;
  public /*Precondition[]*/ $precondition;
  //
  public function __construct($classCode, $moodCode) {
    $this->_classCode = $classCode;
    $this->_moodCode = $moodCode;
  }
  public function addEntryRelationship($e) {
    $this->set('entryRelationship', $e);
  }
}
class Act_PurposeActivity extends Act {
  //
  public function __construct() {
    parent::__construct(ActClass::ACT, ActMood::EVENT);
    $this->templateId = InfrastructureRoot_templateId::asPurposeActivity();
    // TODO
  }
}
class Act_CoverageActivity extends Act {
  //
  public function __construct() {
    parent::__construct(ActClass::ACT, ActMood::DEFINITION);
    $this->templateId = InfrastructureRoot_templateId::asCoverageActivity();
    // TODO
  }
}
class Act_ProblemAct extends Act {
  //
  public function __construct() {
    parent::__construct(ActClass::ACT, ActMood::EVENT);
    $this->code = CD::asNull('NA');
  }
  public function getProblemObservation() {
    $observation = get_recursive($this, 'entryRelationship.observation');
    if ($observation->getTemplateId() == InfrastructureRoot_templateId::asProblemObservation()->_root) 
      return $observation;
  }
  public function getAlertObservation() {
    $observation = get_recursive($this, 'entryRelationship.observation');
    if ($observation->getTemplateId() == InfrastructureRoot_templateId::asAlertObservation()->_root) 
      return $observation;
  }
  //
  static function fromAller($aller) {
    $e = new self();
    $e->templateId = InfrastructureRoot_templateId::asProblemAlertAct();
    $e->id = II::fromAller($aller);
    $e->code = CE_LOINC::asAllergies();
    $observation = Observation_Alert::from($aller);
    $e->statusCode = CS_Status::asCompleted(); 
    $e->effectiveTime = IVL_TS::asLowHighUnk();
    $e->entryRelationship = EntryRelationship::asSubjectObservation($observation);
    return $e;
  }
  static function fromDiag($diag) {
    $e = new self();
    $e->id = II::fromDiag($diag);
    $e->templateId = InfrastructureRoot_templateId::asProblemAct();
    $e->statusCode = CS_Status::asActive();  // TODO
    $e->effectiveTime = IVL_TS::asLow($diag->date);
    $observation = Observation_Problem::from($diag);
    $e->entryRelationship = EntryRelationship::asSubjectObservation($observation, 'false');
    if (get($diag, 'encounterSid'))
      $e->addEntryRelationship(EntryRelationship::asComponentEncounter(Encounter::asComponent($diag->userGroupId, $diag->encounterSid), true));
    return $e;
  }
}
class AssignedAuthor extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*RoleClassAssignedEntity*/ $_classCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*II[]*/ $id; //REQ
  public /*CE*/ $code;
  public /*AD[]*/ $addr;
  public /*TEL[]*/ $telecom;
  public /*Person*/ $assignedPerson;
  public /*Organization*/ $representedOrganization;
  public /*AuthoringDevice*/ $assignedAuthoringDevice;
  //
  static function from($user, $userGroup) {
    $e = new self();
    $e->id = II::fromUser($user);
    $e->addr = AD::from($userGroup->Address);
    $e->telecom = TEL::from($userGroup->Address);
    $e->assignedPerson = Person::fromUser($user);
    $e->representedOrganization = Organization::from($userGroup);
    return $e;
  }
}
class AssignedCustodian extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*RoleClassAssignedEntity*/ $_classCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*CustodianOrganization*/ $representedCustodianOrganization; //REQ
  //
  static function from($userGroup) {
    $e = new self();
    $e->representedCustodianOrganization = CustodianOrganization::from($userGroup);
    return $e;
  }
}
class AssignedEntity extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*RoleClassAssignedEntity*/ $_classCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*II[]*/ $id; //REQ
  public /*CE*/ $code;
  public /*AD[]*/ $addr;
  public /*TEL[]*/ $telecom;
  public /*Person*/ $assignedPerson;
  public /*Organization*/ $representedOrganization;
  //
  static function from($user, $userGroup) {
    $e = new self();
    $e->id = II::fromUser($user);
    $e->assignedPerson = Person::fromUser($user);
    $e->addr = AD::from($userGroup->Address);
    $e->telecom = TEL::from($userGroup->Address);
    //$e->representedOrganization = Organization::from($userGroup);
    return $e;
  }
  static function fromProvider($provider, $address) {
    $e = new self();
    $e->id = II::fromProvider($provider);
    $e->assignedPerson = Person::fromProvider($provider);
    $e->addr = AD::from($address);
    $e->telecom = TEL::from($address);
    //$e->representedOrganization = Organization::from($userGroup);
    return $e;
  }
}
class AssociatedEntity extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*RoleClassAssociative*/ $_classCode; //REQ
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*II[]*/ $id;
  public /*CE*/ $code;
  public /*AD[]*/ $addr;
  public /*TEL[]*/ $telecom;
  public /*Person*/ $associatedPerson;
  public /*Organization*/ $scopingOrganization;
  //
  static function fromClient($client, $classCode) {
    $e = new self();
    $e->_classCode = $classCode;
    $e->id = II::fromClient($client);
    if ($client->Address_Home)
      $e->addr = AD::from($client->Address_Home);
    $e->associatedPerson = Person::fromClient($client);
    return $e;
  }
  static function asGuardian($client) {
    return self::fromClient($client, RoleClassAssociative::GUARDIAN);
  }
  static function asNextOfKin($client, $code) {
    $e = self::fromClient($client, RoleClassAssociative::NEXT_OF_KIN);
    $e->code = $code;
    return $e;
  }
  static function asNextOfKinMother($client) {
    return self::asNextOfKin($client, CE_SNOMED_Relationship::asBiologicalMother());
  }
}
class Authenticator extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*ParticipationType*/ $_typeCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*TS*/ $time; //REQ
  public /*CS*/ $signatureCode; //REQ
  public /*AssignedEntity*/ $assignedEntity; //REQ
}
class Author extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*ParticipationType*/ $_typeCode;
  public /*ContextControl*/ $_contextControlCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*CE*/ $functionCode;
  public /*TS*/ $time; //REQ
  public /*AssignedAuthor*/ $assignedAuthor; //REQ
  //
  static function from($user, $userGroup) {
    $e = new self();
    $e->time = TS::fromNow();
    $e->assignedAuthor = AssignedAuthor::from($user, $userGroup);
    return $e;
  }
}
class AuthoringDevice extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*EntityClassDevice*/ $_classCode;
  public /*EntityDeterminer*/ $_determinerCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*CE*/ $code;
  public /*SC*/ $manufacturerModelName;
  public /*SC*/ $softwareName;
  public /*MaintainedEntity[]*/ $asMaintainedEntity;
}
class Authorization extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*ActRelationshipType*/ $_typeCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*Consent*/ $consent; //REQ
}
class Birthplace extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*RoleClass*/ $_classCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*Place*/ $place; //REQ
}
class Component1 extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*ActRelationshipHasComponent*/ $_typeCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*EncompassingEncounter*/ $encompassingEncounter; //REQ
}
class Component2 extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*ActRelationshipHasComponent*/ $_typeCode;
  public /*bl*/ $_contextConductionInd;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*NonXMLBody*/ $nonXMLBody; //REQ
  public /*StructuredBody*/ $structuredBody; //REQ
  //
  static function asStructuredBody() {
    $e = new self();
    $e->structuredBody = new StructuredBody();
    return $e;
  }
}
class Component3 extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*ActRelationshipHasComponent*/ $_typeCode;
  public /*bl*/ $_contextConductionInd;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*POCD_Section*/ $section; //REQ
  //
  static function bySection($section) {
    $e = new self();
    $e->section = $section;
    return $e;
  }
}
class Component4 extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*ActRelationshipHasComponent*/ $_typeCode;
  public /*bl*/ $_contextConductionInd;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*INT*/ $sequenceNumber;
  public /*BL*/ $seperatableInd;
  public /*Act*/ $act; //REQ
  public /*Encounter*/ $encounter; //REQ
  public /*Observation*/ $observation; //REQ
  public /*ObservationMedia*/ $observationMedia; //REQ
  public /*Organizer*/ $organizer; //REQ
  public /*Procedure*/ $procedure; //REQ
  public /*RegionOfInterest*/ $regionOfInterest; //REQ
  public /*SubstanceAdministration*/ $substanceAdministration; //REQ
  public /*Supply*/ $supply; //REQ
  //
  static function byObservation($observation) {
    $e = new self();
    $e->observation = $observation;
    return $e;
  }
  static function byProcedure($procedure) {
    $e = new self();
    $e->procedure = $procedure;
    return $e;
  }
}
class Component5 extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*ActRelationshipHasComponent*/ $_typeCode;
  public /*bl*/ $_contextConductionInd;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*POCD_Section*/ $section; //REQ
}
class Consent extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*ActClass*/ $_classCode;
  public /*ActMood*/ $_moodCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*II[]*/ $id;
  public /*CE*/ $code;
  public /*CS*/ $statusCode; //REQ
}
class Consumable extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*ParticipationType*/ $_typeCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*ManufacturedProduct*/ $manufacturedProduct; //REQ
  //
  static function fromMed($med) {
    $e = new self();
    $e->manufacturedProduct = ManufacturedProduct::byManufacturedMaterial(Material::fromMed($med));
    return $e;
  }
  static function fromImmun($immun) {
    $e = new self();
    $e->manufacturedProduct = ManufacturedProduct::asImmun($immun);
    return $e;
  }
  static function asNoMeds() {
    $e = new self();
    $e->manufacturedProduct = ManufacturedProduct::byManufacturedMaterial(Material::asNoMeds());
  }
}
class Criterion extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*ActClassObservation*/ $_classCode;
  public /*ActMood*/ $_moodCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*CD*/ $code;
  public /*ED*/ $text;
  public /*ANY*/ $value;
}
class Custodian extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*ParticipationType*/ $_typeCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*AssignedCustodian*/ $assignedCustodian; //REQ
  //
  static function from($userGroup) {
    $e = new self();
    $e->assignedCustodian = AssignedCustodian::from($userGroup);
    return $e;
  }
}
class CustodianOrganization extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*EntityClassOrganization*/ $_classCode;
  public /*EntityDeterminer*/ $_determinerCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*II[]*/ $id; //REQ
  public /*ON*/ $name;
  public /*TEL*/ $telecom;
  public /*AD*/ $addr;
  //
  static function from($userGroup) {
    $e = new self();
    $e->id = II::fromUserGroup($userGroup);
    $e->addr = AD::from($userGroup->Address);
    $e->telecom = TEL::from($userGroup->Address);
    $e->name = ON::asText($userGroup->name);
    return $e;
  }
}
class DataEnterer extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*ParticipationType*/ $_typeCode;
  public /*ContextControl*/ $_contextControlCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*TS*/ $time;
  public /*AssignedEntity*/ $assignedEntity; //REQ
}
class Device extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*EntityClassDevice*/ $_classCode;
  public /*EntityDeterminer*/ $_determinerCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*CE*/ $code;
  public /*SC*/ $manufacturerModelName;
  public /*SC*/ $softwareName;
}
class DocumentationOf extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*ActRelationshipType*/ $_typeCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*ServiceEvent*/ $serviceEvent; //REQ
  //
  static function from($fs) { 
    $e = new self();
    $e->serviceEvent = ServiceEvent::asDocumentationOf($fs);
    return $e; 
  }
}
class EncompassingEncounter extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*ActClass*/ $_classCode;
  public /*ActMood*/ $_moodCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*II[]*/ $id;
  public /*CE*/ $code;
  public /*IVL_TS*/ $effectiveTime; //REQ
  public /*CE*/ $dischargeDispositionCode;
  public /*ResponsibleParty*/ $responsibleParty;
  public /*EncounterParticipant[]*/ $encounterParticipant;
  public /*Location*/ $location;
}
class Encounter extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*ActClass*/ $_classCode; //REQ
  public /*x_DocumentEncounterMood*/ $_moodCode; //REQ
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*II[]*/ $id;
  public /*CD*/ $code;
  public /*ED*/ $text;
  public /*CS*/ $statusCode;
  public /*IVL_TS*/ $effectiveTime;
  public /*CE*/ $priorityCode;
  public /*Subject*/ $subject;
  public /*Specimen[]*/ $specimen;
  public /*Performer2[]*/ $performer;
  public /*Author[]*/ $author;
  public /*Informant12[]*/ $informant;
  public /*Participant2[]*/ $participant;
  public /*EntryRelationship[]*/ $entryRelationship;
  public /*Reference[]*/ $reference;
  public /*Precondition[]*/ $precondition;
  //
  public function __construct($classCode = ActClass::ENCOUNTER, $moodCode = ActMood::EVENT, $id = null) {
    $this->_classCode = $classCode;
    $this->_moodCode = $moodCode;
    $this->id = $id;
  }
  static function asComponent($ugid, $sid) {
    $id = II::fromSid($ugid, $sid);
    return new static(ActClass::ENCOUNTER, ActMood::EVENT, $id);
  }
}
class Encounter_PlanOfCare extends Encounter {
  //
  public function __construct($moodCode = ActMood::APPT_REQUEST) {
    parent::__construct();
    $this->templateId = InfrastructureRoot_templateId::asPlanEncounter();
    $this->_moodCode = $moodCode;
  }
  //
  static function fromTrack($track) {
    $e = new self();
    $e->id = II::fromTrackItem($track);
    $e->code = new CE_HL7_ActCode();
    $e->code->originalText->reference->_value = '#' . TEXT_REF::getForTrackItem($track);
    $e->effectiveTime = TS::fromDate($track->drawnDate ?: $track->orderDate);
    if (get($track, 'Provider'))
      $e->performer = Performer2::fromProvider($track->Provider, $track->Address);
    return $e;
  }
}
class Encounter_EncounterActivity extends Encounter {
  private $__source; 
  //
  public function __construct() {
    parent::__construct();
    $this->templateId = InfrastructureRoot_templateId::asEncounterActivity();
  }
  public function getSource() {
    return $this->__source;
  }
  //
  static function fromSession($session) {
    $e = new self();
    $e->__source = $session;
    $e->id = II::fromSession($session);
    $e->code = CE_HL7_ActCode::asInpatientEncounter();
    $e->effectiveTime = IVL_TS::fromDate($session->dateService);
    $e->performer = Performer2::asEncounterProvider($session);
    return $e;
  }
}
class EncounterParticipant extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*x_EncounterParticipant*/ $_typeCode; //REQ
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*IVL_TS*/ $time;
  public /*AssignedEntity*/ $assignedEntity; //REQ
}
class Entity extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*EntityClassRoot*/ $_classCode;
  public /*EntityDeterminer*/ $_determinerCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*II[]*/ $id;
  public /*CE*/ $code;
  public /*ED*/ $desc;
}
class Entry extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*x_ActRelationshipEntry*/ $_typeCode;
  public /*bl*/ $_contextConductionInd;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*Act*/ $act; //REQ
  public /*Encounter*/ $encounter; //REQ
  public /*Observation*/ $observation; //REQ
  public /*ObservationMedia*/ $observationMedia; //REQ
  public /*Organizer*/ $organizer; //REQ
  public /*Procedure*/ $procedure; //REQ
  public /*RegionOfInterest*/ $regionOfInterest; //REQ
  public /*SubstanceAdministration*/ $substanceAdministration; //REQ
  public /*Supply*/ $supply; //REQ
  //
  public function __construct($typeCode = null) { //ActRelationshipType::IS_DERIVED_FROM) {
    $this->_typeCode = $typeCode;
  }
  //
  static function fromMed($med) {
    $e = new self();
    $e->substanceAdministration = SubstanceAdministration_MedActivity::fromMed($med);
    return $e;
  }
  static function asNoMeds() {
    $e = new self();
    $e->substanceAdministration = SubstanceAdministration_MedActivity::asNoMeds();
    return $e;
  }
  static function fromImmun($immun) {
    $e = new self();
    $e->substanceAdministration = SubstanceAdministration_MedActivity::fromImmun($immun);
    return $e;
  }
  static function fromAller($aller) {
    $e = new self();
    $e->act = Act_ProblemAct::fromAller($aller);
    return $e;
  }
  static function fromDiag($diag) {
    $e = new self();
    $e->act = Act_ProblemAct::fromDiag($diag);
    return $e;
  }
  static function fromProc($proc) {
    $e = new self();
    $e->procedure = Procedure_ProcedureActivity::fromProc($proc);
    return $e;
  }
  static function asNullProc($ugid) {
    $e = new self();
    $e->procedure = Procedure_ProcedureActivity::asNullProc($ugid);
    return $e;
  }
  static function fromPlanOfCare($proc) {
    $e = new self();
    $e->observation = Observation_PlanOfCare::fromProc($proc);
    return $e;
  }
  static function fromFunctional($proc) {
    $e = new self();
    $e->observation = Observation_Functional::from($proc);
    return $e;
  }
  static function asPlanTest($track) {
    $e = new self();
    if ($track->Ipc->cat == 1/*lab*/) {
      $e->observation = Observation_PlanOfCare::fromTrack($track);
    } else {
      $e->procedure = Procedure_PlanOfCare::fromTrack($track);
    }
    return $e;
  }
  static function asPlanEncounter($track) {
    $e = new self();
    $e->encounter = Encounter_PlanOfCare::fromTrack($track);
    return $e;
  }
  static function fromProcResults($proc) {
    $e = new self();
    $e->organizer = Organizer_Result::fromProc($proc);
    return $e;
  }
  static function fromVital($vital) {
    $e = new self();
    $e->organizer = Organizer_VitalSigns::fromVital($vital);
    return $e;
  }
  static function fromSession($session) {
    $e = new self();
    $e->encounter = Encounter_EncounterActivity::fromSession($session);
    return $e;
  }
  static function fromSocial($soc, $ugid) {
    $e = new self();
    $e->observation = Observation_Social::from($soc, $ugid);
    return $e;
  }
  static function fromSmoke($smoke) {
    $e = new self();
    $e->observation = Observation_Smoke::from($smoke);
    return $e;
  }
}
class EntryRelationship extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*x_ActRelationshipEntryRelationship*/ $_typeCode; //REQ
  public /*bl*/ $_inversionInd;
  public /*bl*/ $_contextConductionInd;
  public /*bl*/ $_negationInd;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*INT*/ $sequenceNumber;
  public /*BL*/ $seperatableInd;
  public /*Act*/ $act; //REQ
  public /*Encounter*/ $encounter; //REQ
  public /*Observation*/ $observation; //REQ
  public /*ObservationMedia*/ $observationMedia; //REQ
  public /*Organizer*/ $organizer; //REQ
  public /*Procedure*/ $procedure; //REQ
  public /*RegionOfInterest*/ $regionOfInterest; //REQ
  public /*SubstanceAdministration*/ $substanceAdministration; //REQ
  public /*Supply*/ $supply; //REQ
  //
  public function __construct($typeCode, $inversionInd = null) {
    $this->_typeCode = $typeCode;
    $this->_inversionInd = $inversionInd;
  }
  //
  static function asComponentAct($act, $inversionInd = null) {
    return self::_asComponent('act', $act, $inversionInd);
  }
  static function asComponentEncounter($encounter, $inversionInd = null) {
    return self::_asComponent('encounter', $encounter, $inversionInd);
  }
  static function asManifestationOfObservation($observation, $inversionInd = null) {
    return self::_asManifestationOf('observation', $observation, $inversionInd);
  }
  static function asReasonAct($act, $inversionInd = null) {
    return self::_asReason('act', $act, $inversionInd);
  }
  static function asReasonObservation($observation, $inversionInd = null) {
    return self::_asReason('observation', $observation, $inversionInd);
  }
  static function asRefersToAct($act, $inversionInd = null) {
    return self::_asRefersTo('act', $act, $inversionInd);
  }
  static function asRefersToProcedure($proc, $inversionInd = null) {
    return self::_asRefersTo('procedure', $proc, $inversionInd);
  }
  static function asRefersToObservation($observation, $inversionInd = null) {
    return self::_asRefersTo('observation', $observation, $inversionInd);
  }
  static function asStartsAfterAct($act, $inversionInd = null) {
    return self::_asStartsAfter('act', $act, $inversionInd);
  }
  static function asSubjectObservation($observation, $inversionInd = null) {
    return self::_asSubject('observation', $observation, $inversionInd);
  }
  static function asSubjectProcedure($procedure, $inversionInd = null) {
    return self::_asSubject('procedure', $procedure, $inversionInd);
  }
  //
  private static function _asComponent($fid, $object, $inversionInd) {
    return self::_as(ActRelationshipType::HAS_COMPONENT, $fid, $object, $inversionInd);
  }
  private static function _asManifestationOf($fid, $object, $inversionInd) {
    return self::_as(ActRelationshipType::IS_MANIFESTATION_OF, $fid, $object, $inversionInd);
  }
  private static function _asReason($fid, $object, $inversionInd) {
    return self::_as(ActRelationshipType::HAS_REASON, $fid, $object, $inversionInd);
  }
  private static function _asRefersTo($fid, $object, $inversionInd) {
    return self::_as(ActRelationshipType::REFERS_TO, $fid, $object, $inversionInd);
  }
  private static function _asStartsAfter($fid, $object, $inversionInd) {
    return self::_as(ActRelationshipType::STARTS_AFTER_START_OF, $fid, $object, $inversionInd);
  }
  private static function _asSubject($fid, $object, $inversionInd) {
    return self::_as(ActRelationshipType::HAS_SUBJECT, $fid, $object, $inversionInd);
  }
  private static function _as($typeCode, $fid, $object, $inversionInd) {
    $e = new self($typeCode, $inversionInd);
    $e->$fid = $object;
    return $e;
  }
}
class ExternalAct extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*ActClassRoot*/ $_classCode;
  public /*ActMood*/ $_moodCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*II[]*/ $id;
  public /*CD*/ $code;
  public /*ED*/ $text;
}
class ExternalDocument extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*ActClassDocument*/ $_classCode;
  public /*ActMood*/ $_moodCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*II[]*/ $id;
  public /*CD*/ $code;
  public /*ED*/ $text;
  public /*II*/ $setId;
  public /*INT*/ $versionNumber;
}
class ExternalObservation extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*ActClassObservation*/ $_classCode;
  public /*ActMood*/ $_moodCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*II[]*/ $id;
  public /*CD*/ $code;
  public /*ED*/ $text;
}
class ExternalProcedure extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*ActClass*/ $_classCode;
  public /*ActMood*/ $_moodCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*II[]*/ $id;
  public /*CD*/ $code;
  public /*ED*/ $text;
}
class Guardian extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*RoleClass*/ $_classCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*II[]*/ $id;
  public /*CE*/ $code;
  public /*AD[]*/ $addr;
  public /*TEL[]*/ $telecom;
  public /*Person*/ $guardianPerson; //REQ
  public /*Organization*/ $guardianOrganization; //REQ
}
class HealthCareFacility extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*RoleClassServiceDeliveryLocation*/ $_classCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*II[]*/ $id;
  public /*CE*/ $code;
  public /*Place*/ $location;
  public /*Organization*/ $serviceProviderOrganization;
}
class Informant12 extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*ParticipationType*/ $_typeCode;
  public /*ContextControl*/ $_contextControlCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*AssignedEntity*/ $assignedEntity; //REQ
  public /*RelatedEntity*/ $relatedEntity; //REQ
}
class InformationRecipient extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*x_InformationRecipient*/ $_typeCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*IntendedRecipient*/ $intendedRecipient; //REQ
}
class IntendedRecipient extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*x_InformationRecipientRole*/ $_classCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*II[]*/ $id;
  public /*AD[]*/ $addr;
  public /*TEL[]*/ $telecom;
  public /*Person*/ $informationRecipient;
  public /*Organization*/ $receivedOrganization;
}
class LabeledDrug extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*EntityClassManufacturedMaterial*/ $_classCode;
  public /*EntityDeterminerDetermined*/ $_determinerCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*CE*/ $code;
  public /*EN*/ $name;
}
class LanguageCommunication extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*CS*/ $languageCode;
  public /*CE*/ $modeCode;
  public /*CE*/ $proficiencyLevelCode;
  public /*BL*/ $preferenceInd;
  //
  public function __construct() {
    $this->templateId = InfrastructureRoot_templateId::asLanguageComm();
  }
  static function fromClient($c) {
    $lang = get($c, 'Language');
    if ($lang) {
      $e = new static();
      $e->languageCode = CS_LanguageCode::from($lang);
      $e->preferenceInd = BL::asBoolean(false);
      return $e;
    }
  }
}
class LegalAuthenticator extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*ParticipationType*/ $_typeCode;
  public /*ContextControl*/ $_contextControlCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*TS*/ $time; //REQ
  public /*CS*/ $signatureCode; //REQ
  public /*AssignedEntity*/ $assignedEntity; //REQ
  //
  static function from($user, $userGroup) {
    $e = new static();
    $e->time = TS::fromNow();
    $e->signatureCode = new CS("S");
    $e->assignedEntity = AssignedEntity::from($user, $userGroup);
    return $e;
  }
}
class Location extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*ParticipationTargetLocation*/ $_typeCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*HealthCareFacility*/ $healthCareFacility; //REQ
}
class MaintainedEntity extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*RoleClass*/ $_classCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*IVL_TS*/ $effectiveTime;
  public /*Person*/ $maintainingPerson; //REQ
}
class ManufacturedProduct extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*RoleClassManufacturedProduct*/ $_classCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*II[]*/ $id;
  public /*Organization*/ $manufacturerOrganization;
  public /*LabeledDrug*/ $manufacturedLabeledDrug; //REQ
  public /*Material*/ $manufacturedMaterial; //REQ
  //
  public function __construct() {
    $this->_classCode = 'MANU';
  }
  static function byManufacturedMaterial($manufacturedMaterial) {
    $e = new self();
    $e->templateId = InfrastructureRoot_templateId::asProduct();
    $e->manufacturedMaterial = $manufacturedMaterial;
    return $e;
  }
  static function asImmun($immun) {
    $e = new self();
    $e->templateId = InfrastructureRoot_templateId::asProductImmun();
    $e->manufacturedMaterial = Material::fromImmun($immun);
    return $e;
  }
}
class Material extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*EntityClassManufacturedMaterial*/ $_classCode;
  public /*EntityDeterminerDetermined*/ $_determinerCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*CE*/ $code;
  public /*EN*/ $name;
  public /*ST*/ $lotNumberText;
  //
  static function fromMed($med) {
    $e = new self();
    $e->code = CE_RxNorm::fromMed($med);
    $e->code->originalText->_ = $med->name;
    $e->code->originalText->reference->_value = TEXT_REF::getForMed($med);
    return $e;    
  }
  static function fromImmun($immun) {
    $e = new self();
    $e->code = CE_Immun::fromImmun($immun);
    return $e;
  }
  static function asNoMeds() {
    $e = new self();
    $e->code = CE_RxNorm::asNoMeds();
  }
}
class NonXMLBody extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*ActClass*/ $_classCode;
  public /*ActMood*/ $_moodCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*ED*/ $text; //REQ
  public /*CE*/ $confidentialityCode;
  public /*CS*/ $languageCode;
}
class Observation extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*ActClassObservation*/ $_classCode; //REQ
  public /*x_ActMoodDocumentObservation*/ $_moodCode; //REQ
  public /*bl*/ $_negationInd;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*II[]*/ $id;
  public /*CD*/ $code; //REQ
  public /*ST*/ $derivationExpr;
  public /*ED*/ $text;
  public /*CS*/ $statusCode;
  public /*IVL_TS*/ $effectiveTime;
  public /*CE*/ $priorityCode;
  public /*IVL_INT*/ $repeatNumber;
  public /*CS*/ $languageCode;
  public /*ANY[]*/ $value;
  public /*CE[]*/ $interpretationCode;
  public /*CE[]*/ $methodCode;
  public /*CD[]*/ $targetSiteCode;
  public /*Subject*/ $subject;
  public /*Specimen[]*/ $specimen;
  public /*Performer2[]*/ $performer;
  public /*Author[]*/ $author;
  public /*Informant12[]*/ $informant;
  public /*Participant2[]*/ $participant;
  public /*EntryRelationship[]*/ $entryRelationship;
  public /*Reference[]*/ $reference;
  public /*Precondition[]*/ $precondition;
  public /*ReferenceRange[]*/ $referenceRange;
  //
  public function __construct($classCode = ActClass::OBSERVATION, $moodCode = ActMood::EVENT) {
    $this->_classCode = $classCode;
    $this->_moodCode = $moodCode;
    $this->statusCode = CS_Status::asCompleted();
  }
  public function setCodeAndValue($code, $value, $xsiType = null) {
    if ($value) {
      $this->code = $code;
      $this->value = $value;
      $this->value->setXsiType($xsiType);
    }
  }
  public function addEntryRelationship($e) {
    $this->set('entryRelationship', $e);
  }
}
class Observation_Problem extends Observation {
  public function __construct() {
    parent::__construct();
    $this->templateId = InfrastructureRoot_templateId::asProblemObservation();
  } 
  public function getProblemStatusObservation() {
    $observation = get_recursive($this, 'entryRelationship.observation');
    if ($observation->getTemplateId() == InfrastructureRoot_templateId::asProblemStatusObservation()->_root) 
      return $observation;
  }
  //
  static function from($diag) {
    $e = new self();
    //$e->setCodeAndValue(CE_SNOMED_ProblemCode::asDiagnosis(), CE_ICD9::fromDiag($diag), 'CD');
    $e->id = II::fromDiag($diag);
    $value = CD::asNull('UNK');
    $value->translation = CE_ICD9::fromDiag($diag);
    $e->setCodeAndValue(CE_SNOMED_ProblemCode::asCondition(), $value);
    $e->text = TEXT_REF::asDiag($diag);
    //$e->effectiveTime = IVL_TS::fromDate($diag->date);  // TODO: make this a range
    $e->effectiveTime = IVL_TS::asLow($diag->date);
    //$e->addEntryRelationship(EntryRelationship::asRefersToObservation(Observation_ProblemStatus::from($diag)));
    
    return $e;
  }
}
class Observation_PlanOfCare extends Observation {
  public function __construct($moodCode = ActMood::REQUEST) {
    parent::__construct(ActClass::OBSERVATION, $moodCode);
    $this->templateId = InfrastructureRoot_templateId::asPlanObservation();
  } 
  //
  static function fromProc($proc) {
    $e = new static();
    $e->id = II::fromProc($proc);
    $e->text = TEXT_REF::asPlanOfCare($proc);
    $e->statusCode = CS_Status::asNew();
    $e->code = CD::asNull('NA');
    $e->effectiveTime = IVL_TS::asCenter($proc->date);
    return $e;
  }
  static function fromTrack($track) {
    $e = new static();
    $e->id = II::fromTrackItem($track);
    $e->text = TEXT_REF::asTrackItem($track);
    $e->statusCode = CS_Status::asNew();
    $e->code = CE_CodeSystem::fromIpc($track->Ipc); 
    $e->effectiveTime = IVL_TS::asCenter($track->drawnDate ?: $track->orderDate);
    return $e;
  }
}
class Observation_Functional extends Observation {
  public function __construct($moodCode = ActMood::EVENT) {
    parent::__construct(ActClass::OBSERVATION, $moodCode);
    $this->templateId = InfrastructureRoot_templateId::asFunctional();
  } 
  //
  static function from($proc) {
    $e = new static();
    $e->id = II::fromProc($proc);
    $e->text = TEXT_REF::asProc($proc);
    $e->statusCode = CS_Status::asCompleted();
    $e->code = CE_HL7_ActCode::asAssertion();
    $e->effectiveTime = IVL_TS::asLow($proc->date);
    $e->value = CD_SNOMED::from($proc);
    return $e;
  }
}
class Observation_Social extends Observation {
  public function __construct($moodCode = ActMood::EVENT) {
    parent::__construct(ActClass::OBSERVATION, $moodCode);
    $this->templateId = InfrastructureRoot_templateId::asSocial();
  } 
  //
  static function from($soc, $ugid) {
    $e = new static();
    $e->id = II::fromSoc($soc, $ugid);
    /*
    $e->text = TEXT_REF::asSocial($soc);
    $e->statusCode = CS_Status::asCompleted();
    $e->code = CE_HL7_ActCode::asAssertion();
    $e->effectiveTime = IVL_TS::asLowUnk();
    */
    return $e;
  }
}
class Observation_Smoke extends Observation {
  public function __construct($moodCode = ActMood::EVENT) {
    parent::__construct(ActClass::OBSERVATION, $moodCode);
    $this->templateId = InfrastructureRoot_templateId::asSmoke();
  } 
  //
  static function from($proc) {
    $e = new static();
    $e->id = II::fromProc($proc);
    $e->text = TEXT_REF::asProc($proc);
    $e->statusCode = CS_Status::asCompleted();
    $e->code = CE_HL7_ActCode::asAssertion();
    $e->effectiveTime = IVL_TS::asLowHighUnk();
    $e->value = CD_SNOMED::from($proc);
    return $e;
  }
}
class Observation_ProblemStatus extends Observation {
  public function __construct() {
    parent::__construct();
    $this->templateId = InfrastructureRoot_templateId::asProblemStatusObservation();
  }
  //
  static function from($diag) {
    $e = new self();
    $e->setCodeAndValue(CE_LOINC::asStatus(), CE_SNOMED_Status::fromDiag($diag));
    return $e;
  }
}
class Observation_MedStatus extends Observation {
  public function __construct() {
    parent::__construct();
    $this->templateId = InfrastructureRoot_templateId::asMedStatusObservation();
  }
  //
  static function asActive() {
    $e = new self();
    $e->setCodeAndValue(CE_LOINC::asStatus(), CE_SNOMED_Status::asActive());
    return $e;
  }
}
class Observation_Alert extends Observation {
  private $__source;
  //
  public function __construct() {
    parent::__construct();
    $this->templateId = InfrastructureRoot_templateId::asAlertObservation();
    $this->setCodeAndValue(CE_HL7_ActCode::asAssertion(), CE_SNOMED_Reaction::asAdverseReactionToSubstance(), 'CD');
  }
  public function getSource() {
    return $this->__source;
  }
  public function getPlayingEntityCode() {
    return get_recursive($this, 'participant.participantRole.playingEntity.code');
  }
  //
  static function from($aller) {
    $e = new self();
    $e->__source = $aller;
    $e->id = II::fromAller($aller);
    $e->value = CE_SNOMED_AllergyType::from($aller);
    $e->value->setXsiType('CD');
    $e->code = CE_HL7_ActCode::asAssertion();
    $e->participant = Participant2::fromAller($aller);
    $e->effectiveTime = IVL_TS::asLowUnk();
    $reactions = $aller->getReactions();
    if ($reactions) {
      foreach ($reactions as $reaction) 
        $e->addEntryRelationship(EntryRelationship::asManifestationOfObservation(Observation_Reaction::from($reaction), true));
    }
    $e->addEntryRelationship(EntryRelationship::asRefersToObservation(Observation_AlertStatus::asActive()));
    return $e;
  }
}
class Observation_AlertStatus extends Observation {
  public function __construct() {
    parent::__construct();
    $this->templateId = InfrastructureRoot_templateId::asAlertStatusObservation();
  }
  //
  static function asActive() {  
    $e = new self();
    $e->setCodeAndValue(CE_LOINC::asStatus(), CE_SNOMED_Status::asActive());
    return $e;
  }
}
class Observation_Reaction extends Observation {
  public function __construct() {
    parent::__construct();
    $this->templateId = InfrastructureRoot_templateId::asAlertStatusObservation();
  }
  //
  static function from($reaction) {
    $e = new self();
    $e->setCodeAndValue(CE_HL7_ActCode::asAssertion(), CE_SNOMED_Reaction::fromReaction($reaction), 'CD');
    return $e; 
  }
}
class Observation_Result extends Observation {
  public function __construct($id) {
    parent::__construct();
    $this->templateId = InfrastructureRoot_templateId::asResultObservation();
    $this->id = $id;
  }
  //
  static function asVital($id, $effectiveTime, $code, $value, $encounterSid = null, $ugid = null) {
    $e = new self($id, $effectiveTime);
    $e->effectiveTime = $effectiveTime;    
    $e->setCodeAndValue($code, $value);
    if ($encounterSid)
      $e->entryRelationship = EntryRelationship::asComponentEncounter(Encounter::asComponent($ugid, $encounterSid), true);
    return $e;
  }
  static function from($result, $ugid, $encounterSid) {
    $e = new self(II::fromResult($result, $ugid));
    $e->effectiveTime = IVL_TS::fromDate($result->getDate());
    $code = CE_CodeSystem::fromProc($result);
    $e->text = TEXT_REF::asResult($result);
    $e->interpretationCode = CE_HL7_Interpretation::fromResult($result);
    if ($result->value) {
      if (is_numeric($result->value)) 
        $value = PQ::from($result->value, $result->valueUnit);
      else
        $value = ST::asText($result->value);
    } else {
      $value = CD::from($e->interpretationCode->_code, $e->interpretationCode->_codeSystem);
    }
    $e->setCodeAndValue($code, $value);
    $e->interpretationCode = CE_HL7_Interpretation::fromResult($result);
    $e->referenceRange = ReferenceRange::fromResult($result);
    if ($encounterSid)
      $e->entryRelationship = EntryRelationship::asComponentEncounter(Encounter::asComponent($ugid, $encounterSid), true);
    return $e;
  }
}
class ObservationMedia extends CcdRec {
  public /*xs:ID*/ $_ID;
  public /*NullFlavor*/ $_nullFlavor;
  public /*ActClassObservation*/ $_classCode; //REQ
  public /*ActMood*/ $_moodCode; //REQ
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*II[]*/ $id;
  public /*CS*/ $languageCode;
  public /*ED*/ $value; //REQ
  public /*Subject*/ $subject;
  public /*Specimen[]*/ $specimen;
  public /*Performer2[]*/ $performer;
  public /*Author[]*/ $author;
  public /*Informant12[]*/ $informant;
  public /*Participant2[]*/ $participant;
  public /*EntryRelationship[]*/ $entryRelationship;
  public /*Reference[]*/ $reference;
  public /*Precondition[]*/ $precondition;
}
class ObservationRange extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*ActClassObservation*/ $_classCode;
  public /*ActMood*/ $_moodCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*CD*/ $code;
  public /*ED*/ $text;
  public /*ANY*/ $value;
  public /*CE*/ $interpretationCode;
  //
  static function fromResult($result) {
    if ($result->range) {
      $e = new self();
      $e->text = $result->range;
      return $e;
    }
  }
}
class Order extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*ActClassRoot*/ $_classCode;
  public /*ActMood*/ $_moodCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*II[]*/ $id; //REQ
  public /*CE*/ $code;
  public /*CE*/ $priorityCode;
}
class Organization extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*EntityClassOrganization*/ $_classCode;
  public /*EntityDeterminer*/ $_determinerCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*II[]*/ $id;
  public /*ON[]*/ $name;
  public /*TEL[]*/ $telecom;
  public /*AD[]*/ $addr;
  public /*CE*/ $standardIndustryClassCode;
  public /*OrganizationPartOf*/ $asOrganizationPartOf;
  //
  static function from($userGroup) {
    $e = new self();
    $e->id = II::fromUserGroup($userGroup);
    $e->name = ON::asText($userGroup->name);
    $e->addr = AD::from($userGroup->Address);
    $e->telecom = TEL::from($userGroup->Address);
    return $e;
  }
}
class OrganizationPartOf extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*RoleClass*/ $_classCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*II[]*/ $id;
  public /*CE*/ $code;
  public /*CS*/ $statusCode;
  public /*IVL_TS*/ $effectiveTime;
  public /*Organization*/ $wholeOrganization;
}
class Organizer extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*x_ActClassDocumentEntryOrganizer*/ $_classCode; //REQ
  public /*ActMood*/ $_moodCode; //REQ
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*II[]*/ $id;
  public /*CD*/ $code;
  public /*CS*/ $statusCode; //REQ
  public /*IVL_TS*/ $effectiveTime;
  public /*Subject*/ $subject;
  public /*Specimen[]*/ $specimen;
  public /*Performer2[]*/ $performer;
  public /*Author[]*/ $author;
  public /*Informant12[]*/ $informant;
  public /*Participant2[]*/ $participant;
  public /*Reference[]*/ $reference;
  public /*Precondition[]*/ $precondition;
  public /*Component4[]*/ $component;
  //
  public function __construct($classCode, $moodCode = ActMood::EVENT) {
    $this->_classCode = $classCode;
    $this->_moodCode = $moodCode;
    $this->statusCode = CS_Status::asCompleted();
    $this->component = array();
  }
  public function add($component) {
    $this->component[] = $component;
  }
}
class Organizer_Result extends Organizer {
  //
  public function __construct() {
    parent::__construct(ActClass::BATTERY);
    $this->templateId = InfrastructureRoot_templateId::asResultOrganizer();
  }
  public function addProc($proc) {
    $procedure = Procedure_ProcedureActivity::fromProc($proc);
    parent::add(Component4::byProcedure($procedure));
  }
  public function add($result, $ugid, $encounterSid) {
    $observation = Observation_Result::from($result, $ugid, $encounterSid);
    parent::add(Component4::byObservation($observation));
  }
  //
  static function fromProc($proc) {
    $e = new self();
    $e->id = II::fromProc($proc);
    $e->effectiveTime = IVL_TS::fromDate($proc->date);
    $e->code = CE_CodeSystem::fromProc($proc);
    //$e->addProc($proc);
    foreach ($proc->ProcResults as $result)
      $e->add($result, $proc->userGroupId, get($proc, 'encounterSid'));
    return $e;
  }
}
class Organizer_VitalSigns extends Organizer {
  private $__source;
  //
  public function __construct() {
    parent::__construct(ActClass::CLUSTER);
    $this->templateId = InfrastructureRoot_templateId::asVitalSignsOrganizer();
  }
  public function getSource() {
    return $this->__source;
  }
  public function add($code, $value, $subId = null) {
    $id = $this->id->_extension;
    if ($subId) 
      $id .= '.' . $subId;
    $observation = Observation_Result::asVital(II::from($this->id->_root, $id), $this->effectiveTime, $code, $value, get($this->__source, 'encounterSid'), get($this->__source, 'userGroupId'));
    parent::add(Component4::byObservation($observation));
  }
  //
  static function fromVital($vital) {
    $e = new self();
    $e->__source = $vital;
    $e->id = II::fromVital($vital);
    $e->effectiveTime = IVL_TS::fromDate($vital->date);
    if ($vital->pulse) 
      $e->add(CE_LOINC_Vitals::asPulseRate(), PQ::asPerMin($vital->pulse), '1');
    if ($vital->resp) 
      $e->add(CE_LOINC_Vitals::asRespRate(), PQ::asPerMin($vital->resp), '2');
    if ($vital->bpSystolic) 
      $e->add(CE_LOINC_Vitals::asBpSystolic(), PQ::asMmHg($vital->bpSystolic), '3');
    if ($vital->bpDiastolic) 
      $e->add(CE_LOINC_Vitals::asBpDiastolic(), PQ::asMmHg($vital->bpDiastolic), '4');
      if ($vital->temp) 
      $e->add(CE_LOINC_Vitals::asBodyTemp(), PQ::asFahrenheit($vital->temp), '5');
    if ($vital->wt) 
      $e->add(CE_LOINC_Vitals::asBodyWeight(), PQ::asKg($vital->getWtKg()), '6');
    if ($vital->height) 
      $e->add(CE_LOINC_Vitals::asBodyHeight(), PQ::asCm($vital->getHtCm()), '7');
    if ($vital->o2Sat) 
      $e->add(CE_LOINC_Vitals::asO2Saturation(), PQ::asPercent($vital->o2Sat), '8');
    if ($vital->bmi) 
      $e->add(CE_LOINC_Vitals::asBmi(), PQ::asBmi($vital->bmi), '9');
    return $e;
  }
}
class ParentDocument extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*ActClinicalDocument*/ $_classCode;
  public /*ActMood*/ $_moodCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*II[]*/ $id; //REQ
  public /*CD*/ $code;
  public /*ED*/ $text;
  public /*II*/ $setId;
  public /*INT*/ $versionNumber;
}
class Participant1 extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*ParticipationType*/ $_typeCode; //REQ
  public /*ContextControl*/ $_contextControlCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*CE*/ $functionCode;
  public /*IVL_TS*/ $time;
  public /*AssociatedEntity*/ $associatedEntity; //REQ
  //
  public function __construct($typeCode) {
    $this->_typeCode = $typeCode;
  }
  //
  static function asIndirectTarget($associatedEntity) {
    $e = new self(ParticipationType::INDIRECT_TARGET);
    $e->associatedEntity = $associatedEntity;
    return $e; 
  }
}
class Participant2 extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*ParticipationType*/ $_typeCode; //REQ
  public /*ContextControl*/ $_contextControlCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*IVL_TS*/ $time;
  public /*CE*/ $awarenessCode;
  public /*ParticipantRole*/ $participantRole; //REQ
  //
  public function __construct($typeCode) {
    $this->_typeCode = $typeCode;
  }
  //
  static function asConsumable($participantRole) {
    $e = new self(ParticipationType::CONSUMABLE);
    $e->participantRole = $participantRole;
    return $e;
  }
  static function fromAller($aller) {
    return self::asConsumable(ParticipantRole::fromAller($aller));
  }
}
class ParticipantRole extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*RoleClassRoot*/ $_classCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*II[]*/ $id;
  public /*CE*/ $code;
  public /*AD[]*/ $addr;
  public /*TEL[]*/ $telecom;
  public /*Entity*/ $scopingEntity;
  public /*Device*/ $playingDevice;
  public /*PlayingEntity*/ $playingEntity;
  //
  public function __construct($classCode = null) {
    $this->_classCode = $classCode;
  }
  //
  static function asManufacProduct($playingEntity) {
    $e = new self(RoleClass::MANUFACTURED_PRODUCT);
    $e->playingEntity = $playingEntity;
    return $e;
  }
  static function fromAller($aller) {
    return self::asManufacProduct(PlayingEntity::fromAller($aller));
  }
}
class Patient extends CcdRec {
  private $__source;
  //
  public /*NullFlavor*/ $_nullFlavor;
  public /*EntityClass*/ $_classCode;
  public /*EntityDeterminer*/ $_determinerCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*II*/ $id;
  public /*PN[]*/ $name;
  public /*CE*/ $administrativeGenderCode;
  public /*TS*/ $birthTime;
  public /*CE*/ $maritalStatusCode;
  public /*CE*/ $religiousAffiliationCode;
  public /*CE*/ $raceCode;
  public /*CE*/ $ethnicGroupCode;
  public /*Guardian[]*/ $guardian;
  public /*Birthplace*/ $birthplace;
  public /*LanguageCommunication[]*/ $languageCommunication;
  //
  public function getSource() {
    return $this->__source;
  }
  //
  static function from($client) {
    $e = new self();
    $e->__source = $client;
    $e->name = PN::fromClient($client);
    $e->administrativeGenderCode = CE_HL7_AdministrativeGender::fromClient($client);
    $e->birthTime = TS::fromDate($client->birth);
    $e->raceCode = CE_HL7_Race::fromClient($client);
    $e->ethnicGroupCode = CE_HL7_Ethnicity::fromClient($client);
    $e->languageCommunication = LanguageCommunication::fromClient($client);
    return $e;
  }
}
class PatientRole extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*RoleClass*/ $_classCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*II[]*/ $id; //REQ
  public /*AD[]*/ $addr;
  public /*TEL[]*/ $telecom;
  public /*Patient*/ $patient;
  public /*Organization*/ $providerOrganization;
  //
  static function from($client, $userGroup) {
    $e = new self();
    if ($client) {
      $e->id = II::fromClient($client);
      if ($client->Address_Home) {
        $e->addr = AD::from($client->Address_Home);
        $e->telecom = TEL::from($client->Address_Home);
      }
      $e->patient = Patient::from($client);
      $e->providerOrganization = Organization::from($userGroup);
    } else {
      $e->id = II::asNull('NA');
    }
    return $e;
  }
}
class Performer1 extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*x_ServiceEventPerformer*/ $_typeCode; //REQ
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*CE*/ $functionCode;
  public /*IVL_TS*/ $time;
  public /*AssignedEntity*/ $assignedEntity; //REQ
  //
  static function from($assignedEntity, $typeCode = ParticipationType::PERFORMER) {
    $e = new self();
    $e->_typeCode = $typeCode;
    $e->assignedEntity = $assignedEntity;
    return $e; 
  }
  static function asPrimaryCareProvider($user, $userGroup, $dateFrom, $dateTo) {
    $e = self::from(AssignedEntity::from($user, $userGroup));
    $e->functionCode = new CE_HL7_ParticipationFunction('PCP');
    $e->time = IVL_TS::asLowHigh($dateFrom, $dateTo);
    return $e;
  }
  static function fromFacesheet($fs) {
    $us = array();
    if (get($fs, 'Encounters')) {
      foreach ($fs->Encounters as $encounter) {
        $us[$encounter->Doctor->name] = static::fromDoctor($encounter->Doctor, $fs->UserGroup);
      }
    }
    if (get($fs, 'Vitals')) {
      foreach ($fs->Vitals as $vital) {
        $us[$vital->UpdatedBy->name] = static::fromDoctor($vital->UpdatedBy, $fs->UserGroup);
      }
    }
    /*
    if (get($fs, 'Referrals')) {
      foreach ($fs->Referrals as $referral) {
        $us[] = static::fromProvider($referral->Provider, $referral->Address);
      }
    }
    */
    return array_values($us);
  }
  static function fromDoctor($doctor, $userGroup) {
    $assignedEntity = AssignedEntity::from($doctor, $userGroup);
    $me = static::from($assignedEntity); 
    $me->functionCode = CE_HL7_ParticipationFunction::asPrimary();
    $me->time = IVL_TS::asLowHighUnk();
    return $me;
  }
  static function fromProvider($provider, $address) {
    $assignedEntity = AssignedEntity::fromProvider($provider, $address);
    $me = static::from($assignedEntity); 
    $me->functionCode = CE_HL7_ParticipationFunction::asConsulting();
    $me->time = IVL_TS::asLowHighUnk();
    return $me;
  }
}
class Performer2 extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*ParticipationPhysicalPerformer*/ $_typeCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*IVL_TS*/ $time;
  public /*CE*/ $modeCode;
  public /*AssignedEntity*/ $assignedEntity; //REQ
  //
  static function from($assignedEntity, $typeCode = ParticipationType::PERFORMER) {
    $e = new self();
    $e->_typeCode = $typeCode;
    $e->assignedEntity = $assignedEntity;
    return $e; 
  }
  static function fromDoctor($doctor, $userGroup) {
    $assignedEntity = AssignedEntity::from($doctor, $userGroup);
    return static::from($assignedEntity); 
  }
  static function fromProvider($provider, $address) {
    $assignedEntity = AssignedEntity::fromProvider($provider, $address);
    return static::from($assignedEntity); 
  }
  static function asEncounterProvider($session) {
    $userGroup = $session->UserGroup;
    $id = $session->closedBy ?: $session->createdBy; 
    $user = geta($userGroup->Doctors, $session->createdBy);
    if ($user) {
      $e = self::from(AssignedEntity::from($user, $userGroup));
      $e->time = IVL_TS::fromDate($session->dateService);
      return $e;
    }
  }
}
class Person extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*EntityClass*/ $_classCode;
  public /*EntityDeterminer*/ $_determinerCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*PN[]*/ $name;
  //
  static function fromUser($user) {
    $e = new self();
    if (get($user, 'NcUser'))
    	$e->name = PN::fromNcUser($user->NcUser);
    return $e;
  }
  static function fromClient($client) {
    $e = new self();
    $e->name = PN::fromClient($client);
    return $e;
  }
  static function fromProvider($provider) {
    $e = new self();
    $e->name = PN::fromProvider($provider);
    return $e;
  }
}
class Place extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*EntityClassPlace*/ $_classCode;
  public /*EntityDeterminer*/ $_determinerCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*EN*/ $name;
  public /*AD*/ $addr;
}
class PlayingEntity extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*EntityClassRoot*/ $_classCode;
  public /*EntityDeterminer*/ $_determinerCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*CE*/ $code;
  public /*PQ[]*/ $quantity;
  public /*PN[]*/ $name;
  public /*ED*/ $desc;
  //
  public function __construct($classCode = null) {
    $this->_classCode = $classCode;
  }
  //
  static function asManufacMaterial($code, $name) {
    $e = new self(EntityClass::MANUFACTURED_MATERIAL);
    $e->code = $code;
    $e->name = $name;
    return $e;
  }
  static function fromAller($aller) {
    return self::asManufacMaterial(CE_RxNorm::fromAller($aller), $aller->agent);
  }
}
class Precondition extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*ActRelationshipType*/ $_typeCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*Criterion*/ $criterion; //REQ
}
class Procedure extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*ActClass*/ $_classCode; //REQ
  public /*x_DocumentProcedureMood*/ $_moodCode; //REQ
  public /*bl*/ $_negationInd;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*II[]*/ $id;
  public /*CD*/ $code;
  public /*ED*/ $text;
  public /*CS*/ $statusCode;
  public /*IVL_TS*/ $effectiveTime;
  public /*CE*/ $priorityCode;
  public /*CS*/ $languageCode;
  public /*CE[]*/ $methodCode;
  public /*CD[]*/ $approachSiteCode;
  public /*CD[]*/ $targetSiteCode;
  public /*Subject*/ $subject;
  public /*Specimen[]*/ $specimen;
  public /*Performer2[]*/ $performer;
  public /*Author[]*/ $author;
  public /*Informant12[]*/ $informant;
  public /*Participant2[]*/ $participant;
  public /*EntryRelationship[]*/ $entryRelationship;
  public /*Reference[]*/ $reference;
  public /*Precondition[]*/ $precondition;
  //
  public function __construct($classCode, $moodCode) {
    $this->_classCode = $classCode;
    $this->_moodCode = $moodCode;
  }
}
class Procedure_PlanOfCare extends Procedure {
  //
  public function __construct() {
    parent::__construct(ActClass::PROCEDURE, ActMood::REQUEST);
    $this->templateId = InfrastructureRoot_templateId::asPlanProcedure();
  }
  //
  static function fromTrack($track) {
    $e = new self();
    $e->id = II::fromTrackItem($track);
    $e->entryRelationship = EntryRelationship::asRefersToProcedure(Procedure_ProcedureActivity::fromTrack($track));    
    return $e;
  }
}
class Procedure_ProcedureActivity extends Procedure {
  private $__source;
  //
  public function __construct() {
    parent::__construct(ActClass::PROCEDURE, ActMood::EVENT);
    $this->templateId = InfrastructureRoot_templateId::asProcedureActivity();
    $this->statusCode = CS_Status::asCompleted();
  }
  public function getSource() {
    return $this->__source;
  }
  //
  static function asNullProc($ugid) {
    $e = new self();
    $e->__source = null;
    $e->id = II::asZero($ugid);
    $e->code = CE::asNull('UNK');
    $e->statusCode = CS_Status::asActive();
    $e->effectiveTime = IVL_TS::asLowUnk();
    return $e;
  }
  static function fromProc($proc) {
    $e = new self();
    $e->__source = $proc;
    $e->id = II::fromProc($proc);
    $e->code = CE_CodeSystem::fromProc($proc);
    if ($proc->location) 
      $e->code->addQualifier(CR_SNOMED_Laterality::fromProc($proc));
    if ($proc->priority) 
      $e->code->addQualifier(CR_SNOMED_Priorities::fromProc($proc));
    $e->effectiveTime = TS::fromDate($proc->date);
    //$e->code->originalText->_ = $proc->Ipc->name;
    $e->code->originalText->reference->_value = '#' . TEXT_REF::getForProc($proc);
    //$e->text->_ = $proc->Ipc->name;
    //$e->text->reference->_value = TEXT_REF::getForProc($proc);
    if (get($proc, 'encounterSid'))
      $e->entryRelationship = EntryRelationship::asComponentEncounter(Encounter::asComponent($proc->userGroupId, $proc->encounterSid), true);
    return $e;
  } 
  static function fromTrack($track) {
    $e = new self();
    $e->id = II::fromTrackItem($track);
    $e->code = CE_CodeSystem::fromIpc($track->Ipc);
    $e->code->originalText->reference->_value = '#' . TEXT_REF::getForTrackItem($track);
    $e->statusCode = CS_Status::asActive();
    $e->effectiveTime = TS::fromDate($track->drawnDate ?: $track->orderDate);
    return $e;
  }
}
class Product extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*ParticipationType*/ $_typeCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*ManufacturedProduct*/ $manufacturedProduct; //REQ
}
class RecordTarget extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*ParticipationType*/ $_typeCode;
  public /*ContextControl*/ $_contextControlCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*PatientRole*/ $patientRole; //REQ
  //
  static function from($client, $userGroup) {
    $e = new self();
    $e->patientRole = PatientRole::from($client, $userGroup);
    return $e;
  }
}
class Reference extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*x_ActRelationshipExternalReference*/ $_typeCode; //REQ
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*BL*/ $seperatableInd;
  public /*ExternalAct*/ $externalAct; //REQ
  public /*ExternalObservation*/ $externalObservation; //REQ
  public /*ExternalProcedure*/ $externalProcedure; //REQ
  public /*ExternalDocument*/ $externalDocument; //REQ
}
class ReferenceRange extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*ActRelationshipType*/ $_typeCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*ObservationRange*/ $observationRange; //REQ
  //
  static function fromResult($result) {
    $observationRange = ObservationRange::fromResult($result);
    if ($observationRange) {
      $e = new self();
      $e->observationRange = ObservationRange::fromResult($result);
      return $e;
    }
  }
}
class RegionOfInterest_value extends INT {
  public /*xs:boolean*/ $_unsorted;
}
class RegionOfInterest extends CcdRec {
  public /*xs:ID*/ $_ID;
  public /*NullFlavor*/ $_nullFlavor;
  public /*ActClass*/ $_classCode = "ROIOVL"; //REQ
  public /*ActMood*/ $_moodCode = "EVN"; //REQ
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*II[]*/ $id; //REQ
  public /*CS*/ $code; //REQ
  public /*RegionOfInterest_value[]*/ $value; //REQ
  public /*Subject*/ $subject;
  public /*Specimen[]*/ $specimen;
  public /*Performer2[]*/ $performer;
  public /*Author[]*/ $author;
  public /*Informant12[]*/ $informant;
  public /*Participant2[]*/ $participant;
  public /*EntryRelationship[]*/ $entryRelationship;
  public /*Reference[]*/ $reference;
  public /*Precondition[]*/ $precondition;
}
class RelatedDocument extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*x_ActRelationshipDocument*/ $_typeCode; //REQ
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*ParentDocument*/ $parentDocument; //REQ
}
class RelatedEntity extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*RoleClassMutualRelationship*/ $_classCode; //REQ
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*CE*/ $code;
  public /*AD[]*/ $addr;
  public /*TEL[]*/ $telecom;
  public /*IVL_TS*/ $effectiveTime;
  public /*Person*/ $relatedPerson;
}
class RelatedSubject extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*x_DocumentSubject*/ $_classCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*CE*/ $code;
  public /*AD[]*/ $addr;
  public /*TEL[]*/ $telecom;
  public /*SubjectPerson*/ $subject;
}
class ResponsibleParty extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*ParticipationType*/ $_typeCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*AssignedEntity*/ $assignedEntity; //REQ
}
class POCD_Section extends CcdRec {
  public /*xs:ID*/ $_ID;
  public /*NullFlavor*/ $_nullFlavor;
  public /*ActClass*/ $_classCode;
  public /*ActMood*/ $_moodCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*II*/ $id;
  public /*CE*/ $code;
  public /*ST*/ $title;
  public /*StrucDoc_Text*/ $text;
  public /*CE*/ $confidentialityCode;
  public /*CS*/ $languageCode;
  public /*Subject*/ $subject;
  public /*Author[]*/ $author;
  public /*Informant12[]*/ $informant;
  public /*Entry[]*/ $entry;
  public /*Component5[]*/ $component;
  //
  public function __construct() {
    $this->entry = array();
  }
  public function add($entry) {
    $this->entry[] = $entry;
  }
  public function getEntries() {
    return $this->entry;
  }
  protected function getEntryObjects($tagName) {
    $objects = array();
    $entries = $this->getEntries();
    if ($entries) {
      foreach ($this->getEntries() as $entry) {
        $object = get($entry, $tagName);
        if ($object)
          $objects[] = $object;
      }
    }
    return $objects;
  }
  public function getActs() {
    return $this->getEntryObjects('act');
  }
  public function getOrganizers() {
    return $this->getEntryObjects('organizer');
  }
  public function getProcedures() {
    return $this->getEntryObjects('procedure');
  }
  public function getSubstanceAdmins() {
    return $this->getEntryObjects('substanceAdministration');
  }
  public function getEncounters() {
    return $this->getEntryObjects('encounter');
  }
}
class ServiceEvent extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*ActClassRoot*/ $_classCode;
  public /*ActMood*/ $_moodCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*II[]*/ $id;
  public /*CE*/ $code;
  public /*IVL_TS*/ $effectiveTime;
  public /*Performer1[]*/ $performer;
  //
  static function asDocumentationOf($fs) {
    $e = new self();
    $e->_classCode = ActClass::CARE_PROVISION;
    $e->effectiveTime = IVL_TS::asLowHigh($fs->getEarliestDate(), nowNoQuotes());
    $e->performer = Performer1::fromFacesheet($fs);
    return $e;
  }
}
class Specimen extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*ParticipationType*/ $_typeCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*SpecimenRole*/ $specimenRole; //REQ
}
class SpecimenRole extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*RoleClassSpecimen*/ $_classCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*II[]*/ $id;
  public /*PlayingEntity*/ $specimenPlayingEntity;
}
class StructuredBody extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*ActClass*/ $_classCode;
  public /*ActMood*/ $_moodCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*CE*/ $confidentialityCode;
  public /*CS*/ $languageCode;
  public /*Component3[]*/ $component; //REQ
  //
  public function __construct() {
    $this->component = array();
  }
  public function add($section) {
    $this->component[] = Component3::bySection($section);
  } 
}
class Subject extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*ParticipationTargetSubject*/ $_typeCode;
  public /*ContextControl*/ $_contextControlCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*CE*/ $awarenessCode;
  public /*RelatedSubject*/ $relatedSubject; //REQ
}
class SubjectPerson extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*EntityClass*/ $_classCode;
  public /*EntityDeterminer*/ $_determinerCode;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*PN[]*/ $name;
  public /*CE*/ $administrativeGenderCode;
  public /*TS*/ $birthTime;
}
class SubstanceAdministration extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*ActClass*/ $_classCode = "SBADM"; //REQ
  public /*x_DocumentSubstanceMood*/ $_moodCode; //REQ
  public /*bl*/ $_negationInd;
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*II[]*/ $id;
  public /*CD*/ $code;
  public /*ED*/ $text;
  public /*CS*/ $statusCode;
  public /*SXCM_TS[]*/ $effectiveTime;
  public /*CE*/ $priorityCode;
  public /*IVL_INT*/ $repeatNumber;
  public /*CE*/ $routeCode;
  public /*CD[]*/ $approachSiteCode;
  public /*IVL_PQ*/ $doseQuantity;
  public /*IVL_PQ*/ $rateQuantity;
  public /*RTO_PQ_PQ*/ $maxDoseQuantity;
  public /*CE*/ $administrationUnitCode;
  public /*Subject*/ $subject;
  public /*Specimen[]*/ $specimen;
  public /*Consumable*/ $consumable; //REQ
  public /*Performer2[]*/ $performer;
  public /*Author[]*/ $author;
  public /*Informant12[]*/ $informant;
  public /*Participant2[]*/ $participant;
  public /*EntryRelationship[]*/ $entryRelationship;
  public /*Reference[]*/ $reference;
  public /*Precondition[]*/ $precondition;
  //
  public function addEntryRelationship($e) {
    $this->set('entryRelationship', $e);
  }
}
class SubstanceAdministration_MedActivity extends SubstanceAdministration {
  private $__source;
  //
  public function __construct() {
    $this->_classCode = ActClass::SUBSTANCE_ADMINISTRATION;
    $this->_moodCode = ActMood::EVENT;
    $this->templateId = InfrastructureRoot_templateId::asMedActivity(); 
  }
  public function getSource() {
    return $this->__source;
  }
  public function getEffectiveTimePeriod() {
    $times = arrayify(get($this, 'effectiveTime'));
    foreach ($times as $time) 
      if ($time->getXsiType() == 'PIVL_TS') 
        return $time->period;
  }
  //
  static function fromMed($med) {
    $e = new self();
    $e->__source = $med;
    $e->id = II::fromMed($med);
    $e->statusCode = CS_Status::asCompleted();
    $e->effectiveTime = SXCM_TS::fromMed($med);
    $e->routeCode = CE_NCI_Route::fromNewCrop(get($med, 'ncRouteId'));
    $e->doseQuantity = IVL_PQ::asDoseQuantity($med);
    //$e->administrationUnitCode = CE_NCI_DosageForm::fromMed($med);
    //$e->administrationUnitCode = CE::from($med->ncDosageFormId, 'NewCrop', $med->ncDosageForm);
    $e->consumable = Consumable::fromMed($med);
    $e->addEntryRelationship(EntryRelationship::asRefersToObservation(Observation_MedStatus::asActive()));
    if (get($med, 'encounterSid'))
      $e->addEntryRelationship(EntryRelationship::asComponentEncounter(Encounter::asComponent($med->userGroupId, $med->encounterSid), true));
    return $e;
  }
  static function asNoMeds() {
    $e = new self();
    $e->id = II::from('TODO:asNoMeds');
    $e->code = CE_SNOMED_NoMeds::asNonePrescribed();
    $e->statusCode = CS_Status::asCompleted();
    $e->consumable = Consumable::asNoMeds();
    return $e;
    
  }
  static function fromImmun($immun) {
    $e = new self();
    $e->__source = $immun;
    $e->_negationInd = 'false';
    $e->templateId = InfrastructureRoot_templateId::asImmun();
    $e->id = II::fromImmun($immun);
    $e->statusCode = CS_Status::asCompleted();
    $e->effectiveTime = SXCM_TS::fromImmun($immun);
    //$e->routeCode = CE_NCI_Route::fromImmun($immun);
    $e->consumable = Consumable::fromImmun($immun);
    return $e;
  }
}
class Supply extends CcdRec {
  public /*NullFlavor*/ $_nullFlavor;
  public /*ActClassSupply*/ $_classCode = "SPLY"; //REQ
  public /*x_DocumentSubstanceMood*/ $_moodCode; //REQ
  //
  public /*CS[]*/ $realmCode;
  public /*InfrastructureRoot_typeId*/ $typeId;
  public /*InfrastructureRoot_templateId[]*/ $templateId;
  public /*II[]*/ $id;
  public /*CD*/ $code;
  public /*ED*/ $text;
  public /*CS*/ $statusCode;
  public /*SXCM_TS[]*/ $effectiveTime;
  public /*CE[]*/ $priorityCode;
  public /*IVL_INT*/ $repeatNumber;
  public /*BL*/ $independentInd;
  public /*PQ*/ $quantity;
  public /*IVL_TS*/ $expectedUseTime;
  public /*Subject*/ $subject;
  public /*Specimen[]*/ $specimen;
  public /*Product*/ $product;
  public /*Performer2[]*/ $performer;
  public /*Author[]*/ $author;
  public /*Informant12[]*/ $informant;
  public /*Participant2[]*/ $participant;
  public /*EntryRelationship[]*/ $entryRelationship;
  public /*Reference[]*/ $reference;
  public /*Precondition[]*/ $precondition;
}
/**
 * Base class
 */
abstract class CcdRec extends XmlRec {
  //
  public function getTemplateId() {
    $template = get($this, 'templateId');
    if ($template) 
      return $template->_root;
  }
} 
?>