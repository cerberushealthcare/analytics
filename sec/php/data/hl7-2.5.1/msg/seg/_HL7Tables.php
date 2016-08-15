<?php 
/**
 * HL7 Tables
 */
abstract class HL7Table {
  static $IDS;
  //
  static function lookup($text) {
    return array_search($text, static::$IDS);  
  }  
  static function getText($id) {
    return static::$IDS[$id];
  }
  static function getCodingSystem() {
    return get_called_class();
  }
}
abstract class HL7LoadableTable extends HL7Table {
  static function loadIds($ids) {  // array('ID'=>'Text',..)
    static::$IDS = $ids;
  }
}
abstract class HL7LoadableReverseTable extends HL7Table {
  static function loadIds($ids) {  // array('Text'=>'ID',..)
    static::$IDS = $ids;
  }
  static function lookup($text) {
    return geta(static::$IDS, $text);  
  }  
}
/* RACE */
class HL70005 extends HL7Table {
  static $IDS = array(
    '1002-5' => 'American Indian or Alaska Native',
    '2028-9' => 'Asian',
    '2076-8' => 'Native Hawaiian or Other Pacific Islander',
    '2054-5' => 'Black or African-American', 
    '2106-3' => 'White',
    '2131-1' => 'Other');
  //
  static function getCodingSystem() {
    return 'CDCREC';
  }
  static function lookup($clientRace) {
    switch ($clientRace) {
      case Client::RACE_NATIVE_AMER_ALASKA:
        return '1002-5';
      case Client::RACE_ASIAN:
        return '2028-9';
      case Client::RACE_HAW_PAC_ISLAND:
        return '2076-8';
      case Client::RACE_BLACK:
        return '2054-5';
      case Client::RACE_WHITE:
        return '2106-3';
      default:
        return '';
    }
  }
}
/* ETHNIC GROUP */
class HL70189 extends HL7Table {
  static $IDS = array(
    '2135-2' => 'Hispanic or Latino',
    '2186-5' => 'Not Hispanic or Latino');
  //
  static function lookup($client) {
    switch ($client->ethnicity) {
      case Client::ETHN_HISPANIC:
        return '2135-2';
      case Client::ETHN_NOT_HISPANIC:
        return '2186-5';
      default:
        return '';
    }
  }
  static function getCodingSystem() {
    return 'CDCREC';
  }
}
/* ADDRESS TYPE */
class HL70190 extends HL7Table {
  static $IDS = array(
    'C' => 'Current',
    'P' => 'Permanent',
    'M' => 'Mailing',
    'B' => 'Business',
    'O' => 'Office',
    'H' => 'Home',
    'L' => 'Legal',
    'BR' => 'Residence at Birth');
  //
  static function asHome() {
    return 'H';
  }
  static function asLegal() {
    return 'L';
  }
}
/* TELECOMMUNICATION USE */
class HL70201 extends HL7Table {  
  static $IDS = array(
    'EMR' => 'Emergency Number',
    'NET' => 'Network (Email) Address',
    'ORN' => 'Other Residence Number',
    'PRN' => 'Primary Residence Number',
    'WPN' => 'Work Number');
  //
  static function lookup($phoneType) {
    switch ($phoneType) {
      case Address::PHONE_TYPE_PRIMARY:
        return 'PRN';
      case Address::PHONE_TYPE_WORK:
        return 'WPN';
      case Address::PHONE_TYPE_EMER:
        return 'EMR';
      default:
        return 'ORN';
    }
  }
  static function asEmail() {
    return 'NET';
  }
}
class HL70202 extends HL7Table {
  static $IDS = array(
    'PH' => 'Telephone',
    'FX' => 'Fax',
    'CP' => 'Cellular phone',
    'Internet' => 'Internet address');
  //
  static function lookup($phoneType) {
    switch ($phoneType) {
      case Address::PHONE_TYPE_CELL:
        return 'CP';
      case Address::PHONE_TYPE_FAX:
        return 'FX';
      default:
        return 'PH';
    }
  }
}
/* RELATIONSHIP */
class HL70063 extends HL7Table {
  static $IDS = array(
    'BRO' => 'Brother',
    'CGV' => 'Care giver',
    'FCH' => 'Foster child',
    'FTH' => 'Father',
    'GRD' => 'Guardian',
    'GRP' => 'Grandparent',
    'MTH' => 'Mother',
    'OTH' => 'Other',
    'PAR' => 'Parent',
    'SCH' => 'Stepchild',
    'SEL' => 'Self',
    'SIB' => 'Sibling',
    'SIS' => 'Sister',
    'SPO' => 'Spouse');
  //
  static function asMother() {
    return 'MTH';
  }
  static function asFather() {
    return 'FTH';
  }
  static function asSpouse() {
    return 'SPO';
  }
}
/* PUBLICITY CODE */
class HL70215 extends HL7Table {
  static $IDS = array(
    '01' => 'No reminder/recall',
    '02' => 'Reminder/recall - any method',
    '03' => 'Reminder/recall - no calls',
    '04' => 'Reminder only - any method',
    '05' => 'Reminder only - no calls',
    '06' => 'Recall only - any method',
    '07' => 'Recall only - no calls',
    '08' => 'Reminder/recall - to provider',
    '09' => 'Reminder to provider',
    '10' => 'Only reminder to provider, no recall',
    '11' => 'Recall to provider',
    '12' => 'Only recall to provider, no reminder');
  //
  static function asReminderRecallAny() {
    return '02';
  }   
} 
/* VACCINE ADMINISTERED */
class HL70292 extends HL7LoadableReverseTable {  
  static $IDS;  // CVX
  static function getCodingSystem() {
    return 'CVX';
  }
}
class HL70292_byName extends HL70292 {  
  static $IDS;  // CVX
}
/* MANUFACTURER OF VACCINE */
class HL70227 extends HL7LoadableReverseTable { 
  static $IDS;  // MVX
  static function getCodingSystem() {
    return 'MVX';
  }
}
/* ROUTE OF ADMINISTRATION */
class HL70162 extends HL7LoadableReverseTable {
  static $IDS;  
}
/* ADMINISTRATIVE SITE */
class HL70163 extends HL7LoadableReverseTable {
  static $IDS;  
}
/* FINANCIAL CLASSES */
class HL70064 extends HL7LoadableReverseTable {
  static $IDS;  
}
/* IMMUNIZATION INFORMATION SOURCE */
class NIP001 extends HL7LoadableReverseTable {
  static $IDS;  
}
/* SUBSTANCE REFUSAL REASON */
class NIP002 extends HL7LoadableReverseTable {
  static $IDS;  
} 
/*
 * External Coding Systems
 */
class XCodeSystems {
  const HL7_CODING_SYSTEM_TABLE = 'HL70396';
  const UCUM = 'UCUM';
  const ICD9 = 'ICD-9-CM';
  const LOINC = 'LN';
  const SNOMED = 'SCT';
}
