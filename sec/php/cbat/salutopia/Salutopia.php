<?php
require_once 'php/data/LoginSession.php';
require_once 'php/data/rec/sql/_SalutopiaBatch.php';
require_once 'php/data/rec/sql/_AuditMruRec.php';
require_once 'php/data/curl/Curl.php';
require_once 'php/data/rec/sql/HL7_ClinicalDocuments.php';
//
class Salutopia {
  //
  static function exec() {
    $groups = SalutopiaBatch::fetchAll();
    blog('Group count=' . count($groups));
    foreach ($groups as $group) {
      $ugid = $group->userGroupId;
      LoginSession::loginBatch($ugid, __CLASS__);
      blog('1. Building CCDs');
      $ccds = static::buildCcds($ugid, $group->lastRun);
      blog('2. Sending CCDs');
      static::sendCcds($ccds);
      blog('3. Saving last run timestamp');
      SalutopiaBatch::save_asLastRun($group->userGroupId);
    }
  }
  protected static function sendCcds(/*ClinicalDoc[]*/$ccds) {
    foreach ($ccds as $ccd) {
      $xml = $ccd->toXml();
      blog($xml, 'xml');
      blog('Invoking web service...');
      $response = Curl_Salutopia::send($xml);
      if ($response->isStatusOk())
        blog('Status OK');
      else
        blog($response->body, 'BAD STATUS: ' . $response->status);
    }
  }
  static function /*ClinicalDoc[]*/buildCcds($ugid, $timestamp) {
    $ccds = array();
    $cids = AuditMru_Sal::fetchCids($ugid, $timestamp);
    foreach ($cids as $cid) {
      $ccd = HL7_ClinicalDocuments::buildFull($cid);
      $ccds[] = $ccd;
    }
    return $ccds;
  }
}
//
class AuditMru_Sal extends AuditMruRec {
  //
  public $userGroupId;
  public $clientId;
  public $date;
  public $action;
  //
  static function fetchCids($ugid, $timestamp) {
    $recs = static::fetchAll($ugid, $timestamp);
    $cids = array();
    foreach ($recs as $rec)
      $cids[] = $rec->clientId;
    return $cids;
  }
  static function fetchAll($ugid, $timestamp) {
    $c = new static();
    $c->userGroupId = $ugid;
    $c->date = CriteriaValue::greaterThan($timestamp);
    $recs = static::fetchAllBy($c);
    return $recs;
  }
}
class Curl_Salutopia extends Curl_Post {
  //
  static function send($xml) {
    $url = 'https://212.54.145.110:11009';
    $headers = static::getHeaders(strlen($xml));
     $me = static::create($url, $xml, $headers);
    return $me->exec();
  }
  //
  protected function getHeaders($len) {
    return array(
    	"Content-Type: text/xml",
      "Content-Length: $len");
  }
}