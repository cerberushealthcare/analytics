<?php
require_once 'php/data/hl7/msg/labs/ORU_Lab.php';
//
/**
 * ORU_L0030002 
 * Testing Account 
 */
class ORU_L0030002 extends ORU_Lab {
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
//
class PID_L0030002 extends PID_Lab {
  //
  public $CommonOrder = 'ORC_L0030002';
  public $ObsRequest = 'OBR_L0030002[]';
}
//
class ORC_L0030002 extends ORC_Lab {
  //
  /* qtyTiming.priority */
  const PRIORITY_STAT = 'S';
  const PRIORITY_ASAP = 'A';
  const PRIORITY_ROUTINE = 'R';
  const PRIORITY_CALLBACK = 'C';
  const PRIORITY_TIMING_CRIT = 'T';
  static $PRIORITIES = array(
    self::PRIORITY_STAT => 'STAT',
    self::PRIORITY_ASAP => 'As Soon As Possible',
    self::PRIORITY_ROUTINE => 'Routine',
    self::PRIORITY_CALLBACK => 'Callback',
    self::PRIORITY_TIMING_CRIT => 'Timing Critical');
  //
  public function sanitize() {
    parent::sanitize();
    $this->qtyTiming->priority = geta(static::$PRIORITIES, $this->qtyTiming->priority);
    return $this;
  } 
}
//
class OBR_L0030002 extends OBR_Lab {
  //
  public $Observation = 'OBX_L0030002[]';
}
//
class OBX_L0030002 extends OBX_Lab {
  //
  /* valueType */
  const VALUETYPE_NUMERIC = 'NM';
  const VALUETYPE_STRING = 'ST';
  /* abnormal */
  const ABNORMAL_HIGH = 'H';
  const ABNORMAL_LOW = 'L';
  static $ABNORMALS = array(
    self::ABNORMAL_HIGH => 'High', 
    self::ABNORMAL_LOW => 'Low');
  /* resultStatus */
  const RESULTSTATUS_FINAL = 'F';
  const RESULTSTATUS_CORRECTION = 'C';
  const RESULTSTATUS_PENDING = 'I';
  static $RESULTSTATUSES = array(
  self::RESULTSTATUS_FINAL => 'Final results',
  self::RESULTSTATUS_CORRECTION => 'Record is a correction and replaces a final result',
  self::RESULTSTATUS_PENDING => 'Specimen in lab; results pending');
}
