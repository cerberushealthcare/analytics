<?php
//
/** POCD_Qrda */
class Entry_Qrda extends Entry {
  //
  static function asMeasure($cqm) {
    $e = new static();
    $e->organizer = Organizer_Cqm::asMeasure($cqm);
    return $e;
  }
  static function asCat3Measure($cqm) {
    $e = new static();
    $e->organizer = Organizer_Cqm::asCat3Measure($cqm);
    return $e;
  }
  static function asReportParams($cqm) {
    $e = new static('DRIV');
    $e->act = Act_Cqm::asReportParams($cqm);
    return $e;
  }
}
class Entry_Qrda_Data extends Entry {
  //
  static function asEncounterPerformed($proc, $sdtc = '2.16.840.1.113883.3.464.1003.101.12.1001') { //2.16.840.1.113883.3.600.1916
    $e = new static();
    $e->encounter = Encounter_Cqm::asPerformed($proc, $sdtc);
    return $e;
  } 
  static function asRiskCategoryAssessment($proc) {
    $e = new static();
    $e->observation = Observation_Cqm::asRiskAss($proc);
    return $e;
  }
  static function asFunctionalStatusResult($proc) {
    $e = new static();
    $e->observation = Observation_Cqm::asFuncStat($proc);
    return $e;
  }
  static function asTobaccoUse($proc, $sdtc = null) {
    $e = new static();
    $e->observation = Observation_Cqm::asTobacco($proc, $sdtc);
    return $e;
  }
  static function asDiagnosisActive($diag, $sdtc = null) {
    $e = new static();
    $e->observation = Observation_Cqm::asDiagnosis($diag, $sdtc);
    return $e;
  }
  static function asInterventionPerformed($proc) {
    $e = new static();
    $e->act = Act_Cqm::asIntervention($proc);
    return $e;
  }
  static function asPhysicalExamPerformed($proc) {
    $e = new static();
    $e->observation = Observation_Cqm::asExam($proc);
    return $e;
  }
  static function asProcedurePerformed($proc) {
    $e = new static();
    $e->procedure = Procedure_Cqm::fromProc($proc);
    return $e;
  }
  static function asCommunication($proc) {
    $e = new static();
    $e->act = Act_Cqm::asCommunication($proc);
    return $e;
  }
  static function asMedicationOrder($med, $sdtc = null) {
    $e = new static();
    $e->substanceAdministration = SubstanceAdministration_Cqm::from($med, $sdtc);
    return $e;
  }
} 
class SubstanceAdministration_Cqm extends SubstanceAdministration {
  //
  static function from($med, $sdtc = null) {
    $e = new static();
    $e->_classCode = 'SBADM';
    $e->_moodCode = 'RQO';
    $e->templateId = InfrastructureRoot_templateId::from(
      '2.16.840.1.113883.10.20.22.4.42', '2.16.840.1.113883.10.20.24.3.47');        
    $e->id = II::fromMed($med);
    $e->statusCode = CS_Status::asNew();
    $e->effectiveTime = SXCM_TS::fromMed($med);
    $e->routeCode = CE_NCI_Route::fromNewCrop(get($med, 'ncRouteId'));
    $e->doseQuantity = IVL_PQ::asDoseQuantity($med);
    $e->consumable = Consumable_Cqm::fromMed($med, $sdtc);
    $e->addEntryRelationship(EntryRelationship::asRefersToObservation(Observation_MedStatus::asActive()));
    return $e;
  }
}
class Consumable_Cqm extends Consumable {
  //
  static function fromMed($med, $sdtc = null) {
    $e = new static();
    $e->manufacturedProduct = ManufacturedProduct::byManufacturedMaterial(Material_Cqm::fromMed($med, $sdtc));
    return $e;
  } 
}
class Material_Cqm extends Material {
  //
  static function fromMed($med, $sdtc) {
    $e = new static();
    $e->code = new CE_RxNorm($med->index, $med->name);
    $e->code->originalText->_ = $med->name;
    if ($sdtc) {
      $e->code->_sdtc_valueSet = $sdtc;
    }
    return $e;    
  }
}
class Procedure_Cqm extends Procedure {
  //
  static function fromProc($proc) {
    $e = new static('PROC', 'EVN');
    $e->templateId = InfrastructureRoot_templateId::from(
      '2.16.840.1.113883.10.20.22.4.14', '2.16.840.1.113883.10.20.24.3.64');
    $e->id = II::fromProc($proc);
    $e->code = CE_Cqm::from($proc);
    $e->statusCode = CS_Status::asCompleted();
    $e->effectiveTime = IVL_TS::asLowHigh($proc->date, $proc->dateTo ?: $proc->date);
    return $e;
  }
}
class Observation_Cqm extends Observation {
  //
  static function asExam($proc) {
    $e = new static();
    $e->templateId = InfrastructureRoot_templateId::from(
      '2.16.840.1.113883.10.20.22.4.13','2.16.840.1.113883.10.20.24.3.59');
    $e->id = II::fromProc($proc);
    $e->code = CE_Cqm::from($proc);
    $e->statusCode = CS_Status::asCompleted();
    $e->effectiveTime = IVL_TS::asLowHigh($proc->date, $proc->dateTo ?: $proc->date);
    $e->value = CD_Cqm::fromResult(get($proc, 'Result'));
    return $e;
  }
  static function asTobacco($proc, $sdtc) {
    $e = new static();
    $e->id = II::fromProc($proc);
    $e->code = CE_HL7_ActCode::asAssertion();
    $e->statusCode = CS_Status::asCompleted();
    $e->effectiveTime = IVL_TS::asLowHigh($proc->date, $proc->dateTo ?: $proc->date);
    $e->value = CD_SNOMED::from($proc);
    if ($sdtc) {
      $e->value->_sdtc_valueSet = $sdtc;
    }
    return $e;
  }
  static function asRiskAss($proc) {
    $e = new static();
    $e->templateId = InfrastructureRoot_templateId::from(
      '2.16.840.1.113883.10.20.22.4.69', '2.16.840.1.113883.10.20.24.3.69');
    $e->id = II::fromProc($proc);
    $e->code = CE_Cqm::from($proc);
    $e->effectiveTime = IVL_TS::asLowHigh($proc->date, $proc->dateTo ?: $proc->date);
    if (get($proc, 'Result')) {
      $e->value = CE_Cqm::asResultValue(get($proc, 'Result'));
    } else {
      $e->value = CE_Cqm::from($proc);
      $e->value->_xsi_type = 'CD';
    }
    return $e;
  }
  static function asFuncStat($proc) {
    $e = new static();
    $e->templateId = InfrastructureRoot_templateId::from(
      '2.16.840.1.113883.10.20.22.4.67', '2.16.840.1.113883.10.20.24.3.28');
    $e->id = II::fromProc($proc);
    $e->code = CE_Cqm::from($proc);
    $e->effectiveTime = IVL_TS::asLowHigh($proc->date, $proc->dateTo ?: $proc->date);
    $e->value = CD_Cqm::fromResult(get($proc, 'Result'));
    return $e;
  }
  static function asDiagnosis($diag, $sdtc = null) {
    $e = new static();
    $e->templateId = InfrastructureRoot_templateId::from(
      '2.16.840.1.113883.10.20.22.4.4', '2.16.840.1.113883.10.20.24.3.11');
    $e->id = II::fromDiag($diag);
    $e->code = new CE_SNOMED('282291009', 'diagnosis');
    $e->statusCode = CS_Status::asCompleted();
    $e->effectiveTime = IVL_TS::asLowHigh($diag->date, $diag->dateClosed);
    $e->value = CE_Cqm::asDiagValue($diag, $sdtc);
    $e->entryRelationship = EntryRelationship_Cqm::asActive();
    return $e;
  }
  static function asActive() {
    $e = new static();
    $e->templateId = InfrastructureRoot_templateId::from(
      '2.16.840.1.113883.10.20.22.4.6', '2.16.840.1.113883.10.20.24.3.94');
    $e->id = II::random();
    $e->code = new CE_LOINC('33999-4', 'status');
    $e->statusCode = CS_Status::asCompleted();
    $e->value = CD_SNOMED::asActive();
    return $e;
  }
  static function asPopMeasure($popcode, $oid, $scorecard) {
    $e = new static();
    $e->templateId = InfrastructureRoot_templateId::from('2.16.840.1.113883.10.20.27.3.5');
    $e->code = CE_HL7_ActCode::asAssertion();
    $e->statusCode = CS_Status::asCompleted();
    $e->value = CD_Cqm::asPopulation($popcode);
    $e->entryRelationship = EntryRelationship_Cqm::fromScorecard($scorecard);
    $e->reference = Reference_Cqm::toObservation($oid);
    return $e;
  }
  static function asMeasureCount($count) {
    $e = new static();
    $e->templateId = InfrastructureRoot_templateId::from('2.16.840.1.113883.10.20.27.3.3');
    $e->code = new CE_HL7_ActCode('MSRAGG', 'rate aggregation');
    $e->value = CD_Cqm::asInt($count);
    $e->methodCode = CD_Cqm::asMethodCount();
    unset ($e->statusCode);
    return $e; 
  }
  static function asOid($oid) {
    $e = new static();
    $e->id = II::from($oid);
    unset ($e->statusCode);
    return $e;
  }
}
class EntryRelationship_Cqm extends EntryRelationship {
  //
  static function asActive() {
    $e = static::asRefersToObservation(Observation_Cqm::asActive());
    return $e;
  } 
  static function fromScorecard($scorecard) {
    $us = array(static::asMeasureCount($scorecard->aggregate));
    $us = array_merge($us, EntryRelationship_Cqm_Supp::all($scorecard));
    return $us;
  }
  static function asMeasureCount($count) {
    $e = static::asSubjectObservation(Observation_Cqm::asMeasureCount($count), true);
    return $e;
  }
}
class EntryRelationship_Cqm_Supp extends EntryRelationship_Cqm {
  //
  static function all($scorecard) {
    $us = array();
    $us = array_merge($us, static::allEthnic($scorecard));
    $us = array_merge($us, static::allRace($scorecard));
    $us = array_merge($us, static::allSex($scorecard));
    $us = array_merge($us, static::allPayer($scorecard));
    return $us;
  }
  static function allEthnic($scorecard) {
    $array = $scorecard->ethnicity;
    $templateId = InfrastructureRoot_templateId::from('2.16.840.1.113883.10.20.27.3.7');
    $code = new CE_SNOMED('364699009', 'Ethnic Group');
    $us = array();
    if ($array) {
      foreach ($array as $v => $count) {
        $value = CD_Cqm_Supp::asEthnic($v);
        $us[] = static::from($templateId, $code, $value, $count);
      }
    }
    return $us;
  }
  static function allRace($scorecard) {
    $array = $scorecard->race;
    $templateId = InfrastructureRoot_templateId::from('2.16.840.1.113883.10.20.27.3.8');
    $code = new CE_SNOMED('103579009', 'Race');
    $us = array();
    if ($array) {
      foreach ($array as $v => $count) {
        $value = CD_Cqm_Supp::asRace($v);
        $us[] = static::from($templateId, $code, $value, $count);
      }
    }
    return $us;
  }
  static function allSex($scorecard) {
    $array = $scorecard->sex;
    $templateId = InfrastructureRoot_templateId::from('2.16.840.1.113883.10.20.27.3.6');
    $code = new CE_SNOMED('184100006', 'Gender');
    $us = array();
    if ($array) {
      foreach ($array as $v => $count) {
        $value = CD_Cqm_Supp::asSex($v);
        $us[] = static::from($templateId, $code, $value, $count);
      }
    }
    return $us;
  }
  static function allPayer($scorecard) {
    $array = $scorecard->payer;
    $templateId = InfrastructureRoot_templateId::from('2.16.840.1.113883.10.20.24.3.55', '2.16.840.1.113883.10.20.27.3.9');
    $code = new CE_LOINC('48768-6', 'Payment source');
    $us = array();
    if ($array) {
      foreach ($array as $v => $count) {
        $value = CD_Cqm_Supp::asPayer($v);
        $us[] = static::from($templateId, $code, $value, $count, true);
      }
    }
    return $us;
  }
  static function from($templateId, $code, $value, $count, $asPayor = false) {
    $e = new static('COMP');
    $e->observation = new Observation_Cqm();
    $e->observation->templateId = $templateId;
    if ($asPayor) {
      $e->observation->id = II::asNull('NA');
      $e->observation->effectiveTime = IVL_TS::asLowUnk(); 
    }
    $e->observation->code = $code;
    $e->observation->value = $value;
    $e->observation->entryRelationship = static::asMeasureCount($count);
    return $e;
  }
}
class Encounter_Cqm extends Encounter {
  //
  static function asPerformed($proc, $sdtc = null) {
    $e = new static('ENC', 'EVN');
    $e->templateId = InfrastructureRoot_templateId::from(
      '2.16.840.1.113883.10.20.22.4.49',
      '2.16.840.1.113883.10.20.24.3.23');
    $e->id = II::fromProc($proc);
    $e->code = CE::from('99212', '2.16.840.1.113883.6.12', 'Outpatient Visit');
    if ($sdtc) {
      $e->code->_sdtc_valueSet = $sdtc;
    }
    $e->text = 'Encounter, Performed';
    $e->statusCode = CS_Status::asCompleted();
    $e->effectiveTime = IVL_TS::asLowHigh($proc->date, $proc->dateTo ?: $proc->date);
    return $e;
  }
}
class Act_Cqm extends Act {
  //
  static function asReportParams($cqm) {
    $e = new static('ACT', 'EVN');
    $e->templateId = InfrastructureRoot_templateId::from('2.16.840.1.113883.10.20.17.3.8');
    $e->id = II::random();
    $e->code = new CE_SNOMED('252116004', 'Observation Parameters');
    $e->effectiveTime = IVL_TS::asLowHigh($cqm->from, $cqm->to);
    return $e;
  }
  static function asIntervention($proc) {
    $e = new static('ACT', 'EVN');
    $e->templateId = InfrastructureRoot_templateId::from(
      '2.16.840.1.113883.10.20.22.4.12', '2.16.840.1.113883.10.20.24.3.32');
    $e->id = II::fromProc($proc);
    $e->code = CE_Cqm::from($proc);
    $e->statusCode = CS_Status::asCompleted();
    $e->effectiveTime = IVL_TS::asLowHigh($proc->date, $proc->dateTo ?: $proc->date);
    return $e;
  }
  static function asCommunication($proc) {
    $e = new static('ACT', 'EVN');
    $e->templateId = InfrastructureRoot_templateId::from('2.16.840.1.113883.10.20.24.3.4');
    $e->id = II::fromProc($proc);
    $e->code = CE_Cqm::from($proc);
    $e->statusCode = CS_Status::asCompleted();
    $e->effectiveTime = IVL_TS::asLowHigh($proc->date, $proc->dateTo ?: $proc->date);
    $e->participant = Participant_Cqm::asProviderToProvider();
    return $e;
  }
}
class Participant_Cqm extends Participant2 {
  //
  static function asProviderToProvider() {
    $e1 = new static('AUT');
    $e1->participantRole = ParticipantRole_Cqm::asMd();
    $e2 = new static('IRCP');
    $e2->participantRole = ParticipantRole_Cqm::asMd();
    return array($e1, $e2);
  }
}
class ParticipantRole_Cqm extends ParticipantRole {
  //
  static function asMd() {
    $e = new static('ASSIGNED');
    $e->code = new CE_SNOMED('158965000', 'Medical Practitioner');
    return $e;
  }
}
class Organizer_Cqm extends Organizer {
  //
  static function asMeasure($cqm) {
    $e = new static('CLUSTER');
    $e->templateId = InfrastructureRoot_templateId::from(
      '2.16.840.1.113883.10.20.24.3.98', '2.16.840.1.113883.10.20.24.3.97');
    $e->id = II::random();
    $e->reference = Reference_Cqm::toDocument($cqm);
    return $e;
  } 
  static function asCat3Measure($cqm) {
    $e = new static('CLUSTER');
    $e->templateId = InfrastructureRoot_templateId::from(
      '2.16.840.1.113883.10.20.24.3.98', '2.16.840.1.113883.10.20.27.3.1');
    $e->id = II::random();
    $e->reference = Reference_Cqm::toDocument($cqm, true);
    $e->component = Component4_Cqm::allForCqm($cqm);
    return $e;
  }
}
class Reference_Cqm extends Reference {
  //
  static function toDocument($cqm, $forCat3 = false) {
    $e = new static();
    $e->_typeCode = 'REFR';
    $e->externalDocument = ($forCat3) ? ExternalDocument_Cqm::forCat3($cqm) : ExternalDocument_Cqm::forCat1($cqm);
    return $e;
  }
  static function toObservation($oid) {
    $e = new static();
    $e->_typeCode = 'REFR';
    $e->externalObservation = Observation_Cqm::asOid($oid);
    return $e;
  } 
}
class ExternalDocument_Cqm extends ExternalDocument {
  //
  static function forCat1($cqm) {
    return static::forCat3($cqm);
    $e = new static();
    $e->_classCode = 'DOC';
    $e->_moodCode = 'EVN';
    $e->id = II::from($cqm->id);
    $e->text = $cqm->title;
    $e->setId = II::from($cqm->setid);
    $e->versionNumber = INT::from($cqm->version);
    return $e;
  }
  static function forCat3($cqm) {
    $e = new static();
    $e->_classCode = 'DOC';
    $e->_moodCode = 'EVN';
    $e->id = II::from('2.16.840.1.113883.4.738', $cqm->id);
    $e->text = $cqm->title;
    $e->setId = II::from($cqm->setid);
    $e->versionNumber = INT::from($cqm->version);
    return $e;
  }
}
class Component4_Cqm extends Component4 {
  //
  static function allForCqm($cqm) {
    $us = array();
    foreach ($cqm->Pops as $pop) {
      $us = array_merge($us, static::allForPop($pop));
    }
    return $us;
  }
  static function allForPop($pop) {
    $us = array();
    $us[] = static::from('IPP', $pop::$IPP, $pop->scorecard_IPP());
    $us[] = static::from('DENOM', $pop::$DENOM, $pop->scorecard_DENOM());
    if ($pop::$DENEX) 
      $us[] = static::from('DENEX', $pop::$DENEX, $pop->scorecard_DENEX());
    $us[] = static::from('NUMER', $pop::$NUMER, $pop->scorecard_NUMER());
    if ($pop::$NUMER2)
      $us[] = static::from('NUMER', $pop::$NUMER2, $pop->scorecard_NUMER2());
    if ($pop::$DENEXCEP) 
      $us[] = static::from('DENEXCEP', $pop::$DENEXCEP, $pop->scorecard_DENEXCEP());
    return $us;
  }
  static function from($popcode, $oid, $scorecard) {
    $e = new static();
    $e->observation = Observation_Cqm::asPopMeasure($popcode, $oid, $scorecard);
    return $e;
  }
}
/** CodeSystems_Cqm */
class CE_Cqm extends CE_CodeSystem {
  //
  static function from($proc) {
    if (get($proc, 'Result') && $proc->isNotDone())
      return CE_Cqm::from($proc->Result);
    $ipc = get($proc, 'Ipc');
    if ($ipc) {
      $e = static::fromIpc($ipc);
      $sdtc = $proc->getSdtcValueSet();
      if ($sdtc) {
        $e->_sdtc_valueSet = $sdtc;
      }
      return $e;
    }
  }
  static function asResultValue($result) {
    $e = static::from($result);
    return static::asCd($e);
  }
  static function asDiagValue($diag, $sdtc = null) {
    if ($diag->icd)
      $e = CE_ICD9::fromDiag($diag);
    else
      $e = new CE_SNOMED($diag->snomed);
    if ($sdtc) {
      $e->_sdtc_valueSet = $sdtc;
    }
    return static::asCd($e);
  }
  static function fromIpc($ipc) {
    if ($ipc) {
      if ($ipc->codeSnomed)
        return new CE_SNOMED($ipc->codeSnomed, $ipc->name);
      if ($ipc->codeIcd9)
        return new CE_ICD9($ipc->codeIcd9, $ipc->name);
      if ($ipc->codeLoinc)
        return new CE_LOINC($ipc->codeLoinc, $ipc->name);
      if ($ipc->codeCpt)
        return new CE_CPT4($ipc->codeCpt, $ipc->name);
      return new static($ipc->ipc, $ipc->name);      
    }
  } 
  //
  protected static function asCd($e) {
    $e->_xsi_type = 'CD';
    unset($e->_codeSystemName);
    unset($e->_displayName);
    return $e;
  }
}
class CD_Cqm extends CD {
  //
  static function asEthnicity($v) {
    $e = new static();
    $e->_xsi_type = 'CD';
    $e->_codeSystem = '2.16.840.1.113883.6.238';
    switch ($v) {
      case '1':
        $e->_code = '2135-2';
        $e->_displayName = 'Hispanic or Latino';
    }
  }
  static function asPopulation($code) {
    $e = new static();
    $e->_xsi_type = 'CD';
    $e->_codeSystem = '2.16.840.1.113883.5.1063';
    $e->_codeSystemName = 'ObservationValue';
    $e->_code = $code;
    return $e;
  }
  static function asMethodCount() {
    $e = new static();
    $e->_codeSystem = '2.16.840.1.113883.5.84';
    $e->_codeSystemName = 'Observation Method';
    $e->_code = "COUNT";
    return $e;
  }
  static function asInt($value) {
    $e = new static();
    $e->_xsi_type = 'INT';
    $e->_value = $value;
    return $e;
  }  
  static function fromResult($result) {
    if ($result == null) {
      $e = CD::asNull('UNK');
      $e->_xsi_type = 'CD';
    } else {
      if ($result->value) {
        if (is_numeric($result->value)) { 
          $e = PQ::from($result->value, $result->valueUnit);
          $e->_xsi_type = 'PQ';
        } else {
          $e = ST::asText($result->value);
          $e->_xsi_type = 'ST';
        }
      } else if ($e->interpretationCode) {
        $e = CD::from($e->interpretationCode->_code, $e->interpretationCode->_codeSystem);
        $e->_xsi_type = 'CD';
      } else {
        $e = CD::asNull('UNK');
        $e->_xsi_type = 'CD';
      }
    }
    return $e;
  }
}
class CD_Cqm_Supp extends CD {
  //
  static $MAP_ETHNIC = array(
    'Hispanic or Latino' => '2135-2',
    'Not Hispanic or Latino' => '2186-5');
  static $MAP_RACE = array(
    'American Indian or Alaska Native' => '1002-5',
    'Asian' => '2028-9',
    'Native Hawaiian or Other Pacific Islander' => '2076-8',
    'Black or African-American' => '2054-5', 
    'White' => '2106-3',
    'Other' => '2131-1');
  static $MAP_SEX = array(
    'M' => 'M',
    'F' => 'F');
  static $MAP_PAYER = array(
    'Other' => '349');
  //
  static function asEthnic($value) {
    return static::from('2.16.840.1.113883.6.238', static::$MAP_ETHNIC, geta(ClientRec::$ETHNICITIES, $value));
  }
  static function asRace($value) {
    return static::from('2.16.840.1.113883.6.238', static::$MAP_RACE, geta(ClientRec::$RACES, $value, 'Other'));
  }
  static function asSex($value) {
    return static::from('2.16.840.1.113883.5.1', static::$MAP_SEX, $value);
  }
  static function asPayer($value) {
    return static::from('2.16.840.1.113883.5.1', static::$MAP_PAYER, 'Other');
  }
  static function from($cs, $map, $key) {
    if ($key == "(Declined to Answer)") {
      $e = CD::asNull('ASKU');
      $e->_xsi_type = 'CD';
      return $e;
    }
    $cv = geta($map, $key);
    if ($cv == null) {
      $e = CD::asNull('UNK');
      $e->_xsi_type = 'CD';
      return $e;
    }
    $e = new static();
    $e->_xsi_type = 'CD';
    $e->_codeSystem = $cs;
    $e->_code = $cv;
    $e->_displayName = $key;
    return $e;
  }
}
