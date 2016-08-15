<?php
require_once 'php/data/hl7/msg/labs/ORU_Lab.php';
//
/**
 * ORU_L0090001
 * Quest
 */
class ORU_L0090001 extends ORU_Lab {
  //
  public $PatientId = 'PID_L0090001';
  //
  public function getUgid() {
    return 120;  // testing
  }
}
class PID_L0090001 extends PID_Lab {
  //
  public $ObsRequest = 'OBR_L0090001';
}
class OBR_L0090001 extends OBR_Lab {
  //
}
