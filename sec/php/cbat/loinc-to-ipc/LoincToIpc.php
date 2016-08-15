<?php
require_once 'LoincToIpc_Sql.php';
require_once 'php/data/file/_File.php';
//
/**
 * Lab IPC loader
 * @author Warren Hornsby
 */
class LoincToIpc {
  //
  static function exec() {
    $recs = Loinc::fetchAll();
    $xref = LoincToCpt::fetchAll();
    Loinc::xref($recs, $xref);
    IpcSqlFile::write(Ipc_Export::out($recs));
  }    
}


abstract class SqlFile extends TextFile {
  //
  static function write($recs) {
    $me = static::create($recs);
    $me->save();
  }
}
class IpcSqlFile extends SqlFile {
  //
  static $FILENAME = 'IPC_FROM_LOINC.sql';
}
