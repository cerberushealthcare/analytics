<?php
require_once 'config/MyEnv.php';
require_once 'php/data/rec/cryptastic.php';
//
/**
 * FtpFolder
 * @author Warren Hornsby
 */
class FtpFolder {
  //
  public /*Lab*/$Lab; 
  public $root;    
  //
  public function getPath($subdir) {  
    return "$this->root\\$subdir"; 
  }
  public function getPath_in() {
    return $this->getPath('in');
  }
  public function /*FtpFile[]*/getIncoming() {  
    return $this->getFiles('in');
  }
  public function moveToOut(/*FtpFile*/$file) {
    $file->encrypt();
    static::move($file, 'out');
  }
  public function saveToIn($msg, $filename = null/*auto-generate*/) {
    if ($filename == null) {
      $filename = static::guid() . ".hl7";
    }
    $filename = $this->getPath_in() . "\\" . $filename;
    file_put_contents($filename, MyCrypt_Auto::encrypt($msg));    
  }
  //  
  protected function getFiles($subdir) {
    $filenames = $this->getFilenames($subdir);
    $files = FtpFile::from($this, $subdir, $filenames);
    return $files;
  }
  protected function getFilenames($subdir) {
    $files = array();
    $dh = static::open($subdir);
    if ($dh) {
      while (false !== ($file = readdir($dh))) {
        if ($file != '.' && $file != '..') 
          $files[] = $file;
      }
    }
    return $files;
  }
  protected function open($subdir) {
    return opendir($this->getPath($subdir));
  }
  protected function move($file, $subdir) {
    $from = $file->getFilepath();
    $to = $file->getFilepath($this->getPath($subdir));
    rename($from, $to);
  }
  static function from(/*Lab*/$lab) {
    $me = new static();
    $me->Lab = $lab;
    $me->root = MyEnv::$SFTP_PATH . "\\$lab->sftpFolder";
    if (! is_dir($me->root))
      throw new FtpFolderException('Unable to access directory ' . $me->root);
    return $me;
  }
  protected static function guid() {
    return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
  }
  protected static function getRoot($lab) {
    $root = MyEnv::$SFTP_PATH . "\\$lab->sftpFolder";
    return $root;
  }
}
class FtpFile {
  //
  public $Lab;
  public $path;
  public $filename;
  public $msg;
  //
  public function readContents() {
    $contents = MyCrypt_Auto::decrypt(file_get_contents($this->getFilepath()));
    blog($contents, 'readcontents');
    return $contents;
  }
  public function encrypt() {
    $contents = file_get_contents($this->getFilepath());
    if (! MyCrypt_Auto::isEncrypted($contents))
      file_put_contents($this->getFilePath(), MyCrypt_Auto::encrypt($contents));
  }
  public function getFilepath($path = null) {
    if ($path == null)
      $path = $this->path;
    return "$path\\$this->filename";
  }
  //
  static function /*FptFile[]*/from($folder, $subdir, $filenames) {  
    $lab = $folder->Lab;
    $path = $folder->getPath($subdir);
    $mes = array();
    foreach ($filenames as $filename) 
      $mes[] = static::fromFilename($lab, $path, $filename);
    return $mes;
  }
  //
  protected static function fromFilename($lab, $path, $filename) {
    $me = new static();
    $me->Lab = $lab;
    $me->path = $path;
    $me->filename = $filename;
    $me->msg = $me->readContents();
    return $me;
  } 
}
//
class FtpFolderException extends Exception {
  //
}
