<?php 
//
/**
 * NQF 0043
 * Pneumonia Vaccination Status for Older Adults
 */
class CmsReport_NQF0043 extends CmsReport {
  //
  public $measureNumber = 'NQF 0043';
  //
  public function fetchPop($ugid, $from, $to, $userId) { 
    $c = Client_0043::asPop($ugid, $from, $to, $userId);
    return $this->fetchAllBy($c);
  }
  public function fetchNum($ugid, $from, $to, $userId) {
    $c = Client_0043::asNum($ugid, $from, $to, $userId);
    return $this->fetchAllBy($c);
  }
}
class Client_0043 extends Client_Cms {
  static function asPop($ugid, $from, $to, $userId) {
    $c = parent::asPop($ugid, $from, $to, 64, null, $userId);
    $c->Proc = CriteriaJoin::requires(Proc_0043::asOfficeVisit($from, $to, $userId));
    return $c;
  }
  static function asNum($ugid, $from, $to, $userId) {
    $c = static::asPop($ugid, $from, $to, $userId);
    $c->Immun = CriteriaJoin::requires(Immun_0043::asPneum($from, $to));
    return $c;
  }
}
class Proc_0043 extends Proc_Cms {
  static function asOfficeVisit($from, $to, $userId) {
    $c = static::from('600186');
    $c->Hd_date = Hdata_ProcDate::join(CriteriaValue::betweenAge(array(0, 1), $from, $to));
    $c->setUserId($userId);
    return $c;
  }
}
class Immun_0043 extends Immun_Cms {
  static function asPneum($from, $to) {
    $c = new static();
    $c->Hd_dateGiven = Hdata_ImmunDate::join(CriteriaValue::lessThan($to));
    $c->name = CriteriaValue::in(array('Pneumococcal','Pneumovax','Prevnar'));
    return $c;
  }
}
/**
 * NQF 0034
 * Colorectal Cancer Screening
 */
class CmsReport_NQF0034 extends CmsReport {
  //
  public $measureNumber = 'NQF 0034';
  //
  public function fetchPop($ugid, $from, $to, $userId) { 
    $c = Client_0034::asPop($ugid, $from, $to, $userId);
    return $this->fetchAllBy($c);
  }
  public function fetchNum($ugid, $from, $to, $userId) {
    $c = Client_0034::asNum($ugid, $from, $to, $userId);
    return $this->fetchAllBy($c);
  }
  public function fetchExc($ugid, $from, $to, $userId) {
    $c = Client_0034::asExc($ugid, $from, $to, $userId);
    return $this->fetchAllBy($c);
  }
}
class Client_0034 extends Client_Cms {
  static function asPop($ugid, $from, $to, $userId) {
    $c = parent::asPop($ugid, $from, $to, 50, 75);
    $c->Proc = CriteriaJoin::requires(Proc_0034::asOfficeVisit($from, $to, $userId));
    $c->Proc2 = CriteriaJoin::notExists(Proc_0034::asColectomy());
    return $c;
  }
  static function asNum($ugid, $from, $to, $userId) {
    $c = static::asPop($ugid, $from, $to, $userId);
    $c->Proc3 = CriteriaJoin::requiresOneOf(array(
      Proc_0034::asColonoscopy($from, $to),
      Proc_0034::asFlexSig($from, $to),
      Proc_0034::asFecalHemo($from, $to)));
    return $c;
  }
  static function asExc($ugid, $from, $to, $userId) {
    $c = static::asPop($ugid, $from, $to, $userId);
    $c->Diag = CriteriaJoin::requires(Diag_0034::asColonCancer());
    return $c;
  }
}
class Proc_0034 extends Proc_Cms {
  static function asOfficeVisit($from, $to, $userId) {
    $c = static::from('600186');
    $c->Hd_date = Hdata_ProcDate::join(CriteriaValue::betweenAge(array(0, 1), $from, $to));
    $c->setUserId($userId);
    return $c;
  }
  static function asColectomy() {
    return static::from('842680');
  }
  static function asColonoscopy($from, $to) {
    $c = static::from('918089');
    $c->Hd_date = Hdata_ProcDate::join(CriteriaValue::betweenAge(array(0, 10), $from, $to));
    return $c;
  }
  static function asFlexSig($from, $to) {
    $c = static::from('691795');
    $c->Hd_date = Hdata_ProcDate::join(CriteriaValue::betweenAge(array(0, 5), $from, $to));
    return $c;
  }
  static function asFecalHemo($from, $to) {
    $c = new static();
    $c->ipc = CriteriaValue::in(array('600085','691799','918401'));
    $c->Hd_date = Hdata_ProcDate::join(CriteriaValue::betweenAge(array(0, 1), $from, $to));
    return $c;
  }
} 
class Diag_0034 extends Diag_Cms {
  static function asColonCancer() {
    $c = new static();
    $c->status = CriteriaValue::in(array(1,10,20));
    $c->icd = static::cvIcds('153','153.0','153.1','153.2','153.3','153.4','153.5','153.6','153.7','153.8','153.9','154.0','154.1','197.5','V10.05');
    return $c;
  }
}
/**
 * NQF 0027
 * Smoking and Tobacco Use Cessation, Medical assistance
 */
abstract class CmsReport_NQF0027 extends CmsReport {
  //
  public $measureNumber = 'NQF 0027';
}
class CmsReport_NQF0027_N1 extends CmsReport_NQF0027 {
  //
  public $numNumber = 1;  
  //
  public function fetchPop($ugid, $from, $to, $userId) { 
    $c = Client_0027::asPop($ugid, $from, $to, $userId);
    return $this->fetchAllBy($c);
  }
  public function fetchNum($ugid, $from, $to, $userId) {
    $c = Client_0027::asNum1($ugid, $from, $to, $userId);
    return $this->fetchAllBy($c);
  }
}
class CmsReport_NQF0027_N2 extends CmsReport_NQF0027 {
  //
  public $numNumber = 2;  
  //
  public function fetchPop($ugid, $from, $to, $userId) { 
    $c = Client_0027::asPop($ugid, $from, $to, $userId);
    return $this->fetchAllBy($c);
  }
  public function fetchNum($ugid, $from, $to, $userId) {
    $c = Client_0027::asNum2($ugid, $from, $to, $userId);
    return $this->fetchAllBy($c);
  }
}
class Client_0027 extends Client_Cms {
  static function asPop($ugid, $from, $to, $userId) {
    $c = parent::asPop($ugid, $from, $to, 18, null);
    $c->Proc = CriteriaJoin::requires(Proc_0027::asOfficeVisit($from, $to, $userId));
    return $c;
  }
  static function asNum1($ugid, $from, $to, $userId) {
    $c = static::asPop($ugid, $from, $to, $userId);
    $c->Diag = CriteriaJoin::requires(Diag_0027::asSmoker($from, $to));
    return $c;
  }
  static function asNum2($ugid, $from, $to, $userId) {
    $c = static::asPop($ugid, $from, $to, $userId);
    $c->Proc2 = CriteriaJoin::requires(Proc_0027::asCessationCounseled($from, $to));
    return $c;
  }
}
class Proc_0027 extends Proc_Cms {
  static function asOfficeVisit($from, $to, $userId) {
    $c = new static();
    $c->ipc = CriteriaValue::in(array('600186'));
    $c->Hd_date = Hdata_ProcDate::join(CriteriaValue::betweenAge(array(0, 2), $from, $to));
    $c->setUserId($userId);
    return $c;
  }
  static function asCessationCounseled($from, $to) {
    $c = new static();  //static::from('600004');
    $c->ipc = CriteriaValue::in(array('600004','600921','600922'));
    $c->Hd_date = Hdata_ProcDate::join(CriteriaValue::betweenDates(array($from, $to)));
    return $c;
  }
}
class Diag_0027 extends Diag_Cms {
  static function asSmoker($from, $to) {
    $c = new static();
    $c->icd = static::cvIcds('305.1','649.00','649.01','649.02','649.03','649.04','989.84');
    $c->Hd_date = Hdata_DiagnosisDate::join(CriteriaValue::betweenAge(array(0, 2), $from, $to));
    return $c;
  }
}
/**
 * NQF 0038
 * Childhood Immunization Status
 */
