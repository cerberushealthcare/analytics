<?php
require_once 'lib/file/CsvFile.php';
//
class Chord_In extends CsvFile {
  //
  static $FILENAME = 'chords.csv';
  static $CSVREC_CLASS = 'InRec';
  //
  static function /*InRec[]*/fetch() {
    $me = new static();
    $me->read();
    return $me->recs;
  }
}
class InRec extends CsvRec {
  //
  public $code;
  public $name;
  //
  public function set($fid, $value) {
    if ($fid == 'code')
      $value = substr($value, 1, -1);
    parent::set($fid, $value);
  }
}
class Chord_Out extends CsvFile {
  //
  static $FILENAME = 'chords_out.csv';
  static $CSVREC_CLASS = 'OutRec';
  //
  static function /*OutRec[]*/create(/*InRec[]*/$ins) {
    $me = new static();
    $me->load(OutRec::all($ins));
    $me->save();
    return $me->recs;
  }
}
class OutRec extends CsvRec {
  //
  public $code;
  public $root;
  public $minor;
  public $mod1;
  public $mod2;
  public $slash;
  //
  static $NOTES = array(
    'A#' => 'AB',
    'Bb' => 'AB',
    'C#' => 'CD',
    'Db' => 'CD',
    'D#' => 'DE',
    'Eb' => 'DE',
    'F#' => 'FG',
    'Gb' => 'FG',
    'G#' => 'GA',
    'Ab' => 'GA',
    'A' => 'A',
    'B' => 'B',
    'C' => 'C',
    'D' => 'D',
    'E' => 'E',
    'F' => 'F',
    'G' => 'G');
  static $MINOR = array('m');
  static $MOD1 = array('aug','dim','sus4','sus2','5','6','7','maj7','9','maj9','add9','11','13','maj13');
  static $MOD2 = array('#11#9','#11','#9sus4','#9','7#9','7','/9','9','b5','b9','sus2','sus4');
  static $SLASH = array('/');
  //
  static function all(/*InRec[]*/$ins) {
    $us = array();
    $last = null;
    foreach ($ins as $in) {
      $me = static::from($in, $last);
      if ($me) {
        $us[] = $me;
        $last = $me;
      }
    }
    return $us;
  }
  protected static function from($in, $last) {
    $me = new static();
    $me->code = strtoupper($in->code);
    $me->setFrom($in->name);
    if ($last) {
      if ($me->getKey() == $last->getKey())
        $me = null;
    }
    return $me;
  }
  //
  public function setFrom($name) {
    $this->setRoot($name);
    $this->setMinor($name);
    $this->setMod1($name);
    $this->setMod2($name);
    $this->setSlash($name);
  }
  public function getKey() {
    return implode('|', get_object_vars($this));
  }
  public function getChord() {
    return implode('', array_slice(get_object_vars($this), 1));
  }
  //
  protected function setRoot(&$name) {
    $this->root = $this->extractNote($name);
  }
  protected function setMinor(&$name) {
    if (substr($name, 0, 3) == 'maj')
      return;
    $this->minor = $this->extract($name, static::$MINOR);
  }
  protected function setMod1(&$name) {
    $this->mod1 = $this->extract($name, static::$MOD1);
  }
  protected function setMod2(&$name) {
    $this->mod2 = $this->extract($name, static::$MOD2);
  }
  protected function setSlash(&$name) {
    $slash = $this->extract($name, static::$SLASH);
    if ($slash)
      $this->slash = "/" . $this->extractNote($name);
  }
  protected function extractNote(&$name) {
    $note = $this->extract($name, array_keys(static::$NOTES));
    if ($note)
      return static::$NOTES[$note];
  }
  protected function extract(&$name, $values) {
    foreach ($values as $value) {
      if (substr($name, 0, strlen($value)) == $value) {
        $name = substr($name, strlen($value));
        return $value;
      }
    }
  }
}
class Chord_Json extends TextFile {
  //
  static $FILENAME = 'chords.json';
  //
  static function create(/*OutRec[]*/$recs) {
    $me = new static();
    $a = array();
    foreach ($recs as $rec) 
      $a[] = static::getLine($rec);
    $me->setContent(json_encode($a));
    $me->save();
  }
  protected static function getLine($rec) {
    $a = array($rec->code, $rec->getChord());
    return $a;
  }
}
