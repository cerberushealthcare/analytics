<?php
require_once 'php/data/rec/cryptastic.php';
require_once '_Upload.php';
//
/**
 * File accessors
 * @author Warren Hornsby
 */
abstract class FileSpec {
  //
  static $FILENAME;
  static $BASEPATH; /*leave null to default to same folder as implementing class*/
  static $MIME;
  //
  public $filename;
  public $basePath;
  public $mime;
  //
  public function setBasePath($path) {
    $this->basePath = $path;
    return $this;
  }
  public function setFilename($filename, $basePath = null) {
    $this->filename = $filename;
    if ($basePath)
      $this->setBasePath($basePath);
    return $this;
  }
  public function setMime($mime) {
    $this->mime = $mime;
    return $this;
  }
  public function getFullFilename() {
    return $this->getBasePath() . "\\" . $this->getFilename();
  }
  public function getBasePath() {
    return $this->basePath ?: static::getStaticBasePath();
  }
  public function getFilename() {
    return $this->filename ?: static::getStaticFilename();
  }
  public function getMime() {
    return $this->mime ?: static::getStaticMime($this->getFilename());
  }
  //
  protected static function getStaticFullFilename() {
    return static::getStaticBasePath() . "\\" . static::$FILENAME;
  }
  protected static function getStaticBasePath() {
    if (static::$BASEPATH)
      return static::$BASEPATH;
    $o = new ReflectionClass(get_called_class());
    return realpath(dirname($o->getFileName()));
  }
  protected static function getStaticFilename() {
    return static::$FILENAME;
  }
  protected static function getStaticMime($filename = null) {
    if ($filename == null)
      $filename = static::$FILENAME;
    if (static::$MIME)
      return static::$MIME;
    $ext = static::getExt($filename);
    $mime = geta(static::$EXT_TO_MIME, $ext);
    if ($mime == null)
      throw new UnknownFileType($filename);
    return $mime;
  }
  protected static function getExt($filename) {
    return strtolower(end(explode('.', $filename)));
  }
  protected static $EXT_TO_MIME = array(
    'ai'   => 'application/postscript',
    'asf'  => 'video/x-ms-asf',
    'asx'  => 'video/x-ms-asf',
    'avi'  => 'video/x-msvideo',
    'bmp'  => 'image/bmp',
    'doc'  => 'application/msword',
    'dvi'  => 'application/x-dvi',
    'eps'  => 'application/postscript',
    'gif'  => 'image/gif',
    'htm'  => 'text/html',
    'html' => 'text/html',
    'jpeg' => 'image/jpeg',
    'jpg'  => 'image/jpeg',
    'mov'  => 'video/quicktime',
    'mp2'  => 'audio/mpeg',
    'mp3'  => 'audio/mpeg',
    'mpe'  => 'video/mpeg',
    'mpeg' => 'video/mpeg',
    'mpg'  => 'video/mpeg',
    'mpga' => 'audio/mpeg',
    'pdf'  => 'application/pdf',
    'png'  => 'image/png',
    'ppt'  => 'application/vnd.ms-powerpoint',
    'ps'   => 'application/postscript',
    'qt'   => 'video/quicktime',
    'ras'  => 'image/x-cmu-raster',
    'rgb'  => 'image/x-rgb',
    'rm'   => 'audio/x-pn-realaudio',
    'rtf'  => 'text/rtf',
    'swf'  => 'application/x-shockwave-flash',
    'tif'  => 'image/tiff',
    'tiff' => 'image/tiff',
    'txt'  => 'text/plain',
    'wm'   => 'video/x-ms-wm',
    'wma'  => 'audio/x-ms-wma',
    'wmv'  => 'video/x-ms-wmv',
    'xls'  => 'application/vnd.ms-excel',
    'xml'  => 'text/xml',
    'zip'  => 'application/zip');
}
//
/**
 * Generic file  
 */
