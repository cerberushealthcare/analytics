<?php
require_once 'php/data/hl7/msg/seg/_HL7Segment.php';
//
/**
 * Message Acknowledgment 
 * @author Warren Hornsby
 */
class MSA extends HL7Segment {
  //
  public $segId = 'MSA';
  public $ackCode;
  public $controlId;
  //
  static function from($ackCode, $controlId) {
    $me = new static();
    $me->ackCode = $ackCode;
    $me->controlId = $controlId;
    return $me;
  }
  static function asAccept($controlId) {
    return static::from('AA', $controlId);
  }
}
