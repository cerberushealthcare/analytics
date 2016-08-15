<?php
require_once 'php/data/hl7/msg/seg/_HL7Segment.php';
//
/**
 * Message Header
 * @author Warren Hornsby	
 */
class MSH extends HL7Segment {
  //
  public $segId = 'MSH';
  public $encodingChars = 'ST_EncodingChars';  // Encoding Characters (ST)
  public $sendApp = 'HD';  // Sending Application (HD) 
  public $sendFacility = 'HD';  // Sending Facility (HD)
  public $rcvApp = 'HD';  // Receiving Application (HD)
  public $rcvFacility = 'HD';  // Receiving Facility (HD)
  public $timestamp = 'TS';  // Date/Time Of Message (TS)
  public $security;  // Security (ST)
  public $msgType = 'CM_MsgType';  // Message Type (MSG)   
  public $msgControlId;  // Message Control ID (ST)
  public $processId = 'PT';  // Processing ID (PT)
  public $versionId;  // Version ID (VID)
  public $seq;  // Sequence Number (NM)
  public $continuation;  // Continuation Pointer (ST)
  public $acceptAckType;  // Accept Acknowledgment Type (ID)
  public $appAckType;  // Application Acknowledgment Type (ID)  
  public $countryCode;  // Country Code (ID)
  public $charSet;  // Character Set (ID)
  public $primaryLanguage = 'CE';  // Principal Language Of Message (CE)
  public $altCharSet;  // Alternate Character Set Handling Scheme (ID)
  public $msgProfileId;  // Message Profile Identifier (EI)
  //
  public $_fieldDelim;
  //
  /**
   * @param string $fieldDelim (optional)
   * @param ST_EncodingChars (optional)
   */
  public function __construct($fieldDelim = '|', $encodingChars = null) {
    $this->_fieldDelim = $fieldDelim;
    $this->encodingChars = ($encodingChars) ? $encodingChars : ST_EncodingChars::asStandard();
  }
  /**
   * @return string
   */ 
  public function getSource() {
    $a = array();
    if ($this->sendApp)
      $a[] = $this->sendApp->namespaceId;
    if ($this->sendFacility)
      $a[] = $this->sendFacility->namespaceId;
    if (count($a) == 0) 
      $a[] = '(None)';
    return implode(' - ', $a);
  }
  //
  /**
   * @param CM_MsgType $type
   * @param UserGroup $ug
   * @param string $msgControlId (optional)
   * @param PT $processId (optional, default as production);
   * @return MSH
   */
  static function asSendable($type, $ug, $msgControlId = null, $processId = null) {
    $me = new self();
    $me->clear();
    $me->msgType = $type;
    $me->msgControlId = $msgControlId;
    $me->processId = ($processId) ? $processId : PT::PRODUCTION;
    $me->versionId = '2.3.1';
    $me->timestamp = TS::fromNow();
    $me->sendApp = HD::asClicktate();
    $me->sendFacility = HD::asPractice($ug);
    return $me;
  }
  /**
   * @param HL7Buffer $buffer
   * @return MSH
   */
  static function fromBuffer($buffer) {
    $rec = $buffer->head();
    $me = static::fromHL7Rec($rec);
    $buffer->setDelims($me);
    $fields = $buffer->split($rec);
    array_splice($fields, 1, 1);
    $me->setValues($fields, $me->encodingChars);
    return $me;
  }
  //
  protected function clear() {
    foreach ($this as $fid => $value)
      if ($this->isFid($fid) && $fid != 'segId' && $fid != 'encodingChars') 
        $this->$fid = null;
  }
  protected static function fromHL7Rec($rec) {
    $fieldDelim = substr($rec, 3, 1);
    $encoding = ST_EncodingChars::from(substr($rec, 4, 4));
    $me = new static($fieldDelim, $encoding);
    return $me;
  }
  protected static function isEmptyable($fid) {
    return $fid != 'segId' && $fid != 'encodingChars';
  }
  protected static function makeMsgControlId($fs) {
    return $fs->UserGroup->userGroupId . "-" . $fs->cid . "-" . date("YmdHis");
  }
}
//
class ST_EncodingChars extends HL7Value {
  //
  public $compDelim;
  public $repeatDelim;
  public $escapeChar;
  public $subDelim;
  //
  public function nextDelim($delim) {
    switch ($delim) {
      case $this->compDelim:
        return $this->subDelim;
      default:
        return $this->compDelim;
    }
  }
  public function getValue() {
    return $this->compDelim . $this->repeatDelim . $this->escapeChar . $this->subDelim;
  }
  //
  static function from($value) {
    $me = new static();
    $me->compDelim = substr($value, 0, 1);
    $me->repeatDelim = substr($value, 1, 1);
    $me->escapeChar = substr($value, 2, 1);
    $me->subDelim = substr($value, 3, 1);
    return $me;
  }
  static function asStandard() {
    $me = new static();
    $me->compDelim = '^';
    $me->repeatDelim = '~';
    $me->escapeChar = '\\';
    $me->subDelim = '&';
    return $me;
  }
}
class CM_MsgType extends HL7CompValue {
  //
  public $type;
  public $trigger;
}