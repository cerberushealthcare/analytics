<?php
require_once 'php/data/hl7-2.5.1/msg/seg/_HL7Tables.php';
require_once 'php/newcrop/data/_NameParser.php';
//
/**
 * HL7 value
 */
class HL7Value extends HL7Rec {
  public $_data;  // original value (from HL7 message)
  public $_value;  // current value
  //
  public function getData() {
    return $this->_data;
  }
  public function setValue($value) {
    $this->_value = $value;
  }
  public function getValue() {
    return $this->_value;
  }
  //
  /**
   * @param string $value
   * @return HL7Value
   */
  static function from($value) {
    $me = new static();
    $me->_data = trim($value);
    $me->setValue($me->_data);
    return $me;
  }
}
class DT extends HL7Value {
  static function fromDate($date = null) {
    $ts = strtotime($date);
    return date('Ymd');
  }
  static function asNow() {
    return self::fromDate(nowShortNoQuotes());
  }
}
/* ID Sets */
class ID_AckType/*HL70155*/ extends HL7Value {
  const ALWAYS = 'AL';
  const NEVER = 'NE';
  const SUCCESS_ONLY = 'SU';
  const ERROR_ONLY = 'ER';
}
class ID_NameType/*HL70200*/ extends HL7Value {
  const LEGAL = 'L';
  const MAIDEN = 'M';
  const ADOPTED = 'C';
  const ALIAS = 'A';
  const DISPLAY = 'D';
  const BIRTH = 'B';
  const PARTNER = 'P';
  const UNSPECIFIED = 'U';
}
class ID_ResultStatus extends HL7Value {
  static function asFinal() {
    return 'F';
  }
}
class ID_ValueType extends HL7Value {
  /*alphanumeric*/
  const STRING = 'ST';
  const TEXT_DATA = 'TX';
  const FORMATTED_TEXT = 'FT';
  /*numerical*/
  const COMPOSITE_QTY = 'CQ';
  const MONEY = 'MO';
  const NUMERIC = 'NM';
  const SEQUENCE_ID = 'SI';
  const STRUCTURED_NUM = 'SN';
  /*identifier*/
  const HL7_ID = 'ID';
  const USERDEF_ID = 'IS';
  const PERSON_LOC = 'PL';
  const CODED_ENTRY = 'CE';
  const CODED_WITH_EXCEPTIONS = 'CWE';
  const ENCAPSULATED_DATA = 'ED';
  /*datetime*/
  const TIMESTAMP = 'TS';
  //
  public function isAlphanumeric() {
    switch ($this->_value) {
      case static::STRING:
      case static::TEXT_DATA:
      case static::FORMATTED_TEXT:
        return true;
    }
  }
}
class ID_Completion extends HL7Value {
  static function asComplete() {
    return 'CP';
  }
  static function asRefused() {
    return 'RE';
  }
  static function asNotAdmin() {
    return 'NA';
  }
}
class ID_Action extends HL7Value {
  static function asAdd() {
    return 'A';
  }
}
class IS_PatientClass extends HL7Value {
  static function asOutpatient() {
    return 'O';
  }
}
class IS_Gender extends HL7Value {
  static function fromPatient($c) {
    return static::from($c->sex);
  }
}
class IS_DiagnosisType extends HL7Value {
  static function asAdmitting() {
    return 'A';
  }
  static function asWorking() {
    return 'W';
  }
  static function asFinal() {
    return 'F';
  }
}
class IS_ValueType extends HL7Value {
  static function asCodedEntry() {
    return 'CE';
  }
  static function asNumeric() {
    return 'NM';
  }
}
class IS_ImmunRegStatus/*HL70441*/ extends HL7Value {
  const ACTIVE = 'A';
}
/* Processing Type */
class PT extends HL7Value {
  const TEST = 'T';
  const PRODUCTION = 'P';
  const DEBUGGING = 'D';
}
/**
 * HL7 component-delimited value
 */
abstract class HL7CompValue extends HL7Rec {
  public $_data;  // original value (from HL7 message)
  /**
   * @param string $value
   * @param ST_EncodingChars $encoding 
   * @return HL7CompValue
   */
  static function from($value, $encoding = null) {
    if ($encoding == null) {
      $encoding = ST_EncodingChars::asStandard();
    }
    if ($value) {
      $me = new static();
      $me->_data = trim($value);
      $me->setValues(self::decode($value, $encoding), $encoding);
      return $me;
    }
  }
  //
  protected static function isFid($var, $c1) {
    return parent::isFid($var, $c1) && ! self::isUpper($c1);
  }
  protected static function decode($value, $encoding) {  // ['value',..]
    if ($encoding) {
      $a = explode($encoding->compDelim, $value);
      if (count($a) > 1)
        return $a;
      return explode($encoding->subDelim, $value);
    } else {
      return array($value);
    }
  }
}
/**
 * Coded Entry
 */
