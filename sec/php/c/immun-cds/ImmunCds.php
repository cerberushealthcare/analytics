<?php
require_once 'php/data/_BasicRec.php';
require_once 'php/c/immun-charting/Immun_Charts.php';
require_once 'Immun_Scheds.php';
//
/**
 * Immunization Clinical Decision Support
 * @author Warren Hornsby
 */
class ImmunCds {
  //
  static function /*Immun_Cd*/get($cid, $withHtml = false) {
    $chart = ImmunChart::fetch_asScheduled($cid);
    $info = Immun_Cd::from($chart, $withHtml);
    return $info;
  }
  static function get_withHtml($cid) {
    return static::get($cid, true);
  }
}
//
class Immun_Cd extends BasicRec {
  //
  public /*IC_Sched[]*/$ics;
  public $_html;
  //
  static function from(/*ImmunChart*/$chart, $withHtml = false) {
    $me = new static();
    $me->ics = IC_Sched::all($chart);
    logit_r($me, 'set ics');
    if ($withHtml)
      $me->_html = static::getHtml($me->ics);
    return $me;
  }
  //
  public function getNextDue() {
    $next = null;
    foreach ($this->ics as $ic) {
      if (! $ic->completed && $ic->dose) {
        $date = $ic->due ? nowNoQuotes() : $ic->dose->date;
        if ($next == null || compareDates($date, $next) < 0) 
          $next = $date;
      }
    } 
    return $next;
  }
  public function /*IC_Sched*/get($vcat) {
    foreach ($this->ics as $ic)
      if ($ic->vcat == $vcat)
        return $ic; 
  }
  //
  protected function getHtml($ics) {
    $h = Html::create();
    foreach ($ics as $r) {
      $s = $r->sched;
      $h->p_()
        ->h3($r->vcat);
      if ($r->rec->imms)
        $h->b('History:')->add($r->rec->html());
      else
        $h->b('History: ')->br('(None)');
      if ($s->routine)
        $h->b('On Routine: ')->br($s->name);
      else
        $h->b('On Catchup: ')->br($s->name);
      $h->b('Completed: ')->br($r->count);
      if ($r->completed) {
        $h->b('*FINISHED*')->br();
      } else {
        $h->b('Remaining: ')->br($r->left);
        if ($r->due)
          $h->b('*DUE NOW* ')->br($r->due);  
        if ($r->next)
          $h->b('Next:')->br($r->next->html());  
      }
      if ($r->na)
        $h->b('NA: ')->add($r->na);
      $h->_();
    }
    return $h->out();
  } 
}
class IC_Sched extends BasicRec {
  //
  public $vcat;
  public /*DoseSched*/$sched;
  public /*IS_Record*/$rec;
  public /*bool*/$completed;
  public /*int*/$count;
  public /*Dose*/$dose;
  public /*bool*/$due;
  public /*int*/$left;
  public /*string*/$na; /*not applies comment*/
  //
  static function all(/*ImmunChart*/$chart) {
    $us = array();
    $results = ImmunScheds::from($chart)->testAll();
    foreach ($results as $r)
      $us[] = static::from($r);
    return $us; 
  }
  //
  protected function from(/*IS_Result*/$r) {
    $me = static::fromObject($r);
    if ($r->due) {
      $me->dose = $r->due;
      $me->due = true;
    } else {
      $me->dose = $r->next;
    }
    if ($me->dose)
      $me->left--;
    return $me;
  }
  /*
  protected function onjson() {
    unset($this->sched);
    parent::onjson();
  }
  */
}
