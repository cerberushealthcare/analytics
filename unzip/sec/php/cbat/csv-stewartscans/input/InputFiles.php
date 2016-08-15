<?php
require_once 'php/data/_BasicRec.php';
require_once 'php/data/file/_CsvFile.php';
//
class CsvRec_SH extends CsvRec {
  //
  public function set($fid, $value) {
    if ($value == 'NULL')
      $value = null;
    parent::set($fid, $value);
  } 
}
class ReportMap extends CsvRec_SH {
  //
  public $SocialSec;
  public $Patient_ID;
  public $Report_ID;
  public $rep_cat;
  public $date_time;
  public $report_name;
}
class ReportMapFile extends CsvFile {
  //
  static $FILENAME = 'reportmap.csv';
  static $CSVREC_CLASS = 'ReportMap';
  static $HAS_FID_ROW = true;
  //
}
