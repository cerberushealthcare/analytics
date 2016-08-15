<?php
require_once 'CqmReports_Sql.php';
//
class CMS138v3 extends CqmReport {
  //
  static $CQM = "CMS138v3";
  static $ID = "40280381-4600-425f-0146-1f5867d40e82";
  static $SETID = "e35791df-5b25-41bb-b260-673337bc44a8";
  static $NQF = "0028";
  static $VERSION = "3";
  static $TITLE = "Preventive Care and Screening: Tobacco Use: Screening and Cessation Intervention";
  static $POPCLASSES = array('CMS138v3_Pop');
}
class CMS138v3_Pop extends CqmPop {
  //
  static $IPP = "4E118B62-2AF8-4F51-9355-6FD3F2427D9F";
  static $DENOM = "FA1B3953-AE58-4541-BF7B-84D0EB1B0713";
  static $DENEX = null;
  static $NUMER = "35b1a6df-1871-4633-a74b-bcae371bc030";
  static $DENEXCEP = "3ee6dff5-ab17-482f-a147-e6d1e46dbb79";
    //
  protected function getIpp($ugid, $from, $to, $uid) {
    return Client138_Ipp::fetchAll($ugid, $from, $to, $uid);
  }
  protected function getNumer($ugid, $from, $to, $uid) {
    return Client138_Numer::fetchAll($ugid, $from, $to, $uid);
  }
  protected function getExcep($ugid, $from, $to, $uid) {
    return Client138_Excep::fetchAll($ugid, $from, $to, $uid);    
  }
}
class Client138_Ipp extends Client_Cqm {
  //
  static function asCriteria($ugid, $from, $to, $uid) {
    $c = static::from($ugid, $from, $to, 18, null);
    $c->Encounters = CriteriaJoin::requiresAsArray(Proc138::asEncounter($from, $to, $uid));
    return $c;
  }
  static function filter($recs, $from, $to) {
    return array_filter($recs, function($rec) {
      if (count($rec->Encounters) >= 2)
        return true;
    });
  }
}
class Client138_Numer extends Client138_Ipp {
  //
  static function asCriteria($ugid, $from, $to, $uid) {
    $c = parent::asCriteria($ugid, $from, $to, $uid);
    $date = pastDate(2, 0, 0, $to);
    $c->RiskAss = CriteriaJoin::requiresAsArray(Proc138::asRiskAss($date, $to, $uid));
    $c->User = CriteriaJoin::optionalAsArray(Proc138::asSmoker($date, $to, $uid));
    $c->NonUser = CriteriaJoin::optionalAsArray(Proc138::asNonSmoker($date, $to, $uid));
    $c->CessInt = CriteriaJoin::optionalAsArray(Proc138::asCessInt($date, $to, $uid));
    $c->CessMed = CriteriaJoin::optionalAsArray(MedHist138::asCessMed($date, $to, $uid));
    $c->LimitLife = CriteriaJoin::optionalAsArray(Diag138::asLimitLife($to));
    return $c;
  }
  static function filter($recs, $from, $to) {
    $recs = parent::filter($recs, $from, $to);
    return array_filter($recs, function($rec) {
      if (! empty($rec->NonUser) && $rec->noUseAfterNonUse())
        return true;
      if (! empty($rec->User) && $rec->noNonUseAfterUse() && $rec->hasTreatment())
        return true;
    });
  }
  //
  public function noUseAfterNonUse() {
    if (! empty($rec->User)) {
      foreach ($rec->NonUser as $n) {
        foreach ($rec->User as $u) {
          if ($u->date > $n->date)
            return false;
        }
      }
    }
    return true;
  }
  public function noNonUseAfterUse() {
    if (! empty($rec->NonUser)) {
      foreach ($rec->User as $u) {
        foreach ($rec->NonUser as $n) {
          if ($n->date > $u->date)
            return false;
        }
      }
    }
    return true;
  }
  public function hasTreatment() {
    if (! empty($this->CessInt) || ! empty($this->CessMed))
      return true;
  }
}
class Client138_Excep extends Client138_Ipp {
  //
  static function asCriteria($ugid, $from, $to, $uid) {
    $c = parent::asCriteria($ugid, $from, $to, $uid);
    $date = pastDate(2, 0, 0, $to);
    $c->RiskAss = CriteriaJoin::optionalAsArray(Proc138::asRiskAss($date, $to, $uid));
    $c->LimitLife = CriteriaJoin::optionalAsArray(Diag138::asLimitLife($to));
    return $c;
  }
  static function filter($recs, $from, $to) {
    $recs = parent::filter($recs, $from, $to);
    return array_filter($recs, function($rec) use($to) {
      return $rec->riskAssNotDone() || $rec->hasLimitLife($to);
    });
  }
  //
  public function riskAssNotDone() {
    foreach (gets($this, 'RiskAss') as $proc) {
      if ($proc->isRefusedOrNotDone())
        return true;
    }
  }
  public function hasLimitLife($date) {
    foreach (gets($this, 'LimitLife') as $diag) {
      if (! $diag->closedBefore($date))
        return true;
    }
  }
}
class Proc138 extends Proc_Cqm {
  //
  static function asRiskAss($from, $to, $uid) {
    return static::from($from, $to, $uid)->ipc('602425')->withResult();
  } 
  static function asSmoker($from, $to, $uid) {
    return static::from($from, $to, $uid)->ipcs('602089','602090','602095','602096','602093');
  }
  static function asNonSmoker($from, $to, $uid) {
    return static::from($from, $to, $uid)->ipcs('602091','602092');
  }
  static function asCessInt($from, $to, $uid) {
    return static::from($from, $to, $uid)->ipc('602419');
  }
}
class MedHist138 extends MedHist_Cqm {
  //
  static function asCessMed($from, $to) {
    return static::from($from, $to)->rxnorms('1046847','1046858','151226','198029','198030','198031','198045','198046','198047','199888','199889','199890','205315','205316','250983','311972','311973','311975','312036','314119','317136','359817','359818','419168','636671','636676','749289','749788','892244','896100','993503','993518','993536','993541','993550','993557','993567','993681','998671','998675','998679');
  }
}
class Diag138 extends Diag_Cqm {
  //
  static function asLimitLife($before) {
    return static::from($before)->snomeds('162607003','162608008','170969009','27143004','300936002');
  }
}