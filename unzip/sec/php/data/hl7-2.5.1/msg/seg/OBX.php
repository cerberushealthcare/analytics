<?php
require_once 'php/data/hl7-2.5.1/msg/seg/_HL7Segment.php';
//
/**
 * Observation v2.5.1
 * @author Warren Hornsby
 */
class OBX extends HL7SequencedSegment {
  //
  public $segId = 'OBX';
  public $seq;  // Set ID - OBX (SI)
  public $valueType = 'ID_ValueType';  // Value Type (ID)
  public $obsId = 'CE';  // Observation Identifier (CE)
  public $obsSubId;  // Observation Sub-ID (ST)
  public $value;  // Observation Value (varies)
  public $units = 'CE';  // Units (CE)
  public $range;  // References Range (ST)
  public $abnormal;  // Abnormal Flags (IS)
  public $prob;  // Probability (NM)
  public $abnormTestNature;  // Nature of Abnormal Test (ID)
  public $resultStatus;  // Observation Result Status (ID)
  public $rangeEffective = 'TS';  // Effective Date of Reference Range (TS)
  public $accessChecks;  // User Defined Access Checks (ST)
  public $timestamp = 'TS';  // Date/Time of the Observation (TS)
  public $producerId = 'CE';  // Producer's ID (CE)
  public $observer = 'XCN';  // Responsible Observer (XCN)
  public $method = 'CE';  // Observation Method (CE)
  public $equip;  // Equipment Instance Identifier (EI)
  public $analysisDateTime = 'TS';  // Date/Time of the Analysis (TS)
  public $reserved1;
  public $reserved2;
  public $reserved3;
  public $performingOrg = 'XON';
  public $performingAddress = 'XAD';
  public $performingDirector = 'XCN';
  //
  static $_seq = 0;
  //
  static function asFinal() {
    $me = static::asEmpty();
    $me->resultStatus = ID_ResultStatus::asFinal();
    return $me;
  }
  static function append(&$us, $me) {
    if ($me) {
      $us[] = $me;
    }
  }
}
//
class OBX_VXU extends OBX {
  //
  static /*Immun_HL7Codes*/$Immun_HL7;
  static $_obsSubId = 0;
  //
  static function resetSeq() {
    parent::resetSeq();
    static::$_obsSubId = 0;
  }
  static function asEmpty() {
    $me = parent::asEmpty();
    $me->obsSubId = static::$_obsSubId;
    return $me;
  }
  static function loadIds($fs) {
    if ($fs->Immun_HL7) {
      static::$Immun_HL7 = $fs->Immun_HL7;
      HL70064::loadIds($fs->Immun_HL7->finclass);
    }
  }
  static function all($imm, $cvx = null) {
    static::resetSeq();
    if ($imm->isImmune()) {
      return static::asImmune($imm);
    } 
    $us = array();
    if ($imm->hasFinClass()) {
      $us[] = static::asFinClass($imm);
    }
    static::appendVacTypes($us, $imm, $cvx);
    return $us;
  }
  static function asImmune($imm) {
    $me = static::asFinal()->nextSubId();
    $me->valueType = ID_ValueType::CODED_ENTRY;
    $me->obsId = CE_Observation::asImmunity();
    $me->obsSubId = 1;
    $me->value = CE_Diagnosis::asVaricella();
    return $me;
  }
  static function asFinClass($imm) {
    $me = static::asFinal()->nextSubId();
    $me->valueType = ID_ValueType::CODED_ENTRY;
    $me->obsId = CE_Observation::asVacFundingProgram(); 
    $me->obsSubId = 1;
    $me->value = CE_FinancialClass::fromImmun($imm);
    $me->timestamp = TS::fromDate($imm->dateGiven);
    $me->method = CE_FundingMethod::asImmunLevel();
    return $me;
  }
  static function appendVacTypes(&$us, $imm, $cvx) {
    $gcvxs = static::$Immun_HL7->getCvxGroupsFor($cvx);
    foreach ($gcvxs as $i => $gcvx) {
      $us[] = static::asVacType($imm, $gcvx, $i);
      static::appendVISPair($us, $imm, $i);
    }
  }
  static function asVacType($imm, $gcvx, $i) {
    $me = static::asFinal()->nextSubId();
    $me->valueType = ID_ValueType::CODED_ENTRY;
    $me->obsId = CE_Observation::asVaccineType();
    $me->value = CE_CVX::from($gcvx, static::$Immun_HL7->getVacGroupDesc($gcvx));
    return $me;
  }
  static function appendVISPair(&$us, $imm, $i) {
    $dateVis = $imm->getDateVis($i);
    if ($dateVis) {
      static::appendVIS($us, CE_Observation::asVisPublished(), $dateVis);
      static::appendVIS($us, CE_Observation::asVisPresented(), $imm->dateGiven);
    }
  }
  static function appendVIS(&$us, $obsId, $date) {
    $me = static::asFinal();
    $me->valueType = ID_ValueType::TIMESTAMP;
    $me->obsId = $obsId;
    $me->value = TS::fromDate($date);
    $us[] = $me;
  }
  //
  public function nextSubId() {
    static::$_obsSubId++;
    $this->obsSubId = static::$_obsSubId;
    return $this;
  }
}
class OBX_ADT extends OBX {
  //
  static function all($fs) { 
    static::resetSeq();
    $us = array();
    static::append($us, static::asFacilityVisitType($fs));
    static::append($us, static::asPatientAge($fs));
    static::append($us, static::asChiefComplaint($fs));
    return $us;  
  }
  static function asFacilityVisitType($fs) {
    $type = $fs->getData('facilityVisitType');
    if ($type) {
      $me = static::asFinal();
      $me->valueType = ID_ValueType::CODED_WITH_EXCEPTIONS;
      $me->obsId = CE_Observation::asFacilityVisitType();
      $me->value = CE_CodeSystem::from($type->code, $type->text, 'NUCC');
      return $me;
    }
  }
  static function asPatientAge($fs) {
    $me = static::asFinal();
    $me->valueType = ID_ValueType::NUMERIC;
    $me->obsId = CE_Observation::asAge();
    $me->value = $fs->Client->ageYears;
    $me->units = CE_Units::asYears();
    return $me;
  }
  static function asChiefComplaint($fs) {
    $cc = $fs->getData('chiefComplaint');
    if ($cc) {
      $me = static::asFinal();
      $me->valueType = ID_ValueType::CODED_WITH_EXCEPTIONS;
      $me->obsId = CE_Observation::asChiefComplaint();
      $me->value = CWE::asChiefComplaint($cc->text);
      return $me;
    }
  }
}
