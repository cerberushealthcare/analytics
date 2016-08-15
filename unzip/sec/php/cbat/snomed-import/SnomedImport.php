<?php
require_once 'Snomed_Files.php';
require_once 'Snomed_Sql.php';
//
class SnomedImport {
  //
  static function exec() {
    $snos = SnomedFile::open();
    $snos = Snomed_I::out($snos);
    SnomedSql::write($snos);
    $icds = IcdFile::open();
    $icds = IcdSnomed_I::out($icds);
    IcdSql::write($icds);
  }
}
