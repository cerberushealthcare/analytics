<?php
require_once 'Api.php';
/**
 * Select Patient
 */
class ApiSelectPatient extends Api {
  //
  public $practiceId;
  public $patientId;
  public $sessionId;
  public $page;
  /**
   * Constructor
   * @param ['field'=>value,..] $data
   */
  public function __construct($data) {
    $required = array('practiceId','patientId','sessionId','page');
    $this->load($data, $required);
  }
  /**
   * Get page portion of URL
   * @return 'facesheet.php'
   */
  public function getUrlPage() {
    switch (strtoupper($this->page)) {
      case "FACESHEET":
        return 'face.php';
      case "TRACKING":
        return 'tracking.php';
      default:
        $this->throwApiApiException('Invalid page');
    }
  }
}