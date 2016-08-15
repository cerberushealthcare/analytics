<?php
require_once 'php/data/LoginSession.php';
require_once 'HdCreator_ClientProc.php';
require_once 'HdCreator_OtherSql.php';
//
class HdCreator {
  //
  static function execAll() {
    $ugids = LoginSession::getActiveUgids();
    $bats = array();
    foreach ($ugids as $i => $ugid) {
      becho($i + 1 . " of " . count($ugids));
      //$bats[] = static::exec($ugid);
      $bats[] = static::execMedsOnly($ugid);
    }
    RunAllBat::create($bats);
  }
  static function execMedsOnly($ugid) {
    becho("exec $ugid");
    LoginSession::loginBatch($ugid, __CLASS__);
    $fmeds = MySqlFile::saveAll(SqlSet::fetchAll('Med_Hdc', $ugid));
    $filenames = $fmeds;
    becho("finished $ugid");
    $bat = MySqlBatFile::create($ugid, $filenames);
    return $bat->filename;
  }
  static function exec($ugid) {
    becho("exec $ugid");
    LoginSession::loginBatch($ugid, __CLASS__);
    $fclients = MySqlFile::saveAll(SqlSet::fetchAll('Client_Hdc', $ugid));
    $fprocs = MySqlFile::saveAll(SqlSet::fetchAll('Proc_Hdc', $ugid));
    $fvitals = MySqlFile::saveAll(SqlSet::fetchAll('Vitals_Hdc', $ugid));
    $fmeds = MySqlFile::saveAll(SqlSet::fetchAll('Med_Hdc', $ugid));
    $fdiagnoses = MySqlFile::saveAll(SqlSet::fetchAll('Diagnosis_Hdc', $ugid));
    $fimmuns = MySqlFile::saveAll(SqlSet::fetchAll('Immun_Hdc', $ugid));
    $fsessions = MySqlFile::saveAll(SqlSet::fetchAll('Session_Hdc', $ugid));
    $fscheds = MySqlFile::saveAll(SqlSet::fetchAll('Sched_Hdc', $ugid));
    $ftracks = MySqlFile::saveAll(SqlSet::fetchAll('Track_Hdc', $ugid));
    $filenames = array_merge($fclients, $fprocs, $fvitals, $fmeds, $fdiagnoses, $fimmuns, $fsessions, $fscheds, $ftracks);
    becho("finished $ugid");
    $bat = MySqlBatFile::create($ugid, $filenames);
    return $bat->filename;
  }
}
//
class MySqlFile extends SqlOutputFile {}
class MySqlBatFile extends SqlBatFile {}
class RunAllBat extends TextFile {
  //
  static $FILENAME = 'run_all.bat';
  //
  static function create($filenames) {
    $lines = array("echo == RUN_ALL: Executes SQL statements for all groups!", "pause");
    foreach ($filenames as $file) {
      $lines[] = "CALL $file";
    }
    $lines[] = "pause";
    $me = parent::create($lines);
    return $me->save();
  }
}
