<?php
set_include_path('../sec/');
require_once '../api/dao/data/Api.php';
//
/**
 * PatientMap
 */
class ApiPatientMap extends Api {
  //
  public $practiceId;
  public $patientId;
  public $externalId/*cid*/;
  //
  /**
   * Constructor
   * @param ['field'=>value,..] $data
   */
  public function __construct($data) {
    $required = array('practiceId','patientId','externalId');
    $this->load($data, $required);
  }
}