abstract class CmsReport_NQF0038 extends CmsReport {
  //
  public $measureNumber = 'NQF 0038';
  public $popNumber = 1;  
}
class CmsReport_NQF0038_N1 extends CmsReport_NQF0038 {
  //
  public $numNumber = 1;
  //
  public function fetchPop($ugid, $from, $to, $userId) { 
    return $this->fetchAllBy(
      Client_0038::asPop($ugid, $from, $to, $userId));
  }
  public function fetchNum($ugid, $from, $to, $userId) {
    return $this->fetchAllBy(
      Client_0038::asNum1($ugid, $from, $to, $userId));
  }
} 
class CmsReport_NQF0038_N2 extends CmsReport_NQF0038 {
  //
  public $numNumber = 2;
  //
  public function fetchPop($ugid, $from, $to, $userId) { 
    return $this->fetchAllBy(
      Client_0038::asPop($ugid, $from, $to, $userId));
  }
  public function fetchNum($ugid, $from, $to, $userId) {
    return $this->fetchAllBy(
      Client_0038::asNum2($ugid, $from, $to, $userId));
  }
} 
class CmsReport_NQF0038_N3 extends CmsReport_NQF0038 {
  //
  public $numNumber = 3;
  //
  public function fetchPop($ugid, $from, $to, $userId) { 
    return $this->fetchAllBy(
      Client_0038::asPop($ugid, $from, $to, $userId));
  }
  public function fetchNum($ugid, $from, $to, $userId) {
    return $this->fetchMerge(
      Client_0038::asNum3a($ugid, $from, $to, $userId),
      Client_0038::asNum3b($ugid, $from, $to, $userId),
      Client_0038::asNum3c($ugid, $from, $to, $userId),
      Client_0038::asNum3d($ugid, $from, $to, $userId),
      Client_0038::asNum3e($ugid, $from, $to, $userId));
  }
} 
class CmsReport_NQF0038_N4 extends CmsReport_NQF0038 {
  //
  public $numNumber = 4;
  //
  public function fetchPop($ugid, $from, $to, $userId) { 
    return $this->fetchAllBy(
      Client_0038::asPop($ugid, $from, $to, $userId));
  }
  public function fetchNum($ugid, $from, $to, $userId) {
    return $this->fetchAllBy(
      Client_0038::asNum4($ugid, $from, $to, $userId));
  }
} 
class CmsReport_NQF0038_N5 extends CmsReport_NQF0038 {
  //
  public $numNumber = 5;
  //
  public function fetchPop($ugid, $from, $to, $userId) { 
    return $this->fetchAllBy(
      Client_0038::asPop($ugid, $from, $to, $userId));
  }
  public function fetchNum($ugid, $from, $to, $userId) {
    return $this->fetchMerge(
      Client_0038::asNum5a($ugid, $from, $to, $userId),
      Client_0038::asNum5b($ugid, $from, $to, $userId));
  }
} 
class CmsReport_NQF0038_N6 extends CmsReport_NQF0038 {
  //
  public $numNumber = 6;
  //
  public function fetchPop($ugid, $from, $to, $userId) { 
    return $this->fetchAllBy(
      Client_0038::asPop($ugid, $from, $to, $userId));
  }
  public function fetchNum($ugid, $from, $to, $userId) {
    return $this->fetchMerge(
      Client_0038::asNum6a($ugid, $from, $to, $userId),
      Client_0038::asNum6b($ugid, $from, $to, $userId));
  }
} 
class CmsReport_NQF0038_N7 extends CmsReport_NQF0038 {
  //
  public $numNumber = 7;
  //
  public function fetchPop($ugid, $from, $to, $userId) { 
    return $this->fetchAllBy(
      Client_0038::asPop($ugid, $from, $to, $userId));
  }
  public function fetchNum($ugid, $from, $to, $userId) {
    return $this->fetchAllBy(
      Client_0038::asNum7($ugid, $from, $to, $userId));
  }
} 
class CmsReport_NQF0038_N8 extends CmsReport_NQF0038 {
  //
  public $numNumber = 8;
  //
  public function fetchPop($ugid, $from, $to, $userId) { 
    return $this->fetchAllBy(
      Client_0038::asPop($ugid, $from, $to, $userId));
  }
  public function fetchNum($ugid, $from, $to, $userId) {
    return $this->fetchMerge(
      Client_0038::asNum8a($ugid, $from, $to, $userId),
      Client_0038::asNum8b($ugid, $from, $to, $userId));
  }
} 
class CmsReport_NQF0038_N9 extends CmsReport_NQF0038 {
  //
  public $numNumber = 9;
  //
  public function fetchPop($ugid, $from, $to, $userId) { 
    return $this->fetchAllBy(
      Client_0038::asPop($ugid, $from, $to, $userId));
  }
  public function fetchNum($ugid, $from, $to, $userId) {
    return $this->fetchAllBy(
      Client_0038::asNum9($ugid, $from, $to, $userId));
  }
} 
class CmsReport_NQF0038_N10 extends CmsReport_NQF0038 {
  //
  public $numNumber = 10;
  //
  public function fetchPop($ugid, $from, $to, $userId) { 
    return $this->fetchAllBy(
      Client_0038::asPop($ugid, $from, $to, $userId));
  }
  public function fetchNum($ugid, $from, $to, $userId) {
    return $this->fetchAllBy(
      Client_0038::asNum10($ugid, $from, $to, $userId));
  }
} 
class CmsReport_NQF0038_N11 extends CmsReport_NQF0038 {
  //
  public $numNumber = 11;
  //
  public function fetchPop($ugid, $from, $to, $userId) { 
    return $this->fetchAllBy(
      Client_0038::asPop($ugid, $from, $to, $userId));
  }
  public function fetchNum($ugid, $from, $to, $userId) {
    return $this->fetchMerge(
      Client_0038::asNum1($ugid, $from, $to, $userId),  
      Client_0038::asNum2($ugid, $from, $to, $userId),
      Client_0038::asNum3a($ugid, $from, $to, $userId),
      Client_0038::asNum3b($ugid, $from, $to, $userId),
      Client_0038::asNum3c($ugid, $from, $to, $userId),
      Client_0038::asNum3d($ugid, $from, $to, $userId),
      Client_0038::asNum3e($ugid, $from, $to, $userId),
      Client_0038::asNum4($ugid, $from, $to, $userId),
      Client_0038::asNum5a($ugid, $from, $to, $userId),
      Client_0038::asNum5b($ugid, $from, $to, $userId),
      Client_0038::asNum6a($ugid, $from, $to, $userId),
      Client_0038::asNum6b($ugid, $from, $to, $userId),
      Client_0038::asNum8a($ugid, $from, $to, $userId),
      Client_0038::asNum8b($ugid, $from, $to, $userId),
      Client_0038::asNum9($ugid, $from, $to, $userId),
      Client_0038::asNum10($ugid, $from, $to, $userId));
  }
} 
class CmsReport_NQF0038_N12 extends CmsReport_NQF0038 {
  //
  public $numNumber = 12;
  //
  public function fetchPop($ugid, $from, $to, $userId) { 
    return $this->fetchAllBy(
      Client_0038::asPop($ugid, $from, $to, $userId));
  }
  public function fetchNum($ugid, $from, $to, $userId) {
    return $this->fetchMerge(
      Client_0038::asNum1($ugid, $from, $to, $userId),  
      Client_0038::asNum2($ugid, $from, $to, $userId),
      Client_0038::asNum3a($ugid, $from, $to, $userId),
      Client_0038::asNum3b($ugid, $from, $to, $userId),
      Client_0038::asNum3c($ugid, $from, $to, $userId),
      Client_0038::asNum3d($ugid, $from, $to, $userId),
      Client_0038::asNum3e($ugid, $from, $to, $userId),
      Client_0038::asNum4($ugid, $from, $to, $userId),
      Client_0038::asNum5a($ugid, $from, $to, $userId),
      Client_0038::asNum5b($ugid, $from, $to, $userId),
      Client_0038::asNum6a($ugid, $from, $to, $userId),
      Client_0038::asNum6b($ugid, $from, $to, $userId),
      Client_0038::asNum7($ugid, $from, $to, $userId),
      Client_0038::asNum8a($ugid, $from, $to, $userId),
      Client_0038::asNum8b($ugid, $from, $to, $userId),
      Client_0038::asNum9($ugid, $from, $to, $userId),
      Client_0038::asNum10($ugid, $from, $to, $userId));
  }
} 
class Client_0038 extends Client_Cms {
  static function asPop($ugid, $from, $to, $userId) {
    $c = parent::asPop($ugid, $from, $to, 1, 2);
    $c->Proc = CriteriaJoin::requires(Proc_0038::asOfficeVisit($from, $to, $userId));
    return $c;
  }
  static function asNum1($ugid, $from, $to, $userId) {
    $c = static::asPop($ugid, $from, $to, $userId);
    $c->Immun = CriteriaJoin::requiresCountGreaterThan(Immun_0038::asDtap(), 3);
    $c->NoAller = CriteriaJoin::notExists(Allergy_0038::asDtap());
    $c->NoDiag = CriteriaJoin::notExists(array(
      Diag_0038::asEnceph(),
      Diag_0038::asProgNuero()));
    return $c;
  }
  static function asNum2($ugid, $from, $to, $userId) {
    $c = static::asPop($ugid, $from, $to, $userId);
    $c->Immun = CriteriaJoin::requiresCountGreaterThan(Immun_0038::asIpv(), 2);
    $c->NoAller = CriteriaJoin::notExists(Allergy_0038::asIpv());
    return $c;
  }
  static function asNum3a($ugid, $from, $to, $userId) {
    $c = static::asPop($ugid, $from, $to, $userId);
    $c->Immun = CriteriaJoin::requiresCountGreaterThan(Immun_0038::asMmr(), 0);
    $c->NoDiag = CriteriaJoin::notExists(Diag_0038::asCancers());
    $c->NoAller = CriteriaJoin::notExists(Allergy_0038::asMmr());
    return $c;
  }
  static function asNum3b($ugid, $from, $to, $userId) {
    $c = static::asPop($ugid, $from, $to, $userId);
    $c->Immun1 = CriteriaJoin::requiresCountGreaterThan(Immun_0038::asMeasles(), 0);
    $c->Immun2 = CriteriaJoin::requiresCountGreaterThan(Immun_0038::asMumps(3), 0);
    $c->Immun3 = CriteriaJoin::requiresCountGreaterThan(Immun_0038::asRubella(4), 0);
    $c->NoDiag = CriteriaJoin::notExists(Diag_0038::asCancers());
    $c->NoAller = CriteriaJoin::notExists(array(
      Allergy_0038::asMmr(), 
      Allergy_0038::asMumps(), 
      Allergy_0038::asMeasles(), 
      Allergy_0038::asRubella()));
    return $c;
  }
  static function asNum3c($ugid, $from, $to, $userId) {
    $c = static::asPop($ugid, $from, $to, $userId);
    $c->Immun1 = CriteriaJoin::requiresCountGreaterThan(Immun_0038::asMumps(), 0);
    $c->Immun2 = CriteriaJoin::requiresCountGreaterThan(Immun_0038::asRubella(3), 0);
    $c->Diag = CriteriaJoin::requires(Diag_0038::asMeasles());
    $c->NoDiag = CriteriaJoin::notExists(Diag_0038::asCancers());
    $c->NoAller = CriteriaJoin::notExists(array(
      Allergy_0038::asMmr(), 
      Allergy_0038::asMumps(), 
      Allergy_0038::asRubella()));
    return $c;
  }
  static function asNum3d($ugid, $from, $to, $userId) {
    $c = static::asPop($ugid, $from, $to, $userId);
    $c->Immun1 = CriteriaJoin::requiresCountGreaterThan(Immun_0038::asMeasles(), 0);
    $c->Immun2 = CriteriaJoin::requiresCountGreaterThan(Immun_0038::asRubella(3), 0);
    $c->Diag = CriteriaJoin::requires(Diag_0038::asMumps());
    $c->NoDiag = CriteriaJoin::notExists(Diag_0038::asCancers());
    $c->NoAller = CriteriaJoin::notExists(array(
      Allergy_0038::asMmr(), 
      Allergy_0038::asMeasles(), 
      Allergy_0038::asRubella()));
    return $c;
  }
  static function asNum3e($ugid, $from, $to, $userId) {
    $c = static::asPop($ugid, $from, $to, $userId);
    $c->Immun1 = CriteriaJoin::requiresCountGreaterThan(Immun_0038::asMumps(), 0);
    $c->Immun2 = CriteriaJoin::requiresCountGreaterThan(Immun_0038::asMeasles(3), 0);
    $c->Diag = CriteriaJoin::requires(Diag_0038::asRubella());
    $c->NoDiag = CriteriaJoin::notExists(Diag_0038::asCancers());
    $c->NoAller = CriteriaJoin::notExists(array(
      Allergy_0038::asMmr(), 
      Allergy_0038::asMumps(), 
      Allergy_0038::asMeasles()));
    return $c;
  }
  static function asNum4($ugid, $from, $to, $userId) {
    $c = static::asPop($ugid, $from, $to, $userId);
    $c->Immun = CriteriaJoin::requiresCountGreaterThan(Immun_0038::asHib(), 1);
    $c->NoAller = CriteriaJoin::notExists(Allergy_0038::asHib());
    return $c;
  }
  static function asNum5a($ugid, $from, $to, $userId) {
    $c = static::asPop($ugid, $from, $to, $userId);
    $c->Immun = CriteriaJoin::requiresCountGreaterThan(Immun_0038::asHepb(), 2);
    $c->NoAller = CriteriaJoin::notExists(Allergy_0038::asHepb());
    return $c;
  }
  static function asNum5b($ugid, $from, $to, $userId) {
    $c = static::asPop($ugid, $from, $to, $userId);
    $c->Diag = CriteriaJoin::requiresCountGreaterThan(Diag_0038::asHepb());
    $c->NoAller = CriteriaJoin::notExists(Allergy_0038::asHepb());
    return $c;
  }
  static function asNum6a($ugid, $from, $to, $userId) {
    $c = static::asPop($ugid, $from, $to, $userId);
    $c->Immun = CriteriaJoin::requiresCountGreaterThan(Immun_0038::asVzv(), 0);
    $c->NoDiag = CriteriaJoin::notExists(Diag_0038::asCancers());
    $c->NoAller = CriteriaJoin::notExists(Allergy_0038::asVzv());
    return $c;
  }
  static function asNum6b($ugid, $from, $to, $userId) {
    $c = static::asPop($ugid, $from, $to, $userId);
    $c->Diag = CriteriaJoin::requires(Diag_0038::asVaricella());
    $c->NoDiag = CriteriaJoin::notExists(Diag_0038::asCancers());
    $c->NoAller = CriteriaJoin::notExists(Allergy_0038::asVzv());
    return $c;
  }
  static function asNum7($ugid, $from, $to, $userId) {
    $c = static::asPop($ugid, $from, $to, $userId);
    $c->Immun = CriteriaJoin::requiresCountGreaterThan(Immun_0038::asPneum(), 3);
    $c->NoAller = CriteriaJoin::notExists(Allergy_0038::asPneum());
    return $c;
  }
  static function asNum8a($ugid, $from, $to, $userId) {
    $c = static::asPop($ugid, $from, $to, $userId);
    $c->Immun = CriteriaJoin::requiresCountGreaterThan(Immun_0038::asHepa(), 1);
    $c->NoAller = CriteriaJoin::notExists(Allergy_0038::asHepa());
    return $c;
  }
  static function asNum8b($ugid, $from, $to, $userId) {
    $c = static::asPop($ugid, $from, $to, $userId);
    $c->Diag = CriteriaJoin::requires(Diag_0038::asHepa());
    return $c;
  }
  static function asNum9($ugid, $from, $to, $userId) {
    $c = static::asPop($ugid, $from, $to, $userId);
    $c->Immun = CriteriaJoin::requiresCountGreaterThan(Immun_0038::asRota(), 1);
    $c->NoAller = CriteriaJoin::notExists(Allergy_0038::asRota());
    return $c;
  }
  static function asNum10($ugid, $from, $to, $userId) {
    $c = static::asPop($ugid, $from, $to, $userId);
    $c->Immun = CriteriaJoin::requiresCountGreaterThan(Immun_0038::asFlu(), 1);
    $c->NoDiag = CriteriaJoin::notExists(Diag_0038::asCancers());
    $c->NoAller = CriteriaJoin::notExists(Allergy_0038::asFlu());
    return $c;
  }
}
class Proc_0038 extends Proc_Cms {
  static function asOfficeVisit($from, $to, $userId) {
    $c = static::fromIpcs('600186','600208');
    $c->setDates($from, $to);
    $c->setUserId($userId);
    return $c;
  }
}
class Immun_0038 extends Immun_Cms {
  static function from($names, $t = 2, $days) {
    $c = new static();
    $c->name = CriteriaValues::_orArray(static::cvNames($names));
    $c->Hd_dateGiven = Hdata_ImmunDate::join();
    //$c->dataImmunId = CriteriaValue::sql("DATEDIFF(t0.birth,t$t.date_given) BETWEEN -730 AND $days");
    $t = $t * 2 + 1;
    $c->dataImmunId = CriteriaValue::sql("(t1.d-t$t.d)/86400 between -830 and -42");
    return $c;
  }
  static function from42($names, $t = 2) {
    return static::from($names, $t, '-42');
  }
  static function from0($names, $t = 2) {
    return static::from($names, $t, '0');
  }
  static function asDtap($t = 2) {
    return static::from42(array('DTaP'), $t);
  }
  static function asHib($t = 2) {
    return static::from42(array('HiB','PedvaxHIB','ActHIB','Hiberix'), $t);
  }
  static function asIpv($t = 2) {
    return static::from42(array('IPV'), $t);
  }
  static function asVzv($t = 2) {
    return static::from0(array('Varicella','Varivax'), $t);
  }
  static function asPneum($t = 2) {
    return static::from0(array('Pneumococcal','Pneumovax','Prevnar'), $t);
  }
  static function asMmr($t = 2) {
    return static::from0(array('MMR'), $t);
  }
  static function asMumps($t = 2) {
    return static::from0(array('Mumps','Mumpsvax'), $t);
  }
  static function asMeasles($t = 2) {
    return static::from0(array('Measles','Attenuvax'), $t);
  }
  static function asRubella($t = 2) {
    return static::from0(array('Rubella','Meruvax'), $t);
  }
  static function asHepb($t = 2) {
    return static::from0(array('Hep B','Hepatitis B','HepB','Recombivax','Energix'), $t);
  }
  static function asHepa($t = 2) {
    return static::from0(array('Hep A','Hepatitis A','HepA','Havrix','Vaqta'), $t);
  }
  static function asRota($t = 2) {
    return static::from42(array('Rotavirus','Rotarix','Rotateq'), $t);
  }
  static function asFlu($t = 2) {
    return static::from(array('Influenza','Afluria','Flulaval','Flumist','Fluvirin','Agriflu','Fluzone'), $t, '-180');
  }
}
class Allergy_0038 extends Allergy_Cms {
  static function asDtap() {
    $c = static::asActive();
    $c->agent = CriteriaValues::_or(
      CriteriaValue::contains('DTap'),
      CriteriaValue::contains('DTp'),
      CriteriaValue::contains('Tripedia'),
      CriteriaValue::contains('Infanrix'),
      CriteriaValue::contains('Daptacel'));
    return $c;
  }
  static function asIpv() {
    $c = static::asActive();
    $c->agent = CriteriaValues::_or(
      CriteriaValue::contains('IPV'),
      CriteriaValue::contains('Polio Vaccine'),
      CriteriaValue::contains('IPOL'),
      CriteriaValue::contains('Neomycin'),
      CriteriaValue::contains('Streptomycin'),
      CriteriaValue::contains('Polymyxin'));
    return $c;
  }
  static function asHepa() {
    $c = static::asActive();
    $c->agent = CriteriaValues::_or(
      CriteriaValue::contains('Hep A'),
      CriteriaValue::contains('Hepatitis A'),
      CriteriaValue::contains('HepA'),
      CriteriaValue::contains('Havrix'),
      CriteriaValue::contains('Vaqtz'));
    return $c;
  }
  static function asHepb() {
    $c = static::asActive();
    $c->agent = CriteriaValues::_or(
      CriteriaValue::contains('Hep B'),
      CriteriaValue::contains('Hepatitis B'),
      CriteriaValue::contains('HepB'),
      CriteriaValue::contains('Recombivax'),
      CriteriaValue::contains('Energix'),
      CriteriaValue::contains('Bakers Yeast'),
      CriteriaValue::contains("Baker's Yeast"));
    return $c;
  }
  static function asPneum() {
    $c = static::asActive();
    $c->agent = CriteriaValues::_or(
      CriteriaValue::contains('Pneumococcal'),
      CriteriaValue::contains('Pneumovax'),
      CriteriaValue::contains('Prevnar'));
    return $c;
  }
  static function asMmr() {
    $c = static::asActive();
    $c->agent = CriteriaValue::contains('MMR');
    return $c;
  }
  static function asMumps() {
    $c = static::asActive();
    $c->agent = CriteriaValue::contains('Mumps');
    return $c;
  }
  static function asMeasles() {
    $c = static::asActive();
    $c->agent = CriteriaValues::_or(
      CriteriaValue::contains('Measles'),
      CriteriaValue::contains('Attenuvax'));
    return $c;
  }
  static function asRubella() {
    $c = static::asActive();
    $c->agent = CriteriaValues::_or(
      CriteriaValue::contains('Rubella'),
      CriteriaValue::contains('Meruvax'));
    return $c;
  }
  static function asVzv() {
    $c = static::asActive();
    $c->agent = CriteriaValues::_or(
      CriteriaValue::contains('Varicella'),
      CriteriaValue::contains('Varivax'));
    return $c;
  }
  static function asHib() {
    $c = static::asActive();
    $c->agent = CriteriaValues::_or(
      CriteriaValue::contains('HIB'),
      CriteriaValue::contains('Hib'));
    return $c;
  }
  static function asRota() {
    $c = static::asActive();
    $c->agent = CriteriaValues::_or(
      CriteriaValue::contains('Rotavirus'),
      CriteriaValue::contains('Rotarix'),
      CriteriaValue::contains('Rotateq'));
    return $c;
  }
  static function asFlu() {
    $c = static::asActive();
    $c->agent = CriteriaValues::_or(
      CriteriaValue::contains('Influenza'),
      CriteriaValue::contains('Afluria'),
      CriteriaValue::contains('Flulaval'),
      CriteriaValue::contains('Flumist'),
      CriteriaValue::contains('Fluvirin'),
      CriteriaValue::contains('Agriflu'),
      CriteriaValue::contains('Fluzone'));
    return $c;
  }
}
class Diag_0038 extends Diag_Cms { 
  static function asEnceph() {
    $c = static::asActive();
    $c->icd = '323.51';
    return $c;
  }
  static function asProgNuero() {
    $c = static::asActive();
    $c->text = CriteriaValue::contains('Progressive Neuronal Degeneration of Childhood');
    return $c;
  }
  static function asCancers() {
    $c = static::asActive();
    $c->icd = CriteriaValues::_orArray(
      static::cvIcdStarts('201','202','203','196','201','202','203','196','042','V08','200','202','204','205','206','207','208','200','202','204','205','206','207','208','279'));
    return $c;
  }
  static function asVaricella() {
    $c = static::asResolved();
    $c->icd = CriteriaValue::in(array('052', '053')); 
    return $c;
  }
  static function asMeasles() {
    $c = static::asResolved();
    $c->icd = '055';
    return $c;
  }
  static function asMumps() {
    $c = static::asResolved();
    $c->icd = '072';
    return $c;
  }
  static function asRubella() {
    $c = static::asResolved();
    $c->icd = '056';
    return $c;
  }
  static function asHepa() {
    $c = static::asResolved();
    $c->icd = CriteriaValue::in(array('070.0', '070.1'));
    return $c;
  }
  static function asHepb() {
    $c = static::asResolved();
    $c->icd = CriteriaValue::in(array('070.20','070.2','070.3','070.30','V020.61'));
    return $c;
  }
}
/**
 * NQF 0024
 * Weight Assessment and Counseling for Children and Adolescents
 */
