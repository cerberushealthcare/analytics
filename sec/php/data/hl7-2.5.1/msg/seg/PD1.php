<?php
require_once 'php/data/hl7-2.5.1/msg/seg/_HL7Segment.php';   
//
/**
 * Patient Demographic v2.5.1
 * @author Warren Hornsby
 */
class PD1 extends HL7Segment {
  //
  public $segId = 'PD1';
  public $livingDependency;  // Living Dependency (IS) optional repeating
  public $livingArrangement;  // Living Arrangement (IS) optional
  public $primaryFacility;  // Patient Primary Facility (XON) optional repeating
  public $primaryCareProvider;  // Patient Primary Care Provider Name & ID No. (XCN) optional repeating
  public $studentId;  // Student Indicator (IS) optional
  public $handicap;  // Handicap (IS) optional
  public $livingWill;  // Living Will (IS) optional
  public $organDonor;  // Organ Donor (IS) optional
  public $separateBill;  // Separate Bill (ID) optional
  public $dupePatient;  // Duplicate Patient (CX) optional repeating
  public $pubCode;  // Publicity Code (CE) optional
  public $protId;  // Protection Indicator (ID) optional
  public $protIdDate;  // Protection Indicator Effective Date (DT) optional
  public $placeWorship;  // Place of Worship (XON) optional repeating
  public $advanceDirective;  // Advance Directive Code (CE) optional repeating
  public $immRegStatus;  // Immunization Registry Status (IS) optional
  public $immRegStatusDate;  // Immunization Registry Status Effective Date (DT) optional
  public $pubCodeDate;  // Publicity Code Effective Date (DT) optional
}
//
class PD1_VXU extends PD1 {
  //
  static function create($fs) {
    $me = static::asEmpty();
    if ($fs->Client->immRegReminders) {
      $me->pubCode = CE_PubCode::from($fs->Client->immRegReminders);
      $me->immRegStatus = IS_ImmunRegStatus::ACTIVE;
      $me->immRegStatusDate = DT::asNow();
      $me->pubCodeDate = DT::asNow();
    }
    if ($fs->Client->immRegRefuse) { 
      $me->protId = $fs->Client->immRegRefuse;
      $me->protIdDate = DT::asNow();
    }
    return $me;
  }
}
