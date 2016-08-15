<?php
require_once 'php/newcrop/data/soap/_SoapRec.php';
require_once 'php/newcrop/data/SoapData.php';
//
/**
 * PatientAllergyHistoryV3
 */
class PatientAllergyHistoryV3 extends SoapRec {
  public /*xs:int*/ $AllergyType;
  public /*xs:int*/ $CompositeAllergyID;
  public /*xs:string*/ $AllergySourceID;
  public /*xs:string*/ $AllergyId;
  public /*xs:int*/ $AllergyConceptId;
  public /*xs:string*/ $ConceptType;
  public /*xs:string*/ $AllergyName;
  public /*xs:string*/ $Status;
  public /*xs:int*/ $AllergySeverityTypeId;
  public /*xs:string*/ $AllergySeverityName;
  public /*xs:string*/ $AllergyNotes;
  public /*xs:string*/ $ConceptID;
  public /*xs:int*/ $ConceptTypeId;
  public /*xs:string*/ $rxcui;
  //
  static function fetch($host, $credentials, $cid) {
    $response = self::call(self::getUrl($host), self::getParam($credentials, $cid));
    $recs = self::getResponseRecs($response);
    return self::fromArray($recs);
  }
  protected static function getResponseRecs($response) {
    return parent::getResponseRecs(
      $response,
      'GetPatientAllergyHistoryV3Result',
      'NewDataSet',
      'Table',
      true);
  }
  protected static function fromArray($recs) {
    return parent::fromArray($recs, __CLASS__);
  }
  protected static function getUrl($host) {
    return parent::getUpdate1Url($host);
  }
  private static function getParam($credentials, $cid) {
    return new GetPatientAllergyHistoryV3(
      SoapCredentials::fromCredentials($credentials),
      SoapAccount::fromCredentials($credentials),
      new SoapPatient($cid),
      new SoapPtInfoReq());
  }}
?>