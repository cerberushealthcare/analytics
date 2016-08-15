<?php
require_once 'InputFiles.php';
require_once 'OutputFiles.php';
//
class CsvImport {
  //
  static function exec() {
    $patient = PatientCsv::fetch();
    $clients = $patient->getClients();
    $fallouts = Client_Import::extractFallouts($clients);
    OutputSql::create($clients)->save();
    OutputFalloutsCsv::create($fallouts)->save();
  }
}
