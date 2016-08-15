<?php
require_once 'php/data/xml/ccd/datatypes_base.php';
require_once 'php/data/hl7-2.5.1/msg/seg/_HL7Tables.php';
//
class CE_CodeSystem extends CE {
  //
  public function __construct($code = null, $displayName = null) {
    $this->_code = $code;
    $this->_displayName = $displayName;
  }
  //
  static function fromProc($proc) {
    $ipc = get($proc, 'Ipc');
    if ($ipc) {
      return static::fromIpc($ipc);
    }
  }
  static function fromIpc($ipc) {
    if ($ipc) {
      switch ($ipc->codeSystem) {
        case Ipc::CS_CPT4:
          return new CE_CPT4($ipc->code, $ipc->name);
        case Ipc::CS_ICD9:
          return new CE_ICD9($ipc->code, $ipc->name);
        case Ipc::CS_LOINC:
          return new CE_LOINC($ipc->code, $ipc->name);
        case Ipc::CS_SNOMED:
          return new CE_SNOMED($ipc->code, $ipc->name);
        default:
          return new self(null, $ipc->name);
      }
    }
  }
}
abstract class CE_HL7Table extends CE {
  static $TABLE = 'HL7XXXX';
  static $CS = null; 
  //
  public function set($id, $text = null) {
    $table = static::getTable();
    $this->_code = $id;
    $this->_displayName = $text ?: $table::getText($id);
    $this->_codeSystem = static::$CS ?: $table::getCodingSystem();
  }
  //
  static function from($id, $text = null) {
    $me = new static();
    $me->set($id, $text);
    return $me;
  }
  static function fromLookup($e) {  
    $table = static::getTable();
    $id = $table::lookup($e);
    if ($id) 
      return static::from($id);
    else 
      return static::asNotFound($e);
  }
  static function asNotFound($e) {
    if (is_string($e))
      return CE_Local::from('NOTFOUND', $e);
  }
  static function getTable() {
    return static::$TABLE; 
  }
}
abstract class CE_HL7ReverseTable extends CE_HL7Table {
  static function fromLookup($text) {
    $table = static::getTable();
    $id = $table::lookup($text);
    if ($id) 
      return static::from($id, $text);
    else 
      return static::asNotFound($id);
  }
}
class CE_Immun extends CE_HL7ReverseTable {
  static $TABLE = 'HL70292';
  static $CS = '2.16.840.1.113883.12.292';
  //
  static function fromImmun($imm) {
    if ($imm->tradeName)
      return static::fromLookup($imm->tradeName);
    else
      return CE_Immun_byName::fromLookup($imm->name);   
  }
}
class CE_Immun_byName extends CE_HL7ReverseTable {
  static $TABLE = 'HL70292_byName';
  static $CS = '2.16.840.1.113883.12.292';
}
/**
 * SNOMED 
 */
