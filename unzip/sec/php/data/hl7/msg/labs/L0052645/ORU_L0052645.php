<?php
require_once 'php/data/hl7/msg/labs/ORU_Lab.php';
//
/**
 * ORU_L0052645 - West Pacific
 */
class ORU_L0052645 extends ORU_Lab {
  //
  /* Segments */
  public $Header;
  public $Software = 'SFT';
  public $PatientId = 'PID_L0052645';
  public $Eof = 'FTS';
  //
  public function getUgid() {
    return 2645;  // John Richard
  }
}
class PID_L0052645 extends PID_Lab {
  //
  public $CommonOrder = 'ORC_L0052645';
  public $ObsRequest = 'OBR_Lab[]';
}
class ORC_L0052645 extends ORC_Lab {
  //
}