abstract class File extends FileSpec {
  //
  protected /*string*/$content;  
  //
  static function fetch($filename = null) {
    $me = new static();
    $me->read($filename);
    return $me;
  }
  static function fromUpload(/*Upload*/$up) {
    $filename = static::getStaticFullFilename();
    $up->save($filename);
    $me = new static();
    if ($me instanceof AutoEncrypt) 
      $this->read()->save();
    return /*File*/$me; 
  }
  //
  public function getContent() {
    return /*string*/$this->content;
  }
  public function setContent(/*string*/$content) {
    $this->content = $content;
    return $this;
  }
  public function read($filename = null) {
    if ($filename)
      $this->setFilename($filename);
    $content = $this->file_get_contents();
    if ($this instanceof AutoEncrypt)
      $content = MyCrypt_Auto::decrypt($content);
    $this->setContent($content);
    return $this;
  }
  public function save($filename = null) {
    if ($filename)
      $this->setFilename($filename);
    $content = $this->getContent();
    if ($this instanceof AutoEncrypt)
      $content = MyCrypt_Auto::encrypt($content);
    $this->file_put_contents($content);
    return $this;
  }
  public function output($filename = null) {
    if ($filename)
      $this->setFilename($filename);
    static::header($this->getMime());
    echo $this->getContent();
  }
  public function download($filename = null) {
    if ($filename)
      $this->setFilename($filename);
    $filename = $this->getFilename();
    static::header($this->getMime());
    header("Content-Disposition: attachment; filename=$filename");
    echo $this->getContent();
  }
  //
  protected static function header($mime = null) {
    if ($mime == null)
      $mime = static::getStaticMime();
    ob_clean();
    header("Pragma: ");
    header("Cache-Control: ");
    header("Content-Type: $mime"); 
  }
  protected function fopen_asRead() {
    return $this->fopen('rb');
  }
  protected function fopen_asWrite() {
    return $this->fopen('wb');
  }
  protected function fopen($mode) {
    $filename = $this->getFullFilename();
    if (($handle = fopen($filename, $mode, true)) == false)
      throw new FileCannotOpen($filename);
    return $handle;
  }
  protected function file_get_contents() {
    $filename = $this->getFullFilename();
	$content = file_get_contents($filename, FILE_USE_INCLUDE_PATH);
	Logger::debug('php/data/file/_File.php: Content is ' . gettype($content) . ' ' . $content);
    if ($content == false)
      throw new FileCannotOpen($filename);
    return /*string*/$content;
  } 
  protected function file_put_contents($content) {
    $filename = $this->getFullFilename();
    $result = file_put_contents($filename, $content);
  } 
}
/**
 * Text file composed of lines 
 */
abstract class TextFile extends File {
  //
  protected /*string[]*/$lines;
  //
  static function create($lines = null) {
    $me = new static();
    if ($lines)
      $me->setLines($lines);
    return $me;
  }
  //
  public function getLines() {
    return $this->lines;
  }
  public function setLines(/*string[]*/$lines) {
    $this->lines = $lines;
    return $this;
  }
  public function getContent() {
    $lines = $this->getLines();
    $content = empty($lines) ? null : implode("\n", $lines); 
    return $content;
  }
  public function setContent(/*string*/$content) {
    $lines = explode("\r\n", $content);
    $this->setLines($lines);
    return $this;
  }
  public function save() {
    $content = $this->getContent();
    if ($this instanceof AutoEncrypt)
      $content = MyCrypt_Auto::encrypt($content);
    $handle = $this->fopen_asWrite();
    fwrite($handle, $content);
    fclose($handle);
    return $this;
  }
  //
  protected function fopen_asRead() {
    return $this->fopen('r');
  }
  protected function fopen_asWrite() {
    return $this->fopen('w');
  }
}
/**
 * Text file composed of records (objects)
 */
abstract class RecFile extends TextFile {
  //
  static $REC_CLASS/*'MyRec'*/; 
  //
  protected /*Rec[]*/$recs; 
  //
  public function getRecs() {
    return $this->recs;
  }
  public function load(/*Rec[]*/$recs) {
    $this->recs = $recs;
    return $this;
  }
  public function add(/*Rec*/$rec) {
    if ($this->recs == null)
      $this->recs = array();
    $this->recs[] = $rec;
    return $this;
  }
  public function getLines() {
    $lines = array();
    foreach ($this->recs as $rec) 
      $lines[] = $rec->toString(); /*to save(), must implement $rec->toString()*/
    return $lines;
  }
  public function setLines($lines) {
    $class = static::$REC_CLASS;
    $recs = array();
    foreach ($lines as $line)
      $recs[] = $class::fromString($line); /*to read(), must implement MyRec::fromString($s)*/
    $this->load($recs);
  }
}
//
class FileException extends Exception {}
class FileCannotOpen extends FileException {}
class UnknownFileType extends FileException {}