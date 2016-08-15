<?php
require_once 'php/data/hl7-2.5.1/msg/seg/_HL7Segment.php';
//
/**
 * Pharmacy/Treatment Admininstration v2.5.1
 * @author Warren Hornsby
 */
class RXA extends HL7Segment {
  //
  public $segId = 'RXA';
  public $giveSubId;  // 1: Give Sub-ID Counter (NM)
  public $adminSubId;  // 2: Administration Sub-ID Counter (NM)
  public $startAdmin = 'TS';  // 3: Date/Time Start of Administration (TS)
  public $endAdmin = 'TS';  // 4: Date/Time End of Administration (TS)
  public $code = 'CE';  // 5: Administered Code (CE)
  public $amount;  // 6: Administered Amount (NM)
  public $units = 'CE';  // 7: Administered Units (CE)
  public $dosage = 'CE';  // 8: Administered Dosage Form (CE)
  public $notes = 'CE';  // 9: Administration Notes (CE) 
  public $provider = 'XCN';  // 10: Administering Provider (XCN)
  public $location;  // 11: Administered-at Location (LA2)
  public $per;  // 12: Administered Per (Time Unit) (ST)
  public $strength;  // 13: Administered Strength (NM)
  public $strengthUnits;  // 14: Administered Strength Units (CE)
  public $lot;  // 15: Substance Lot Number (ST)
  public $expiration;  // 16: Substance Expiration Date (TS)
  public $manufac = 'CE';  // 17: Substance Manufacturer Name (CE)
  public $refusal = 'CE';  // 18: Substance/Treatment Refusal Reason (CE)
  public $indication = 'CE';  // 19: Indication (CE)
  public $completion;  // 20: Completion Status (ID)
  public $action;  // 21: Action Code - RXA (ID)
  public $entryDate;  // 22: System Entry Date/Time (TS)
  public $volume;  // 23: Administered Drug Strength Volume (NM)
  public $volumeUnits;  // 24: Administered Drug Strength Volume Units (CWE)
  public $barcode;  // 25: Administered Barcode Identifier (CWE)
  public $rxOrderType;  // 26: Pharmacy Order Type (ID)\
  //
  static function asStandard($date) {
    $me = static::asEmpty();
    $me->giveSubId = 0;
    $me->adminSubId = 1;
    $me->startAdmin = TS::fromDate($date);
    return $me;
  }
}
class RXA_VXU extends RXA {
  //
  /* Segments */
  public $RxRoute = 'RXR';  // optional
  public $Observation = 'OBX[]';  // optional
  //
  const NM_NOT_ADMIN = 999;
  //  
  static function loadIds($fs) {
    if ($fs->Immun_HL7) {
      HL70292::loadIds($fs->Immun_HL7->CVX);
      HL70292_byName::loadIds($fs->Immun_HL7->CVXI);
      HL70227::loadIds($fs->Immun_HL7->MVX);
      NIP001::loadIds($fs->Immun_HL7->source);
      NIP002::loadIds($fs->Immun_HL7->refusal);
    }
  }
  static function from($imm) {
    if ($imm->isHistorical()) { 
      return static::asHistorical($imm);
    } else if ($imm->isImmune()) { 
      return static::asImmune($imm);
    } else if ($imm->isRefused()) { 
      return static::asRefused($imm);
    } else if ($imm->isAdministered()) {
      return static::asAdministered($imm);
    }
  }
  static function asAdministered($imm) {
    $me = static::asStandard($imm->dateGiven);
    $me->code = CE_Immun::fromImmun($imm);
    $me->amount = $imm->dose;
    $me->units = CE_Units::fromImmun($imm);
    $me->notes = CE_ImmunNotes::fromImmun($imm);
    $me->provider = XCN::asImmProvider($imm);
    $me->location = LA2::asFacility($imm->userGroupId);
    $me->lot = $imm->lot;
    $me->expiration = TS::fromDate($imm->dateExp);
    $me->manufac = CE_ImmunManufac::fromImmun($imm);
    $me->completion = ID_Completion::asComplete();
    $me->action = ID_Action::asAdd();
    $me->RxRoute = RXR_VXU::from($imm);
    $me->Observation = OBX_VXU::all($imm, $me->code->getId());
    return $me;
  }
  static function asHistorical($imm) {
    $me = static::asStandard($imm->dateGiven);
    $me->code = CE_Immun::fromImmun($imm);
    $me->amount = static::NM_NOT_ADMIN;
    $me->notes = CE_ImmunNotes::fromImmun($imm);
    return $me;
  }
  static function asImmune($imm) {
    $me = static::asStandard($imm->dateGiven);
    $me->code = CE_Immun::asNotAdministered();
    $me->amount = static::NM_NOT_ADMIN;
    $me->completion = ID_Completion::asNotAdmin();
    $me->Observation = OBX_VXU::all($imm);
    return $me;
  }
  static function asRefused($imm) {
    $me = static::asStandard($imm->dateGiven);
    $me->code = CE_Immun::fromImmun($imm);
    $me->amount = static::NM_NOT_ADMIN;
    $me->refusal = CE_Refusal::fromImmun($imm);
    $me->completion = ID_Completion::asRefused();
    return $me;
  }
}
