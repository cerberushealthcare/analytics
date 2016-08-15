<?php
require_once 'php/data/hl7-2.5.1/msg/_HL7Message.php';
require_once 'php/data/hl7-2.5.1/msg/seg/PID.php';
require_once 'php/data/hl7-2.5.1/msg/seg/PD1.php';
require_once 'php/data/hl7-2.5.1/msg/seg/NK1.php';
require_once 'php/data/hl7-2.5.1/msg/seg/ORC.php';
require_once 'php/data/hl7-2.5.1/msg/seg/RXA.php';
require_once 'php/data/hl7-2.5.1/msg/seg/RXR.php';
require_once 'php/data/hl7-2.5.1/msg/seg/OBX.php';
//
/**
 * Vaccine Message v.2.5.1
 * @author Warren Hornsby
 */
class VXUMessage extends HL7Message {
  //
  /* Segments */
  public $Header;
  public $PatientId = 'PID';
  public $NextOfKin = 'NK1[]';
  public $OrderRequest = 'ORC[]';
  //
  //
  static function from(/*Facesheet_Hl7Immun*/$fs, $sendFacility = null, $rcvFacility = null) {
    static::loadIds($fs);
    $header = MSH_VXU::from($fs, $sendFacility, $rcvFacility);
    $me = new static($header);
    $me->PatientId = PID_VXU::from($fs);
    $me->NextOfKin = NK1::all($fs);
    $me->OrderRequest = ORC_VXU::all($fs);
    $me->_fs = $fs;
    return $me;
  }
  static function loadIds($fs) {
    RXA_VXU::loadIds($fs);
    RXR_VXU::loadIds($fs);
    OBX_VXU::loadIds($fs);
  }
}
//
class MSH_VXU extends MSH {
  //
  static function from(/*Facesheet_Hl7Immun*/$fs, $sendFacility = null, $rcvFacility = null) {
    $me = static::asClicktate();
    $me->sendFacility = $sendFacility ?: HD::asPractice($fs->UserGroup);
    $me->rcvFacility = $rcvFacility;
    $me->msgType = CM_MsgType::create('VXU', 'V04', 'VXU_V04');
    $me->msgControlId = static::makeMsgControlId($fs);
    $me->acceptAckType = ID_AckType::NEVER;
    $me->appAckType = ID_AckType::NEVER;
    return $me;    
  }
}
