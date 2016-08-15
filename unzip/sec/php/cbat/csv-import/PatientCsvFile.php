<?php
require_once 'php/data/_BasicRec.php';
require_once 'php/data/file/_CsvFile.php';
//
abstract class PatientCsvFile extends CsvFile {
  //
  static $FILENAME;
  static $BASEPATH;      
  static $REC_CLASS;  
  static $HAS_FID_ROW;   
  static $UGID;
  static $FIRST_UID_COUNT = 1;  /* for defaulting UID; set to current patient count + 1 */
  // 
  public function /*Client[]*/getClients() {
    $clients = array();
    $ct = static::$FIRST_UID_COUNT;
    foreach ($this->recs as $rec) 
      $clients[] = $rec->asClient(static::$UGID, $ct++);
    return $clients;
  }
}
abstract class PatientCsvRec extends CsvRec {
  //
  abstract public function asClient($ugid, $ct);
}