class CE_SNOMED extends CE_CodeSystem {
  public /*uid*/ $_codeSystem = '2.16.840.1.113883.6.96';
  public /*st*/ $_codeSystemName = 'SNOMED CT';
}
class CE_SNOMED_Relationship extends CE_SNOMED {
  static function asBiologicalMother() {
    return new self('65656005', 'Biological Mother');
  }
} 
class CE_SNOMED_Status extends CE_SNOMED {
  static function asActive() {
    return new self('55561003', 'Active');    
  }
  static function asInactive() {
    return new self('73425007', 'Inactive');
  }
  static function asChronic() {
    return new self('90734009', 'Chronic');
  }
  static function asIntermittent() {
    return new self('7087005', 'Intermittent');
  }
  static function asRecurrent() {
    return new self('255227004', 'Recurrent');
  }
  static function asRuleOut() {
    return new self('415684004', 'Rule Out');
  }
  static function asRuledOut() {
    return new self('410516002', 'Ruled Out');    
  }
  static function asResolved() {
    return new self('413322009', 'Resolved');    
  }
  static function fromBoolean($active) {
    return ($active) ? self::asActive() : self::asInactive();
  }
  static function fromDiag($diag) {
    return self::fromBoolean($diag->active);
  }
}
class CE_SNOMED_ProblemCode extends CE_SNOMED {
  static function asDiagnosis() {
    return new self('282291009', 'Diagnosis');
  }
  static function asProblem() {
    return new self('55607006', 'Problem');
  }
  static function asSymptom() {
    return new self('418799008', 'Symptom');
  }
  static function asFinding() {
    return new self('404684003', 'Finding');
  }
  static function asCondition() {
    return new self('64572001', 'Condition');
  }
  static function asComplaint() {
    return new self('409586006', 'Complaint');
  }
  static function asFunctionalLimitation() {
    return new self('248536006', 'Functional Limitation');
  }
}
class CE_SNOMED_NoMeds extends CE_SNOMED {
  //
  static function asNonePrescribed() {
    return new self('182849000', 'No Drug Therapy Prescribed');
  }
  static function asNoneActive() {
    return new self('408350003', 'Patient Not On Self-Medications');
  }
  static function asNoneKnown() {
    return new self('182904002', 'Drug Treatment Unknown');
  }
}
class CE_SNOMED_AllergyType extends CE_SNOMED {
  //
  static function from($aller) {
    return new self ('416098002', 'Drug Allergy');
  }
}
class CE_SNOMED_NoAllergies extends CE_SNOMED {
  //
  static function asNoKnown() {
    return new self('160244002', 'No Known Allergies');
  }
  static function asNoKnownDrug() {
    return new self('409137002', 'No Known Drug Allergies');
  }
}
class CE_SNOMED_Reaction extends CE_SNOMED {
  static function asAdverseReactionToSubstance() {
    return new self('282100009', 'Adverse Reaction to Substance');
  }
  static function asHives() {
    return new self('247472004', 'Hives');
  }
  static function fromReaction($reaction) {
    switch ($reaction) {
      case 'Hives':
        return self::asHives();
      case 'Rash':
      case 'Itching':
      case 'Anaphylaxis':
      case 'Generalized Swelling':
      case 'Swollen Mouth':
      case 'Swollen Tongue':
      case 'Abdominal Pain':
      case 'Abdominal Upset':
      case 'Nausea':
      case 'Vomiting':
      case 'Diarrhea':
      case 'Constipation':
      case 'Shortness of Breath':
      case 'Cough':
      case 'Headache':
      case 'Dizziness':
      case 'Chest Pain':
      case 'Syncope':
      case 'Bleeding':
      case 'Bruising':
      case 'Epistaxis':
      case 'Sore Throat':
      case 'Dysphagia':
      case 'Breast Pain':
      case 'Breast Swelling':
      case 'Urinary Frequency':
      case 'Urinary Retention':
      case 'Dry Mouth':
      case 'Vaginal Dryness':
      case 'Excessive Sedation':
      case 'Myalgias':
      case 'Fatigue':
      case 'Liver Dysfunction':
      case 'Renal Failure':
      // TODO see http://bioportal.bioontology.org/visualize/44777/?conceptid=288526004
      default:
        return new self('NOTFOUND-Reaction', $reaction);
    }
  }
}
class CR_SNOMED_Priorities extends CR {
  // TODO
}
class CR_SNOMED_Laterality extends CR {
  //
  static function asLeft() {
    return self::from('7771000', 'Left');
  }
  static function asRight() {
    return self::from('24028007', 'Right');
  }
  static function asRightAndLeft() {
    return self::from('51440002', 'Right and Left');
  }
  static function asUnilateral() {
    return self::from('66459002', 'Unilateral');
  }
  static function from($code, $displayName = null) {
    $e = new self();
    $e->name = new CV_SNOMED_Laterality();
    $e->value = new CD($code, $displayName);
    return $e;
  }
  static function fromProc($proc) {
    // TODO
  }
}
class CV_SNOMED_Laterality extends CV {
  public /*cs*/ $_code = '272741003';
  public /*ED*/ $_originalText = 'Laterality';
}
class CD_SNOMED extends CD {
  //
  static function create($code, $displayName) {
    $e = new static();
    $e->_xsi_type = 'CD';
    $e->_codeSystem = '2.16.840.1.113883.6.96';
    $e->_codeSystemName = 'SNOMED CT';
    $e->_code = $code;
    $e->_displayName = $displayName;
    return $e;
  }
  static function asActive() {
    return static::create('55561003', 'active');
  }
  static function from($proc) {
    return static::create($proc->Ipc->codeSnomed, $proc->Ipc->name);
  }
}
/**
 * HL7
 */
