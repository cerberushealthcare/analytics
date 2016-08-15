<?php
require_once 'php/data/file/_CsvFile.php';
//
/**
 * select distinct client_id
 * from sessions
 * where user_group_id=3 and closed_by=2 and date_closed>='2010-01-01'
 * order by client_id;
 */
class PatientCsv extends CsvFile {
  //
  static $FILENAME = 'input-patient.csv';
  static $REC_CLASS = 'PatientRec';  
  static $HAS_FID_ROW = true;
  //
  public function getCids() {
    $cids = array();
    foreach ($this->recs as $rec)
      $cids[] = $rec->cid;
    return $cids;
  }
}
class PatientRec extends CsvRec {
  //
  public $cid;
}
