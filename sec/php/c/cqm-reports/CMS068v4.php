<?php
require_once 'CqmReports_Sql.php';
//
class CMS068v4 extends CqmReport {
  //
  static $CQM = "CMS068v4";
  static $ID = "40280381-4555-e1c1-0145-dc7dc26a44bf";
  static $SETID = "9a032d9c-3d9b-11e1-8634-00237d5bf174";
  static $NQF = "0419";
  static $VERSION = "4";
  static $TITLE = "Documentation of Current Medications in the Medical Record";
  static $POPCLASSES = array('CMS068v4_Pop');
}
class CMS068v4_Pop extends CqmPop {
  //
  static $IPP = "DC87C081-5272-4E9A-B10A-F607FCEB560B";
  static $DENOM = "169025B4-2133-44F9-9879-9014A2028C81";
  static $DENEX = null;
  static $NUMER = "14a01d7a-b936-4148-889f-c9b1a8b3d8a3";
  static $DENEXCEP = "6c8d1f46-3a00-4b7b-bafa-a2fe49499ae2";
    //
  protected function getIpp($ugid, $from, $to, $uid) {
    return Client068_Ipp::fetchAll($ugid, $from, $to, $uid);
  }
  protected function getNumer($ugid, $from, $to, $uid) {
    return Client068_Numer::fetchAll($ugid, $from, $to, $uid);
  }
  protected function getExcep($ugid, $from, $to, $uid) {
    return Client068_Excep::fetchAll($ugid, $from, $to, $uid);    
  }
  //
}
class Client068_Ipp extends Client_Cqm {
  //
  static function asCriteria($ugid, $from, $to, $uid) {
    $c = static::from($ugid, $from, $to, 18, null);
    $c->Encounters = CriteriaJoin::requiresAsArray(Proc068::asEncounter($from, $to, $uid));
    return $c;
  }
  static function fetchAllBy($c) { /*associate by encounter rather than patient (CMS68 is encounter-based)*/
    $clients = SqlRec::fetchAllBy($c, null, 2000);
    $recs = array();
    foreach ($clients as $client) {
      foreach ($client->Encounters as $e) {
        $recs[$e->procId] = $client;
      }
    }
    return $recs;
  }
}
class Client068_Numer extends Client068_Ipp {
  //
  static function asCriteria($ugid, $from, $to, $uid) {
    $c = parent::asCriteria($ugid, $from, $to, $uid);
    $c->MedsDocs = CriteriaJoin::requiresAsArray(Proc068::asMedsDocumented($from, $to, $uid));
    return $c;
  }
  static function filter($clients) {
    $recs = array();
    foreach ($clients as $procId => $client) {
      $date = $client->getEncounterDate($procId);
      if ($client->isDocumented($date))
        $recs[$procId] = $client; 
    }
    return $recs;
  }
  //
  public function getEncounterDate($procId) {
    foreach ($this->Encounters as $e) {
      if ($e->procId == $procId)
        return $e->dateOnly();
    }
  }
  public function isDocumented($date) {
    return $this->isDocumentedAs($date, true);
  }
  public function isDocumentedAs($date, $asDone) {
    foreach ($this->MedsDocs as $proc) {
      if ($proc->dateOnly() == $date) {
        if ($asDone == ! $proc->isNotDone())
          return $proc;
      }
    }
  }
}
class Client068_Excep extends Client068_Numer {
  //
  public function isDocumented($date) {
    return $this->isDocumentedAs($date, false); 
  }
}
class Proc068 extends Proc_Cqm {
  //
  static function asMedsDocumented($from, $to, $uid) {
    return static::from($from, $to, $uid)->ipc('602465')->withResult();
  }
}
