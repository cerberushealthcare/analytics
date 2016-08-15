<?php
require_once 'php/data/hl7/msg/labs/ORU_Lab.php';
//
/**
 * ORU_L0101737
 * Central Florida Health Alliance
 */
class ORU_L0101737 extends ORU_Lab {
  //
  public function getUgid() {
    // return 1737;  // Hammesfahr
    return 1;  // testing
  }
}