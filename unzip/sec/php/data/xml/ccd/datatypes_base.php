<?php
//
class ANY extends XmlRec {
  public $_xsi_type;
  public /*cs_NullFlavor*/ $_nullFlavor;
  //
  public function setXsiType($class = null) {
    if ($class == null) { 
      $a = explode('_', get_class($this));
      $class = $a[0];
    }
    $this->_xsi_type = $class;
  }
  public function getXsiType() {
    return $this->_xsi_type;
  }
  //
  static function asNull($flavor) {
    $e = new static();
    $e->_nullFlavor = $flavor;
    return $e;  
  }
}
class BL extends ANY {
  public /*bl*/ $_value;
  //
  static function asBoolean($b) {
    $e = new static();
    $e->_value = ($b) ? 'true' : 'false';
    return $e;
  }
}
class BIN extends ANY {
  public /*cs_BinaryDataEncoding*/ $_representation;
  //
  public /*(innerText)*/ $_;
  //
  static function asText($text) {
    $e = new static();
    $e->_ = $text;
    return $e;
  }
}
class ED extends BIN {
  public /*cs*/ $_mediaType;
  public /*cs*/ $_language;
  public /*cs_CompressionAlgorithm*/ $_compression;
  public /*bin*/ $_integrityCheck;
  public /*cs_IntegrityCheckAlgorithm*/ $_integrityCheckAlgorithm;
  //
  public /*TEL*/ $reference;
  public /*thumbnail*/ $thumbnail;
  public /*(innerText)*/ $_;
}
class thumbnail extends ED {
  //
  public /*TEL*/ $reference;
  public /*thumbnail*/ $thumbnail;
}
class ST extends ED {
  public /*cs_BinaryDataEncoding*/ $_representation;
  public /*cs*/ $_mediaType;
  //
  public /*TEL*/ $reference;
  public /*ED*/ $thumbnail;
  public /*(innerText)*/ $_;
}
class CD extends ANY {
  public /*cs*/ $_code;
  public /*uid*/ $_codeSystem;
  public /*st*/ $_codeSystemName;
  public /*st*/ $_codeSystemVersion;
  public /*st*/ $_displayName;
  //
  public /*ED*/ $originalText;
  public /*CR[]*/ $qualifier;
  public /*CD[]*/ $translation;
  //
  function addQualifier($qualifier) {  // $e->code->addQualifier(CR_SNOMED_Laterality::asLeft());
    $this->set('qualifier', $qualifier);
  }
  //
  static function from($code, $codeSystem, $displayName = null, $codeSystemName = null) {
    $e = new static();
    $e->_code = $code;
    $e->_codeSystem = $codeSystem;
    $e->_displayName = $displayName;
    if ($codeSystemName)
      $e->_codeSystemName = $codeSystemName;
    return $e;
  }
}
class CE extends CD {
  //
  public /*CR*/ $qualifier;
  public /*ED*/ $originalText;
  public /*CD[]*/ $translation;
}
class CV extends CE {
  //
  public /*ED*/ $originalText;
  public /*CD*/ $translation;
}
class CS extends CV {
  public function __construct($code) {
    $this->_code = $code;
  }
}
class CO extends CV {
}
class CR extends CD {
  public /*bl*/ $_inverted;
  //
  public /*CV*/ $name;
  public /*CD*/ $value;
}
class SC extends ST {
  public /*cs*/ $_code;
  public /*uid*/ $_codeSystem;
  public /*st*/ $_codeSystemName;
  public /*st*/ $_codeSystemVersion;
  public /*st*/ $_displayName;
  //
  public /*(innerText)*/ $_;
}
class II extends ANY {
  public /*uid*/ $_root;
  public /*st*/ $_extension;
  public /*st*/ $_assigningAuthorityName;
  public /*bl*/ $_displayable;
  //
  static function from($root, $extension = null) {
    $e = new static();
    if ($root == null)
      $root = static::makeGuid();
    $e->_root = $root;
    if ($extension)
      $e->_extension = $extension; 
    return $e;
  }
  static function fromExtension($extension) {
    $e = new static();
    $e->_extension = $extension; 
    return $e;
  }
  static function fromUserGroup($userGroup) {
    return static::from("G$userGroup->userGroupId");
    //return static::fromUgid("UGID$userGroup->userGroupId");
  }
  static function fromClient($client) {
    $id = static::from("G$client->userGroupId", $client->clientId);
    $ssn = static::asSocial($client);
    if ($ssn)
      return array($id, $ssn);
    else
      return $id;
  }
  static function asSocial($client) {
    if (get($client, 'ssn'))
      return static::from('2.16.840.1.113883.4.1', $client->ssn);
  }
  static function fromMed($med) {
    return static::from("G$med->userGroupId", "M$med->dataMedId");
    //return static::asGuid();
  }
  static function fromSession($session) {
    return static::from("G$session->userGroupId", "S$session->sessionId");
    //return static::fromSid($session->sessionId); 
  }
  static function fromSid($ugid, $sid) {
    return static::from("G$ugid", "S$sid");
    //return static::asGuid("SESS$sid");
  }
  static function fromImmun($immun) {
    return static::from("G$immun->userGroupId", "I$immun->dataImmunId");
    //return static::asGuid();
  }
  static function fromDiag($diag) {
    return static::from("G$diag->userGroupId", "D$diag->dataDiagnosesId");
    //return static::asGuid();
  }
  static function asZero($ugid) {
    return static::from("G$ugid", "0");
  }
  static function fromProc($proc) {
    return static::from("G$proc->userGroupId", "P$proc->procId");
    //return static::asGuid(); 
  }
  static function fromTrackItem($track) {
    return static::from("G$track->userGroupId", "TI$track->trackItemId");
    //return static::asGuid(); 
  }
  static function fromSoc($soc, $ugid) {
    return static::from("G$ugid", "SH$soc->name");
    //return static::asGuid(); 
  }
  static function fromResult($result, $ugid) {
    return static::from("G$ugid", "R$result->procResultId");
    //return static::asGuid();
  }
  static function fromAller($aller) {
    return static::from("G$aller->userGroupId", "A$aller->dataAllergyId");
    //return static::asGuid("ALLER$aller->dataAllergyId"); 
  }
  static function fromVital($vital) {
    return static::from("G$vital->userGroupId", "V$vital->dataVitalsId");
    //return static::asGuid(); 
  }
  static function fromProvider($provider) {
    return static::from("G$vital->userGroupId", "P$provider->providerId");
    //return static::asGuid(); 
  }
  static function fromUser($user) {
    if (! $user)
      return static::asGuid();
    $ids = array();
    $ids[] = static::from("G$user->userGroupId", "U$user->userId");
    if (get($user, 'npi'))
      $ids[] = II::fromNPI($user->npi);
    return $ids;
  }
  static function fromNPI($npi) {
    return static::from('2.16.840.1.113883.4.6', $npi);
  }
  static function random() {
    return static::asGuid();
  }
  //
  private static function asGuid($id = null) {
    return static::from(Guid::get($id));
  }
  private static function fromUgid($ugid) {
    $root = '54c87390-d67e-4644-b514-b40caaed4aa3';  
    return static::from($root);  // TODO
  }
}
class Guid {
  static $GUIDS = array();  // ['U123'=>'guid',..]
  static function from($id) {
    return static::get($id);
  } 
  static function get($id = null) {
    if ($id == null) 
      $guid = strtolower(GUID());
    else {
      $guid = geta(static::$GUIDS, $id);
      if ($guid == null) {
        $guid = strtolower(GUID());
        static::$GUIDS[$id] = $guid;
      }
    }
    return $guid;
  }
}
function GUID()
{
    if (function_exists('com_create_guid') === true)
        return trim(com_create_guid(), '{}');

    return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
}
class URL extends ANY {
  public /*url*/ $_value;
}
class TS extends QTY {
  public /*ts*/ $_value;
  //
  static function fromDate($date = null) {
    $e = new static();
    if ($date && strlen($date) <= 8) {
      $e->_value = $date;
    } else {    
      if ($date == null)
        $date = nowNoQuotes();
      $ts = strtotime($date);
      $time = date('H:i:s', $ts);
      if ($time == '00:00:00')
        $e->_value = date('Ymd', $ts);
      else if ($time == '01:00:00')
        $e->_value = date('Ym', $ts);
      else if ($time == '02:00:00')
        $e->_value = date('Y', $ts);
      else
        $e->_value = date("YmdHi", $ts) . "00";
    }
    return $e;
  }
  static function fromNow() {
    return self::fromDate();
  }
}
class TS_EffectiveTime extends QTY {
  //
  static function fromDate($date) {
    $e = new static();
    $ts = strtotime($date);
    $e->_value = date("Ymdhis", $ts). "+0500";
    return $e;
  }
  static function fromNow() {
    return self::fromDate(nowNoQuotes());
  }
}
class TEL extends URL {
  public /*set_cs_TelecommunicationAddressUse*/ $_use;
  //
  public /*SXCM_TS[]*/ $useablePeriod;
  //
  static function from($address) {
    if ($address->phone1) {
      $e = new static();
      $e->_value = $address->phone1;
      return $e;
    } else {
      return static::asNull('UNK');
    }
  }
}
class ADXP extends ST {
  public /*cs_AddressPartType*/ $_partType;
  //
  public /*(innerText)*/ $_;
}
class AD extends ANY {
  public /*cs_AddressPartType*/ $_partType;
  public /*set_cs_PostalAddressUse*/ $_use;
  public /*bl*/ $_isNotOrdered;
  //
  public /**/ $delimiter; 
  public /**/ $country; 
  public /**/ $state; 
  public /**/ $county; 
  public /**/ $city; 
  public /**/ $postalCode; 
  public /**/ $streetAddressLine; 
  public /**/ $houseNumber; 
  public /**/ $houseNumberNumeric; 
  public /**/ $direction; 
  public /**/ $streetName; 
  public /**/ $streetNameBase; 
  public /**/ $streetNameType; 
  public /**/ $additionalLocator; 
  public /**/ $unitID; 
  public /**/ $unitType; 
  public /**/ $carrier; 
  public /**/ $censusTract; 
  public /*SXCM_TS[]*/ $useablePeriod;
  public /*(innerText)*/ $_;
  //
  static function from($address) {
    $e = new static();
    $e->streetAddressLine = $address->addr1;
    if (! empty($address->addr2)) {
      $e->streetAddressLine = array($address->addr1, $address->addr2);
    } else {
      $e->streetAddressLine = $address->addr1;
    }
    $e->city = $address->city;
    $e->state = $address->state;
    $e->postalCode = $address->zip;
    $e->country = $address->country;
    return $e;
  }
}
class ENXP extends ST {
  public /*cs_EntityNamePartType*/ $_partType;
  public /*set_cs_EntityNamePartQualifier*/ $_qualifier;
  //
  public /*(innerText)*/ $_;
}
class en_delimiter extends ENXP {
  public /*cs_EntityNamePartType*/ $_partType;
  //
  public /*(innerText)*/ $_;
}
class en_family extends ENXP {
  public /*cs_EntityNamePartType*/ $_partType;
  //
  public /*(innerText)*/ $_;
}
class en_given extends ENXP {
  public /*cs_EntityNamePartType*/ $_partType;
  //
  public /*(innerText)*/ $_;
}
class en_prefix extends ENXP {
  public /*cs_EntityNamePartType*/ $_partType;
  //
  public /*(innerText)*/ $_;
}
class en_suffix extends ENXP {
  public /*cs_EntityNamePartType*/ $_partType;
  //
  public /*(innerText)*/ $_;
}
class EN extends ANY {
  public /*set_cs_EntityNameUse*/ $_use;
  //
  //  public /*en_delimiter*/ $delimiter; 
  //  public /*en_family*/ $family; 
  //  public /*en_given*/ $given; 
  //  public /*en_prefix*/ $prefix; 
  //  public /*en_suffix*/ $suffix; 
  //  public /*IVL_TS*/ $validTime;
  //  public /*(innerText)*/ $_;
  //
  public function from($last, $first, $middle = null, $suffix = null, $prefix = null) {
    $e = new static();
    if ($prefix) 
      $e->prefix = $prefix;
    $e->given = $first;
    if ($middle) 
      $e->set('given', $middle);
    $e->family = $last;
    if ($suffix)
      $e->suffix = $suffix;
    return $e;
  }
}
class PN extends EN {
  //
  public /*(innerText)*/ $_;
  //
  static function fromClient($client) {
    return parent::from($client->lastName, $client->firstName, $client->middleName);
  }
  static function fromNcUser($user) {
    return parent::from($user->nameLast, $user->nameFirst, $user->nameMiddle, $user->nameSuffix, $user->namePrefix);
  }
  static function fromProvider($provider) {
    return parent::from($provider->last, $provider->first, $provider->middle, $provider->suffix, $provider->prefix);
  }
}
class ON extends EN {
  //
  public /*en_delimiter*/ $delimiter; 
  public /*en_prefix*/ $prefix; 
  public /*en_suffix*/ $suffix; 
  public /*IVL_TS*/ $validTime;
  public /*(innerText)*/ $_;
  //
  static function asText($text) {
    $e = new static();
    $e->_ = $text;
    return $e;
  }
}
class TN extends EN {
  //
  public /*(innerText)*/ $_;
}
class QTY extends ANY {
}
class INT extends QTY {
  public /*int*/ $_value;
  //
  static function from($value) {
    $e = new static();
    $e->_value = $value;
    return $e;
  }
}
class REAL extends QTY {
  public /*real*/ $_value;
}
class PQR extends CV {
  public /*real*/ $_value;
}
class PQ extends QTY {
  public /*real*/ $_value;
  public /*cs*/ $_unit;
  //
  public /*PQR[]*/ $translation;
  //
  static function from($value, $unit = null) {
    $e = new static();
    $e->_value = $value;
    $e->_unit = $unit;
    return $e;
  }
  static function asHours($value) {
    return self::from($value, 'h');
  }
  static function asCm($value) {
    return self::from($value, 'cm');
  }
  static function asKg($value) {
    return self::from($value, 'kg');
  }
  static function asMmHg($value) {
    return self::from($value, 'mm[Hg]');
  }
  static function asPerMin($value) {
    return self::from($value, '/min');
  }
  static function asPercent($value) {
    return self::from($value, '%');
  }
  static function asCelsius($value) {
    return self::from($value, 'Cel');
  }
  static function asFahrenheit($value) {
    return self::from($value, '[degF]');
  }
  static function asBmi($value) {
    return self::from($value, 'kg/m2');
  }
}
class MO extends QTY {
  public /*real*/ $_value;
  public /*cs*/ $_currency;
}
class RTO extends RTO_QTY_QTY {
}
class SXCM_TS extends TS {
  public /*cs_SetOperator*/ $_operator;
  //
  static function from($e) {
    $e->setXsiType();
    return $e;
  }
  static function add(&$array, $e) {
    $e = self::from($e);
    if (! empty($array))
      $e->operator = 'A';
    $array[] = $e;
  }
  static function fromMed($med) {
    $array = array();
    $dateFrom = $med->date;
    $dateTo = nowNoQuotes(); 
    self::add($array, IVL_TS::asLowHigh($dateFrom, $dateTo));
    //$hours = $med->getFreqInHours();
    //self::add($array, PIVL_TS::asFrequencyHours($hours));
    return $array;
  }
  static function fromImmun($immun) {
    return self::from(IVL_TS::asCenter($immun->dateGiven));
  }
}
class IVL_TS extends SXCM_TS {
  //
  public /*IVXB_TS*/ $low; //REQ 
  public /*PQ*/ $width;
  public /*IVXB_TS*/ $high; 
  public /*TS*/ $center; //REQ
  //
  public function setXsiType($class = null) {
    $this->_xsi_type ='IVL_TS';
  }
  static function asLowHigh($dateFrom, $dateTo) {
    $e = new static();
    $e->low = IVXB_TS::fromDate($dateFrom);
    if ($dateTo)
      $e->high = IVXB_TS::fromDate($dateTo);
    else
      $e->high = IVXB_TS::asNull('UNK');
    return $e;
  }
  static function asLowHighUnk() {
    $e = new static();
    $e->low = IVXB_TS::asNull('UNK');
    $e->high = IVXB_TS::asNull('UNK');
    return $e;
  }
  static function asLow($date) {
    $e = new static();
    $e->low = IVXB_TS::fromDate($date);
    return $e;
  }
  static function asLowUnk() {
    $e = new static();
    $e->low = IVXB_TS::asNull('UNK');
    return $e;
  }
  static function asCenter($date) {
    $e = new static();
    $e->center = IVXB_TS::fromDate($date);
    return $e;
  }
}
class IVXB_TS extends TS {
  public /*bl*/ $_inclusive;
}
class RTO_QTY_QTY extends QTY {
  //
  public /*QTY*/ $numerator;  //REQ
  public /*QTY*/ $denominator; //REQ
}
/**
 * Remainder from "datatypes.xsd"
 */
