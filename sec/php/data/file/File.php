<?php
//
abstract class FileSpec {
  //
  static $FILENAME;
  static $BASEPATH;  
  //
  public function setFilename($filename) {
    static::$FILENAME = $filename;
    return $this;
  }
  public function setBasePath($path) {
    static::$BASEPATH = $path;
    return $this;
  }
  protected static function getFilename() {
    return static::getBasePath() . "\\" . static::$FILENAME;
  }
  protected static function getBasePath() {
    if (static::$BASEPATH)
      return static::$BASEPATH;
    $o = new ReflectionClass(get_called_class());
    return realpath(dirname($o->getFileName()));
  }
}
abstract class File extends FileSpec {
  //
  static $FILENAME;
  static $BASEPATH;  // leave null to default to same folder as class
  //
  public /*string[]*/$recs;
  //
  public function save($lines = null) {
    if ($lines == null)
      $lines = $this->recs;
    $handle = static::fopen_asWrite();
    foreach ($lines as $line) 
      fwrite($handle, $line . "\n");
    fclose($handle);
    return $this;
  }
  public function download($mime, /*string[]*/$lines = null) {
    if ($lines == null)
      $lines = $this->recs;
    $content = implode("\n", $lines);
    $filename = static::$FILENAME;
    ob_clean();
    header("Pragma: ");
    header("Cache-Control: ");
    header("Content-Type: $mime"); 
    header("Content-Disposition: attachment; filename=$filename");
    echo $content;
  }
  public function getRecs() {
    return $this->recs;
  }
  public function load($recs) {
    $this->recs = $recs;
    return $this;
  }
  //
  static function create($recs = null) {
    $me = new static();
    $me->load($recs);
    return $me;
  }
  static function fetch() {
    $content = static::file_get_contents();
    $recs = explode("\n", $content);
    return static::create($recs);
  }
  //
  protected static function fopen_asRead() {
    return static::fopen('r');
  }
  protected static function fopen_asWrite() {
    return static::fopen('w');
  }
  protected static function fopen($mode) {
    $filename = static::getFilename();
    if (($handle = fopen($filename, $mode, true)) == false)
      throw new FileCannotOpen($filename);
    return $handle;
  }
  protected static function file_get_contents() {
    $filename = static::getFilename();
    if (($content = file_get_contents($filename, FILE_USE_INCLUDE_PATH)) == false)
      throw new FileCannotOpen($filename);
    return $content;
  }
}
abstract class RecFile extends File {
  //
  public /*SqlRec[]*/$recs;
  //
  public function save() {
    $lines = $this->toStrings();
    return parent::save($lines);
  }
  public function download($mime) {
    $lines = $this->toStrings();
    parent::download($mime, $lines);
  }
  public function toStrings() {
    $lines = array();
    foreach ($this->recs as $rec) {
      $line = $rec->toString();
      $lines[] = $line;
      blog($line);
    }
    return $lines;
  }
}
//
class FileException extends Exception {}
class FileCannotOpen extends FileException {}
