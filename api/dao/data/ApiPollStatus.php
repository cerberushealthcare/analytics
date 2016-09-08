<?php
require_once 'Api.php';
/**
 * Poll Status
 */
class ApiPollStatus extends Api {
  //
  public $practiceId;
  public $sessionId;
  /**
   * Constructor
   * @param ['field'=>value,..] $data
   */
  public function __construct($data) {
    $required = array('practiceId','sessionId');
    $this->load($data, $required);
  }
}
