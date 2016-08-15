<?php
require_once 'CqmReports_Sql.php';
//
class CMS165v3 extends CqmReport {
  //
  static $CQM = "CMS165v3";
  static $ID = "40280381-4600-425f-0146-1f6f722b0f17";
  static $SETID = "abdc37cc-bac6-4156-9b91-d1be2c8b7268";
  static $NQF = "0018";
  static $VERSION = "3";
  static $TITLE = "Controlling High Blood Pressure";
  static $POPCLASSES = array('CMS165v3_Pop');
}
class CMS165v3_Pop extends CqmPop {
  //
  static $IPP = "A72855CE-3C60-41F9-AEE2-64D4F584DDD4";
  static $DENOM = "26046A5C-E2CC-4A27-B480-FF7E3575691F";
  static $DENEX = "4327d845-6194-410d-a48d-d6e1802cad55";
  static $NUMER = "0899a359-0cd8-4977-aa29-666892aa3ad4";
  static $DENEXCEP = null;
  //
  protected function getIpp($ugid, $from, $to, $uid) {
    return Client165_Ipp::fetchAll($ugid, $from, $to, $uid);
  }
  protected function getExclu($ugid, $from, $to, $uid) {
    return Client165_Exclu::fetchAll($ugid, $from, $to, $uid);
  }
  protected function getNumer($ugid, $from, $to, $uid) {
    return Client165_Numer::fetchAll($ugid, $from, $to, $uid);
  }
}
class Client165_Ipp extends Client_Cqm {
  //
  static function asCriteria($ugid, $from, $to, $uid) {
    $c = static::from($ugid, $from, $to, 18, 85);
    $c->Encounters = CriteriaJoin::requiresAsArray(Proc165::asEncounter($from, $to, $uid));
    $c->Hyper = CriteriaJoin::requiresAsArray(Diag165::asHyper(futureDate(2, 6, 0, $from)));
    return $c;
  }
  static function filter($recs, $from, $to) {
    return array_filter($recs, function($rec) use($from) {
      $rec->Hyper = $rec->filterClosedPrior('Hyper', $from);
      return $rec->Hyper;
    });
  }
}
class Client165_Exclu extends Client165_Ipp {
  //
  static function asCriteria($ugid, $from, $to, $uid) {
    $c = parent::asCriteria($ugid, $from, $to, $uid);
    $c->Preg = CriteriaJoin::optionalAsArray(Diag165::asPregnant($to));
    $c->Renal = CriteriaJoin::optionalAsArray(Diag165::asRenal($to));
    $c->Kidney = CriteriaJoin::optionalAsArray(Diag165::asKidney($to));
    $c->ProcExclu = CriteriaJoin::optionalAsArray(Proc165::asProcExclu($to, $uid));
    $c->EncExclu = CriteriaJoin::optionalAsArray(Proc165::asEncExclu($to, $uid));
    $c->IntExclu = CriteriaJoin::optionalAsArray(Proc165::asIntExclu($to, $uid));
    return $c;
  }
  static function filter($recs, $from, $to) {
    $recs = parent::filter($recs, $from, $to);
    return array_filter($recs, function($rec) use($from) {
      $rec->Preg = $rec->filterClosedPrior('Preg', $from);
      $rec->Renal = $rec->filterClosedPrior('Renal', $from);
      $rec->Kidney = $rec->filterClosedPrior('Kidney', $from);
      return
        $rec->Preg ||
        $rec->Renal ||
        $rec->Kidney ||
        $rec->has('ProcExclu') ||
        $rec->has('EndExclu') ||
        $rec->has('IntExclu');
    });
  }
}
class Client165_Numer extends Client165_Ipp {
  //
  static function asCriteria($ugid, $from, $to, $uid) {
    $c = parent::asCriteria($ugid, $from, $to, $uid);
    $c->Diastolic = CriteriaJoin::requiresAsArray(Proc165::asDiastolic($from, $to, $uid));
    $c->Systolic = CriteriaJoin::requiresAsArray(Proc165::asSystolic($from, $to, $uid));
    return $c;
  }
  static function filter($recs, $from, $to) {
    $recs = parent::filter($recs, $from, $to);
    return array_filter($recs, function($rec) {
      $rec->Diastolic = $rec->filterMostRecent('Diastolic');
      $rec->Systolic = $rec->filterMostRecent('Systolic');
      return
        $rec->Diastolic->date == $rec->Systolic->date &&
        $rec->Diastolic->isLower(90) &&
        $rec->Systolic->isLower(140);
    });
  }
}
class Proc165 extends Proc_Cqm {
  //
  static function asProcExclu($before, $uid) {
    return static::from(null, $before, $uid)->ipcs('602401','602402','602403');
  }
  static function asEncExclu($before, $uid) {
    return static::from(null, $before, $uid)->ipc('602397');
  }
  static function asIntExclu($before, $uid) {
    return static::from(null, $before)->ipcs('602399','602400');
  }
  static function asDiastolic($from, $to, $uid) {
    return static::from($from, $to)->ipc('602353')->withResult();
  }
  static function asSystolic($from, $to, $uid) {
    return static::from($from, $to)->ipc('602354')->withResult();
  }
}
class Diag165 extends Diag_Cqm {
  //
  static function asHyper($before) {
    $c = static::from($before)->icds('401.0','401.1','401.9');
    return $c;
  }
  static function asRenal($before) {
    $c = static::from($before)->icds('585.6');
    return $c;
  }
  static function asKidney($before) {
    $c = static::from($before)->icds('585.5');
    return $c;
  }
}