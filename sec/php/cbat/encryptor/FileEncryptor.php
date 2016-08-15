<?php
require_once 'php/data/file/_File.php';
//
class FileEncryptor {
  //
  static $DIRS = array('faces', 'scan', 'session-images');
  //
  static function /*Folder_In[]*/getFolders($ugid) {
    $folders = Folder_In::all($ugid, static::$DIRS);
    return $folders;
  }
}
//
abstract class Folder {
  //
  public $root;
  //
  static function create($root) {
    $me = new static();
    $me->root = $root;
    return $me;
  }
  //
  public function /*string*/getPath() {
    return MyEnv::$BASE_PATH . "\\" . $this->root;
  }
  public function /*string[]*/getFilenames() {
    $dh = static::open();
    $files = array();
    if ($dh) {
      while (false !== ($file = readdir($dh))) {
        if ($file != '.' && $file != '..') 
          $files[] = $file;
      }
    }
    return $files;
  }
  //
  protected function /*resource*/open() {
    return @opendir($this->getPath());
  }
}
class Folder_In extends Folder {
  //
  static function /*Me[]*/all($ugid, /*string[]*/$dirs) {
    $us = array();
    foreach ($dirs as $dir) 
      $us[] = static::create($ugid, $dir);
    return $us;      
  }  
  static function /*Me*/create($ugid, /*string*/$dir) {
    $root = "user-folders\\G$ugid\\$dir";
    $me = parent::create($root);
    $me->subdir = $dir;
    return $me;
  }
  //
  public function /*File_In[]*/getFiles() {
    $filenames = $this->getFilenames();
    $files = File_In::all($this, $filenames);
    return $files;
  }
}
class File_In extends File implements AutoEncrypt {
  //
  static function /*Me[]*/all(/*Folder_In*/$folder, /*string[]*/$filenames) {
    $us = array();
    foreach ($filenames as $filename) 
      $us[] = static::from($folder, $filename);
    return $us;
  }
  //
  protected static function from($folder, $filename) {
    $me = new static();
    $me->setFilename($filename, $folder->getPath());
    $me->subdir = $folder->subdir;
    return $me;
  } 
}
class File_Out extends File implements AutoEncrypt {
  //
  static function copy(/*File_in*/$in) {
    $me = static::from($in);
    $content = $in->read()->getContent();
    $me->setContent($content)->save();
  }
  static function /*Me*/from(/*File_in*/$file) {
    $me = new static();
    $base = $me->getBasePath() . "\\" . $file->subdir;
    if (! is_dir($base))
      mkdir($base, 0, true);
    $me->setFilename($file->getFilename(), $base);
    return $me;
  }
}
