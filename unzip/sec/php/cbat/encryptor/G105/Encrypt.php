<?php
require_once 'php/data/LoginSession.php';
require_once 'php/cbat/encryptor/Encryptor.php';
//
class Encrypt {
  //
  static $UGID = 3;
  //
  static function exec() {
    LoginSession::loginBatch(static::$UGID, __CLASS__);
    $sets = Encryptor::getAllSets(static::$UGID);
    foreach ($sets as $set)
      MySqlFile::from($set)->save();
  }
}
//
class MySqlFile extends SqlFile_E {}
