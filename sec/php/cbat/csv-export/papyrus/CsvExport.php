<?php
require_once 'php/data/LoginSession.php';
require_once 'Export_Sql.php';
require_once 'Export_Files.php';
//
/**
 * CSV Export of Clinical Data
 * @author Warren Hornsby
 */
class CsvExport {
  //
  static $UGID = 1;
  //
  static function exec() {
    LoginSession::loginBatch(static::$UGID, __CLASS__);
    $ugid = static::$UGID;
    $statuses = ApptStatuses::create(); 
    static::exportPatients($ugid);
    static::exportAppts($ugid, $statuses);
  }
  //
  protected static function exportPatients($ugid) {
    $csvPatients = PatientsCsv::create();
    $clients = Client_Export::fetchAll($ugid);
    foreach ($clients as $client) 
      $csvPatients->add($client);
    $clients = null;
    $csvPatients->save();
  }
  protected static function exportAppts($ugid, $statuses) {
    $csvAppts = ApptsCsv::create();
    $scheds = Sched_Export::fetchAll($ugid);
    $csvAppts->load($scheds, $statuses);
    $csvAppts->save();  
  }
}
//
class ApptStatuses extends BasicRec {
  //
  public /*int[]*/$arrived;
  public /*int[]*/$rescheduled;
  public /*int[]*/$cancelled;
  //
  public function getStatusCode($status) {
    if (in_array($status, $this->arrived))
      return '02';
    if (in_array($status, $this->rescheduled))
      return '03';
    if (in_array($status, $this->cancelled))
      return '66666';
    else
      return '01'/*SCHEDULED*/;
  }
  //
  static function create($arrived = null, $rescheduled = null, $cancelled = null) {
    if ($arrived == null)
      $arrived = array('1','2','4','12','33');
    if ($rescheduled == null)
      $rescheduled = array('5','22','23','30');
    if ($cancelled == null)
      $cancelled = array('9','10','20','21','24','31');
    return new static($arrived, $rescheduled, $cancelled);
  } 
}