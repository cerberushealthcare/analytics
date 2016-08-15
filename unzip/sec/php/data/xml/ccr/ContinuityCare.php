<?php
require_once 'php/data/xml/_XmlRec.php';
require_once 'CcrData.php';
//
/**
 * Continuity Care Record (CCR) 
 */
class ContinuityOfCareRecord extends XmlRec {
  //
  public $CCRDocumentObjectId;
  public $Language = 'Ccr_CodedDescType';
  public $Version;
  public $DateTime = 'Ccr_DateTimeType';
  public $Patient = 'Ccr_Patient'; 
  public $From = 'Ccr_From'; 
  public $To = 'Ccr_To';
  public $Purpose = 'Ccr_PurposeType[]';
  public $Body = 'Ccr_Body';
  public $Actors = 'Ccr_Actors'; 
  //
  public function /*Ccr_ActorType*/getPatient() {
    $id = $this->Patient->ActorID;
    return $this->Actors->get($id);
  }
}
class Ccr_Patient extends XmlRec {
  public $ActorID;
}
class Ccr_From extends XmlRec {
  public $ActorLink = 'Ccr_ActorRefType[]';
}
class Ccr_To extends XmlRec {
  public $ActorLink = 'Ccr_ActorRefType[]';
}
class Ccr_Body extends XmlRec {
  public $Payers;
  public $AdvanceDirectives = 'Ccr_Body_ADs';
  public $Support;
  public $FunctionalStatus;
  public $Problems = 'Ccr_Body_Problems';
  public $FamilyHistory = 'Ccr_Body_FamHx';
  public $SocialHistory = 'Ccr_Body_SocHx';
  public $Alerts = 'Ccr_Body_Alerts';
  public $Medications = 'Ccr_Body_Meds';
  public $MedicalEquipment;
  public $Immunizations = 'Ccr_Body_Immuns';
  public $VitalSigns = 'Ccr_Body_Vitals';
  public $Results = 'Ccr_Body_Results';
  public $Procedures = 'Ccr_Body_Procs';
  public $Encounters = 'Ccr_Body_Encounters';
  public $PlanOfCare = 'Ccr_Body_PlanOfCare';
  public $HealthCareProviders;
}
class Ccr_Body_ADs extends XmlRec {
  public $AdvanceDirective = 'Ccr_CodedDataObjectType[]';
}
class Ccr_Body_Problems extends XmlArray_Ccr {
  public $Problem = 'Ccr_ProblemType[]'; 
}
class Ccr_Body_FamHx extends XmlRec {
  
}
class Ccr_Body_SocHx extends XmlRec {
  
}
class Ccr_Body_Alerts extends XmlArray_Ccr {
  public $Alert = 'Ccr_AlertType[]';
}
class Ccr_Body_Meds extends XmlArray_Ccr {
  public $Medication = 'Ccr_StructProdType[]';
}
class Ccr_Body_Immuns extends XmlArray_Ccr {
  public $Immunization = 'Ccr_StructProdType[]';
}
class Ccr_Body_Vitals extends XmlArray_Ccr {
  public $Result = 'Ccr_ResultType[]';
}
class Ccr_Body_Results extends XmlArray_Ccr {
  public $Result = 'Ccr_ResultType[]';
}
class Ccr_Body_Procs extends XmlArray_Ccr {
  public $Procedure = 'Ccr_ProcedureType[]';
}
class Ccr_Body_Encounters extends XmlRec {
  
}
class Ccr_Body_PlanOfCare extends XmlRec {
  
}
class Ccr_Actors extends XmlArray {
  public $Actor = 'Ccr_ActorType[]';
  static $ID_FID = 'ActorObjectID';
}
class Ccr_References extends XmlRec {
}
/**
 * CCR array */
class XmlArray_Ccr extends XmlArray {
  static $ID_FID = 'CCRDataObjectID';
}
