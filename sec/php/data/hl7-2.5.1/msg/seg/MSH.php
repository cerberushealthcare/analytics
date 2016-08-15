<?php
require_once 'php/data/hl7-2.5.1/msg/seg/_HL7Segment.php';
//
/**
 * Message Header v2.5.1
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
  static function asClicktate() {
    $me = new static();
    $me->clear();
    $me->sendApp = HD::asClicktate();
    $me->processId = PT::PRODUCTION;
    $me->versionId = '2.5.1';
    $me->timestamp = TS::asNow();
    return $me;
  }
  static function create(/*CM_MsgType*/$msgType, $sendFacility, $rcvFacility, $msgControlId, $acceptAckType, $appAckType, $msgProfileId = null) {
    $me = new static();
    $me->clear();
    $me->sendApp = HD::asClicktate();
    $me->sendFacility = $sendFacility;
    $me->rcvFacility = $rcvFacility;
    $me->msgType = $msgType;
    $me->msgControlId = $msgControlId;
    $me->processId = PT::PRODUCTION;
    $me->versionId = '2.5.1';
    $me->timestamp = TS::asNow();
    $me->acceptAckType = $acceptAckType;
    $me->appAckType = $appAckType;
    $me->msgProfileId = $msgProfileId;
    return $me;
  }
  //
  public function __construct($fieldDelim = '|', $encodingChars = null) {
    $this->_fieldDelim = $fieldDelim;
    $this->encodingChars = ($encodingChars) ? $encodingChars : ST_EncodingChars::asStandard();
  }
  public function /*string*/getSource() {
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
  public function unencode($value) {
    if ($value) {
      $s = str_replace($this->compDelim, ' ', $value);
      $s = str_replace($this->repeatDelim, ' ', $s);
      $s = str_replace($this->escapeChar, ' ', $s);
      $s = str_replace($this->subDelim, ' ', $s);
      return $s;
    }
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
    static $me;
    if ($me == null) {
      $me = new static();
      $me->compDelim = '^';
      $me->repeatDelim = '~';
      $me->escapeChar = '\\';
      $me->subDelim = '&';
    }
    return $me;
  }
}
class CM_MsgType extends HL7CompValue {
  //
  public $code; /*e.g. 'ORU'*/
  public $trigger; /*e.g. 'R01'*/
  public $structure; /*e.g. 'ORU_R01'*/
  //
  static function create($code, $trigger, $structure) {
    $me = new static();
    $me->code = $code;
    $me->trigger = $trigger;
    $me->structure = $structure;
    return $me;
  }
}