class PIVL_TS extends SXCM_TS {
  public /*cs_CalendarCycle*/ $_alignment;
  public /*bl*/ $_institutionSpecified;
  //
  public /*IVL_TS*/ $phase;
  public /*PQ*/ $period;
  public /*RTO_INT_PQ*/ $frequency;
  //
  public function setXsiType($class = null) {
    $this->_xsi_type ='PIVL_TS';
  }
  static function asFrequencyHours($hours) {
    $e = new self();
    $e->period = PQ::asHours($hours);
    return $e;
  }
}
class RTO_INT_PQ extends QTY {
  //
  public /*INT*/ $numerator; //REQ
  public /*PQ*/ $denominator; //REQ
}
class EIVL_TS extends SXCM_TS {
  //
  public /*CE*/ $event;
  public /*IVL_PQ*/ $offset;
}
class IVL_PQ extends SXCM_PQ {
  //
  public /*IVXB_PQ*/ $low; //REQ
  public /*PQ*/ $width;
  public /*IVXB_PQ*/ $high;
  public /*PQ*/ $center; //REQ
  //
  static function asDoseQuantity($med) {
    if (isset($med->ncDosageNum)) {
      $amt = floatval($med->ncDosageNum);
      if ($amt)
        return self::from($amt);
    }
  }
}
class SXCM_PQ extends PQ {
  public /*cs_SetOperator*/ $_operator;
}
class IVXB_PQ extends PQ {
  public /*bl*/ $_inclusive;
}
class PPD_TS extends TS {
  public /*cs_ProbabilityDistributionType*/ $_distributionType;
  //
  public /*PQ*/ $standardDeviation;
}
class PPD_PQ extends PQ {
  public /*cs_ProbabilityDistributionType*/ $_distributionType;
  //
  public /*PQ*/ $standardDeviation;
}
class SXCM_PPD_TS extends PPD_TS {
  public /*cs_SetOperator*/ $_operator;
}
class PIVL_PPD_TS extends SXCM_PPD_TS {
  public /*cs_CalendarCycle*/ $_alignment;
  public /*bl*/ $_institutionSpecified;
  //
  public /*IVL_PPD_TS*/ $phase;
  public /*PPD_PQ*/ $period;
  public /*RTO_INT_PPD_PQ*/ $frequency;
}
class IVL_PPD_TS extends SXCM_PPD_TS {
  //
  public /*IVXB_PPD_TS*/ $low; //REQ
  public /*PPD_PQ*/ $width;
  public /*IVXB_PPD_TS*/ $high;
  public /*PPD_TS*/ $center; //REQ
}
class IVXB_PPD_TS extends PPD_TS {
  public /*bl*/ $_inclusive;
}
class RTO_INT_PPD_PQ extends QTY {
  //
  public /*INT*/ $numerator; //REQ
  public /*PPD_PQ*/ $denominator; //REQ
}
class EIVL_PPD_TS extends SXCM_PPD_TS {
  //
  public /*CE*/ $event;
  public /*IVL_PPD_PQ*/ $offset;
}
class IVL_PPD_PQ extends SXCM_PPD_PQ {
  //
  public /*IVXB_PPD_PQ*/ $low; //REQ
  public /*PPD_PQ*/ $width;
  public /*IVXB_PPD_PQ*/ $high;
  public /*PPD_PQ*/ $center; //REQ
}
class SXCM_PPD_PQ extends PPD_PQ {
  public /*cs_SetOperator*/ $_operator;
}
class IVXB_PPD_PQ extends PPD_PQ {
  public /*bl*/ $_inclusive;
}
class SXPR_TS extends SXCM_TS {
  //
  public /*SXCM_TS[]*/ $comp; //REQ
}
class SXCM_CD extends CD {
  public /*cs_SetOperator*/ $_operator;
}
class SXCM_MO extends MO {
  public /*cs_SetOperator*/ $_operator;
}
class SXCM_INT extends INT {
  public /*cs_SetOperator*/ $_operator;
}
class SXCM_REAL extends REAL {
  public /*cs_SetOperator*/ $_operator;
}
class IVL_INT extends SXCM_INT {
  //
  public /*IVXB_INT*/ $low; //REQ
  public /*INT*/ $width;
  public /*IVXB_INT*/ $high;
  public /*INT*/ $center; //REQ
}
class IVXB_INT extends INT {
  public /*bl*/ $_inclusive;
}
class IVL_REAL extends SXCM_REAL {
  //
  public /*IVXB_REAL*/ $low; //REQ
  public /*REAL*/ $width;
  public /*IVXB_REAL*/ $high;
  public /*REAL*/ $center; //REQ
}
class IVXB_REAL extends REAL {
  public /*bl*/ $_inclusive;
}
class IVL_MO extends SXCM_MO {
  //
  public /*IVXB_MO*/ $low; //REQ
  public /*MO*/ $width;
  public /*IVXB_MO*/ $high;
  public /*MO*/ $center; //REQ
}
class IVXB_MO extends MO {
  public /*bl*/ $_inclusive;
}
class HXIT_PQ extends PQ {
  //
  public /*IVL_TS*/ $validTime;
}
class HXIT_CE extends CE {
  //
  public /*IVL_TS*/ $validTime;
}
class BXIT_CD extends CD {
  public /*int*/ $_qty;
}
class BXIT_IVL_PQ extends IVL_PQ {
  public /*int*/ $_qty;
}
class SLIST_PQ extends ANY {
  //
  public /*PQ*/ $origin; //REQ
  public /*PQ*/ $scale; //REQ
  public /*list_int*/ $digits; //REQ
}
class SLIST_TS extends ANY {
  //
  public /*TS*/ $origin; //REQ
  public /*PQ*/ $scale; //REQ
  public /*list_int*/ $digits; //REQ
}
class GLIST_TS extends ANY {
  public /*int*/ $_period;
  public /*int*/ $_denominator;
  //
  public /*TS*/ $head; //REQ
  public /*PQ*/ $increment; //REQ
}
class GLIST_PQ extends ANY {
  public /*int*/ $_period;
  public /*int*/ $_denominator;
  //
  public /*PQ*/ $head; //REQ
  public /*PQ*/ $increment; //REQ
}
class RTO_PQ_PQ extends QTY {
  //
  public /*PQ*/ $numerator; //REQ
  public /*PQ*/ $denominator; //REQ
}
class RTO_MO_PQ extends QTY {
  //
  public /*MO*/ $numerator; //REQ
  public /*PQ*/ $denominator; //REQ
}
class UVP_TS extends TS {
  public /**/ $_probability;
}
/**
 * Free text
 */
