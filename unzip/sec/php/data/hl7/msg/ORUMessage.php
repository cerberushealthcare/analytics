<?php
require_once 'php/data/hl7/msg/seg/PID.php';
require_once 'php/data/hl7/msg/seg/PV1.php';
require_once 'php/data/hl7/msg/seg/ORC.php';
require_once 'php/data/hl7/msg/seg/OBR.php';
require_once 'php/data/hl7/msg/seg/OBX.php';
require_once 'php/data/hl7/msg/seg/NTE.php';
require_once 'php/data/hl7/msg/seg/NK1.php';
require_once 'php/data/hl7/msg/seg/FTS.php';
//
/**
 * Unsolicited Observation Message
 * @author Warren Hornsby
 */
class ORUMessage extends HL7Message {
  //
  /* Segments */
  public $Header;
  public $Software = 'SFT';
  public $PatientId = 'PID';
  public $Eof = 'FTS';
  //
  public function getPatientId() {
    return $this->get('PatientId');  
  }
  public function getObsRequests() {
    $pid = $this->getPatientId();
    return $pid->getObsRequests();
  }
}