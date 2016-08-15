<?php
require_once 'php/data/rec/_Rec.php';
require_once 'php/data/rec/sql/Clients.php';
require_once 'php/data/rec/sql/UserStub.php';
require_once 'php/newcrop/data/NCScript.php';
/**
 * ERX Pharmacy Renewal Line
 */
class ErxPharm extends Rec {
  //
  public $renewalRequestGuid;
  public $receivedTimestamp;
  public $locationName;
  public $doctorFullName;
  public $pharmacyInfo;
  public $pharmacyFullInfo;
  public $pharmacyStoreName;
  public $patientFirstName;
  public $patientMiddleName;
  public $patientLastName;
  public $patientDOB;
  public $patientGender;
  public $drugInfo;
  public $numberOfRefills; 
  public $externalLocationId;
  public $externalDoctorId;
  public $externalPatientId;
  public $externalPrescriptionId;
  public $quantity;
  public $sig;
  public $ncpdpId;
  public $spare1;
  public $spare2;
  public $spare3;
  public $spare4;
  public $spare5;
  //
  public function getJsonFilters() {
    return array(
      'patientDOB' => JsonFilter::informalDate(),
      'receivedTimestamp' => JsonFilter::informalDateTime());
  }
  public function toJsonObject(&$o) {
    $o->_name = "$this->patientLastName, $this->patientFirstName $this->patientMiddleName";
  }
  //
  /**
   * @param [RenewalSummaryV2,..] $reqs
   * @return array(guid=>ErxPharm,..)
   */
  public static function fromRenewalRequests($rqs) {
    if (! empty($rqs)) {
      $recs = array();
      foreach ($rqs as &$rq) {
        $rec =  static::from($rq);
        $doc = UserStub::fetch($rec->externalDoctorId);
        $rec->_doc = $doc->name;
        $recs[$rec->renewalRequestGuid] = $rec;
      } 
      return $recs;
    }
  }
  static function from($r) {
    return new static(
      get($r, 'RenewalRequestGuid'),
      get($r, 'ReceivedTimestamp'),
      get($r, 'LocationName'),
      get($r, 'DoctorFullName'),
      get($r, 'PharmacyInfo'),
      get($r, 'PharmacyFullInfo'),
      get($r, 'PharmacyStoreName'),
      get($r, 'PatientFirstName'),
      get($r, 'PatientMiddleName'), 
      get($r, 'PatientLastName'),
      get($r, 'PatientDOB'),
      get($r, 'PatientGender'),
      get($r, 'DrugInfo'),
      get($r, 'NumberOfRefills'),
      get($r, 'ExternalLocationId'),
      get($r, 'ExternalDoctorId'),
      get($r, 'ExternalPatientId'), 
      get($r, 'ExternalPrescriptionId'), 
      get($r, 'Quantity'),
      get($r, 'Sig'),
      get($r, 'NcpdpId'),
      get($r, 'Spare1'), 
      get($r, 'Spare2'),
      get($r, 'Spare3'),
      get($r, 'Spare4'),
      get($r, 'Spare5'));
  }
}

