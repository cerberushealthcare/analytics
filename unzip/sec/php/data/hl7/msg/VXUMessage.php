<?php
require_once 'php/data/hl7/msg/_HL7Message.php';
require_once 'php/data/hl7/msg/seg/PID.php';
require_once 'php/data/hl7/msg/seg/ORC.php';
require_once 'php/data/hl7/msg/seg/RXA.php';
//
/**
 * Vaccine Message
 * @author Warren Hornsby
 */
class VXUMessage extends HL7Message {
  //
  /* Segments */
  public $Header;
  public $PatientId = 'PID';
  //
  public function getPatientId() {
    return $this->get('PatientId');  
  }
  //
  /**
   * @param Facesheet_Hl7Immun $fs
   * @return VXUMessage
   */
  static function from($fs) {
    $header = MSH_VXU::asV04($fs);
    $me = static::fromHeader($header);
    $me->PatientId = PID_VXU::from($fs);
    $me->_fs = $fs;
    return $me;
  }
}
//
class PID_VXU extends PID {
  //
  /* Segments */
  public $CommonOrder = 'ORC';
  public $RxAdmin = 'RXA[]';
  //
  static function from($fs) {
    $me = parent::from($fs);
    $me->CommonOrder = ORC::asStandard();
    $me->RxAdmin = RXA::from($fs);
    return $me;
  }
}
class MSH_VXU extends MSH {
  //
  static function asSendable($type, $fs) {
    $msgControlId = static::makeMsgControlId($fs);
    return parent::asSendable($type, $fs->UserGroup, $msgControlId);
  }
  /**
   * @param Facesheet_Hl7Immun $fs
   */
  static function asV04($fs) {
    return static::asSendable(CM_MsgType_VXU::asV04(), $fs);
  }
}
class CM_MsgType_VXU extends HL7CompValue {
  //
  static function asV04() {
    return static::from('V04');
  }
  static function from($trigger) {
    $me = new static();
    $me->type = 'VXU';
    $me->trigger = $trigger;
    return $me;
  }
}