<?php
require_once '_DomData.php';
/**
 * Data objects for invoking NewCrop web services 
 */
//
// UPDATE1
// Request params for /v7/WebServices/Update1.asmx
//
class GetAccountStatusDetailParam extends DomData {
  const STATUS_DR_REVIEW = 'DrReview';
  const STATUS_FAILED_ERX = 'FailedElectronicRx';
  const STATUS_FAILED_FAX = 'FailedFax';
  const STATUS_STAFF_PROC = 'StaffProcessing';
  const STATUS_ALL_DR_REVIEW = 'AllDoctorReview';
  const NO_SCHEMA = 'N';
  //
  public /*SoapCredentials*/ $credentials;
  public /*SoapAccount*/ $accountRequest;
  public $locationId;
  public $licensedPrescriberId;
  public $statusSectionType;
  public $includeSchema;
  public $sortOrder;
}
class GetAllRenewalRequestsV2Param extends DomData {
  public /*SoapCredentials*/ $credentials;
  public /*SoapAccount*/ $accountRequest;
  public $locationId;
  public $licensedPrescriberId;
}
class GetPatientFullMedicationHistory6 extends DomData {
  public /*SoapCredentials*/ $credentials;
  public /*SoapAccount*/ $accountRequest;
  public /*SoapPatient*/ $patientRequest;
  public /*SoapRxReq*/ $prescriptionHistoryRequest;
  public /*SoapPtInfoReq*/ $patientInformationRequester;
  public $patientIdType;
  public $includeSchema = 'N';
}
class GetPatientAllergyHistoryV3 extends DomData {
  public /*SoapCredentials*/ $credentials;
  public /*SoapAccount*/ $accountRequest;
  public /*SoapPatient*/ $patientRequest;
  public /*SoapPtInfoReq*/ $patientInformationRequester;
}
class GetDailyMeaningfulUseReport extends DomData {
  public /*SoapCredentials*/ $credentials;
  public /*SoapAccount*/ $accountRequest;
  public $locationId;
  public $licensedPrescriberId;
  public $reportDateCCYYMMDD;
  public $includeSchema = 'N';
}
//
// PATIENT (deprecated)
// Request params for /v7/WebServices/Patient.asmx
//
class GetPatientHistoryParam extends DomData {  // used for GetPatientAllergyHistory and GetPatientFullMedicationHistory
  public /*SoapCredentials*/ $credentials;
  public /*SoapAccount*/ $accountRequest;
  public /*SoapPatient*/ $patientRequest;
  public /*SoapRxReq*/ $prescriptionHistoryRequest;
  public /*SoapPtInfoReq*/ $patientInformationRequester;
  public $date;
  public function __construct() { 
    $this->date = date('Y-m-d');
    $args = func_get_args(); 
    call_user_func_array(array('DomData', '__construct'), $args);
  }
}
class GetAccountStatusParam extends DomData {
  public /*SoapCredentials*/ $credentials;
  public /*SoapAccount*/ $accountRequest;
  public $locationId;
  public $userId;
  public $userType;
}
//
// Common data structures
//
class SoapCredentials extends DomData {
  public $PartnerName;
  public $Name;
  public $Password;
  /**
   * Static builder
   * @param NewCrop.Credentials $cred
   * @return SoapCredentials
   */
  public static function fromCredentials($cred) {
    return new SoapCredentials(
      $cred->partner,
      $cred->name, 
      $cred->password );
  }
}
class SoapAccount extends DomData {
  public $AccountId;
  public $SiteId;
  /**
   * Static builder
   * @param Credentials $cred
   * @return SoapAccount
   */
  public static function fromCredentials($cred) {
    return new SoapAccount(
      $cred->accountId,
      $cred->siteId);
  }
}
class SoapPatient extends DomData {
  public $PatientId;
}
class SoapRxReq extends DomData {
  public $StartHistory = '2004-01-01';
  public $EndHistory;
  public $PrescriptionStatus = 'C';
  public $PrescriptionSubStatus = 'S';
  public $PrescriptionArchiveStatus = 'N';
  //
  const RX_CURRENT = 'N';
  const RX_ARCHIVED = 'Y';
  /**
   * Overriden constructor (to default date)
   */
  public function __construct() {
    $this->EndHistory = date('Y-m-d');
    $args = func_get_args(); 
    call_user_func_array(array('DomData', '__construct'), $args);
  }
}
class SoapPtInfoReq extends DomData {
  public $UserType;
  public $UserId;
}
/**
 * Exceptions
 */
class SoapResultException extends Exception {
  public $status;  
  public $message;
  public $xml;
  public $rowCount;
  public $timing;
  //
  const STATUS_OK = 'OK';
  const STATUS_FAIL = 'Fail';
  const STATUS_NOT_FOUND = 'NotFound';
  const STATUS_UNKNOWN = 'Unknown';
  //
  private $notFounds;  // ['ACCOUNT'=>id,'PATIENT'=>id,..]
  /**
   * Constructor
   * @param GetPatient_HistoryResult $result
   */
  public function __construct($result) {
    $this->status = $result->Status;
    $this->message = 'Status: ' . $result->Status . ', Message: ' . $result->Message;
    $this->xml = $result->XmlResponse;
    $this->rowCount = $result->RowCount;
    $this->timing = $result->Timing;
    $this->setNotFounds($this->status);
  }
  public function isNotFound() {
    return $this->status == static::STATUS_NOT_FOUND;
  }
  /**
   * Returns supplied patient ID if not found
   * @return string 
   */
  public function isPatientNotFound() {
    if (isset($this->notFounds['PATIENT'])) {
      return $this->notFounds['PATIENT'];
    }
  }
  //
  private function setNotFounds($status) { 
    $nf = array();
    if ($status == SoapResultException::STATUS_NOT_FOUND) {
      $msg = trim(strtoupper($this->message));
      $a = explode(' FOUND', $msg);
      foreach ($a as $b) {
        $c = explode(' >', $b);
        if (count($c) > 1) {
          $d = explode('< ', $c[1]);
          if (count($d) > 1) {
            $type = trim($c[0]);
            $id = trim($d[0]);
            $nf[$type] = $id;
          }
        }
      }
    }
    $this->notFounds = $nf;
  }
  /**
   * Static thrower
   * @param GetPatient_HistoryResult $result
   */
  public static function throwIfNotOk($result) {
    if ($result->Status != 'OK') 
      throw new SoapResultException($result);
  }
}
?>