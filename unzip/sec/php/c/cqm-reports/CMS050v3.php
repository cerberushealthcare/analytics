<?php
require_once 'CqmReports_Sql.php';
//
class CMS050v3 extends CqmReport {
  //
  static $CQM = "CMS050v3";
  static $ID = "40280381-4555-e1c1-0145-9002b50a2963";
  static $SETID = "f58fc0d6-edf5-416a-8d29-79afbfd24dea";
  static $NQF = "N/A";
  static $VERSION = "3";
  static $TITLE = "Closing the Referral Loop: Receipt of Specialist Report";
  static $POPCLASSES = array('CMS050v3_Pop');
}
class CMS050v3_Pop extends CqmPop {
  //
  static $IPP = "E130E522-895A-470A-AA6F-5385772F5F9E";
  static $DENOM = "D5920A27-7752-4D10-8492-835CFC81764E";
  static $DENEX = null;
  static $NUMER = "593ec589-78d3-4bb2-9a91-0836214eab39";
  static $DENEXCEP = null;
  //
  protected function getIpp($ugid, $from, $to, $uid) {
    return Client050_Ipp::fetchAll($ugid, $from, $to, $uid);
  }
  protected function getNumer($ugid, $from, $to, $uid) {
    return Client050_Numer::fetchAll($ugid, $from, $to, $uid);
  }
}
class Client050_Ipp extends Client_Cqm {
  //
  static function asCriteria($ugid, $from, $to, $uid) {
    $c = static::from($ugid);
    $c->Referrals = CriteriaJoin::requiresAsArray(Proc050::asReferral($from, $to, $uid)); 
    $c->Encounter = CriteriaJoin::requires(Proc050::asEncounter($from, $to, $uid));
    return $c;
  }
}
class Client050_Numer extends Client050_Ipp {
  //
  static function asCriteria($ugid, $from, $to, $uid) {
    $c = parent::asCriteria($ugid, $from, $to, $uid);
    $c->Reports = CriteriaJoin::requiresAsArray(Proc050::asConsultReport($from, $to, $uid));
    return $c;
  }
  static function filter($recs) {
    return array_filter($recs, function($rec) {
      return $rec->hasReportAfterReferral();
    });
  }
  //
  public function hasReportAfterReferral() {
    foreach ($this->Referrals as $refer) {
      foreach ($this->Reports as $report) {
        if ($report->date > $refer->date)
          return true;
      }
    }
  }
}
class Proc050 extends Proc_Cqm {
  //
  static function asReferral($from, $to, $uid) {
    return static::from($from, $to, $uid)->ipc('602347');
  }
  static function asConsultReport($from, $to, $uid) {
    return static::from($from, $to, $uid)->ipc('602329');
  }
}
