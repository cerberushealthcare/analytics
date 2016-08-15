<?php
require_once 'CqmReports_Sql.php';
//
class CMS069v3 extends CqmReport {
  //
  static $CQM = "CMS069v3";
  static $ID = "40280381-4555-e1c1-0145-d2b36dbb3fe6";
  static $SETID = "9a031bb8-3d9b-11e1-8634-00237d5bf174";
  static $NQF = "0421";
  static $VERSION = "3";
  static $TITLE = "Preventive Care and Screening: Body Mass Index (BMI) Screening and Follow-Up Plan";
  static $POPCLASSES = array('CMS069v3_Pop1', "CMS069v3_Pop2");
}
class CMS069v3_Pop1 extends CqmPop {
  //
  static $IPP = "1C936855-E644-44C0-B264-49A28756FDB1";
  static $DENOM = "27F1591C-2060-462C-B5D7-7FE86A44B534";
  static $DENEX = "9b0c3c26-d621-4ea3-81fb-a839a3012044";
  static $NUMER = "3095531c-24d7-4afb-9bcb-f1901ff0ff69";
  static $DENEXCEP = null;
    //
  protected function getIpp($ugid, $from, $to, $uid) {
    return Client069_Ipp::fetchAll($ugid, $from, $to, $uid, 18, 65);
  }
  protected function getExclu($ugid, $from, $to, $uid) {
    return Client069_Exclu::fetchAll($ugid, $from, $to, $uid, 18, 65);    
  }
  protected function getNumer($ugid, $from, $to, $uid) {
    return Client069_Numer::fetchAll($ugid, $from, $to, $uid, 18, 65, 18.5, 25);
  }
}
class CMS069v3_Pop2 extends CqmPop {
  //
  static $IPP = "6E701B1C-6CA5-4AD5-98C9-5F766745EA89";
  static $DENOM = "E4DC29B8-EB26-4A01-ABB0-4F99FC03BA39";
  static $DENEX = "bb1b4301-c275-4bac-87c9-6e960b1601da";
  static $NUMER = "7669026d-3683-44cc-a2c5-3d62eb2f8a33";
  static $DENEXCEP = null;
  //
  protected function getIpp($ugid, $from, $to, $uid) {
    return Client069_Ipp::fetchAll($ugid, $from, $to, $uid, 65, null);
  }
  protected function getExclu($ugid, $from, $to, $uid) {
    return Client069_Exclu::fetchAll($ugid, $from, $to, $uid, 65, null);    
  }
  protected function getNumer($ugid, $from, $to, $uid) {
    return Client069_Numer::fetchAll($ugid, $from, $to, $uid, 65, null, 23, 30);
  }
}
class Client069_Ipp extends Client_Cqm {
  //
  static function fetchAll($ugid, $from, $to, $uid, $ageFrom, $ageTo, $lo = null, $hi = null) {
    $c = static::asCriteria($ugid, $from, $to, $uid, $ageFrom, $ageTo, $lo, $hi);
    $recs = static::fetchAllBy($c);
    $recs = static::filter($recs, $from, $to, $lo, $hi);
    return $recs;
  } 
  static function asCriteria($ugid, $from, $to, $uid, $ageFrom, $ageTo, $lo, $hi) {
    $c = static::from($ugid, $from, $to, $ageFrom, $ageTo);
    $c->Encounters = CriteriaJoin::requiresAsArray(Proc069::asEncounter($from, $to, $uid));
    $c->Palliative = CriteriaJoin::optional(Proc069::asPalliativeCare($from, $to));
    $c->Bmis = CriteriaJoin::optionalAsArray(Proc069::asBmi($from, $to, $uid));
    return $c;
  }
  static function filter($recs, $from, $to, $lo, $hi) {
    return array_filter($recs, function($rec) {
      foreach (gets($rec, 'Bmis') as $proc) {
        if ($proc->isRefusedOrNotDone())
          return false;
      }
      $date = $rec->getPalliativeCareDate();
      if ($date) {
        $rec->Encounters = $rec->filterEncounters($date);
      }
      return $rec->has('Encounters');
    });
  }
  //
  public function getPalliativeCareDate() {
    if (get($this, 'Palliative')) {
      return $this->Palliative->dateOnly();
    }
  }
  public function filterEncounters($date) {
    return array_filter($this->Encounters, function($rec) use($date) {
      return $rec->dateOnly() < $date;
    });
  }
}
class Client069_Exclu extends Client069_Ipp {
  //
  static function asCriteria($ugid, $from, $to, $uid, $ageFrom, $ageTo, $lo, $hi) {
    $c = parent::asCriteria($ugid, $from, $to, $uid, $ageFrom, $ageTo, $lo, $hi);
    $c->Preg = CriteriaJoin::requiresAsArray(Diag069::asPregnant($to));
    return $c;
  }
  static function filter($recs, $from, $to, $lo, $hi) {
    $recs = parent::filter($recs, $from, $to, $lo, $hi);
    return array_filter($recs, function($rec) use($from) {
      $rec->Preg = $rec->filterClosedPrior('Preg', $from);
      return $rec->Preg;
    });
  }
}
class Client069_Numer extends Client069_Ipp {
  //
  static function asCriteria($ugid, $from, $to, $uid, $ageFrom, $ageTo, $lo, $hi) {
    $c = parent::asCriteria($ugid, $from, $to, $uid, $ageFrom, $ageTo, $lo, $hi);
    $c->AboveInts = CriteriaJoin::optionalAsArray(Proc069::asAboveInts($from, $to, $uid));
    $c->BelowInts = CriteriaJoin::optionalAsArray(Proc069::asBelowInts($from, $to, $uid));
    $c->AboveMeds = CriteriaJoin::optionalAsArray(MedHist069::asAbove($from, $to, $uid));
    $c->BelowMeds = CriteriaJoin::optionalAsArray(MedHist069::asBelow($from, $to, $uid));
    return $c;
  }
  static function filter($recs, $from, $to, $lo, $hi) {
    $recs = parent::filter($recs, $from, $to, $lo, $hi);
    return array_filter($recs, function($rec) use($lo, $hi) {
      $keep = false;
      if (get($rec, 'Bmis')) {
        foreach ($rec->Encounters as $enc) {
          $prior6 = pastDate(0, 6, 0, $enc->date);
          $bmi = $rec->getBmiWithin($prior6, $enc->dateOnly());
          if ($bmi) {
            if ($bmi->isInRange($lo, $hi)) {
              $keep = true;
              break;
            } else if ($bmi->isLower($lo)) {
              if ($rec->hasBelowFollowUps($prior6, $enc->date)) {
                $keep = true;
                break;
              }
            } else {
              if ($rec->hasAboveFollowUps($prior6, $enc->date)) {
                $keep = true;
                break;
              }
            }
          }
        }
      }
      return $keep;
    });
  }
  //
  public function getBmiWithin($from, $to) {
    foreach ($this->Bmis as $e) {
      if (isWithin($e->dateOnly(), $from, $to))
        return $e;
    }
  }
  public function hasBelowFollowUps($from, $to) {
    foreach (gets($this, 'BelowInts') as $e) {
      if (isWithin($e->dateOnly(), $from, $to))
        return true;
    }
    foreach (gets($this, 'BelowMeds') as $e) {
      if (isWithin($e->dateOnly(), $from, $to))
        return true;
    }
  }
  public function hasAboveFollowUps($from, $to) {
    foreach (gets($this, 'AboveInts') as $e) {
      if (isWithin($e->dateOnly(), $from, $to)) 
        return true;
    }
    foreach (gets($this, 'AboveMeds') as $e) {
      if (isWithin($e->dateOnly(), $from, $to)) 
        return true;
    }
  }
}
class Proc069 extends Proc_Cqm {
  //
  static function asPalliativeCare($from, $to) {
    return static::from()->ipc('602390');
  }
  static function asBmi($from, $to, $uid) {
    $c = static::from($from, $to, $uid)->ipc('602473')->withResult();
    return $c;
  }
  static function asAboveInts($from, $to, $uid) {
    return static::from($from, $to, $uid)->ipcs('602468','602470','602471');
  }
  static function asBelowInts($from, $to, $uid) {
    return static::from($from, $to, $uid)->ipcs('602469','602470','602472');
  }
}
class Diag069 extends Diag_Cqm {
}
class MedHist069 extends MedHist_Cqm {
  //
  static function asBelow() {
    return static::from()->rxnorms('577154','860215','860221','860225','860231');
  } 
  static function asAbove() {
    return static::from()->rxnorms('314153','692876','226917','723846');
  } 
}