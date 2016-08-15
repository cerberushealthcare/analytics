<?php
require_once 'php/data/xml/_XmlRec.php';
//
/** 
 * Main body types */
class Ccr_ProblemType extends Ccr_CodedDataObjectType {
  public $Episodes = '[]';
  public $HealthStatus;
  public $PatientKnowledge;
  //
  public function getIcd() {
    return Ccr_CodeType::getIcdFrom($this->getCodes()); 
  }
  public function getSnomed() {
    return Ccr_CodeType::getSnomedFrom($this->getCodes()); 
  }
}
class Ccr_AlertType extends Ccr_CodedDataObjectType {
  public $Agent = 'Ccr_Agent[]';
  public $Reaction = 'Ccr_Reaction[]';
  //
  public function getSingleProduct() {
    $agent = $this->first('Agent');
    if ($agent) 
      return $agent->getSingleProduct();
  }
  public function getSingleEnvAgent() {
    $agent = $this->first('Agent');
    if ($agent) 
      return $agent->getSingleEnvAgent();
  }
  public function getReactionTexts() {
    $texts = array();
    if ($this->Reaction)
      foreach ($this->Reaction as $r) {
        $texts[] = $r->getDesc();
      }
    return $texts;
  }
}
class Ccr_ProcedureType extends Ccr_CodedDataObjectType {
  public $Locations = '<Location>Ccr_Location[]';
  public $Practitioners = '<Practitioner>Ccr_ActorRefType[]';
  public $Frequency = 'Ccr_FreqType[]';
  public $Interval = 'Ccr_IntervalType[]';
  public $Duration = 'Ccr_DurationType[]';
  public $Indications = '<Indication>[]';
  public $Instructions = '<Instruction>Ccr_CodedDescType[]';
  public $Consent = 'Ccr_CodedDataObjectType';
  public $Products = '<Product>Ccr_StrucProdType[]';
  public $Substance = 'Ccr_CodedDescType';
  public $Method = '[]';
  public $Position = '[]';
  public $Site = '[]';
}
class Ccr_ResultType extends Ccr_CodedDataObjectType {
  public $Procedure = 'Ccr_ProcedureType[]';
  public $Substance = 'Ccr_CodedDescType';
  public $Test = 'Ccr_TestType[]';
}
class Ccr_StructProdType extends Ccr_CodedDataObjectType {
  public $Product = 'Ccr_StructProdTypeProduct[]';
  public $Quantity = 'Ccr_QuantityType[]';
  public $Directions = '<Direction>Ccr_Direction[]';
  public $PatientInstructions = 'XmlArray_Ccr:<Instruction>Ccr_CodedDescType[]';
  public $Refills = 'XmlArray_Ccr:<Refill>Ccr_StructProdTypeRefill[]';
  public $Reaction = 'Ccr_Reaction';
  //
  public function getProductName() {
    return $this->first('Product')->getName();
  }
  public function getDirectionsDesc() {
    $dir = $this->Directions->first('Direction');
    if ($dir) 
      return $dir->getDesc();
  }
}
class Ccr_StructProdTypeProduct extends XmlRec {
  public $ProductName = 'Ccr_CodedDescType';
  public $BrandName = 'Ccr_CodedDescType';
  public $Strength = 'Ccr_MeasureType[]';
  public $Form = 'Ccr_CodedDescType[]';
  public $Concentration = 'Ccr_MeasureType[]';
  public $Size = 'Ccr_CodedDescType[]';
  public $Manufacturer = 'Ccr_ActorRefType';
  public $IDs = 'Ccr_IDType[]';
  //
  public function getName() {
    return $this->ProductName->Text;
  }
}
class Ccr_StructProdTypeRefill extends XmlRec {
  public $Number = '[]';
  public $Quantity = 'Ccr_QuantityType[]';
  public $Status = 'Ccr_Status';
  public $DateTime = 'Ccr_DateTimeType[]';
  public $Comment = 'Ccr_CommentType[]';
}
/**
 * Primary components */
