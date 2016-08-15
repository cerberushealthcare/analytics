<?php
require_once 'php/data/LoginSession.php';
require_once 'php/cbat/encryptor/SqlEncryptor.php';
require_once 'php/cbat/encryptor/FileEncryptor.php';
//
class Encrypt {
  //
  static $UGID = 1;
  //
  static function exec() {
    LoginSession::loginBatch(static::$UGID, __CLASS__);
    blog('-- SQL --');
    //static::execSql();
    blog('-- USER FILES --');
    static::execFiles();
    blog('-- COMPLETE --');
  }
  //
  protected static function execSql() {
    $sets = SqlEncryptor::getAllSets(static::$UGID);
    blog('SQL set count=' . count($sets));
    foreach ($sets as &$set) {
      blog('Saving ' . $set->name);
      MySqlFile::from($set)->save();
      $set = null;
    }
  }
  protected static function execFiles() {
    $folders = FileEncryptor::getFolders(static::$UGID);
    blog('Folders count=' . count($folders));
    foreach ($folders as $folder) {
      $files = $folder->getFiles();
      blog('Folder ' . $folder->subdir . ' file count=' . count($files));
      if (! empty($files)) {
        blog('Encrypting...');
        foreach ($files as &$file) {
          MyUserFile::copy($file);
          $file = null;
        }
      }
    }
  }
}
//
class MySqlFile extends SqlFile_E {}
class MyUserFile extends File_Out {}
