<?php
require_once 'php/newcrop/data/soap/_SoapRec.php';
require_once 'php/newcrop/data/SoapData.php';
//
class DailyMeaningfulUseReport extends SoapRec {
  //
  public $SourceTypeID;  // type="xs:unsignedByte" minOccurs="0" />
  public $ActionTypeID;  // type="xs:unsignedByte" minOccurs="0" />
  public $DoctorExternalID;  // type="xs:string" minOccurs="0" />
  public $LocationExternalID;  // type="xs:string" minOccurs="0" />
  public $PatientExternalID;  // type="xs:string" minOccurs="0" />
  public $PerformerUserTypeID;  // type="xs:unsignedByte" minOccurs="0" />
  public $DocumentMimeType;  // type="xs:string" minOccurs="0" />
  public $DocumentClassificationType;  // type="xs:string" minOccurs="0" />
  public $DocumentClassificationSubType;  // type="xs:string" minOccurs="0" />
  public $DestinationTypeID;  // type="xs:unsignedByte" minOccurs="0" />
  public $ReconciliationPerformed;  // type="xs:boolean" minOccurs="0" />
  public $EpisodeID;  // type="xs:string" minOccurs="0" />
  public $EncounterID;  // type="xs:string" minOccurs="0" />
  public $CreatedTimestamp;  // type="xs:string" minOccurs="0" />
  public $ReportDate; /* added */
  //
  const SOURCE_WEB_APP = 1;
  const SOURCE_PATIENT_PORTAL = 2;
  const SOURCE_WEB_SERVICE = 3;
  //
  const ACTION_VIEW = 1;
  const ACTION_DOWNLOAD = 2;
  const ACTION_TRANSMIT = 3;
  //
  const PERFORMER_AGENT = 1;
  const PERFORMER_DOCTOR = 2;
  const PERFORMER_MIDLEVEL = 3;
  const PERFORMER_PATIENT = 4;
  const PERFORMER_STAFF = 5;
  //
  const DEST_PATIENT_PORTAL = 1;
  const DEST_DOCTOR = 2;
  //
  static function /*self[]*/fetch($host, $credentials, $lpid, $date) {
    $response = static::call(static::getUrl($host), static::getParam($credentials, $lpid, $date));
    $result = get($response, 'GetDailyMeaningfulUseReportResult'); 
    if ($result) {
      if ($result->Status == 'OK') {
        $recs = static::getResponseRecs($response);
        $recs = static::fromArray($recs);
        $date = dateToString($date);
        foreach ($recs as $rec) {
          $rec->ReportDate = $date;
        }
        return $recs;
      }
    }
  }
  static function extractPatientTransmits(/*self[]*/$recs) {
    $us = array();
    if ($recs) {
      foreach ($recs as $rec) {
        if ($rec->isPatientTransmit())
          $us[] = $rec;
      }
    }
    return $us;
  }
  //
  public function getDoctorId() {
    return $this->DoctorExternalID;
  }
  public function getClientId() {
    return $this->PatientExternalID;
  }
  //
  /* MU2 trackables */
  public function isMu2_portalVdt() {
    return $this->is_portal_patient();
  }
  public function isMu2_portalDsm() {
    return $this->is_portal_patient() && $this->isAction_transmit() && $this->isDest_doctor(); 
  }
  public function isMu2_summaryToPatient() {
    return $this->is_webApp_transmit_office() && $this->isDest_portal();
  }
  public function isMu2_summaryToDoctor() {
    return $this->is_webApp_transmit_office() && $this->isDest_doctor();
  }
  public function isMu2_medRecon() {
    return $this->ReconciliationPerformed == 'true';
  }
  public function isMu2_summaryFromDoctor() {
    return $this->isSource_webApp() && $this->isAction_view() && $this->DocumentMimeType = 'text/xml';
  }
  //
  protected function is_portal_patient() {
    return $this->isSource_portal() && $this->isPerformer_patient();
  }
  protected function is_webApp_transmit_office() {
    return $this->isSource_webApp() && $this->isAction_transmit() && $this->isPerformer_office(); 
  }
  protected function isSource_portal() {
    return $this->SourceTypeID == static::SOURCE_PATIENT_PORTAL;
  }
  protected function isSource_webService() {
    return $this->SourceTypeID == static::SOURCE_WEB_SERVICE;
  }
  protected function isSource_webApp() {
    return $this->SourceTypeID == static::SOURCE_WEB_APP;
  }
  protected function isAction_transmit() {
    return $this->ActionTypeID == static::ACTION_TRANSMIT;
  }
  protected function isAction_view() {
    return $this->ActionTypeID == static::ACTION_VIEW;
  }
  protected function isAction_download() {
    return $this->ActionTypeID == static::ACTION_DOWNLOAD;
  }
  protected function isPerformer_patient() {
    return $this->PerformerUserTypeID == static::PERFORMER_PATIENT;
  }
  protected function isPerformer_office() {
    return $this->PerformerUserTypeID == static::PERFORMER_DOCTOR || $this->PerformerUserTypeID == static::PERFORMER_MIDLEVEL || $this->PerformerUserTypeID == static::PERFORMER_STAFF;
  }
  protected function isDest_portal() {
    return $this->DestinationTypeID == static::DEST_PATIENT_PORTAL;
  }
  protected function isDest_doctor() {
    return $this->DestinationTypeID == static::DEST_DOCTOR;
  }
  //
  protected static function getResponseRecs($response) {
    try {
      return parent::getResponseRecs(
        $response,
        'GetDailyMeaningfulUseReportResult',
        'NewDataSet',
        'Table',
        true);
    } catch (SoapResultException $e) {
      if ($e->isNotFound())
        return null;
      else
        throw $e;
    }
  }
  protected static function fromArray($recs) {
    return parent::fromArray($recs, __CLASS__);
  }
  protected static function getUrl($host) {
    return parent::getUpdate1Url($host);
  }
  private static function getParam($credentials, $lpid, $date) {
    return new GetDailyMeaningfulUseReport(
      SoapCredentials::fromCredentials($credentials),
      SoapAccount::fromCredentials($credentials),
      $credentials->locationId,
      '',
      $date,
      'N');
  }
}
