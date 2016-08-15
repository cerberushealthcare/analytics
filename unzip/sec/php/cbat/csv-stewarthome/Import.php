<?php
require_once 'php/data/LoginSession.php';
require_once 'input/InputFiles.php';
require_once 'OutputFiles.php';
require_once 'Import_Sql.php';
//
class Import {
  //
  static $UGID = 3022;
  //
  static function exec() {
    LoginSession::loginBatch(static::$UGID, __CLASS__);
    $patients = ActivePatient::all();
    $clients = ActivePatient::asClients($patients, static::$UGID);
    $fallouts = Client_Import::extractFallouts($clients);
    OutputSql::create($clients)->save();
    exit;
  }
}
