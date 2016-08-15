<?php
require_once 'php/data/hl7/msg/labs/ORU_Lab.php';
//
/**
 * ORU_L0020110
 * Clinical Pathology Labs
 */
class ORU_L0020110 extends ORU_Lab {
  //
  public function getUgid() {
    // return 110;  // Loc
    return 1;  // testing
  }
}