abstract class CmsReport_NQF0024 extends CmsReport {
  //
  public $measureNumber = 'NQF 0024';
}
class CmsReport_NQF0024_P1N1 extends CmsReport_NQF0024 {
  //
  public $popNumber = 1;  
  public $numNumber = 1;  
  //
  public function fetchPop($ugid, $from, $to, $userId) { 
    return $this->fetchAllBy(
      Client_0024::asPop1($ugid, $from, $to, $userId));
  }
  public function fetchNum($ugid, $from, $to, $userId) {
    return $this->fetchAllBy(
      Client_0024::asP1Num1($ugid, $from, $to, $userId));
  }
}
class CmsReport_NQF0024_P1N2 extends CmsReport_NQF0024 {
  //
  public $popNumber = 1;  
  public $numNumber = 2;  
  //
  public function fetchPop($ugid, $from, $to, $userId) { 
    return $this->fetchAllBy(
      Client_0024::asPop1($ugid, $from, $to, $userId));
  }
  public function fetchNum($ugid, $from, $to, $userId) {
    return $this->fetchAllBy(
      Client_0024::asP1Num2($ugid, $from, $to, $userId));
  }
}
class CmsReport_NQF0024_P1N3 extends CmsReport_NQF0024 {
  //
  public $popNumber = 1;  
  public $numNumber = 3;  
  //
  public function fetchPop($ugid, $from, $to, $userId) { 
    return $this->fetchAllBy(
      Client_0024::asPop1($ugid, $from, $to, $userId));
  }
  public function fetchNum($ugid, $from, $to, $userId) {
    return $this->fetchAllBy(
      Client_0024::asP1Num3($ugid, $from, $to, $userId));
  }
}
class CmsReport_NQF0024_P2N1 extends CmsReport_NQF0024 {
  //
  public $popNumber = 2;  
  public $numNumber = 1;  
  //
  public function fetchPop($ugid, $from, $to, $userId) { 
    return $this->fetchAllBy(
      Client_0024::asPop2($ugid, $from, $to, $userId));
  }
  public function fetchNum($ugid, $from, $to, $userId) {
    return $this->fetchAllBy(
      Client_0024::asP2Num1($ugid, $from, $to, $userId));
  }
}
class CmsReport_NQF0024_P2N2 extends CmsReport_NQF0024 {
  //
  public $popNumber = 2;  
  public $numNumber = 2;  
  //
  public function fetchPop($ugid, $from, $to, $userId) { 
    return $this->fetchAllBy(
      Client_0024::asPop2($ugid, $from, $to, $userId));
  }
  public function fetchNum($ugid, $from, $to, $userId) {
    return $this->fetchAllBy(
      Client_0024::asP2Num2($ugid, $from, $to, $userId));
  }
}
class CmsReport_NQF0024_P2N3 extends CmsReport_NQF0024 {
  //
  public $popNumber = 2;  
  public $numNumber = 3;  
  //
  public function fetchPop($ugid, $from, $to, $userId) { 
    return $this->fetchAllBy(
      Client_0024::asPop2($ugid, $from, $to, $userId));
  }
  public function fetchNum($ugid, $from, $to, $userId) {
    return $this->fetchAllBy(
      Client_0024::asP2Num3($ugid, $from, $to, $userId));
  }
}
class CmsReport_NQF0024_P3N1 extends CmsReport_NQF0024 {
  //
  public $popNumber = 3;  
  public $numNumber = 1;  
  //
  public function fetchPop($ugid, $from, $to, $userId) { 
    return $this->fetchAllBy(
      Client_0024::asPop3($ugid, $from, $to, $userId));
  }
  public function fetchNum($ugid, $from, $to, $userId) {
    return $this->fetchAllBy(
      Client_0024::asP3Num1($ugid, $from, $to, $userId));
  }
}
class CmsReport_NQF0024_P3N2 extends CmsReport_NQF0024 {
  //
  public $popNumber = 3;  
  public $numNumber = 2;  
  //
  public function fetchPop($ugid, $from, $to, $userId) { 
    return $this->fetchAllBy(
      Client_0024::asPop3($ugid, $from, $to, $userId));
  }
  public function fetchNum($ugid, $from, $to, $userId) {
    return $this->fetchAllBy(
      Client_0024::asP3Num2($ugid, $from, $to, $userId));
  }
}
class CmsReport_NQF0024_P3N3 extends CmsReport_NQF0024 {
  //
  public $popNumber = 3;  
  public $numNumber = 3;  
  //
  public function fetchPop($ugid, $from, $to, $userId) { 
    return $this->fetchAllBy(
      Client_0024::asPop3($ugid, $from, $to, $userId));
  }
  public function fetchNum($ugid, $from, $to, $userId) {
    return $this->fetchAllBy(
      Client_0024::asP3Num3($ugid, $from, $to, $userId));
  }
}
class Client_0024 extends Client_Cms {
  static function asPop1($ugid, $from, $to, $userId) {
    $c = parent::asPop($ugid, $from, $to, 2, 16);
    $c->Proc = CriteriaJoin::requires(Proc_0024::asOfficeVisit($from, $to, $userId));
    $c->Diag = CriteriaJoin::notExists(Diag_0024::asPregnant($from, $to));
    return $c;
  }
  static function asPop2($ugid, $from, $to, $userId) {
    $c = parent::asPop($ugid, $from, $to, 2, 10);
    $c->Proc = CriteriaJoin::requires(Proc_0024::asOfficeVisit($from, $to, $userId));
    $c->Diag = CriteriaJoin::notExists(Diag_0024::asPregnant($from, $to));
    return $c;
  }
  static function asPop3($ugid, $from, $to, $userId) {
    $c = parent::asPop($ugid, $from, $to, 11, 16);
    $c->Proc = CriteriaJoin::requires(Proc_0024::asOfficeVisit($from, $to, $userId));
    $c->Diag = CriteriaJoin::notExists(Diag_0024::asPregnant($from, $to));
    return $c;
  }
  static function asP1Num1($ugid, $from, $to, $userId) {
    $c = static::asPop1($ugid, $from, $to, $userId);
    $c->Num = CriteriaJoin::requires(Proc_0024::asBmiEval($from, $to));
    return $c;
  }
  static function asP2Num1($ugid, $from, $to, $userId) {
    $c = static::asPop2($ugid, $from, $to, $userId);
    $c->Num = CriteriaJoin::requires(Proc_0024::asBmiEval($from, $to));
    return $c;
  }
  static function asP3Num1($ugid, $from, $to, $userId) {
    $c = static::asPop3($ugid, $from, $to, $userId);
    $c->Num = CriteriaJoin::requires(Proc_0024::asBmiEval($from, $to));
    return $c;
  }
  static function asP1Num2($ugid, $from, $to, $userId) {
    $c = static::asPop1($ugid, $from, $to, $userId);
    $c->Num = CriteriaJoin::requires(Proc_0024::asNutritionCounsel($from, $to));
    return $c;
  }
  static function asP2Num2($ugid, $from, $to, $userId) {
    $c = static::asPop2($ugid, $from, $to, $userId);
    $c->Num = CriteriaJoin::requires(Proc_0024::asNutritionCounsel($from, $to));
    return $c;
  }
  static function asP3Num2($ugid, $from, $to, $userId) {
    $c = static::asPop3($ugid, $from, $to, $userId);
    $c->Num = CriteriaJoin::requires(Proc_0024::asNutritionCounsel($from, $to));
    return $c;
  }
  static function asP1Num3($ugid, $from, $to, $userId) {
    $c = static::asPop1($ugid, $from, $to, $userId);
    $c->Num = CriteriaJoin::requires(Proc_0024::asPhysActCounsel($from, $to));
    return $c;
  }
  static function asP2Num3($ugid, $from, $to, $userId) {
    $c = static::asPop2($ugid, $from, $to, $userId);
    $c->Num = CriteriaJoin::requires(Proc_0024::asPhysActCounsel($from, $to));
    return $c;
  }
  static function asP3Num3($ugid, $from, $to, $userId) {
    $c = static::asPop3($ugid, $from, $to, $userId);
    $c->Num = CriteriaJoin::requires(Proc_0024::asPhysActCounsel($from, $to));
    return $c;
  }
}
class Proc_0024 extends Proc_Cms {
  static function asOfficeVisit($from, $to, $userId) {
    return static::from('600186', $from, $to, $userId);
  }
  static function asBmiEval($from, $to) {
    return static::from('600209', $from, $to);
  }
  static function asNutritionCounsel($from, $to) {
    return static::from('600127', $from, $to);
  }
  static function asPhysActCounsel($from, $to) {
    return static::from('600128', $from, $to);
  }
}
class Diag_0024 extends Diag_Cms {
  static function asPregnant($from, $to) {
    $c = self::asActive();
    $c->icd = CriteriaValues::_orArray(array_merge(array(
      static::cvIcds('V61.6','V61.7','V72.42'),
      static::cvIcds('V24','V24.0','V24.2','V25','V25.01','V25.02','V25.03','V25.09','V26.81','V28','V28.3','V28.81','V28.82','V72.4','V72.40','V72.41','V72.42')),
      static::cvIcdStarts('645','V22','633','V23','639','677','651','761','640','643','671','646','642','649','760')));
    return $c;
  }
}
/**
 * NQF 0041
 * Preventive Care and Screening: Influenza Immunization for Patients >= 50 Years Old
 */
