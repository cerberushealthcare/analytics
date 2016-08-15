<?php
require_once 'Immun_Scheds_All.php';
require_once 'php/data/Html.php';
//
/**
 * Immunization Schedules
 * @author Warren Hornsby
 */
class ImmunScheds {
  //
  public /*ImmunChart*/$chart;
  public /*ImmunSched[]*/$ischeds;
  public /*IS_Record[vcat]*/$isrecs;
  //
  static function from(/*ImmunChart*/$chart) {
    $me = new static();
    $me->chart = $chart;
    $me->ischeds = static::getScheds();
    $me->isrecs = IS_Record::all($me->ischeds, $chart);
    return $me;
  } 
  //
  public function /*IS_Result[]*/testAll() {
    $results = array();
    foreach ($this->ischeds as &$is)
      $this->test($results, $is, $this->chart->Client->birth);
    return $results;
  }
  //
  protected function test(&$results, $is, $dob) {
    $rec = $this->isrecs[$is::$VCAT];
    $results[] = $is->test($rec, $dob);
  }
  protected static function getScheds() {
    return array(
      IS_HepB::create(),
      IS_RV::create(),
      IS_DTP::create(),
      IS_Hib::create(),
      IS_PCV::create(),
      IS_Polio::create(),
      IS_MMR::create(),
      IS_Varicella::create(),
      IS_HepA::create(),
      IS_HPV::create(),
      IS_MCV::create());
  }
}
class IS_Result extends BasicRec {
  //
  public $vcat;
  public /*DoseSched*/$sched;
  public /*IS_Record*/$rec;
  public /*bool*/$completed;
  public /*int*/$count;
  public /*int*/$left;
  public /*Dose*/$due; /*next dose, due now ($next will be null)*/
  public /*Dose*/$next; /*next dose, due later ($due will be null)*/
  public /*string*/$na; /*not applies reason*/
  //
  static function from($vcat, /*DoseSched*/$sched, /*DS_Result*/$result) {
    $left = count($sched->doses) - $result->count;
    return new static($vcat, $sched, $result->rec, $result->completed, $result->count, $left, $result->due, $result->next);
  }
  static function asNotApplies($vcat, /*IS_Record*/$rec, $reason = null) {
    return new static($vcat, null, $rec, false, $rec->count(), null, null, null, $reason);
  }
}
abstract class ImmunSched {
  //
  static $VCAT/*e.g. VCats::HepB_All*/;
  static $DS_CLASS/*DoseSched class, e.g. 'DS_HepB'*/;
  //
  public /*DoseSched[]*/$routines;
  public /*DoseSched[]*/$catchups;
  //
  static function create() {
    $ds = static::$DS_CLASS;
    $me = new static();
    $me->routines = $ds::asRoutines();
    $me->catchups = $ds::asCatchups();
    foreach ($me->routines as &$ds)
      $ds->routine = true;
    return $me; 
  }
  //
  public function /*IS_Result*/test(/*IS_Record*/$rec, $dob) {
    // p_r(static::$VCAT, 'test');
    $reason = $this->isNotApplicable($rec);
    if ($reason) 
      return IS_Result::asNotApplies(static::$VCAT, $rec, $reason);
    $result = $this->getResult($this->routines, $rec, $dob);
    if (! $result)    
      $result = $this->getResult($this->catchups, $rec, $dob);
    if (! $result)    
      $result = IS_Result::asNotApplies(static::$VCAT, $rec);
    return $result;
  }
  //
  protected function isNotApplicable($rec) {
    return null/*override*/; 
  }
  protected function getResult($scheds, $rec, $dob) {
    foreach ($scheds as &$ds) {
      $result = $ds->applies($rec, $dob);
      if ($result)
        return IS_Result::from(static::$VCAT, $ds, $result); 
    }
  }  
  protected static function getMyDoseSchedClass() {
    return 'DS_' . static::$CODE;
  }
}
abstract class DoseSched {
  //
  public $name;
  public /*Dose[]*/$doses;
  public $routine;
  //
  abstract static function asRoutines();
  abstract static function asCatchups();
  //
  static function create($name) {
    $me = new static();
    $me->name = $name;
    return $me;
  }
  public function doses(/*Dose,..*/) {
    $this->doses = func_get_args();
    for ($i = 0, $j = count($this->doses); $i < $j; $i++) 
      $this->doses[$i]->index = $i + 1;
    return $this;
  }
  //
  public function /*DS_Result*/applies(/*IS_Record*/$rec, $dob) {
    if ($this->allApply($rec)) {
      // p_r($this, 'all apply');
      $count = $rec->count();
      if ($count >= count($this->doses)) { 
        return DS_Result::asComplete($rec, $count);
      } else { 
        $dose = $this->getNextDose($rec);
        // p_r($dose, 'dose');
        $date = $dose->getDueDate($rec, $dob);
        // p_r($date, 'date');
        if ($date) {
          if (isTodayOrPast($date))
            return DS_Result::asDue($rec, $count, $dose, $date);
          else 
            return DS_Result::asPartial($rec, $count, $dose, $date);
        }
      }
    }
  }
  protected function allApply($rec) {
    for ($i = 0, $j = $rec->count(), $k = count($this->doses); $i < $j && $i < $k; $i++) {
      if (! $this->doses[$i]->applies($rec))
        return false;
    }
    return true;
  }
  protected function getNextDose($rec) {
    return geta($this->doses, $rec->count());
  }
}
class DS_Result extends BasicRec {
  //
  public /*IS_Record*/$rec;
  public /*bool*/$completed;
  public /*int*/$count;
  public /*Dose*/$due; 
  public /*Dose*/$next; 
  //
  static function asComplete($rec, $count) {
    return new static($rec, true, $count);
  }
  static function asDue($rec, $count, $dose, $date) {
    $dose->setDueDate($date);
    return new static($rec, false, $count, $dose);
  }
  static function asPartial($rec, $count, $dose, $date) {
    $dose->setDueDate($date);
    return new static($rec, false, $count, null, $dose);
  }
}
class Dose extends BasicRec {
  //
  public /*Ages*/$ages;
  public /*Int[]*/$ints;
  public /*string(VType)*/$vtype;
  public /*string(VCat)*/$vname;
  public /*bool*/$optional;
  public $index;
  public $date; /*due date*/ 
  //
  static function from(/*Ages*/$ages = null, /*Int*/$int = null) {
    $me = new static($ages);
    if ($int)
      $me->ints = array($int);
    return $me;
  }
  public function ints(/*Int,..*/) {
    $this->ints = func_get_args();
    return $this;
  }    
  public function type($vtype) {
    $this->vtype = $vtype;
    return $this;
  }
  public function name($vname) {
    $this->vname = $vname;
    return $this;
  }
  public function optional() {
    $this->optional = true;
    return $this;
  }
  public function setDueDate($date) {
    $this->date = $date;
    $this->_date = formatDate($date); 
  }
  //
  public function applies(/*IS_Record*/$rec) {
    $imm = $rec->imms[$this->index - 1];
    return $this->agesApply($imm->cage)
      && $this->nameApplies($imm)
      && $this->intsApply($imm->dateGiven, $rec);
  }
  public function getDueDate(/*IS_Record*/$rec, $dob) {
    // p_r($rec, 'getDueDate');
    $date = nowNoQuotes();
    if ($this->ints) {
      $date2 = $this->calcNextDate($rec, $dob);
      // p_r($date2, 'calcNextDate');
      if (compareDates($date2, $date) > 0)
        $date = $date2;
    }
    $date2 = $this->ages->calcDate($dob);
    // p_r($date2, 'this->ages->calcDate');
    if (compareDates($date2, $date) > 0)
      $date = $date2;
    $cage = Cage::from($dob, $date);
    // p_r($cage, 'cage @ ' . $date);
    if ($this->agesApply($cage))
      return $date;
  }
  public function html() {
    $h = Html::create()->ul_('ml10')
      ->li_()->span('Dose: ')->span($this->index)->_()
      ->li_()->span('Age Range: ')->add($this->ages->html())->_();
    if ($this->ints)
      $h->li_()->span('Interval: ')->add(Int::htmls($this->ints))->_();
    if ($this->_date)
      $h->li_()->span('Date: ')->span($this->_date)->_();
    $h->_();
    return $h->out();
  }
  //
  protected function agesApply($cage) {
    return $this->ages->applies($cage);
  }
  protected function nameApplies($imm) {
    if ($this->vname == VNames::Recombivax_Adult) {
      logit_r($imm, 'nameApplies imm');
      logit_r(VNames::applies($imm, $this->vname), 'applies');
    }
    return $this->vname ? VNames::applies($imm, $this->vname) : true;
  }
  protected function intsApply($dateGiven, $rec) {
    if ($this->ints) {
      foreach ($this->ints as $int)
        if (! $this->intApplies($int, $dateGiven, $rec))
          return false;
    }
    return true;
  }
  protected function intApplies($int, $date, $rec) {
    $imm = $this->getImm($int, $rec);
    $cint = Cage::from($imm->dateGiven, $date);
    return $int->applies($cint);
  }
  protected function getImm($int, $rec) {
    return $rec->imms[$int->calcIndex($this->index)];
  }
  protected function calcNextDate($rec, $dob) {
    $next = null; 
    // p_r('startloop');
    foreach ($this->ints as $int) {
      // p_r($int, 'int'); 
      $imm = $this->getImm($int, $rec);
      // p_r($imm, 'imm');
      $this->setNextDate($next, $int->calcDate($imm->dateGiven));
      // p_r($next, 'next');
    }
    // p_r('endloop');
    return $next;
  }
  protected function setNextDate(&$next, $date) {
    if ($next == null)
      $next = $date;
    else if (compareDates($date, $next, true) > 0)
      $next = $date; 
  }
}
/** Time Value */
class Tv extends BasicRec {
  //
  public $value;
  public $unit;
  public $_out;
  //
  const DAYS = 0;
  const MONTHS = 1;
  //
  static function w($weeks) {
    $out = "$weeks weeks";
    return new static($weeks * 7, static::DAYS, $out); 
  }
  static function m($months) {
    $out = "$months months";
    return new static($months, static::MONTHS, $out); 
  }
  static function y($years) {
    $out = "$years years";
    return new static($years * 12, static::MONTHS, $out); 
  }
  //
  public function lte(/*Cage*/$cage) {
    switch ($this->unit) {
      case static::DAYS:
        return $this->value <= $cage->days;
      case static::MONTHS:
        return $this->value <= $cage->months;
    }
  }
  public function gt(/*Cage*/$cage) {
    switch ($this->unit) {
      case static::DAYS:
        return $this->value > $cage->days;
      case static::MONTHS:
        return $this->value > $cage->months;
    }
  }
  public function calcDate(/*string(date)*/$from) {
    switch ($this->unit) {
      case static::DAYS:
        return futureDate($this->value, 0, 0, $from);
      case static::MONTHS:
        return futureDate(0, $this->value, 0, $from);
    }
  }
  public function html() {
    return Html::create()->span($this->_out)->out();
  }
}
/** Age range */
class Ages extends BasicRec {
  //
  public /*Tv*/$min;
  public /*Tv*/$max;
  //
  static function w($min, $max) {
    return new static(Tv::w($min), Tv::w($max));
  }
  static function wm($min, $max) {
    return new static(Tv::w($min), Tv::m($max));
  }
  static function wy($min, $max) {
    return new static(Tv::w($min), Tv::y($max));
  }
  static function m($min, $max = 216/*18y*/) {
    return new static(Tv::m($min), Tv::m($max));
  }
  static function my($min, $max) {
    return new static(Tv::m($min), Tv::y($max));
  }
  static function y($min, $max = 18) {
    return new static(Tv::y($min), Tv::y($max));
  }
  //
  public function applies(/*Cage*/$cage) {
    return $this->min->lte($cage) && $this->max->gt($cage);
  }
  public function calcDate(/*string(date)*/$from) {
    return $this->min->calcDate($from);
  }
  public function html() {
    return Html::create()
      ->span($this->min->html())->span(' to ')->span($this->max->html())->out();
  }
}
/** Interval from prior dosage */
class Int extends BasicRec {
  //
  public $index/*dose index, -1 for prior*/;
  public /*Tv*/$tv;
  //
  const PRIOR = -1;
  //
  static function w($weeks) {
    return static::create(Tv::w($weeks));
  }
  static function m($months) {
    return static::create(Tv::m($months));
  }
  static function y($years) {
    return static::create(Tv::y($years));
  }
  protected static function create(/*Tv*/$tv, $index = self::PRIOR) {
    return new static($index, $tv);
  } 
  public function from($index) {
    $this->index = $index;
    return $this; 
  }
  //
  public function applies(/*Cage*/$cint) {
    return $this->tv->lte($cint);
  }
  public function calcIndex(/*int*/$from) {
    return $this->index > 0 ? $this->index - 1 : $from + $this->index - 1;
  }
  public function calcDate(/*string(date)*/$from) {
    return $this->tv->calcDate($from);
  }
  public function html() {
    $a = array($this->tv->html());
    $a[] = $this->index == static::PRIOR ? 'prior' : 'dose #' . $this->index;
    return implode(' from ', $a);
  }
  //
  static function htmls($ints) {
    $a = array();
    foreach ($ints as $int)
      $a[] = $int->html();
    return implode(' and ', $a);
  }
}
/** Age @ date */
class Cage extends BasicRec {
  //
  public $months; /*age in terms of months*/
  public $days; /*age in terms of days*/
  public $_cage;
  //
  static function from($from, $to = null/*today*/) {
    $from = dateToString($from);
    $to = dateToString($to);
    $cage = chronAge($from, $to);
    $m = $cage['y'] * 12 + $cage['m'];
    $d = daysBetween($from, $to);
    return new static($m, $d, $cage);
  } 
  //
  public function html() {
    $y = $this->_cage['y'];
    $m = $this->_cage['m'];
    $d = $this->_cage['d'];
    return $y . 'y ' . $m . 'm ' . $d . 'd';
  }
}
/** Immun schedule record */
class IS_Record extends BasicRec {
  //
  public $vcat;
  public /*Immun_C*/$imms; /*with cage (age @ date given) assigned to each*/
  public /*PStub_C*/$client;
  //
  static function /*IS_Record[vcat]*/all(/*ImmunSched[]*/$ischeds, /*ImmunChart*/$chart) {
    $us = array();
    $dob = $chart->Client->birth;
    foreach ($ischeds as &$isched) {
      $vcat = $isched::$VCAT;
      $row = $chart->Rows[$vcat];
      $us[$vcat] = static::from($vcat, $dob, $row, $chart);
    }
    return $us;
  }
  protected static function from($vcat, $dob, $row, $chart) {
    $me = new static();
    $me->vcat = $vcat;
    $me->client = $chart->Client;
    if (! empty($row->Immuns)) {
      $me->imms = $row->Immuns;
      foreach ($me->imms as &$imm) 
        $imm->cage = Cage::from($dob, $imm->dateGiven);
    }
    return $me;
  }
  //
  public function count() {
    return ($this->imms) ? count($this->imms) : 0;
  }
  public function onjson() {
    unset($this->vcat); 
    unset($this->client); 
    parent::onjson();
  }
  public function html() {
    $h = Html::create()->ul_('ml10');
    foreach ($this->imms as $imm)  
      $h->li($this->html_imm($imm));
    $h->_();
    return $h->out();
  }
  protected function html_imm($imm) {
    return Html::create()
      ->span(formatDate($imm->dateGiven) . ' (' . $imm->cage->html() . '): ' . $imm->tradeName)
      ->out();
  }
}  