class CE_HL7_AdministrativeGender extends CE_CodeSystem {
  public /*uid*/ $_codeSystem = '2.16.840.1.113883.5.1';
  public /*st*/ $_codeSystemName = 'HL7 AdministrativeGender';
  //
  static function fromClient($client) {
    return new self($client->sex);
  }
  static function from($value) {
    return new self($value);
  }
}
class CE_HL7_ActCode extends CE_CodeSystem {
  public /*uid*/ $_codeSystem = '2.16.840.1.113883.5.4';
  public /*st*/ $_codeSystemName = 'HL7 ActCode';
  //
  static function asAssertion() {
    return new self('ASSERTION');
  }
  static function asInpatientEncounter() {
    return new self('IMP', 'Inpatient Encounter');
  }
}
class CE_HL7_Confidentiality extends CE_CodeSystem {
  public /*uid*/ $_codeSystem = '2.16.840.1.113883.5.25';
  public /*st*/ $_codeSystemName = 'HL7 Confidentiality';
}
class CE_HL7_Ethnicity extends CE_HL7Table {
  static $TABLE = 'HL70189';
  static $CS = '2.16.840.1.113883.6.238';
  //
  static function fromClient($c) {
    return static::fromLookup($c);
  }
}
class CE_HL7_Race extends CE_HL7Table {
  static $TABLE = 'HL70005';
  static $CS = '2.16.840.1.113883.6.238';
  //
  static function fromClient($c) {
    return static::fromLookup($c->race);
  }
}
class CE_HL7_Interpretation extends CE_CodeSystem {
  public /*uid*/ $_codeSystem = '2.16.840.1.113883.5.83';
  //
  static function fromResult($result) {
    if ($result->interpretCode) 
      return new self($result->interpretCode);
  }
}
class CE_HL7_ParticipationFunction extends CE_CodeSystem {
  public /*uid*/ $_codeSystem = '2.16.840.1.113883.12.443';
  public /*st*/ $_codeSystemName = 'HL7 ParticipationFunction';
  //
  static function asPrimary() {
    return new static('PP');
  }
  static function asConsulting() {
    return new static('CP');
  }
}
/**
 * LOINC
 */
class CE_LOINC extends CE_CodeSystem {
  public /*uid*/ $_codeSystem = '2.16.840.1.113883.6.1';
  public /*st*/ $_codeSystemName = 'LOINC';
  
  //
  static function asStatus() {
    return new self('33999-4', 'Status');
  }
  static function asAllergies() {
    return new self('48765-2', 'Allergies, adverse reactions, alerts');
  }
}
class CE_LOINC_ProblemContent extends CE_LOINC {
  static function asProblemList() {
    return new self('11450-4', 'Problem List');
  }
  static function asResolved() {
    return new self('11348-0', 'Resolved');
  }
  static function asReasonForVisit() {
    return new self('29299-5', 'Reason for Visit');
  }
  static function asChiefComplaint() {
    return new self('10154-3', 'Chief Complaint');
  }
}
class CE_LOINC_Vitals extends CE_LOINC {
  static function asPulseRate() {
    return new self('8867-4', 'Heart Beat');
  }
  static function asRespRate() {
    return new self('9279-1', 'Respiration Rate');
  }
  static function asO2Saturation() {
    return new self('2710-2', 'Oxygen Saturation');
  }
  static function asBpDiastolic() {
    return new self('8462-4', 'Intravascular Diastolic');
  }
  static function asBpSystolic() {
    return new self('8480-6', 'Intravascular Systolic');
  }
  static function asBodyTemp() {
    return new self('8310-5', 'Body Temperature');
  }
  static function asBodyHeight() {
    return new self('8302-2', 'Body Height');
  }
  static function asBodyWeight() {
    return new self('3141-9', 'Body Weight');
  }
  static function asBmi() {
    return new self('41909-3', 'BMI');
  }
}
/**
 * NCI 
 */
