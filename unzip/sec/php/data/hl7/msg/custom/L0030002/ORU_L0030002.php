<?php
require_once 'php/data/hl7/msg/custom/_ORUCustom.php';
require_once 'php/data/hl7/msg/custom/L0030002/PID_L0030002.php';
require_once 'php/data/hl7/msg/custom/L0030002/ORC_L0030002.php';
require_once 'php/data/hl7/msg/custom/L0030002/OBR_L0030002.php';
require_once 'php/data/hl7/msg/custom/L0030002/OBX_L0030002.php';
//
/**
 * ORU_L0030002 
 * Testing Account 
 */
class ORU_L0030002 extends ORUMessage {
  //
  /* Segments */
  public $Header;
  public $Software = 'SFT';
  public $PatientId = 'PID_L0030002';
  public $Eof = 'FTS';
  //
  public function getUgid() {
    return 2;  // wghornsby
  }
}