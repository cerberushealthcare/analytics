<?php
require_once 'php/data/LoginSession.php';
require_once 'input/InputFiles.php';
require_once 'OutputFiles.php';
require_once 'Import_Sql.php';
//
class Import {
  //
  static $UGID = 1;  // 3022;
  //
  static function exec() {
    $outrecs = array();
    LoginSession::loginBatch(static::$UGID, __CLASS__);
    $ssnmap = Client_Import::fetchSsnMap();
    $file = ReportMapFile::fetch();
    $csvs = $file->getRecs();
    $siid = ScanIndex_Import::getLastId();
    //
    foreach ($csvs as $csv) {
      $cid = geta($ssnmap, $csv->SocialSec);
      if ($cid) {
        $siid++;
        $outrecs[] = ScanIndex_Import::from($csv, $cid, $siid);
        $outrecs[] = ScanFile_Import::from($csv, $siid);
      }
    }
    OutputSql::create($outrecs)->save();
  }
}