class CE_NCI extends CE_CodeSystem {
  public /*uid*/ $_codeSystem = '2.16.840.1.113883.3.26.1.1';
  public /*st*/ $_codeSystemName = 'NCI';
}
class CE_NCI_Route extends CE_NCI {
  static function fromNewCrop($routeId) {
    switch ($routeId) {
      case 11:
        return new self('C38216', 'Inhaled');
      case 25:
        return new self('C38305', 'Transdermal');
      default:
        return new self('C38288', 'Oral');
    }
  }
  static function fromMed($med) {
      p_r($med->route);
    switch ($med->route) {
      case 'orally':
      case 'by mouth':
        return new self('C38288', 'Oral');
      case 'rectally':
      case 'rectum, in the':
        return new self('C38295', 'Rectal');
      case 'intravaginally':
      case 'vagina, in the':
        return new self('C38313', 'Vaginal');
      case 'IV':
        return new self('C38276', 'Intravenous');
      // TODO (also add remaining NewCrop values)
      case 'inhaled':
      case 'subcutaneously':
      case 'transdermally':
      case 'on the nails':
      case 'on the skin':
      case 'in the left eye':
      case 'in the right eye':
      case 'in both eyes':
      case 'in the left ear':
      case 'in the right ear':
      case 'in both ears':
      case 'in the left nostril':
      case 'in the right nostril':
      case 'in both nostrils':
      case 'in alternating nostrils':
      case 'IM':
      default:
        return new self('NOTFOUND-Route', $med->route);
    }
  }
  static function fromImmun($immun) {
    return new self('NOTFOUND-Route', $immun->route);  // TODO
  }
}
class CE_NCI_DosageForm extends CE_NCI {
  static function fromMed($med) {
    // TODO see http://www.fda.gov/ForIndustry/DataStandards/StructuredProductLabeling/ucm162038.htm
    //return new self('C42998', 'Tablet');
    return new self('NOTFOUND-DosageForm', $med->amt);
  }
}
class CE_RxNorm extends CE_CodeSystem {
  public /*uid*/ $_codeSystem = '2.16.840.1.113883.6.88';
  public /*st*/ $_codeSystemName = 'RxNorm';
  //
  public function __construct($code, $displayName, $originalText = null) {
    parent::__construct($code, $displayName);
    if ($originalText)
      $this->originalText = ED::asText($originalText);
  }
  //
  static function fromMed($med) {
    if ($med instanceof FaceMedNc) {
      $drugId = $med->getDrugId();
      if ($drugId)  
        return new self($drugId, $med->name);      
    }
    return CE_Local::fromMed($med);
  }
  static function fromAller($aller) {
    if ($aller instanceof FaceAllergyNc) {
      $drugId = $aller->getDrugId();
      if ($drugId) {
        $code = new self($drugId, $aller->agent);
        $code->originalText->reference->_value = '#' . TEXT_REF::getForAller($aller);
        return $code;
      }
    }
    return new self(null, $aller->agent);
  }
  static function asNoMeds() {
    return new self(null, null, 'Patient not on any medication');
  }
}
class CE_Local extends CE_CodeSystem {
  //
  static function fromMed($med) {
    return new self(null, $med->name);
  }
}
class CE_CVX extends CE_CodeSystem {
  public /*uid*/ $_codeSystem = '2.16.840.1.113883.6.59';
  //
  public function __construct($code, $displayName, $originalText = null) {
    parent::__construct($code, $displayName);
    if ($originalText)
      $this->originalText = ED::asText($originalText);
  }
  //
  static function byCode($code, $name) {
    switch ($code) {
      case '02':
        return new self($code, 'OPV', 'poliovirus vaccine, live, oral');
      case '33':
        return new self($code, 'Pneumococcal conjugate PCV 13', 'pneumococcal conjugate vaccine, 13 valent');
      case '109':
        return new self($code, 'pneumococcal, unspecified formulation', 'pneumococcal vaccine, unspecified formulation');
      // TODO see http://www2a.cdc.gov/nip/IIS/IISStandards/vaccines.asp?rpt=cvx
      default:
        return new self('UNK', $name, $name);
    }
  }
  static function fromImmun($immun) {
    return self::byCode(null, $immun->name);
    //$me = self::byCode($immun->getCvxCode(), $immun->name);
    //return $me;
  }
}
/**
 * ICD9
 */
class CE_ICD9 extends CE_CodeSystem {
  public /*uid*/ $_codeSystem = '2.16.840.1.113883.6.42';
  public /*st*/ $_codeSystemName = 'ICD9';
  //
  static function fromDiag($diag) {
    $e = new static($diag->icd, $diag->text);
    $e->_codeSystem = '2.16.840.1.113883.6.103';
    return $e;
  }
}
/**
 * CPT4
 */
class CE_CPT4 extends CE_CodeSystem {
  public /*uid*/ $_codeSystem = '2.16.840.1.113883.6.12';
  public /*st*/ $_codeSystemName = 'CPT4';
}
/**
 * CS Types
 */
class CS_LanguageType extends CS {
  //
  static function asUsEnglish() {
    return new self('en-US');
  }
}
class CS_LanguageCode extends CS {
  //
  static function from(/*IsoLang*/$lang) {
    if ($lang)
      return new self($lang->alpha3Code);
  }
}
class CS_Status extends CS {
  //
  static function fromMed($med) {
    if ($med->active)
      return self::asActive();
    else
      return self::asCompleted();
  }
  static function asActive() {
    return new self('active');
  }
  static function asCompleted() {
    return new self('completed');
  }
  static function asAborted() {
    return new self('aborted');
  }
  static function asCancelled() {
    return new self('cancelled');
  }
  static function asNew() {
    return new self('new');
  }
}