class CmsReport_NQF0041 extends CmsReport {
  //
  public $measureNumber = 'NQF 0041';
  //
  public function calculate($ugid, $from, $to, $userId) {
    $from = pastDate(0, 0, 122, $from);
    $to = futureDate(58, 0, 0, $to);
    parent::calculate($ugid, $from, $to, $userId);
  }
  public function fetchPop($ugid, $from, $to, $userId) { 
    return $this->fetchMerge(
      Client_0041::asPopa($ugid, $from, $to, $userId),
      Client_0041::asPopb($ugid, $from, $to, $userId));
  }
  public function fetchNum($ugid, $from, $to, $userId) {
    return $this->fetchMerge(
      Client_0041::asNuma($ugid, $from, $to, $userId),
      Client_0041::asNumb($ugid, $from, $to, $userId));
  }
  public function fetchExc($ugid, $from, $to, $userId) {
    return $this->fetchMerge(
      Client_0041::asExc1a1($ugid, $from, $to, $userId),
      Client_0041::asExc1a2($ugid, $from, $to, $userId),
      Client_0041::asExc1a3($ugid, $from, $to, $userId),
      Client_0041::asExc1b1($ugid, $from, $to, $userId),
      Client_0041::asExc1b2($ugid, $from, $to, $userId),
      Client_0041::asExc1b3($ugid, $from, $to, $userId));
  }
}
class Client_0041 extends Client_Cms {
  static function asPopa($ugid, $from, $to, $userId) {
    $c = parent::asPop($ugid, $from, $to, 50, null);
    $c->Pop = CriteriaJoin::requiresCountGreaterThan(Proc_0041::asOfficeVisit($from, $to, $userId), 1);
    return $c;
  }
  static function asPopb($ugid, $from, $to, $userId) {
    $c = parent::asPop($ugid, $from, $to, 50, null);
    $c->Pop = CriteriaJoin::requiresCountGreaterThan(Proc_0041::asPreventative($from, $to), 0);
    return $c;
  }
  static function asNuma($ugid, $from, $to, $userId) {
    $c = static::asPopa($ugid, $from, $to, $userId);
    $c->Num = CriteriaJoin::requiresCountGreaterThan(Immun_0041::asInfluenza($from, $to));
    return $c;
  }
  static function asNumb($ugid, $from, $to, $userId) {
    $c = static::asPopb($ugid, $from, $to, $userId);
    $c->Num = CriteriaJoin::requiresCountGreaterThan(Immun_0041::asInfluenza($from, $to));
    return $c;
  }
  static function asExc1a1($ugid, $from, $to, $userId) {
    $c = static::asPopa($ugid, $from, $to, $userId);
    $c->Exc1a = CriteriaJoin::requiresCountGreaterThan(Allergy_0041::asEgg());
    return $c;
  }
  static function asExc1a2($ugid, $from, $to, $userId) {
    $c = static::asPopa($ugid, $from, $to, $userId);
    $c->Exc1b = CriteriaJoin::requiresCountGreaterThan(Diag_0041::asEgg());
    return $c;
  }
  static function asExc1a3($ugid, $from, $to, $userId) {
    $c = static::asPopa($ugid, $from, $to, $userId);
    $c->Exc1c = CriteriaJoin::requiresCountGreaterThan(Proc_0041::asNotGivenReason($from, $to));
    return $c;
  }
  static function asExc1b1($ugid, $from, $to, $userId) {
    $c = static::asPopb($ugid, $from, $to, $userId);
    $c->Exc1a = CriteriaJoin::requiresCountGreaterThan(Allergy_0041::asEgg());
    return $c;
  }
  static function asExc1b2($ugid, $from, $to, $userId) {
    $c = static::asPopb($ugid, $from, $to, $userId);
    $c->Exc1b = CriteriaJoin::requiresCountGreaterThan(Diag_0041::asEgg());
    return $c;
  }
  static function asExc1b3($ugid, $from, $to, $userId) {
    $c = static::asPopb($ugid, $from, $to, $userId);
    $c->Exc1c = CriteriaJoin::requiresCountGreaterThan(Proc_0041::asNotGivenReason($from, $to));
    return $c;
  }
}
class Proc_0041 extends Proc_Cms {
  static function asOfficeVisit($from, $to, $userId) {
    $c = self::fromIpcs('600186');
    $c->Hd_date = Hdata_ProcDate::join(CriteriaValue::betweenDates(array($from, $to)));
    $c->setUserId($userId);
    return $c;
  }
  static function asPreventative($from, $to) {
    $c = self::fromIpcs('600202','600199','600201','600203','600187','600200');
    $c->Hd_date = Hdata_ProcDate::join(CriteriaValue::betweenDates(array($from, $to)));
    return $c;
  }
  static function asNotGivenReason($from, $to) {
    $c = self::fromIpcs('600206','600205','600207');
    $c->Hd_date = Hdata_ProcDate::join(CriteriaValue::betweenDates(array($from, $to)));
    return $c;
  }
}
class Immun_0041 extends Immun_Cms {
  static function asInfluenza($from, $to) {
    $c = new static();
    $c->name = 'Influenza';
    $c->Hd_dateGiven = Hdata_ImmunDate::join(CriteriaValue::betweenDates(array($from, $to)));
    return $c;
  }
}
class Allergy_0041 extends Allergy_Cms {
  static function asEgg() {
    $c = static::asActive();
    $c->agent = CriteriaValue::startsWith('egg');
    return $c;
  }
}
class Diag_0041 extends Diag_CMS {
  static function asEgg() {
    $c = static::asActive();
    $c->icd = 'V15.03';
    return $c;
  }
}
/**
 * NQF 0028a and b
 * Preventive Care and Screening Measure Pair: a. Tobacco Use Assessment, b. Tobacco Cessation
 */
