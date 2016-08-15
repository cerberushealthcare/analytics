<?php
require_once 'php/data/file/_File.php';
//
/**
 * SFTP wrapper
 * @author Warren Hornsby 
 */
class Sftp {
  //
  static $HOST;
  static $PORT = 22;
  static $TIMEOUT = 10;
  //
  private /*Net_SFTP*/$ns;
  //
  /** Get file stubs in server directory */
  public function /*SfStub[]*/dir() {
    $list = $this->ns->rawlist();
    $stubs = SfStub::fromRawList($list);
    return $stubs;
  }
  /** Get server files with contents */
  public function /*SfFile[]*/getFiles(/*SfStub[]*/$stubs = null) {
    if ($stubs == null)
      $stubs = $this->dir();
    $files = array();
    foreach ($stubs as $stub) 
      if ($this->isDownload($stub)) 
        $files[] = SfFile::create($stub, $this->get($stub));
    return $files;
  }
  /** Delete server files */
  public function deleteFiles(/*SfFile[]*/$files) {
    if ($files) {
      foreach ($files as $file) 
        $this->delete($file->stub);
    }
  }
  /** Get contents of stub */
  public function /*string*/get(/*SfStub*/$stub) {
    $content = $this->ns->get($stub->name);
    return $content;
  }
  /** Delete server file by stub */
  public function delete(/*SfStub*/$stub) {
    if ($stub)
      $this->ns->delete($stub->name);
  }
  //
  protected function isDownload($stub) {
    return $stub->isFile();
  }
  //
  static function /*Sftp*/login($user, $pw) {
    $me = new static();
    set_include_path(dirname(__FILE__) . '/phpseclib');
    require_once 'Net/SFTP.php';
    $me->ns = new Net_SFTP(static::$HOST);
    if ($me->ns->login($user, $pw)) 
      return $me;
  }
}
class SfStub {
  //
  public $name; 
  public /*bytes*/$size;
  public /*timestamp*/$accessed;
  public /*timestamp*/$modified;
  public $perm;
  public $uid;
  public $gid;
  public $type;
  //
  const TYPE_UNK = 0;
  const TYPE_FILE = 1;
  const TYPE_DIR = 2;
  //
  public function isFile() {
    return $this->type == static::TYPE_FILE;
  }
  //
  static function /*SfStubs[]*/fromRawList($list) {
    $us = array();
    foreach ($list as $name => $args) 
      $us[] = static::from($name, $args);
    return $us;
  } 
  static function from($name, $a) {
    $me = new static();
    $me->name = $name;
    $me->size = $a['size'];
    $me->uid = $a['uid'];
    $me->gid = $a['gid'];
    $me->accessed = $a['atime'];
    $me->modified = $a['mtime'];
    $me->perm = $a['permissions'];
    $me->type = isset($a['type']) ? $a['type'] : 0;
    return $me;
  }
}
class SfFile extends File { // implements AutoEncrypt {
  //
  public /*SfStub*/$stub;
  //
  static function create(/*SfStub*/$stub, /*string*/$content) {
    $me = new static();
    $me->stub = $stub;
    $me->setContent($content);
    return $me;
  }
  static function saveAll(/*StfpFile[]*/$files, $basePath) {
    foreach ($files as $file) 
      $file->setFilename($file->stub->name, $basePath)->save();
  }
}