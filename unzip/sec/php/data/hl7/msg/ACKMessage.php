<?php
require_once 'php/data/hl7/msg/_HL7Message.php';
require_once 'php/data/hl7/msg/seg/MSA.php';
//
/**
 * Acknowledgment message
 * @author Warren Hornsby
 */
class ACKMessage extends HL7Message {
  //
  /* Segments */
  public $Header;
  public $Ack = 'MSA';
  //
  static function fromORU($msg) {
    $id = $msg->Header->msgControlId;
    $header = MSH_ACK::asClicktate($msg->getUgid(), $id);
    $me = static::fromHeader($header);
    $me->Ack = MSA::asAccept($id);
    return $me;
  }
}
//
class MSH_ACK extends MSH {
  //
  static function asClicktate($ugid, $id) {
    return static::asSendable(CM_MsgType_ACK::from('R01'), $ugid, $id);
  }
}
class CM_MsgType_ACK extends HL7CompValue {
  //
  static function from($trigger) {
    $me = new static();
    $me->type = 'ACK';
    $me->trigger = $trigger;
    return $me;
  }
}
