<?php
require_once 'php/data/xml/_XmlRec.php';
//
/**
 * PQRI (Clinical Quality Measures)
 * @author Warren Hornsby
 */
class PQRI {
  //
  /**
   * @param CmsReport $report
   * @param User $user
   * @return PqriSubmission
   */
  static function from($report) {
    if (is_array($report))
      return static::fromGroup($report);
    $xml = submission::fromReport($report);
    return $xml;
  }
  /**
   * @param CmsReport[] $reports
   * @param User $user
   * @return PqriSubmission
   */
  static function fromGroup($reports) {
    $xml = submission::fromGroup($reports);
    return $xml;
  }
  //
  static function asDate($date) {
    return date('m-d-Y', strtotime($date));
  }
}
class submission extends XmlRec {
  public $_type = 'PQRI-REGISTRY';
  public $_option = 'PAYMENT';
  public $_version = '2.0';
  public $_xmlns_xsi = 'http://www.w3.org/2001/XMLSchema-instance';
  public $_xsi_noNamespaceSchemaLocation = 'Registry_Payment.xsd';
  public /*PqriFileAuditData*/ $file_audit_data;
  public /*PqriRegistry*/ $registry;
  public /*PqriMeasureGroup*/ $measure_group;
  //
  static function fromReport($report) {
    $me = new static();
    $me->file_audit_data = PqriFileAuditData::fromReport($report);
    $me->registry = PqriRegistry::asClicktate();
    $me->measure_group = PqriMeasureGroup::fromReport($report);
    return $me;
  }
  static function fromGroup($reports) {
    $me = new static();
    $me->file_audit_data = PqriFileAuditData::fromReport($reports[0]);
    $me->registry = PqriRegistry::asClicktate();
    $me->measure_group = PqriMeasureGroup::fromGroup($reports);
    return $me;
  }
}
class PqriFileAuditData extends XmlRec {
  public $create_date;
  public $create_time;
  public $create_by;
  public $version = '1.0';
  public $file_number;
  public $number_of_files;
  //
  static function fromReport($report) {
    return static::from($report->UserGroup);
  }
  static function from($UserGroup, $fileNumber = 1, $numberOfFiles = 1) {
    $me = new static();
    $me->create_date = date('m-d-Y');
    $me->create_time = date('H:i');
    $me->create_by = $UserGroup->name;
    $me->file_number = $fileNumber;
    $me->number_of_files = $numberOfFiles;
    return $me;
  }
}
class PqriRegistry extends XmlRec {
  public $registry_name = 'Clicktate';
  public $registry_id = '123456';
  public $submission_method;
  //
  static $SUBMISSION_METHODS = array(
    'A' => '12 months, 80%, 3 or more measures',
    'B' => '6 months, 80%, 3 or more measures',
    'C' => '12 months, 30 consecutive, measure group',
    'E' => '12 months, 80%, measure group',
    'F' => '6 months, 80%, measure group');
  //
  static function asClicktate($method = 'C') {
    $me = new static();
    $me->registry_name = 'Clicktate';
    $me->registry_id = '123456';  // TODO
    $me->submission_method = $method;
    return $me;
  }
}
class PqriMeasureGroup extends XmlRec {
  public $_ID;
  public /*PqriProvider*/ $provider;
  //
  static $IDS = array(
    'A' => 'Diabetes Melitis',
    'C' => 'CKD',
    'D' => 'Preventive Care',
    'E' => 'Perioperative Care',
    'F' => 'Rheumatoid Arthritis',
    'G' => 'Back Pain',
    'H' => 'CABG',
    'X' => 'Not Applicable'); 
  //
  static function fromReport($report) {
    $id = $report->measureGroup;
    $provider = PqriProvider::fromReport($report);
    return static::from($id, $provider);
  }
  static function fromGroup($reports) {
    $id = $reports[0]->measureGroup;
    $provider = PqriProvider::fromGroup($reports);
    return static::from($id, $provider);
  }
  static function from($id, $PqriProvider) {
    $me = new static();
    $me->_ID = $id;
    $me->provider = $PqriProvider;
    return $me;
  }
}
class PqriProvider extends XmlRec {
  public $npi;
  public $tin;
  public $waiver_signed;
  public $encounter_from_date;
  public $encounter_to_date;
  public /*PqriMeasure*/ $pqri_measure;
  //
  static function fromReport($report) {
    $measure = PqriMeasure::fromReport($report);
    return static::from($report->User, $measure, $report->from, $report->to);
  }
  static function fromGroup($reports) {
    $measure = PqriMeasure::fromGroup($reports);
    $report = $reports[0];
    return static::from($report->User, $measure, $report->from, $report->to);
  }
  static function from($User, $PqriMeasure, $from, $to) {
    $me = new static();
    $me->npi = $User->npi;
    $me->tin = $User->_tin;
    $me->waiver_signed = $User->_waiverSigned;
    $me->encounter_from_date = PQRI::asDate($from);
    $me->encounter_to_date = PQRI::asDate($to);
    $me->pqri_measure = $PqriMeasure;
    return $me;
  }
}
class PqriMeasure extends XmlRec {
  public $pqri_measure_number;
  public $eligible_instances;
  public $meets_performance_instances;
  public $performance_exclusion_instances;
  public $performance_not_met_instances;
  public $reporting_rate;
  public $performance_rate;
  //
  static function fromReport($report) {
    $score = $report->Scorecard; 
    $me = new static();
    $me->pqri_measure_number = $report->measureNumber;
    $me->eligible_instances = $score->countEligibles();
    $me->meets_performance_instances = $score->countMeets();
    $me->performance_exclusion_instances = $score->countExclusions();
    $me->performance_not_met_instances = $score->countNotMeets();
    $me->reporting_rate = CmsScorecard::REPORTING_RATE;
    $me->performance_rate = $score->getPerformanceRate();
    return $me;
  }
  static function fromGroup($reports) {
    $mes = array();
    foreach ($reports as $report) {
      if ($report->popNumber) 
        $report->measureNumber .= ' P' . $report->popNumber;
      if ($report->numNumber) 
        $report->measureNumber .= ' N' . $report->numNumber;
      $mes[] = static::fromReport($report);
    }
    return $mes;
  }
}