class Ccr_CodedDataObjectType extends XmlRec {
  public $CCRDataObjectID;
  public $DateTime = 'Ccr_DateTimeType[]';
  public $IDs = 'Ccr_IDType[]';
  public $Type = 'Ccr_Type';
  public $Description = 'Ccr_Description';
  public $Status = 'Ccr_Status';
  public function __construct() {
    $this->merge('Ccr_SLRCGroup');
  }
  public function getType() {
    return getr($this, 'Type.Text');
  }
  public function getDesc() {
    return getr($this, 'Description.Text');
  }
  public function getCodes() {
    return getr($this, 'Description.Code');
  }
  public function getStatus() {
    return getr($this, 'Status.Text');
  }
  public function getSqlDates() {
    return Ccr_DateTimeType::getSqlDates($this->DateTime);
  }
  public function getSqlDateTimes() {
    return Ccr_DateTimeType::getSqlDateTimes($this->DateTime);
  }
  public function getSqlDate($withTime = false) {
    $date = $this->first('DateTime');
    if ($date)
      return $date->getSqlDate($withTime);
  }
  public function getSqlDateTime() {
    return $this->getSqlDate(true);
  }
}
class Ccr_CodedDescType extends XmlRec {
  public $Text;
  public $ObjectAttribute;
  public $Code = 'Ccr_CodeType[]';
}
class Ccr_CodedDesc_Gender extends Ccr_CodedDescType { 
  //
  public function asSex() {
    return substr($this->Text, 0, 1);
  }
}
class Ccr_CodeType extends XmlRec {
  public $Value;
  public $CodingSystem;
  public $Version;
  //
  static function /*string[system]*/getValues($us) {
    if ($us) {
      $values = array();
      foreach ($us as $me) 
        $values[getr($me, 'CodingSystem')] = $me->Value;
      return $values;
    }
  }
  static function getIcdFrom($us) {
    return geta(static::getValues($us), 'ICD9-CM');
  }
  static function getSnomedFrom($us) {
    return geta(static::getValues($us), 'SNOMED CT');
  }
}
/**
 * Common groups */
class Ccr_SLRCGroup extends XmlRec {
  public $Source = 'Ccr_SourceType[]';
  public $InternalCCRLink = '[]';
  public $ReferenceID = '[]';
  public $CommentID = '[]';
  public $Signature = '[]';
}
class Ccr_MeasureGroup extends XmlRec {
  public $Value;
  public $Units = 'Ccr_MeasureGroupUnits';
  public $Code = 'Ccr_CodeType[]';
}
class Ccr_MeasureGroupUnits extends XmlRec {
  public $Unit;
  public $Code = 'Ccr_CodeType[]';
}
/** 
 * Other types */
