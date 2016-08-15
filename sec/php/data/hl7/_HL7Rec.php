<?php
require_once 'php/data/rec/_Rec.php';
//
/**
 * HL7Rec
 * Properties composed of HL7Segments ($Upper), HL7Values ($lower), and helpers ($_underscore)   
 * Class of both is designated through initial value ('ClassName')
 * @author Warren Hornsby
 */
class HL7Rec {
  /*
  public $field1 = 'ClassName1'
   */
  //
  /** Events */
  protected function onload() {}
  //
  /**
   * Recursive getter
   * @param string $fid 'child.fid'
   * @return HL7Segment | Hl7Value | null
   */
  public function get($fid) {
    $value = getr($this, $fid);
    if ($value && self::isSegFid($fid) && is_scalar($value)) 
      $value = null;
    return $value;
  }
  /**
   * @return array(HL7Segment,..) non-null
   */
  public function getSegments() {
    $recs = array();
    foreach ($this as $fid => $value) {
      if (self::isSegFid($fid)) 
        if (is_object($value))
          $recs[] = $value;
        else if (is_array($value))
          $recs = array_merge($recs, $value);
    }
    return $recs;
  }
  /**
   * @return array(Hl7Value,string,..) 
   */
  public function getValues() {
    $values = array();
    foreach ($this as $fid => $value) {
      if (self::isFid($fid))
        $values[] = $value;
    }
    return $values;
  }
  /**
   * @return string
   */
  public function toJson() {
    $this->sanitize();
    return jsonencode($this);
  }
  /**
   * Remove nulls and helper props 
   */
  public function sanitize() {
    $hasValue = false;
    foreach ($this as $fid => &$value) {
      if ($value == null || self::isHelperFid($fid)) {
        unset($this->$fid);
      } else if (is_string($value)) {
        if (self::isSegFid($fid))
          unset($this->$fid);
        else
          $hasValue = true;
      } else {
        if ($value instanceof HL7Rec) {
          $value = $value->sanitize();
        } else if ($value instanceof Rec) {
          $value = $value->_toJsonObject();
        } else if (is_array($value)) {
          array_walk($value, function($e) {
            if ($e instanceof HL7Rec)
              $e->sanitize();
          });
          if (empty($value))
            $value = null; 
        }
        if ($value == null)
          unset($this->$fid);
        else 
          $hasValue = true;
      } 
    }
    return ($hasValue) ? $this : null;
  }
  //
  /**
   * @return static with all fields null'd
   */
  static function asEmpty() {
    $me = new static();
    foreach ($me as $fid => $value) 
      $me->$fid = null;
    return $me;
  }
  //
  protected function setSegments($buffer, $encoding) {
    $segIds = $this->getSegIds();
    $fids = $this->getSegFids();
    do {
      $segId = $buffer->currentSegId();
      $fid = geta($segIds, $segId);
      if ($fid) {
        $class = $fids[$fid];
        if (static::isArrayClass($class))
          $this->setSegmentArray($fid, $this->createSegment(substr($class, 0, -2), $buffer, $encoding));
        else
          $this->set($fid, $this->createSegment($class, $buffer, $encoding));
      }
    } while ($fid);
    foreach ($fids as $fid => $value) {
      if (is_string($this->$fid)) 
        $this->$fid = null; 
    }
  }
  protected function createSegment($class, $buffer, $encoding) {
    return $class::fromBuffer($buffer, $encoding);
  }
  protected function setValues($values, $encoding) {
    if (is_array($values)) {
      $fids = $this->getFids();
      foreach ($fids as $fid => $class) {
        $value = current($values);
        if ($class) {
          if (static::isArrayClass($class))
            $this->set($fid, $this->createValueObjects(substr($class, 0, -2), $value, $encoding));
          else
            $this->set($fid, $this->createValueObject($class, $value, $encoding));
        } else {
          if (strpos($value, $encoding->repeatDelim) !== false)
            $this->setArray($fid, explode($encoding->repeatDelim, $value));
          else
            $this->set($fid, $value);
        }
        next($values);
      }
      $this->onload();
    }
  }
  protected function createValueObjects($class, $value, $encoding) {
    $values = explode($encoding->repeatDelim, $value);
    foreach ($values as &$value)
      $value = static::createValueObject($class, $value, $encoding);
    return $values;
  }
  protected function createValueObject($class, $value, $encoding) {
    if (strpos($value, $encoding->repeatDelim) !== false)
      return $this->createValueObjects($class, $value, $encoding);
    else
      return $class::from($value, $encoding);
  }
  protected function set($fid, $value) {
    $current = $this->get($fid);
    if (is_string($value))
      $value = trim($value);
    if (is_array($current)) 
      array_push($this->$fid, $value);
    else if (is_object($current))
      $this->$fid = array($current, $value);
    else 
      $this->$fid = $value;
  }
  protected function setArray($fid, $values) {
    $current = $this->get($fid);
    if (is_array($current))
      $this->$fid = array_merge($current, $values); 
    else if (is_object($current))
      $this->$fid = array_merge(array($current), $values);
    else 
      $this->$fid = $values;
  }
  protected function setSegmentArray($fid, $value) {
    $current = $this->get($fid);
    if (is_array($current)) 
      array_push($this->$fid, $value);
    else 
      $this->$fid = array($value);
  }
  protected function getFids() {  // ['fid1'=>'Class',..]
    static $fids;
    if ($fids == null) 
      $fids = $this->makeFidArray(false);
    return $fids;
  }
  protected function getSegFids() {  // ['Fid1'=>'PID_Subtype',..]
    static $fids;
    if ($fids == null) 
      $fids = $this->makeFidArray(true);
    return $fids;
  }
  protected function getSegIds() {  // ['PID'=>'Fid1',..]
    static $segIds;
    if ($segIds == null) {
      $segIds = array();
      $fids = $this->getSegFids();
      foreach ($fids as $fid => $class) 
        if ($class)
          $segIds[substr($class, 0, 3)] = $fid;
    }
    return $segIds;
  }
  protected function makeFidArray($asSeg) {
    $vars = $this->getVars();
    $fids = array();
    foreach ($vars as $var => $class) {
      if ($class == null || is_string($class)) { 
        if ($asSeg) { 
          if (self::isSegFid($var)) 
            $fids[$var] = $class;
        } else { 
          if (self::isFid($var)) 
            $fids[$var] = $class;
        }
      }
    }
    return $fids;
  } 
  protected function getVars() {
    $vars = get_object_vars($this);
    return $vars;
  }
  protected static function isFid($var) {
    $c1 = substr($var, 0, 1);
    return $c1 != '_' && ! self::isUpper($c1);
  }
  protected static function isSegFid($var) {
    $c1 = substr($var, 0, 1);
    return $c1 != '_' && self::isUpper($c1);
  }
  protected static function isHelperFid($var) {
    $c1 = substr($var, 0, 1);
    return $c1 == '_';
  }
  protected static function isArrayClass($class) {
    return substr($class, -2) == '[]';
  }
  protected static function isUpper($c) {
    return ($c == strtoupper($c)); 
  }
}
/**
 * HL7 Messaage Buffer
 * Holds raw message data
 */
