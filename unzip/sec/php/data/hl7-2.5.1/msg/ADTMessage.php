<?php
require_once 'php/data/hl7-2.5.1/msg/_HL7Message.php';
require_once 'php/data/hl7-2.5.1/msg/seg/EVN.php';
require_once 'php/data/hl7-2.5.1/msg/seg/PID.php';
require_once 'php/data/hl7-2.5.1/msg/seg/PV1.php';
require_once 'php/data/hl7-2.5.1/msg/seg/DG1.php';
require_once 'php/data/hl7-2.5.1/msg/seg/OBX.php';
//
/**
 * Admit/Transfer Message v.2.5.1
 * @author Warren Hornsby
 */
class ADTMessage extends HL7Message {
  //
  static function asRegister(/*Facesheet_Syndrome*/$fs) {
    return ADT_A01::asRegister($fs);
  }
  static function asEndVisit(/*Facesheet_Syndrome*/$fs) {
    return ADT_A03::asEndVisit($fs);
  }
  static function byType($type, $fs) {
    switch ($type) {
      case 'A04':
        return static::asRegister($fs);
      case 'A03':
        return static::asEndVisit($fs);
    }
  }
  //
  protected static function from($fs, $header) {
    $me = new static($header);
    $me->_fs = $fs;
    return $me;
  }
}
class ADT_A01 extends ADTMessage {
  //
  static function asRegister($fs) {
    $header = MSH_ADT::asRegister($fs);
    $me = static::from($fs, $header);
    $me->EventType = EVN::from($header);
    $me->PatientId = PID_ADT::from($fs);
    $me->Observation = OBX_ADT::all($fs);
    $me->Diagnosis = DG1_ADT::all($fs);
    return $me;
  }
}
class ADT_A03 extends ADTMessage {
  //
  static function asEndVisit($fs) {
    $header = MSH_ADT::asEndVisit($fs);
    $me = static::from($fs, $header);
    $me->EventType = EVN::from($header);
    $me->PatientId = PID_ADT::from($fs);
    $me->Diagnosis = DG1_ADT::all($fs);
    $me->Observation = OBX_ADT::all($fs);
    return $me;
  }
}
//
class MSH_ADT extends MSH {
  //
  /*ADT_A01 structure*/
  const MSGTYPE_ADMIT = 'ADT^A01^ADT_A01';
  const MSGTYPE_REGISTER = 'ADT^A04^ADT_A01';
  const MSGTYPE_UPDATE = 'ADT^A08^ADT_A01';
  /*ADT_A03 structure*/
  const MSGTYPE_END_VISIT = 'ADT^A03^ADT_A03';
  //
  static function asRegister($fs) {
    return static::from($fs, self::MSGTYPE_REGISTER);
  }
  static function asEndVisit($fs) {
    return static::from($fs, self::MSGTYPE_END_VISIT);
  }
  protected static function from($fs, $msgType) {
    $me = static::asClicktate();
    $me->sendFacility = HD::asPractice($fs->UserGroup);
    $me->msgType = CM_MsgType::from($msgType);
    $me->msgControlId = static::makeMsgControlId($fs);
    $me->msgProfileId = EI::asNoAckSender();
    return $me;    
  }
}