class CE extends HL7CompValue {
  public $id;
  public $text;
  public $codingSystem;
  public $altId;
  public $altText;
  public $altCodingSystem;
  //
  public function isEmptyPrimary() {
    return (empty($this->id));
  }
  public function getId() {
    return ($this->isEmptyPrimary()) ? $this->altId : $this->id;
  }
  public function getText() {
    return ($this->isEmptyPrimary()) ? $this->altText : $this->text;
  }  
  public function getCodingSystem() {
    return ($this->isEmptyPrimary()) ? $this->altCodingSystem : $this->codingSystem;
  }
}
class CE_CodeSystem extends CE {
  static $CS;
  //
  static function from($id, $text = null, $codeSystem = null) {
    $me = new static();
    $me->id = $id;
    $me->text = $text ?: $id;
    $me->codingSystem = $codeSystem ?: static::$CS;
    return $me;
  }
}
class CE_Local extends CE_CodeSystem {
  static $CS = 'LOCAL CODE SET';
}
class CE_LOINC extends CE_CodeSystem {
  static $CS = 'LN';
}
class CE_SNOMED extends CE_CodeSystem {
  static $CS = 'SCT';
}
class CE_ICD9 extends CE_CodeSystem {
  static $CS = 'I9CDX';
  //
  static function fromDiagnosis($diag) {
    $id = $diag->icd;
    if ($id)
      return static::from($id, $diag->text);
    else
      return CE_Local::from($diag->dataDiagnosesId, $diag->text);
  }
}
class CE_Units extends CE_CodeSystem {
  static $CS = 'UCUM';
  //
  static function fromImmun($imm) {
    if ($imm->dose)
      return static::asMl();
  }
  static function asMl() {
    return static::from('mL', 'milliliter');
  }
  static function asYears() {
    return static::from('a', 'year');
  }
}
class CE_Diagnosis extends CE_SNOMED {
  //
  static function asVaricella() {
    return static::from('38907003', 'Varicella infection');
  }
}
class CE_Observation extends CE_LOINC {
  //
  static function asAge() {
    return static::from('21612-7', 'Reported patient age');
  }
  static function asFacilityVisitType() {
    return static::from('SS003', 'Facility visit type', 'PHINQUESTION');
  }
  static function asChiefComplaint() {
    return static::from('8661-1', 'Chief complaint');
  }
  static function asImmunity() {
    return static::from('59784-9', 'Disease with presumed immunity');
  }
  static function asVacFundingProgram() {
    return static::from('64994-7', 'Vaccine funding program eligibility category');
  }
  static function asVisPublished() {
    return static::from('29768-9', 'Date vaccine information statement published');
  }
  static function asVisPresented() {
    return static::from('29769-7', 'Date vaccine information statement presented');
  }
  static function asVaccineType() {
    return static::from('30956-7', 'Vaccine type');
  }
}
class CE_FundingMethod extends CE_CodeSystem {
  static $CS = 'CDCPHINVS';
  //
  static function asImmunLevel() {
    return static::from('VXC40', 'Eligibility captured at the immunization level');
  }
}
class CE_CVX extends CE_CodeSystem {
  static $CS = 'CVX';
}
abstract class CE_HL7Table extends CE {
  static $TABLE = 'HL7XXXX'; 
  //
  public function set($id, $text = null) {
    $table = static::getTable();
    $this->id = $id;
    $this->text = ($text) ? $text : $table::getText($this->id); 
    $this->codingSystem = $table::getCodingSystem(); 
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
      return CE_Local::from($e);
  }
  static function getTable() {
    return static::$TABLE; 
  }
}
class CE_PubCode extends CE_HL7Table {
  static $TABLE = 'HL70215';
  //
  static function asReminderRecallAny() {
    return static::from(HL70215::asReminderRecallAny());
  }
}
class CE_Relation extends CE_HL7Table {
  static $TABLE = 'HL70063';
  //
  static function asMother() {
    return static::from(HL70063::asMother());
  }
  static function asFather() {
    return static::from(HL70063::asFather());
  }
  static function asSpouse() {
    return static::from(HL70063::asSpouse());
  }
}
class CE_Race extends CE_HL7Table {
  static $TABLE = 'HL70005';
  //
  static function fromPatient($c) {
    $races = $c->getRaces();
    if ($races)
      return static::fromLookup($races[0]); /*TODO get all?*/
  }
}
class CE_Ethnic extends CE_HL7Table {
  static $TABLE = 'HL70189';
  //
  static function fromPatient($c) {
    return static::fromLookup($c);
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
  //
  static function fromImmun($imm) {
    if ($imm->tradeName)
      return static::fromLookup($imm->tradeName);
    else
      return CE_Immun_byName::fromLookup($imm->name); 
  }
  static function asNotAdministered() {
    return static::from('998', 'No vaccine administered');
  }
}
class CE_Immun_byName extends CE_HL7ReverseTable {
  static $TABLE = 'HL70292_byName';
}
class CE_ImmunNotes extends CE_HL7ReverseTable {
  static $TABLE = 'NIP001';
  //
  static function fromImmun($imm) {
    if ($imm->isHistorical()) 
      return static::fromLookup($imm->adminBy);
    else
      return static::asNew();
  }
  static function asNew() {
    return static::from('00', 'New immunization record');
  }
}
class CE_Refusal extends CE_HL7ReverseTable {
  static $TABLE = 'NIP002';
  //
  static function fromImmun($imm) {
    return static::fromLookup($imm->refusalReason);
  }
}
class CE_ImmunManufac extends CE_HL7ReverseTable {
  static $TABLE = 'HL70227';
  //
  static function fromImmun($imm) {
    return static::fromLookup($imm->manufac); 
  }
}
class CE_Route extends CE_HL7ReverseTable {
  static $TABLE = 'HL70162';
  //
  static function fromImmun($imm) {
    return static::fromLookup($imm->route); 
  }
}
class CE_Site extends CE_HL7ReverseTable {
  static $TABLE = 'HL70163';
  //
  static function fromImmun($imm) {
    return static::fromLookup($imm->site); 
  }
}
class CE_FinancialClass extends CE_HL7ReverseTable {
  static $TABLE = 'HL70064';
  //
  static function fromImmun($imm) {
    return static::fromLookup($imm->financialClass); 
  }
}
/**
 * Identifier 
 */
class CX extends HL7CompValue {
  public $id;
  public $checkDigit;
  public $checkDigitScheme;
  public $assignAuth = 'HD';
  public $idTypeCode;
  public $assignFacility = 'HD';
  public $effectiveDate;
  public $expireDate;
  public $assignJuris;  // CWE
  public $assignAgency;  // CWE
  //
  static function asPatientList($fs) {
    $us = array();
    $us[] = static::asPatient_MR($fs);
    static::appendPatient_SSN($us, $fs);
    return $us;
  }
  static function asPatient_MR($fs) {
    $me = static::asEmpty();
    $me->id = $fs->Client->clientId;
    $me->assignAuth = HD::asPractice($fs->UserGroup);
    $me->idTypeCode = 'MR'; 
    return $me;
  }
  static function asVisitNumber($fs) {
    $me = static::asEmpty();
    $me->id = $fs->Session->sessionId;
    $me->idTypeCode = 'VN'; 
    return $me;
  }
  protected static function appendPatient_SSN(&$us, $fs) {
    if ($fs->Client->cdata1) {
      $me = static::asEmpty();
      $me->id = $fs->Client->cdata1;
      $me->assignAuth = 'MAA';
      $me->idTypeCode = 'SS';
      $us[] = $me;
    } 
  }
  
}
class DR extends HL7CompValue {
  public $start = 'TS';
  public $end = 'TS';
}
class CWE extends HL7CompValue {
  public $id;
  public $text;
  public $codingSystem;
  public $altId;
  public $altText;
  public $altCodingSystem;
  public $versionId;
  public $altVersionId;
  public $origText;
  //
  static function asChiefComplaint($text) {
    $me = new static();
    $me->origText = $text;
    return $me;
  }
}
class EI extends HL7CompValue {
  public $id;
  public $namespace;
  public $univId;
  public $univIdType;
  //
  static function fromImmun($imm) {
    $me = static::asEmpty();
    $me->id = $imm->dataImmunId;
    $me->namespace = 'LCD';
    return $me;
  }
  static function asNotAdministered() {
    $me = static::asEmpty();
    $me->id = '9999';
    $me->namespace = 'CDC';
    return $me;
  }
  static function asNoAckSender() {
    $me = static::asEmpty();
    $me->id = 'PH_SS-NoAck';
    $me->namespace = 'SS Sender';
    $me->univId = '2.16.840.1.114222.4.10.3';
    $me->univIdType = 'ISO';
    return $me;
  }
}
/**
 * Time Stamp
 */
class TS extends HL7CompValue {
  public $time;
  public $precision;
  //
  public function asSqlValue() {
    return date("Y-m-d H:i:s", strtotime($this->time));
  }
  public function asSqlDate() {
    return date("Y-m-d", strtotime($this->time));
  }
  public function asFormatted() {
    return date("d-M-Y h:iA", strtotime($this->time));
  }
  public function sanitize() {
    parent::sanitize();
    $this->_time = formatTimestamp($this->time);
    $this->_date = formatDate($this->time);
    return $this;
  }
  //
  static function fromDate($date = null) {
    $e = new static();
    if ($date) {
      if (strlen($date) <= 8) {
        $e->time = $date;
      } else {    
        $ts = strtotime($date);
        $time = date('H:i:s', $ts);
        if ($time == '00:00:00')
          $e->time = date('Ymd', $ts);
        else if ($time == '01:00:00')
          $e->time = date('Ym', $ts);
        else if ($time == '02:00:00')
          $e->time = date('Y', $ts);
        else 
          $e->time = date("YmdHi", $ts) . ":00";
      }
    }
    return $e;
  }
  static function asNow() {
    return self::fromDate(nowNoQuotes());
  }
}
/**
 * Timing Quantity
 */
class TQ extends HL7CompValue {
  public $qty;  // CQ
  public $interval;  // RI
  public $duration;
  public $start;  // TS
  public $end;  // TS
  public $priority;
  public $cond;
  public $text;
  public $conj;
  public $orderSeq;  // OSD
  public $occurDuration;  // CE
  public $occurTotal;   
}
/**
 * Specimen Source
 */
class SPS extends HL7CompValue {
  public $source = 'CWE';
  public $additives = 'CWE';
  public $collectMethod = 'CWE';
  public $bodySite = 'CWE';
  public $siteModifier = 'CWE';
  public $collectMethodModifier = 'CWE';
  public $role = 'CWE';
}
/**
 * Extended Address 
 */
class XAD extends HL7CompValue {
  public $addr1;
  public $addr2;
  public $city;
  public $state;
  public $zip;
  public $country;
  public $type;
  public $other;
  public $county;
  //
  static function fromAddress($a, $type = null) {
    $me = static::asEmpty();
    $me->addr1 = $a->addr1;
    $me->addr2 = $a->addr2;
    $me->city = $a->city;
    $me->state = $a->state;
    $me->zip = $a->zip;
    $me->county = get($a, '_countyCode');
    if ($me->state) {
      $me->country = 'USA';
    }
    $me->type = $type;
    return $me;
  }
  static function asHome($a) {
    return static::fromAddress($a, HL70190::asHome());
  }
  static function asLegal($a) {
    return static::fromAddress($a, HL70190::asLegal());
  }
  static function asAnonymous($a) {
    $me = static::asEmpty();
    $me->zip = $a->zip;
    $me->county = get($a, '_countyCode');
    return $me;
  }
}
/**
 * Extended Telephone
 */
class XTN extends HL7CompValue {
  public $phone;
  public $useCode;
  public $equipType;
  public $email;
  public $countryCode;
  public $areaCode;
  public $local;
  public $ext;
  public $anyText;
  public $extPrefix;
  public $speedCode;
  public $unformatted;
  //
  static function asPhone($phone, $phoneType) {
    $pf = Phone::from($phone);
    $me = static::asEmpty();
    if ($phone) {
      $me->useCode = HL70201::lookup($phoneType);
      $me->equipType = HL70202::lookup($phoneType);
      $me->areaCode = $pf->area;
      $me->local = $pf->local;
    }
    return $me;
  }
  static function asEmail($email) {
    $me = static::asEmpty();
    $me->useCode = HL70201::asEmail();
    $me->email = $email;
    return $me;
  }
  static function fromAddress($a) {
    $us = array();
    $us[] = static::asPhone($a->phone1, $a->phone1Type);
    if ($a->email1) 
      $us[] = static::asEmail($a->email1);  
    return $us;
  }
}
/**
 * Extended Composite Name And Number For Persons 
 */
class XCN extends HL7CompValue {
  public $id;
  public $familyName;  // FN
  public $givenName;
  public $secondName;
  public $suffix;
  public $prefix;
  public $degree;
  public $source;
  public $assignAuth;  // HD
  public $nameType;
  public $idCheckDigit;
  public $checkDigitScheme;
  public $idTypeCode;
  public $assignFacility;  // HD
  public $nameRepresentCode;
  public $nameContext;  // CE
  public $nameValidityRange;  // DR
  public $nameAssembly; 
  public $effective;  // TS
  public $expiration;  // TS
  public $profSuffix;
  public $assignJurisdic;  // CWE
  public $assignAgency;  // CWE
  //
  public function asFormatted() {
    $s = array();
    pushIfNotEmpty($s, $this->prefix);
    pushIfNotEmpty($s, $this->givenName);
    pushIfNotEmpty($s, $this->secondName);
    pushIfNotEmpty($s, $this->familyName);
    pushIfNotEmpty($s, $this->suffix);
    return implode(' ', $s);
  }
  //
  static function fromUser(/*User*/$user) {
    return static::fromName($user->userId, $user->name);
  }
  static function asImmOrderedBy(/*Immun*/$imm) {
    if ($imm->orderBy) {
      return static::fromName($imm->dataImmunId . '-O', $imm->orderBy, ID_NameType::LEGAL);
    }
  }
  static function asImmProvider(/*Immun*/$imm) {
    if ($imm->adminBy) {
      return static::fromName($imm->dataImmunId . '-P', $imm->adminBy, ID_NameType::LEGAL);
    }
  }
  static function asImmEnteredBy(/*Immun*/$imm) {
    if ($imm->orderEnterBy) {
      return static::fromName($imm->dataImmunId . '-E', $imm->orderEnterBy);
    }
  }
  static function fromName($id, $name, $nameType = null, $assignAuth = null) {
    $p = new NameParser($name);
    $me = static::asEmpty();
    $me->id = $id;
    $me->familyName = $p->last;
    $me->givenName = $p->first;
    $me->secondName = $p->middle;
    $me->suffix = $p->suffix;
    $me->prefix = $p->title;
    $me->nameType = $nameType;
    $me->assignAuth = $assignAuth ?: HD::asClicktate();
    return $me;
  }
}
/**
 * Location With Address 2
 */
class LA2 extends HL7CompValue {
  public $poc;
  public $room;
  public $bed;
  public $facility;
  //
  static function asFacility($ugid) {
    $me = new static();
    $me->facility = 'G' . $ugid;
    return $me;
  }
}
/**
 * Extended Organization Name
 */
class XON extends HL7CompValue {
  public $name;
  public $typeCode;
  public $id;
  public $checkDigit;
  public $checkDigitScheme;
  public $assignAuth;  // HD
  public $idTypeCode;
  public $assignFacility;  // HD
  public $nameRepresentCode;
  public $orgId;
}
/**
 * Extended Person Name
 */
class XPN extends HL7CompValue {
  public $last;  // FN
  public $first;
  public $middle;
  public $suffix;
  public $prefix;
  public $degree;
  public $nameType;  // ID_NameType
  //
  public function makeFullName() {
    if (! empty($this->last) && ! empty($this->first)) 
      return trim($this->last . ', ' . $this->first . ' ' . $this->middle);
  }
  //
  static function asPatient(/*Client*/$c) {
    $me = static::asEmpty();
    $me->last = $c->lastName;
    $me->first = $c->firstName;
    $me->middle = $c->middleName;
    $me->nameType = ID_NameType::LEGAL;
    return $me;
  }
  static function asAnonymous() {
    $us = array();
    $us[] = static::asEmpty();
    $me = static::asEmpty();
    $me->nameType = 'S';
    $us[] = $me;
    return $us;
  }
  static function fromAddress(/*Address*/$a, $nameType = null) {
    if ($a && $a->name) {
      $me = static::fromFullName($a->name);
      $me->nameType = $nameType;
      return $me;
    }
  }
  static function fromAddress_asLegal($a) {
    return static::fromAddress($a, ID_NameType::LEGAL);
  }
  static function fromFullName($name) {
    $p = new NameParser($name);
    $me = static::asEmpty();
    $me->last = $p->last;
    $me->first = $p->first;
    $me->middle = $p->middle;
    $me->suffix = $p->suffix;
    $me->prefix = $p->title;
    return $me;
  }
}
/**
 * Identifier Manager  
 */
class HD extends HL7CompValue {
  public $namespaceId;
  public $universalId;
  public $universalIdType;
  //
  static function asPractice($ug) {
    $me = new static();
    $me->namespaceId = $ug->userGroupId;
    if (get($ug, 'npi')) {
      $me->universalId = $ug->npi;
      $me->universalIdType = 'NPI';
    }
    return $me;
  }
  static function asClicktate() {
    $me = new static();
    $me->namespaceId = 'Clicktate';
    return $me;
  }
}
