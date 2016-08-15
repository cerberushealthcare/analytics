<?php
//
class Coding {
  
}
//
class CodingTally {
  //
  public $counts;  // array('template_bill_code' => count,..)
  public $status;  // patient status  
  //
  const STATUS_NEW = 1;
  const STATUS_ESTABLISHED = 2;
  const STATUS_EMERGENCY = 3;
  //
  public function isNewPatient() {
    return $this->status == static::STATUS_NEW;
  }
  public function count($code) {
    return geta($this->counts, $code, 0);
  }
  public function has($code) {
    return (isset($this->counts[$code])) ? 1 : 0;
  }
  public function sum_byHas() {
    $codes = func_get_args();
    $sum = 0;
    foreach ($codes as $code)
      $sum += $this->has($code);
    return $sum;
  }
}
class CodingWorksheet {
  //
  public /*CW_History*/ $history;
    
}
class CW_History extends CW_HistoryScore {
  //
  const PROBLEM_FOCUSED = 1;
  const EXPANDED = 2;
  const DETAILED = 3;
  const COMPREHENSIVE = 4;
  //
  static function count($tally) {
    $me = new static();
    $hpi = CW_History_HPI::count($tally);
    
  }
}
class CW_History_HPI extends CW_History_ {
  //
  const BRIEF = 2;
  const EXTENDED = 4;
  //
  static function count($tally) {
    $ct1 = static::byStatus($tally);
    $ct2 = static::byElements($tally);
    return max($ct1, $ct2);
  }
  static function byStatus($tally) {
    $ct = $tally->count('hpi.status.chronic');
    if ($ct < 3) 
      return static::BRIEF;
    else
      return static::EXTENDED; 
  }
  static function byElements($tally) {
    $ct = $tally->sum_byHas(
      'hpi.elem.qual',
      'hpi.elem.loc',
      'hpi.elem.dur',
      'hpi.elem.sev',
      'hpi.elem.tim',
      'hpi.elem.cxt',
      'hpi.elem.mod',
      'hpi.elem.sx');
    return ($ct < 4) ? static::BRIEF : static::EXTENDED;
  }
}
class CW_History_ROS {
  //
  const NA = 1;
  const PERTINENT = 2;
  const EXTENDED = 3;
  const COMPLETE = 4;
  //
  static function count($tally) {
    $ct = $tally->sum_byHas(
      'ros.const',
      'ros.ent',
      'ros.eyes',
      'ros.cv',
      'ros.skin',
      'ros.resp',
      'ros.endo',
      'ros.gi',
      'ros.gu',
      'ros.heme',
      'ros.ms',
      'ros.neuro',
      'ros.psych',
      'ros.aller');
    if ($ct == 0)
      return static::NA;
    else if ($ct == 1)
      return static::PERTINENT;
    else if ($ct < 10) 
      return static::EXTENDED;
    else
      return static::COMPLETE;
  } 
}
class CW_History_PFSH {
  //
  const NA = 2;
  const PERTINENT = 3;
  const COMPLETE = 4;
  //
  static function count($tally) {
    $complete = ($tally->isNewPatient()) ? 3 : 2;
    $ct = ($tally->has('pfsh.med') || $tally->has('pfsh.surg'))
        + $tally->has('pfsh.fam')
        + $tally->has('pfsh.soc');
    if ($ct == 0)
      return static::NA;
    else if ($ct < $complete) 
      return static::PERTINENT; 
    else 
      return static::COMPLETE;
  }
}
