<?php
require_once 'InputFiles.php';
require_once 'OutputFiles.php';
//
class CsvImport {
  //
  static function exec() {
    $patient = PatientCsv::fetch();
    $insurance = InsuranceCsv::fetch();
    $clients = $patient->getClients();
    $fallouts = Client_Import::extractFallouts($clients);
    $insurance->addICardsTo($clients);
    OutputSql::create($clients)->save();
    InsuranceOnlySql::create($clients)->save();
    OutputFalloutsCsv::create($fallouts)->save();
  }
}