class CmsReport_NQF0028a extends CmsReport {
  //
  public $measureNumber = 'NQF 0028a';
  //
  public function fetchPop($ugid, $from, $to, $userId) {
    return $this->fetchMerge(
      Client_0028::asPopa1($ugid, $from, $to, $userId),
      Client_0028::asPopa2($ugid, $from, $to, $userId)); 
  }
  public function fetchNum($ugid, $from, $to, $userId) {
    return $this->fetchMerge(
      Client_0028::asNuma1($ugid, $from, $to, $userId),
      Client_0028::asNuma2($ugid, $from, $to, $userId)); 
  }
}
class CmsReport_NQF0028b extends CmsReport {
  //
  public $measureNumber = 'NQF 0028b';
  //
  public function fetchPop($ugid, $from, $to, $userId) { 
    return $this->fetchMerge(
      Client_0028::asPopb1($ugid, $from, $to, $userId),
      Client_0028::asPopb2($ugid, $from, $to, $userId)); 
  }
  public function fetchNum($ugid, $from, $to, $userId) {
    return $this->fetchMerge(
      Client_0028::asNumb1a($ugid, $from, $to, $userId),
      Client_0028::asNumb2a($ugid, $from, $to, $userId),
      Client_0028::asNumb1b($ugid, $from, $to, $userId),
      Client_0028::asNumb2b($ugid, $from, $to, $userId)); 
  }
}
class Client_0028 extends Client_Cms {
  static function asPopa1($ugid, $from, $to, $userId) {
    $c = parent::asPop($ugid, $from, $to, 18, null);
    $c->Proc = CriteriaJoin::requiresCountGreaterThan(Proc_0028::asOfficeVisit($from, $to, $userId), 1);
    return $c;
  }
  static function asPopa2($ugid, $from, $to, $userId) {
    $c = parent::asPop($ugid, $from, $to, 18, null);
    $c->Proc = CriteriaJoin::requiresCountGreaterThan(Proc_0028::asCounselingVisit($from, $to, $userId));
    return $c;
  }
  static function asNuma1($ugid, $from, $to, $userId) {
    $c = static::asPopa1($ugid, $from, $to, $userId);
    $c->Proc2 = CriteriaJoin::requiresCountGreaterThan(array(
      Proc_0028::asUseInquired($from, $to),
      Proc_0028::asSmokingHxRecorded($from, $to)));
    return $c;
  }
  static function asNuma2($ugid, $from, $to, $userId) {
    $c = static::asPopa2($ugid, $from, $to, $userId);
    $c->Proc2 = CriteriaJoin::requiresCountGreaterThan(array(
      Proc_0028::asUseInquired($from, $to),
      Proc_0028::asSmokingHxRecorded($from, $to)));
    return $c;
  }
  static function asPopb1($ugid, $from, $to, $userId) {
    return static::asPopa1($ugid, $from, $to, $userId);
  }
  static function asPopb2($ugid, $from, $to, $userId) {
    return static::asPopa2($ugid, $from, $to, $userId);
  }
  static function asNumb1a($ugid, $from, $to, $userId) {
    $c = static::asPopb1($ugid, $from, $to, $userId);
    $c->Proc2 = CriteriaJoin::requiresCountGreaterThan(Proc_0028::asCessationCounseled($from, $to));
    return $c;
  }
  static function asNumb1b($ugid, $from, $to, $userId) {
    $c = static::asPopb1($ugid, $from, $to, $userId);
    $c->Med = CriteriaJoin::requiresCountGreaterThan(Med_0028::asSmokingCessation($from, $to));
    return $c;
  }
  static function asNumb2a($ugid, $from, $to, $userId) {
    $c = static::asPopb2($ugid, $from, $to, $userId);
    $c->Proc2 = CriteriaJoin::requiresCountGreaterThan(Proc_0028::asCessationCounseled($from, $to));
    return $c;
  }
  static function asNumb2b($ugid, $from, $to, $userId) {
    $c = static::asPopb2($ugid, $from, $to, $userId);
    $c->Med = CriteriaJoin::requiresCountGreaterThan(Med_0028::asSmokingCessation($from, $to));
    return $c;
  }
}
class Med_0028 extends Med_Cms {
  static function asSmokingCessation($from, $to) {
    $c = parent::asSmokingCessation();
    $c->Hd_date = Hdata_MedDate::join(CriteriaValue::betweenAge(array(0, 2), $from, $to));
    return $c;
  }
}
class Proc_0028 extends Proc_Cms {
  static function asOfficeVisit($from, $to, $userId) {
    $c = new static();
    $c->ipc = '600186';
    $c->Hd_date = Hdata_ProcDate::join(CriteriaValue::betweenAge(array(0, 2), $from, $to));
    $c->setUserId($userId);
    return $c;
  }
  static function asCounselingVisit($from, $to, $userId) {
    $c = new static();
    $c->ipc = CriteriaValue::in(array('600230','600202','600201','600199','600203'));
    $c->Hd_date = Hdata_ProcDate::join(CriteriaValue::betweenAge(array(0, 2), $from, $to));
    $c->setUserId($userId);
    return $c;
  }
  static function asUseInquired($from, $to) {
    $c = static::from('600079');
    $c->Hd_date = Hdata_ProcDate::join(CriteriaValue::betweenAge(array(0, 2), $from, $to));
    return $c;
  }
  static function asSmokingHxRecorded($from, $to) {
    $c = static::from('600084');
    $c->Hd_date = Hdata_ProcDate::join(CriteriaValue::betweenAge(array(0, 2), $from, $to));
    return $c;
  }
  static function asCessationCounseled($from, $to) {
    $c = new static();  //static::from('600004');
    $c->ipc = CriteriaValue::in(array('600004','600921','600922'));
    $c->Hd_date = Hdata_ProcDate::join(CriteriaValue::betweenAge(array(0, 2), $from, $to));
    return $c;
  }
}
/**
 * NQF 0421
 * Adult Weight Screening and Follow-Up
 */
