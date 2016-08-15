<?php
require_once 'php/data/hl7-2.5.1/msg/seg/_HL7Segment.php';   
//
/**
 * Patient Identification v2.5.1
 * @author Warren Hornsby
 */
class PID extends HL7SequencedSegment {
  //
  public $segId = 'PID';
  public $seq;  // Set ID - PID (SI)
  public $patientId = 'CX';  // Patient ID (CX)
  public $patientIdList = 'CX[]';  // Patient Identifier List (CX)
  public $altPid = 'CX';  // Alternate Patient ID - PID (CX)
  public $name = 'XPN';  // Patient Name (XPN)
  public $mothersMaiden;  // Mother's Maiden Name (XPN)
  public $birthDate = 'TS';  // Date/Time of Birth (TS)
  public $gender;  // Administrative Sex (IS)
  public $alias = 'XPN';  // Patient Alias (XPN)
  public $race = 'CE';  // Race (CE)
  public $address = 'XAD';  // Patient Address (XAD)
  public $county;  // County Code (IS)
  public $phoneHome = 'XTN';  // Phone Number - Home (XTN)
  public $phoneWork = 'XTN';  // Phone Number - Business (XTN)
  public $language = 'CE';  // Primary Language (CE)
  public $marital = 'CE';  // Marital Status (CE)
  public $religion = 'CE';  // Religion (CE)
  public $account = 'CX';  // Patient Account Number (CX)
  public $ssn;  // SSN Number - Patient (ST)
  public $license;  // Driver's License Number - Patient (DLN)
  public $mother;  // Mother's Identifier (CX)
  public $ethnic = 'CE';  // Ethnic Group (CE)
  public $birthplace;  // Birth Place (ST)
  public $multipleBirth;  // Multiple Birth Indicator (ID)
  public $birthOrder;  // Birth Order (NM)
  public $citizenship = 'CE';  // Citizenship (CE)
  public $veteran = 'CE';  // Veterans Military Status (CE)
  public $nationality = 'CE';  // Nationality (CE)  
  public $deathDate = 'TS';  // Patient Death Date and Time (TS)
  public $death;  // Patient Death Indicator (ID)
  public $unknown;  // Identity Unknown Indicator (ID)
  public $reliability;  // Identity Reliability Code (IS)
  public $lastUpdate = 'TS';  // Last Update Date/Time (TS)
  public $lastUpdateFacility;  // Last Update Facility (HD)
  public $species = 'CE';  // Species Code (CE)
  public $breed = 'CE';  // Breed Code (CE)
  public $strain;  // Strain (ST)
  public $productionClass = 'CE';  // Production Class Code (CE)
  public $tribal;  // Tribal Citizenship (CWE)
  //
  static $_seq = 0;
}
//
class PID_VXU extends PID {
  //
  public $PatientDemo = 'PD1';
  //
  static function from(/*Facesheet_Immun*/$fs) {
    static::resetSeq();
    $me = static::asEmpty();
    $me->patientIdList = CX::asPatientList($fs);
    $me->name = XPN::asPatient($fs->Client);
    $me->birthDate = TS::fromDate($fs->Client->birth);
    $me->gender = IS_Gender::fromPatient($fs->Client);
    $me->race = CE_Race::fromPatient($fs->Client);
    $me->address = XAD::asLegal($fs->Client->Address_Home);
    $me->phoneHome = XTN::fromAddress($fs->Client->Address_Home);
    $me->ethnic = CE_Ethnic::fromPatient($fs->Client);
    $me->mothersMaiden = XPN::fromAddress($fs->Client->Address_Mother);
    if ($fs->Client->immRegReminders || $fs->Client->immRegRefuse) {
      $me->PatientDemo = PD1_VXU::create($fs);
    }
    return $me;
  }
}
class PID_ADT extends PID {
  //
  public $PatientVisit = 'PV1'; 
  //
  static function from(/*Facesheet_Syndrome*/$fs) {
    $me = static::asEmpty();
    $me->patientIdList = CX::asPatientList($fs);
    $me->name = XPN::asAnonymous();
    $me->gender = IS_Gender::fromPatient($fs->Client);
    $me->race = CE_Race::fromPatient($fs->Client);
    $me->address = XAD::asAnonymous($fs->Client->Address_Home);
    $me->ethnic = CE_Ethnic::fromPatient($fs->Client);
    $me->PatientVisit = PV1_ADT::from($fs);
    return $me;
  }
}