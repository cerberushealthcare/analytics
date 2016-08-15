<?php
require_once 'php/data/file/_CsvFile.php';
//
class CsvFile_Import extends CsvFile {
  //
  static function from(/*GroupFile*/$file) {
    $me = new static();
    static::$FILENAME = $file->filename;
    static::$BASEPATH = $file->folder->dir;
    $me->read();
    return $me;
  } 
}