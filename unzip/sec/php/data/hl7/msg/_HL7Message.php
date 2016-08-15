<?php
require_once 'php/data/hl7/_HL7Rec.php';
require_once 'php/data/hl7/msg/seg/MSH.php';
//
/**
 * HL7 Message 
 * @author Warren Hornsby
 */
abstract class HL7Message extends HL7Rec {
  //
  /* Segments */
  public /*MSH*/ $Header;
  //
  public function __construct($header) {
    $this->Header = $header;
  }
  /*
   * @return string HL7
   */
  public function getData() {
    return $this->_data;
  }
  /*
   * @return ST_EncodingChars
   */
  public function getEncoding() {
    return $this->Header->encodingChars;
  }
  /**
   * @return string
   */
  public function toHL7() {
    $encoding = $this->getEncoding();
    $buffer = HL7Buffer::fromMessage($this);
    return $buffer->toString();
  }
  //
  /**
   * @param string $data
   * @return HL7Message
   */
  static function fromHL7($data) {
    $buffer = HL7Buffer::fromHL7($data);
    $header = MSH::fromBuffer($buffer);
    $me = static::fromHeader($header);
    $me->_data = $data;
    $me->setSegments($buffer, $me->getEncoding());
    return $me;
  }
  //
  protected static function fromHeader($header) {
    $class = $header->msgType->type . 'Message';
    $path = "php/data/hl7/msg/$class.php";
    @include_once $path;
    if (! class_exists($class, false))
      throw new HL7ClassNotFoundEx($path);
    return new $class($header);
  }
}
//
class HL7ClassNotFoundEx extends HL7Exception {}