class Ccr_ActorType extends XmlRec {
  protected static function getInstanceFor($o) {
    if (isset($o->Person))
      return new Ccr_Actor_Person();
    else if (isset($o->Organization))
      return new Ccr_Actor_Org();
    else if (isset($o->InformationSystem))
      return new Ccr_Actor_IS();
  }
  //
  public $ActorObjectID;
  public $IDs = 'Ccr_IDType[]';
  public $Relation = 'Ccr_CodedDescType[]';
  public $Specialty = 'Ccr_CodedDescType[]';
  public $Address = 'Ccr_ActorTypeAddr[]';
  public $Telephone = 'Ccr_CommType[]';
  public $EMail = 'Ccr_CommType[]';
  public $URL = 'Ccr_CommType[]';
  public $Status = 'Ccr_Status';
  public function __construct() {
    $this->merge('Ccr_SLRCGroup');
  }
  //
  public function getPrimaryAddr() {
    return Ccr_ActorTypeAddr::getPrimary($this->Address);
  }
  public function getPrimaryPhone() {
    return Ccr_CommType::getPrimaryValue($this->Telephone);
  }
  public function getPrimaryEmail() {
    return Ccr_CommType::getPrimaryValue($this->EMail);
  }
}
class Ccr_ActorTypeAddr extends XmlRec {
  public $Type = 'Ccr_Type';
  public $Line1;
  public $Line2;
  public $City;
  public $County;
  public $State;
  public $Country;
  public $PostalCode;
  public $Priority;
  public $Status = 'Ccr_Status';
  public $_Preferred;
  //
  static function getPrimary($us) {
    if ($us)
      return reset($us);
  }
}
class Ccr_Actor_Person extends Ccr_ActorType {
  public $Person = 'Ccr_ActorTypePerson';
}
class Ccr_ActorTypePerson extends XmlRec {
  public $Name = 'Ccr_ActorTypePersonName';
  public $DateOfBirth = 'Ccr_DateTimeType';
  public $Gender = 'Ccr_CodedDesc_Gender';
}
class Ccr_ActorTypePersonName extends XmlRec {
  public $BirthName = 'Ccr_PersonNameType';
  public $AdditionalName = 'Ccr_PersonNameType[]';
  public $CurrentName = 'Ccr_PersonNameType';
  public $DisplayName;
}
class Ccr_Actor_Org extends Ccr_ActorType {
  public $Organization = 'Ccr_ActorTypeOrg';
}
class Ccr_ActorTypeOrg extends XmlRec {
  public $Name;
}
class Ccr_Actor_IS extends Ccr_ActorType {
  public $InformationSystem = 'Ccr_ActorTypeIS';
}
class Ccr_ActorTypeIS extends XmlRec {
  public $Name;
  public $Type;
  public $Version;
}
class Ccr_ActorRefType extends XmlRec {
  public $ActorID;
  public $ActorRole = 'Ccr_CodedDescType';
}
class Ccr_CommType extends XmlRec {
  public $Value;
  public $Type = 'Ccr_Type';
  public $Priority;
  public $Status = 'Ccr_Status';
  //
  static function getPrimary($us) {
    if ($us)
      return reset($us);
  }
  static function getPrimaryValue($us) {
    $me = static::getPrimary($us);
    if ($me)
      return $me->Value;
  }
}
class Ccr_CommentType extends XmlRec {
  public $CommentObjectID;
  public $DateTime = 'Ccr_DateTimeType';
  public $Type = 'Ccr_Type';
  public $Description = 'Ccr_Description';
  public $Source = 'Ccr_ActorRefType[]';
  public $ReferenceID = '[]';
}
class Ccr_DateTimeType extends XmlRec {
  public $Type = 'Ccr_Type';
  public $ExactDateTime;
  public $Age = 'Ccr_Age';
  public $ApproximateDateTime = 'Ccr_CodedDescType';
  public $DateTimeRange = 'Ccr_DateTimeTypeDtr';
  //
  public function getType() {
    return getr($this, 'Type.Text');
  }
  public function getSqlDate($withTime = false) {
    $v = null;
    if ($this->isExact())
      $v = $this->ExactDateTime;
    else if (is_object($this->ApproximateDateTime)) 
      $v = $this->ApproximateDateTime->Text;
    if ($v)
      return date($withTime ? 'Y-m-d H:i:s' : 'Y-m-d', strtotime($v));
  }
  public function getSqlDateTime() {
    return $this->getSqlDate(true);
  }
  public function isExact() {
    return ! empty($this->ExactDateTime);
  }
  //
  static function /*string[type]*/getSqlDates($us, $withTime = false) {
    if ($us) {
      $values = array();
      foreach ($us as $me) 
        $values[$me->getType()] = $me->getSqlDate($withTime);
      return $values;
    }
  }
  static function getSqlDateTimes($us) {
    return static::getSqlDates($us, true);
  }
}
class Ccr_DateTimeTypeDtr extends XmlRec {
  public $BeginRange = 'Ccr_DateTimeTypeDtrR';
  public $EndRange = 'Ccr_DateTimeTypeDtrR';
}
class Ccr_DateTimeTypeDtrR extends XmlRec {
  public $ExactDateTime;
  public $Age = 'Ccr_Age';
  public $ApproximateDateTime = 'Ccr_CodedDescType';
}
class Ccr_FreqType extends XmlRec {
  public $Description = 'Ccr_Description';
  public function __construct() {
    $this->merge('Ccr_MeasureGroup');
  }
}
class Ccr_IntervalType extends XmlRec {
  public $Description = 'Ccr_Description';
  public function __construct() {
    $this->merge('Ccr_MeasureGroup');
  }
}
class Ccr_DurationType extends XmlRec {
  public $Description = 'Ccr_Description';
  public $DateTime = 'Ccr_DateTimeType[]';
  public function __construct() {
    $this->merge('Ccr_MeasureGroup');
  }
}
class Ccr_IDType extends XmlRec {
  public $DateTime = 'Ccr_DateTimeType';
  public $Type = 'Ccr_Type';
  public $ID;
  public $IssuedBy = 'Ccr_ActorRefType';
  public function __construct() {
    $this->merge('Ccr_SLRCGroup');
  }
}
class Ccr_Location extends XmlRec {
  public $Description = 'Ccr_Description';
  public $Actor = 'ActorRefType';
}
class Ccr_MeasureType extends XmlRec {
  public $Value;
  public $Units = 'Ccr_MeasureType_U';
  public $Code = 'Ccr_CodeType';
}
class Ccr_MeasureType_U extends XmlRec {
  public $Unit;
  public $Code = '[]';
}
class Ccr_PersonNameType extends XmlRec {
  public $Given;
  public $Middle;
  public $Family;
  public $Suffix;
  public $Title;
  public $NickName;
  //
  public function getLast() {
    $last = $this->Family;
    if (! empty($this->Suffix))
      $last .= ' ' . $this->Suffix;
    return $last;
  }
  public function getFirst() {
    if (is_array($this->Given))
      return implode($this->Given, ' ');
    else
      return $this->Given;
  }
  public function getMiddle() {
    return $this->Middle;
  }
}
class Ccr_PurposeType extends XmlRec {
  public $DateTime = 'Ccr_DateTimeType[]';
  public $Description = 'Ccr_Description[]';
  public $OrderRequest = '[]';
  public $Indications = '<Indication>[]';
  public $ReferenceID = '[]';
  public $CommentID = '[]';
}
class Ccr_SourceType extends XmlRec {
  public $Description = 'Ccr_Description';
  public $Actor = 'Ccr_ActorRefType[]';
  public $DateTime = 'Ccr_DateTimeType';
  public $ReferenceID = '[]';
  public $CommentID = '[]';
}
class Ccr_RateType extends XmlRec {
  public $Description = 'Ccr_Description';
  public function __construct() {
    $this->merge('Ccr_MeasureGroup');
  }
}
class Ccr_TestType extends Ccr_CodedDataObjectType {
  public $Method = 'Ccr_CodedDescType[]';
  public $Agent = 'Ccr_Agent[]';
  public $TestResult = 'Ccr_TestResultType';
  public $NormalResult;
  public $Flag = 'Ccr_CodedDescType[]';
  //
  public function getValue() {
    return getr($this, 'TestResult.Value');
  }
  public function getUnit() {
    return getr($this, 'TestResult.Units.Unit');
  }
  public function getResultDesc() {
    return $this->TestResult->getDesc();
  }
  public function getFlag() {
    return getr($this->first('Flag'), 'Text');
  }
}
class Ccr_TestResultType extends XmlRec {
  public $Description = 'Ccr_Description[]';  
  public function __construct() {
    $this->merge('Ccr_MeasureGroup');
  }
  //
  public function getDesc() {
    return getr($this->first('Description'), 'Text');
  }
}
/**
 * Recurrent refs */
