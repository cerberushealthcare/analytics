<?php
require_once 'php/data/LoginSession.php';
require_once 'php/cbat/decryptor/SqlDecryptor.php';
require_once 'php/cbat/decryptor/FileDecryptor.php';
//
class Decrypt {
  //
  static $UGID = 3;
  //
  static function exec() {
    LoginSession::loginBatch(static::$UGID, __CLASS__);
    blog('-- SQL --');
    static::execSql();
    blog('-- USER FILES --');
    static::execFiles();
    blog('-- COMPLETE --');
  }
  //
  protected static function execSql() {
    $sets = SqlDecryptor::getAllSets(static::$UGID);
    blog('SQL set count=' . count($sets));
    $filenames = array();
    foreach ($sets as &$set) {
      blog('Saving ' . $set->name);
      $file = MySqlFile::from($set)->save();
      $filenames[] = $file->filename;
      $set = null;
    }
    MySqlBatFile::create(static::$UGID, $filenames);
  }
  protected static function execFiles() {
    $folders = FileDecryptor::getFolders(static::$UGID);
    blog('Folders count=' . count($folders));
    foreach ($folders as $folder) {
      $files = $folder->getFiles();
      blog('Folder ' . $folder->subdir . ' file count=' . count($files));
      if (! empty($files)) {
        blog('Decrypting...');
        foreach ($files as &$file) {
          MyUserFile::copy($file);
          $file = null;
        }
      }
    }
  }
}
//
class MySqlFile extends SqlFile_D {}
class MyUserFile extends File_Out {}
class MySqlBatFile extends SqlBatFile {}
