<?php
require_once 'CqmReports_Sql.php';
//
class CMS002v4 extends CqmReport {
  //
  static $CQM = "CMS002v4";
  static $ID = "40280381-4555-e1c1-0145-dd4e02e44678";
  static $SETID = "9a031e24-3d9b-11e1-8634-00237d5bf174";
  static $NQF = "0418";
  static $VERSION = "4";
  static $TITLE = "Preventive Care and Screening: Screening for Clinical Depression and Follow-Up Plan";
  static $POPCLASSES = array('CMS002v4_Pop');
}
class CMS002v4_Pop extends CqmPop {
  //
  static $IPP = "0A220117-5622-4514-AD92-83AFD25B713D";
  static $DENOM = "37613083-CBC2-4AC4-9B10-B7325A7A8C3D";
  static $DENEX = "82eddba8-fee6-4ee1-963b-af0e2e52022b";
  static $NUMER = "dcb5a4df-8a12-4bed-a6b1-b1d99965cdb0";
  static $DENEXCEP = "6e2e3e1c-0e87-4107-b330-7a3f46111752";
  //
  protected function getIpp($ugid, $from, $to, $uid) {
    return Client002_Ipp::fetchAll($ugid, $from, $to, $uid);
  }
  protected function getExclu($ugid, $from, $to, $uid) {
    return Client002_Exclu::fetchAll($ugid, $from, $to, $uid);    
  }
  protected function getNumer($ugid, $from, $to, $uid) {
    return Client002_Numer::fetchAll($ugid, $from, $to, $uid);
  }
  protected function getExcep($ugid, $from, $to, $uid) {
    return Client002_Excep::fetchAll($ugid, $from, $to, $uid);    
  }
}
class Client002_Ipp extends Client_Cqm {
  //
  static function asCriteria($ugid, $from, $to, $uid) {
    $c = static::from($ugid, $from, $to, 12, null);
    $c->Encounter = CriteriaJoin::requires(Proc002::asEncounter($from, $to, $uid));
    return $c;
  }
}
class Client002_Numer {
  //
  static function fetchAll($ugid, $from, $to, $uid) {
    $teens = static::filter(Client002_ScreenTeen::fetchAll($ugid, $from, $to, $uid));
    $adults = static::filter(Client002_ScreenAdult::fetchAll($ugid, $from, $to, $uid));
    return array_replace($teens, $adults);
  }
  static function filter($recs) {
    return array_filter($recs, function($rec) {
      return (
        $rec->isCorrectAge() && ( 
          $rec->Screen->isNegative()) || (
          $rec->Screen->isPositive() && (
            $rec->hasInterventions())));
    });
  }
}
class Client002_Excep {
  //
  static function fetchAll($ugid, $from, $to, $uid) {
    $teens = static::filter(Client002_ScreenTeen::fetchAll($ugid, $from, $to, $uid));
    $adults = static::filter(Client002_ScreenAdult::fetchAll($ugid, $from, $to, $uid));
    return array_replace($teens, $adults);
  }
  static function filter($recs) {
    return array_filter($recs, function($rec) {
      return (
        $rec->isCorrectAge() && (
          $rec->Screen->isContraindicated() ||
          $rec->Screen->isRefused()));
    });
  }
}
class Client002_Exclu {
  //
  static function fetchAll($ugid, $from, $to, $uid) {
    $teens = static::filter(Client002_ScreenTeen_Exclu::fetchAll($ugid, $from, $to, $uid));
    $adults = static::filter(Client002_ScreenAdult_Exclu::fetchAll($ugid, $from, $to, $uid));
    return array_replace($teens, $adults);
  }
  static function filter($recs) {
    return array_filter($recs, function($rec) {
      return (
        $rec->hasExclusions());
    });
  }
}
class Client002_Screen extends Client002_Ipp {
  //
  static function fetchAll($ugid, $from, $to, $uid) {
    $c = static::asCriteria($ugid, $from, $to, $uid);
    $recs = static::fetchAllBy($c);
    $recs = static::setMostRecents($recs, 'Screen', 'Screens');
    return $recs;
  }
  //
  public function hasInterventions() {
    $this->Interventions = Proc002::fetchInterventions($this->clientId, $this->Screen->date);
    return count($this->Interventions) > 0;
    //return Proc002::countInterventions($this->clientId, $this->Screen->date) > 0;
  }
  public function hasExclusions() {
    $this->Depression = $this->hasExclusion('Depression');
    $this->Bipolar = $this->hasExclusion('Bipolar');
    if ($this->Depression || $this->Bipolar)
      return true;
  }
  public function hasExclusion($fid) {
    $diags = get($this, $fid);
    if ($diags) {
      $date = $this->Screen->date;
      foreach ($diags as $diag) {
        if ($diag->date < $date && ($diag->dateClosed == null || $diag->dateClosed > $date)) {
          return $diag; 
        }
      }
    }
  }
}
class Client002_ScreenTeen extends Client002_Screen {
  //
  static function asCriteria($ugid, $from, $to, $uid) {
    $c = parent::asCriteria($ugid, $from, $to, $uid);
    $c->Screens = CriteriaJoin::requiresAsArray(Proc002::asTeenScreen($from, $to, $uid));
    return $c;
  }
  //
  public function isCorrectAge() {
    return $this->ageAt($this->Screen->date) < 18;
  }
}
class Client002_ScreenAdult extends Client002_Screen {
  //
  static function asCriteria($ugid, $from, $to, $uid) {
    $c = parent::asCriteria($ugid, $from, $to, $uid);
    $c->Screens = CriteriaJoin::requiresAsArray(Proc002::asAdultScreen($from, $to, $uid));
    return $c;
  }
  //
  public function isCorrectAge() {
    return $this->ageAt($this->Screen->date) >= 18;
  }
}
class Client002_ScreenTeen_Exclu extends Client002_ScreenTeen {
  //
  static function asCriteria($ugid, $from, $to, $uid) {
    $c = parent::asCriteria($ugid, $from, $to, $uid);
    $c->Depression = CriteriaJoin::optionalAsArray(Diag002::asDepression());
    $c->Bipolar = CriteriaJoin::optionalAsArray(Diag002::asBipolar());
    return $c;
  }
}
class Client002_ScreenAdult_Exclu extends Client002_ScreenAdult {
  //
  static function asCriteria($ugid, $from, $to, $uid) {
    $c = parent::asCriteria($ugid, $from, $to, $uid);
    $c->Depression = CriteriaJoin::optionalAsArray(Diag002::asDepression());
    $c->Bipolar = CriteriaJoin::optionalAsArray(Diag002::asBipolar());
    return $c;
  }
}
class Proc002 extends Proc_Cqm {
  //
  static function asTeenScreen($from, $to, $uid) {
    return static::from($from, $to, $uid)->ipc('602458')->withResult();
  }
  static function asAdultScreen($from, $to, $uid) {
    return static::from($from, $to, $uid)->ipc('602459')->withResult();
  }
  static function countInterventions($cid, $from) {
    $to = futureDate(2, 0, 0, $from);
    $c = static::from($from, $to)->cid($cid)->ipcs('602447','602445','602451','602449','602454');
    return static::count($c);
  }
  static function fetchInterventions($cid, $from) {
    $to = futureDate(2, 0, 0, $from);
    $c = static::from($from, $to)->cid($cid)->ipcs('602447','602445','602451','602449','602454');
    return static::fetchAllBy($c);
  }
}
class Diag002 extends Diag_Cqm {
  //
  static function asDepression() {
    return static::from()->icds('290.13','290.21','290.43','296.21','296.22','296.23','296.24','296.25','296.26','296.31','296.32','296.33','296.34','296.36','296.82','298.0','300.4','301.12','309.0','309.1','309.28','311');
  }
  static function asBipolar() {
    return static::from()->icds('296.40','296.41','296.42','296.43','296.44','296.45','296.46','296.80');
  }
}