class Ccr_Age extends Ccr_MeasureType {
}
class Ccr_Description extends Ccr_CodedDescType {
}
class Ccr_Type extends Ccr_CodedDescType {
}
class Ccr_Status extends Ccr_CodedDescType {
}
class Ccr_QuantityType extends Ccr_MeasureType {
}
/** 
 * Recurrent elements */
class Ccr_Agent extends XmlRec {
  public $Products = 'XmlArray_Ccr:<Product>Ccr_StructProdType[]';
  public $EnvironmentalAgents = 'XmlArray_Ccr:<EnvironmentalAgent>Ccr_CodedDataObjectType[]';
  public $Problems = 'XmlArray_Ccr:<Problem>Ccr_ProblemType[]';
  public $Procedures = 'XmlArray_Ccr:<Procedure>Ccr_ProcedureType[]';
  public $Results = 'XmlArray_Ccr:<Result>Ccr_ResultType[]';
  //
  public function getSingleProduct() {
    if (! empty($this->Products))
      return $this->Products->get();
  }
  public function getSingleEnvAgent() {
    if (! empty($this->EnvironmentalAgents))
      return $this->EnvironmentalAgents->get();
  }
}
class Ccr_Direction extends XmlRec {
  public $Description = 'Ccr_Description';
  public $DeliveryMethod = 'Ccr_Description';
  public $Dose = 'Ccr_DirectionDose[]';
  public $DoseCalculation = '[]';
  public $Vehicle = '[]';
  public $Route = 'Ccr_CodedDescType[]';
  public $Site = 'Ccr_CodedDescType[]';
  public $AdministrationTiming = '[]';
  public $Frequency = 'Ccr_FreqType[]';
  public $Interval = 'Ccr_IntervalType[]';
  public $Duration = 'Ccr_DurationType[]';
  public $DoseRestriction = '[]';
  //
  public function getDesc() {
    if (isset($this->Description))
      return $this->Description->Text;
  }
}
class Ccr_DirectionDose extends XmlRec {
  public $Rate = 'Ccr_RateType[]';
}
class Ccr_Reaction extends XmlRec {
  public $Description = 'Ccr_Description';
  public $Status = 'Ccr_Status';
  public $Severity = 'Ccr_CodedDescType';
  public $Interventions = '<Intervention>[]';
  //
  public function getDesc() {
    $desc = getr($this, 'Description.Text');
    return $desc;
  }
}