class HL7Buffer {
  //
  public $recs;
  public $delim = '|';
  public /*ST_EncodingChars*/ $encoding;
  //
  /**
   * @param MSH $header
   */
  public function setDelims($header) {
    $this->delim = $header->_fieldDelim;
    $this->encoding = $header->encodingChars;
  }
  /**
   * @return string 
   */
  public function current() {
    return current($this->recs);
  }
  /**
   * @return string 'OBR' (or null if EOF)
   */
  public function currentSegId() {
    $rec = $this->current();
    if ($rec)
      return substr($rec, 0, 3);
  }
  /**
   * @return string header record (shifted off)
   */
  public function head() {
    return array_shift($this->recs);
  }
  /**
   * @return string[] values of current record (and advance pointer)
   */
  public function pop() {
    $values = $this->split(current($this->recs));
    next($this->recs);
    return $values;
  }
  /**
   * @param string $rec
   * @return string[] values
   */
  public function split($rec) {
    return explode($this->delim ?: '|', $rec);
  }
  /**
   * @return string
   */
  public function toString() {
    return implode("\n", $this->recs);
  }
  //
  public function pushMsg($msg) {
    $segs = $msg->getSegments();
    $this->pushSegs($segs);
  }
  public function pushSegs($segs) {
    if ($segs) 
      foreach ($segs as $seg)
        $this->pushSeg($seg);
  }
  public function pushSeg($seg) {
    $this->pushValues($seg->getValues());
    $this->pushSegs($seg->getSegments());
  }
  public function pushValues($values) {
    if ($values) 
      $this->recs[] = $this->concatValues($values, $this->delim);
  }
  public function concatValues($values, $delim) {
    $a = array();
    foreach ($values as $value) {
      if (is_array($value))
        $a[] = $this->concatValues($value, $this->encoding->repeatDelim);
      else if ($value instanceof HL7CompValue)
        $a[] = $this->concatValues($value->getValues(), $this->encoding->nextDelim($delim)); 
      else if ($value instanceof HL7Value)
        $a[] = $value->getValue(); 
      else  
        $a[] = $value;
    }
    $e = end($a);
    while (empty($e) && count($a) > 0) {
      array_pop($a);
      $e = end($a);
    }
    return implode($delim, $a);
  }
  //
  static function fromHL7(/*string*/$data) {
    $recs = static::splitHL7($data);
    return static::fromHL7Recs($recs);
  } 
  static function fromHL7Recs(/*string[]*/$recs) {
    $me = new self();
    $me->recs = array();
    foreach ($recs as $rec) {
      $rec = trim($rec);
      if ($rec != '')
        $me->recs[] = $rec;
    }
    return $me;
  }
  static function fromMessage(/*HL7Message*/$msg) {
    $me = new self();
    $me->setDelims($msg->Header);
    $me->pushMsg($msg);
    return $me;
  }
  static function /*HL7Buffer[]*/fromFtpFile(/*FtpFile*/$file) {
    $recs = static::splitHL7($file->msg);
    $blocks = static::splitMsgBlocks($recs);
    $us = array();
    foreach ($blocks as $block)
      $us[] = static::fromHL7Recs($block);
    return $us;
  }
  //
  protected static function splitHL7($data) {
    return preg_split('/\n|\r\n?/', $data);
  }
  protected static function splitMsgBlocks($lines) {
    $blocks = array();
    $block = null;
    foreach ($lines as $line) {
      if (substr($line, 0, 3) == 'MSH') {
        if ($block)
          $blocks[] = $block;
        $block = array();
      }
      $block[] = $line;
    }
    if ($block)
      $blocks[] = $block;
    return $blocks;
  }
}
//
class HL7Exception extends Exception {}
