<?php
require_once 'CqmReports_Sql.php';
//
class CMS090v4 extends CqmReport {
  //
  static $CQM = "CMS090v4";
  static $ID = "40280381-4555-e1c1-0145-af598c243391";
  static $SETID = "bb9b8ef7-0354-40e0-bec7-d6891b7df519";
  static $NQF = "N/A";
  static $VERSION = "4";
  static $TITLE = "Functional Status Assessment for Complex Chronic Conditions";
  static $POPCLASSES = array('CMS090v4_Pop');
}
class CMS090v4_Pop extends CqmPop {
  //
  static $IPP = "64D1755C-E61F-4FC8-AA8E-250A195D75F2";
  static $DENOM = "B6299783-BFE1-495C-8006-DF1FF2F768CD";
  static $DENEX = "339773d0-b398-404e-8c7c-2c7e50d40257";
  static $NUMER = "4fe21ec9-41e9-4eaa-b599-2d172913e61d";
  static $DENEXCEP = null;
    //
  protected function getIpp($ugid, $from, $to, $uid) {
    return Client090_Ipp::fetchAll($ugid, $from, $to, $uid);
  }
  protected function getExclu($ugid, $from, $to, $uid) {
    return Client090_Exclu::fetchAll($ugid, $from, $to, $uid);    
  }
  protected function getNumer($ugid, $from, $to, $uid) {
    return Client090_Numer::fetchAll($ugid, $from, $to, $uid);
  }
}
class Client090_Ipp extends Client_Cqm {
  //
  static function asCriteria($ugid, $from, $to, $uid) {
    $c = static::from($ugid, $from, $to, 65, null);
    $c->Encounters = CriteriaJoin::requiresAsArray(Proc090::asEncounter($from, $to, $uid));
    $c->HeartFailure = CriteriaJoin::requires(Diag090::asHeartFailure($to));
    return $c;
  }
  static function filter($recs, $from, $to) {
    return array_filter($recs, function($rec) use($from) {
      if (count($rec->Encounters) < 2)  
        return false;
      if ($rec->HeartFailure->closedBefore($from))
        return false;
      $rec->Encounter1 = null;
      $rec->Encounter2 = end($rec->Encounters);
      foreach ($rec->Encounters as $e) {
         $days = daysBetween($e->date, $rec->Encounter2->date);
         if ($days >= 30 && $days <= 180) {
           $rec->Encounter1 = $e;
           break;
         }
         if (-$days >= 30 && -$days <= 180) {
           $rec->Encounter1 = $rec->Encounter2;
           $rec->Encounter2 = $e;
           break;
         }
      }
      if ($rec->Encounter1)
        return true;
    });
  }
}
class Client090_Exclu extends Client090_Ipp {
  //
  static function asCriteria($ugid, $from, $to, $uid) {
    $c = parent::asCriteria($ugid, $from, $to, $uid);
    $c->Dementia = CriteriaJoin::optionalAsArray(Diag090::asDementia($to));
    $c->Cancer = CriteriaJoin::optionalAsArray(Diag090::asCancer($to));
    return $c;
  }
  static function filter($recs, $from, $to) {
    $recs = parent::filter($recs, $from, $to);
    return array_filter($recs, function($rec) use($from) {
      foreach (gets($rec, 'Dementia') as $diag) {
        if (! $diag->closedBefore($from))
          return true;
      }
      foreach (gets($rec, 'Cancer') as $diag) {
        if (! $diag->closedBefore($from))
          return true;
      }
    });
  }
}
class Client090_Numer extends Client090_Ipp {
  //
  static function asCriteria($ugid, $from, $to, $uid) {
    $c = parent::asCriteria($ugid, $from, $to, $uid);
    $c->FuncStats = CriteriaJoin::requiresAsArray(Proc090::asFunctionalStatus($from, $to, $uid));
    return $c;
  }
  static function filter($recs, $from, $to) {
    $recs = parent::filter($recs, $from, $to);
    return array_filter($recs, function($rec) {
      foreach ($rec->FuncStats as $stat) {
        $days = daysBetween($stat->date, $rec->Encounter1->date);
        if ($days >= 0 && $days <= 14)
          return true;
      }
    });
  }
}
class Proc090 extends Proc_Cqm {
  //
  static function asFunctionalStatus($from, $to, $uid) {
    return static::from($from, $to, $uid)->ipc('602479');
  }
}
class Diag090 extends Diag_Cqm {
  //
  static function asHeartFailure($before) {
    $c = static::from($before)->icds(
      '402.01','402.11','402.91','404.01','404.03','404.11','404.13','404.91','404.93','428.0','428.1','428.20','428.21','428.22',
      '428.23','428.30','428.31','428.32','428.33','428.40','428.41','428.42','428.43','428.9');
    return $c;
  }
  static function asDementia($before) {
    $c = static::from($before);
    $c->snomed = '428351000124105';
    return $c;
  }
}