abstract class CmsReport_NQF0421 extends CmsReport {
  //
  public $measureNumber = 'NQF 0421';
}
class CmsReport_NQF0421_P1 extends CmsReport_NQF0421 {
  //
  public $popNumber = 1;
  //
  public function fetchPop($ugid, $from, $to, $userId) {
    logit_r(func_get_args(), __METHOD__); 
    return $this->fetchAllBy(
      Client_0421::asPop1($ugid, $from, $to, $userId));
  }
  public function fetchNum($ugid, $from, $to, $userId) {
    logit_r(func_get_args(), __METHOD__); 
    return $this->fetchMerge(
      Client_0421::asNum1a($ugid, $from, $to, $userId),
      Client_0421::asNum1b($ugid, $from, $to, $userId),
      Client_0421::asNum1c($ugid, $from, $to, $userId));
  }
  public function fetchExc($ugid, $from, $to, $userId) {
    logit_r(func_get_args(), __METHOD__); 
    return $this->fetchMerge(
      Client_0421::asExc1a($ugid, $from, $to, $userId),
      Client_0421::asExc1b($ugid, $from, $to, $userId),
      Client_0421::asExc1c($ugid, $from, $to, $userId),
      Client_0421::asExc1d($ugid, $from, $to, $userId),
      Client_0421::asExc1e($ugid, $from, $to, $userId));
  }
}
class CmsReport_NQF0421_P2 extends CmsReport_NQF0421 {
  //
  public $popNumber = 2;
  //
  public function fetchPop($ugid, $from, $to, $userId) { 
    logit_r(func_get_args(), __METHOD__); 
    return $this->fetchAllBy(
      Client_0421::asPop2($ugid, $from, $to, $userId));
  }
  public function fetchNum($ugid, $from, $to, $userId) {
    logit_r(func_get_args(), __METHOD__); 
    return $this->fetchMerge(
      Client_0421::asNum2a($ugid, $from, $to, $userId),
      Client_0421::asNum2b($ugid, $from, $to, $userId),
      Client_0421::asNum2c($ugid, $from, $to, $userId));
  }
  public function fetchExc($ugid, $from, $to, $userId) {
    logit_r(func_get_args(), __METHOD__); 
    return $this->fetchMerge(
      Client_0421::asExc2a($ugid, $from, $to, $userId),
      Client_0421::asExc2b($ugid, $from, $to, $userId),
      Client_0421::asExc2c($ugid, $from, $to, $userId),
      Client_0421::asExc2d($ugid, $from, $to, $userId),
      Client_0421::asExc2e($ugid, $from, $to, $userId));
  }
}
class Client_0421 extends Client_Cms {
  static function asPop1($ugid, $from, $to, $userId) {
    $c = parent::asPop($ugid, $from, $to, 65, null);
    $c->Proc = CriteriaJoin::requiresCountGreaterThan(Proc_0421::asTherapyCounsel($from, $to), 1);
    return $c;
  }
  static function asPop2($ugid, $from, $to, $userId) {
    $c = parent::asPop($ugid, $from, $to, 18, 64);
    $c->Proc = CriteriaJoin::requiresCountGreaterThan(Proc_0421::asTherapyCounsel($from, $to), 1);
    return $c;
  }
  static function asNum1a($ugid, $from, $to, $userId) {
    $c = static::asPop1($ugid, $from, $to, $userId);
    $c->Vital = CriteriaJoin::requiresCountGreaterThan(Vital_0421::asBmiNormal_mpast('22' ,'30'));
    return $c;
  }
  static function asNum2a($ugid, $from, $to, $userId) {
    $c = static::asPop2($ugid, $from, $to, $userId);
    $c->Vital = CriteriaJoin::requiresCountGreaterThan(Vital_0421::asBmiNormal_mpast('18.5' ,'25'));
    return $c;
  }
  static function asNum1b($ugid, $from, $to, $userId) {
    $c = static::asPop1($ugid, $from, $to, $userId);
    $c->Vital = CriteriaJoin::requiresCountGreaterThan(Vital_0421::asBmiHigh_mpast('30'));
    $c->VProc = CriteriaJoin::requiresCountGreaterThan(Proc_0421::asWeightCounsel());
    return $c;
  }
  static function asNum2b($ugid, $from, $to, $userId) {
    $c = static::asPop2($ugid, $from, $to, $userId);
    $c->Vital = CriteriaJoin::requiresCountGreaterThan(Vital_0421::asBmiHigh_mpast('25'));
    $c->VProc = CriteriaJoin::requiresCountGreaterThan(Proc_0421::asWeightCounsel());
    return $c;
  }
  static function asNum1c($ugid, $from, $to, $userId) {
    $c = static::asPop1($ugid, $from, $to, $userId);
    $c->Vital = CriteriaJoin::requiresCountGreaterThan(Vital_0421::asBmiLow_mpast('22'));
    $c->VProc = CriteriaJoin::requiresCountGreaterThan(Proc_0421::asWeightCounsel());
    return $c;
  }
  static function asNum2c($ugid, $from, $to, $userId) {
    $c = static::asPop2($ugid, $from, $to, $userId);
    $c->Vital = CriteriaJoin::requiresCountGreaterThan(Vital_0421::asBmiLow_mpast('18.5'));
    $c->VProc = CriteriaJoin::requiresCountGreaterThan(Proc_0421::asWeightCounsel());
    return $c;
  }
  static function asExc1a($ugid, $from, $to, $userId) {
    $c = static::asPop1($ugid, $from, $to, $userId);
    $c->Proc2 = CriteriaJoin::requiresCountGreaterThan(Proc_0421::asTerminal_mpast());
    return $c;
  }
  static function asExc2a($ugid, $from, $to, $userId) {
    $c = static::asPop2($ugid, $from, $to, $userId);
    $c->Proc2 = CriteriaJoin::requiresCountGreaterThan(Proc_0421::asTerminal_mpast());
    return $c;
  }
  static function asExc1b($ugid, $from, $to, $userId) {
    $c = static::asPop1($ugid, $from, $to, $userId);
    $c->Diag = CriteriaJoin::requiresCountGreaterThan(Diag_0421::asPregnancy());
    return $c;
  }
  static function asExc2b($ugid, $from, $to, $userId) {
    $c = static::asPop2($ugid, $from, $to, $userId);
    $c->Diag = CriteriaJoin::requiresCountGreaterThan(Diag_0421::asPregnancy());
    return $c;
  }
  static function asExc1c($ugid, $from, $to, $userId) {
    $c = static::asPop1($ugid, $from, $to, $userId);
    $c->Proc2 = CriteriaJoin::requiresCountGreaterThan(Proc_0421::asPxReason());
    return $c;
  }
  static function asExc2c($ugid, $from, $to, $userId) {
    $c = static::asPop2($ugid, $from, $to, $userId);
    $c->Proc2 = CriteriaJoin::requiresCountGreaterThan(Proc_0421::asPxReason());
    return $c;
  }
  static function asExc1d($ugid, $from, $to, $userId) {
    $c = static::asPop1($ugid, $from, $to, $userId);
    $c->Proc2 = CriteriaJoin::requiresCountGreaterThan(Proc_0421::asMedReason());
    return $c;
  }
  static function asExc2d($ugid, $from, $to, $userId) {
    $c = static::asPop2($ugid, $from, $to, $userId);
    $c->Proc2 = CriteriaJoin::requiresCountGreaterThan(Proc_0421::asMedReason());
    return $c;
  }
  static function asExc1e($ugid, $from, $to, $userId) {
    $c = static::asPop1($ugid, $from, $to, $userId);
    $c->Proc2 = CriteriaJoin::requiresCountGreaterThan(Proc_0421::asSystemReason());
    return $c;
  }
  static function asExc2e($ugid, $from, $to, $userId) {
    $c = static::asPop2($ugid, $from, $to, $userId);
    $c->Proc2 = CriteriaJoin::requiresCountGreaterThan(Proc_0421::asSystemReason());
    return $c;
  }
}
class Diag_0421 extends Diag_Cms {
  static function asPregnancy() {
    $c = new static();
    $c->icd = CriteriaValues::_or(
      CriteriaValue::startsWith('645'),
      CriteriaValue::startsWith('V22'),
      CriteriaValue::startsWith('633'),
      CriteriaValue::startsWith('V23'),
      CriteriaValue::startsWith('639'),
      CriteriaValue::startsWith('677'),
      CriteriaValue::startsWith('651'),
      CriteriaValue::startsWith('761'),
      CriteriaValue::startsWith('640'),
      CriteriaValue::startsWith('643'),
      CriteriaValue::startsWith('671'),
      CriteriaValue::startsWith('646'),
      CriteriaValue::startsWith('642'),
      CriteriaValue::startsWith('649'),
      CriteriaValue::startsWith('760'),
      CriteriaValue::in(array('V61.6','V61.7','V72.42')));
    return $c;
  }
}
class Proc_0421 extends Proc_Cms {
  static function asTherapyCounsel($from, $to) {
    $c = new static();
    $c->ipc = CriteriaValue::in(array('600186','600192','600190','600127','600191'));
    $c->Hd_date = Hdata_ProcDate::join(CriteriaValue::betweenDates(array($from, $to)));
    return $c;
  }
  static function asWeightCounsel() {
    $c = new static();
    $c->ipc = CriteriaValue::in(array('600193', '600005', '600194'));  
    return $c;
  }
  static function asTerminal_mpast() {
    $c = self::from('600195');
    $c->Hd_date = Hdata_ProcDate::join();
    $c->procId = CmsReport::monthsPast();
    return $c;
  }
  static function asPxReason() {
    $c = new static();
    $c->ipc = '600196';
    return $c;
  }
  static function asMedReason() {
    $c = new static();
    $c->ipc = '600197';
    return $c;
  }
  static function asSystemReason() {
    $c = new static();
    $c->ipc = '600198';
    return $c;
  }
} 
class Vital_0421 extends Vital_Cms {
  static function asBmiNormal_mpast($low, $high) {
    $c = static::asActive();
    $c->bmi = CriteriaValues::_and(
      CriteriaValue::greaterThanOrEqualsNumeric($low), 
      CriteriaValue::lessThanNumeric($high));
    $c->dataVitalsId = CmsReport::monthsPast();
    return $c;
  }
  static function asBmiHigh_mpast($bmi) {
    $c = static::asActive();
    $c->bmi = CriteriaValue::greaterThanOrEqualsNumeric($bmi);
    $c->dataVitalsId = CmsReport::monthsPast();
    return $c;
  }
  static function asBmiLow_mpast($bmi) {
    $c = static::asActive();
    $c->bmi = CriteriaValue::lessThanOrEqualsNumeric($bmi);
    $c->dataVitalsId = CmsReport::monthsPast();
    return $c;
  }
}
/**
 * NQF 0013
 * Hypertension: Blood Pressure Measurement
 */
