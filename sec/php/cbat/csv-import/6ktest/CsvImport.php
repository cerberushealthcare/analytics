<?php
require_once 'InputFiles.php';
require_once 'OutputFiles.php';
//
class CsvImport {
  //
  static function exec() {
    $first_cid = 9796;
    $patient = PatientCsv::fetch();
    //$recs = $patient->getPsis($first_cid);
    $recs = $patient->getHdatas($first_cid);
    Output2Sql::create($recs)->save();
  }
  static function execSql() {
    $patient = PatientCsv::fetch();
    $clients = $patient->getClients();
    $fallouts = Client_Import::extractFallouts($clients);
    OutputSql::create($clients)->save();
    OutputFalloutsCsv::create($fallouts)->save();
  }
  static function execCreate() {
    PatientCsv::create();
  }
}
