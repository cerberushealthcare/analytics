<?php
require_once 'php/data/hl7-2.5.1/msg/seg/_HL7Values.php';
/**
 * HL7 Segment  
 * @author Warren Hornsby
 */
abstract class HL7Segment extends HL7Rec {
  //
  public $_rec;
  /*
   * public $segId = 'PID';        // first field must be segId
   * public $seq;                  // remainder of data fields
   * public $patientId = 'CX';     // data fields may be assigned an HL7Value class name
   * //
   * public $PatientVisit = 'PV1'; // segments are assigned an HL7Segment class name, e.g. 'PV1'
   * //
   * When you extend a defined HL7Segment class, you will not need to redeclare the data fields
   * if you are not changing any of their values. If you want to redefine a segment class name,
   * for example, you only need to declare that segment's assignment in the extended class.
   */
  //
  /**
   * @return array('Comment',..)
   */
  public function getComments() {
    $a = array();
    $segs = $this->get('Comment');
    if ($segs) {
      foreach ($segs as $seg) 
        $a[] = $seg->comment;
    }
    return $a;
  }
  //
  /**
   * @param HL7Buffer $buffer
   * @param ST_EncodingChars $encoding
   * @return HL7Segment 
   */
  static function fromBuffer($buffer, $encoding) {
    if ($buffer) {
      $me = new static();
      $me->_rec = $buffer->current();
      $me->setValues($buffer->pop(), $encoding);
      $me->setSegments($buffer, $encoding);
      return $me;
    }
  }
  static function asEmpty() {
    $me = new static();
    foreach ($me as $fid => $value)
      if ($fid != 'segId') 
        $me->$fid = null;
    return $me;
  }
  //
  protected function setValues($values, $encoding) {
    next($values);
    parent::setValues($values, $encoding);
  }
  protected function getVars() {
    $vars = parent::getVars();
    $vars = $this->sortVars($vars);
    array_shift($vars);  // shift off 'segId'
    return $vars;
  }
  protected function sortVars($vars) {  // ensure segId and data fields are defined before segments
    $move = array();
    foreach ($vars as $fid => $value) {
      if ($fid != 'segId')
        $move[$fid] = $value;
      else 
        break;
    }
    if (! empty($move)) 
      $vars = array_merge(array_slice($vars, count($move), null, true), $move);
    return $vars;
  }
}
//
abstract class HL7SequencedSegment extends HL7Segment {
  //
  static $_seq = 0;
  //
  static function resetSeq() {
    static::$_seq = 0;
  }
  static function asEmpty() {
    static::$_seq++;
    $me = parent::asEmpty();
    $me->seq = static::$_seq;
    return $me;
  }
}