class CmsReport_NQF0013 extends CmsReport {
  //
  public $measureNumber = 'NQF 0013';
  //
  public function fetchPop($ugid, $from, $to, $userId) { 
    $c = Client_0013::asPop($ugid, $from, $to, $userId);
    return $this->fetchAllBy($c);
  }
  public function fetchNum($ugid, $from, $to, $userId) {
    $c = Client_0013::asNum($ugid, $from, $to, $userId);
    return $this->fetchAllBy($c);
  }
}
class Client_0013 extends Client_Cms {
  static function asPop($ugid, $from, $to, $userId) {
    $c = new static();
    $c->userGroupId = $ugid;
    $c->Hd_birth = Hdata_ClientDob::join(CriteriaValue::betweenAge(array(18, null), $from, $to));
    $c->Diag = CriteriaJoin::requiresCountGreaterThan(Diag_0013::asHypertension());
    $c->Proc = CriteriaJoin::requiresCountGreaterThan(Proc_0013::asOfficeVisit($from, $to, $userId), 1);
    return $c;
  }
  static function asNum($ugid, $from, $to, $userId) {
    $c = Client_0013::asPop($ugid, $from, $to, $userId);
    $c->Vital = CriteriaJoin::requires(Vital_0013::asBpRecorded($from, $to));
    return $c;
  }
}
class Diag_0013 extends Diag_Cms {
  static function asHypertension() {
    $c = new static();
    $c->icd = CriteriaValue::in(array('401.0','401.1','401.9','402.00','402.01','402.10','402.11','402.90','402.91','403.00','403.01','403.10','403.11','403.90','403.91','404.00','404.01','404.02','404.03','404.10','404.11','404.12','404.13','404.90','404.91','404.92','404.93'));
    return $c;
  }
}
class Proc_0013 extends Proc_Cms {
  static function asOfficeVisit($from, $to, $userId) {
    $c = new static();
    $c->ipc = CriteriaValue::in(array('600186','600187'));
    $c->Hd_date = Hdata_ProcDate::join(CriteriaValue::betweenDates(array($from, $to)));
    $c->setUserId($userId);
    return $c;
  }
} 
class Vital_0013 extends Vital_Cms {
  static function asBpRecorded() {
    $c = self::asActive();
    $c->bpSystolic = CriteriaValue::greaterThanNumeric(0);
    $c->bpDiastolic = CriteriaValue::greaterThanNumeric(0);
    return $c;
  }
}