class TEXT_REF extends ANY {
  public $reference;
  //
  static function from($ref) {
    $e = new static();
    $e->reference = array('value' => '#' . $ref);
    return $e;
  }
  static function asDiag($diag) {
    return static::from(self::getForDiag($diag));
  }
  static function asMed($med) {
    return static::from(self::getForMed($med));
  }
  static function asAller($aller) {
    return static::from(self::getForAller($aller));
  }
  static function asProc($proc) {
    return static::from(self::getForProc($proc));
  }
  static function asPlanOfCare($proc) {
    return static::from(self::getForPlanOfCare($proc));
  }
  static function asTrackItem($track) {
    return static::from(self::getForTrackItem($track));
  }
  static function asResult($result) {
    return static::from(self::getForResult($result));
  }
  static function getForProc($proc) {
    return 'PROC' . $proc->procId;
  }
  static function getForInstruct($i) {
    return 'PI' . $i;
  }
  static function getForPda($i) {
    return 'PDA' . $i;
  }
  static function getForPlanOfCare($proc) {
    return 'PLAN' . $proc->procId;
  }
  static function getForTrackItem($rec) {
    return 'OE' . $rec->trackItemId;
  }
  static function getForSocial($soc) {
    return 'SHX' . $soc->name;
  }
  static function getForResult($result) {
    return 'RES' . $result->procResultId;
  }
  static function getForDiag($diag) {
    return 'DIAG' . $diag->dataDiagnosesId;
  }
  static function getForMed($med) {
    return 'MED' . $med->dataMedId;
  }
  static function getForAller($aller) {
    return 'ALL' . $aller->dataAllergyId;
  }
}
class TextPar2 extends XmlRec {
  public $_ID;
  public $_;
  static function from($ref, $text) {
    $e = new static($ref, $text);
    return $e;
  }
}