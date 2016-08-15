<?php
require_once 'Tracking_Recs.php';
//
class Area_Home extends Area {
  //
  static function create() {
    $me = new static();
    $me->radius = 60;
    $me->Obs = new Point(38.25, -85.66);
    $me->Airport = Airport_SDF::create();
    return $me;
  }
}
class Airport_SDF extends Airport {
  //
  static function create() {
    //
    $me = new static('SDF');
    $me->center = new Point(38.17, -85.74);
    return $me;
  }
}
class FlightTrack_SDF extends FlightTrack {
  //
  public $dist;
  public $mph;
  public $bearing;
  public $ema;
  public $_dir;
  public $_bearing;
  public $_ema;
  public $_interest;
  public $_inbound;
  public $_outbound;
  public $_dist;
  public $_ts;
  public $_oa;
  public $_bo;
  //
  static /*Area*/$AREA;
  //
  public function isOutbound() {
    return $this->origin == static::$AREA->Airport->id;
  }
  public function isInbound() {
    return $this->dest == static::$AREA->Airport->id;
  }
  public function isThrubound() {
    return ! $this->isOutbound() && ! $this->isInbound();
  }
  public function withinArea() {
    return static::$AREA->contains($this->Pos);
  }
  public function isSpecialAircraft() {
    switch (substr($this->craft, 0, 3)) {
      case 'A30':
      case 'A33':
      case 'A35':
      case 'A38':
      case 'B74':
      case 'B77':
      case 'MD1':
        return true;
    }
  }
  public function getEma($dist, $mph) {
    return ($dist / $mph * 60);
  }
  public function getOtherAirport() {
    if ($this->isOutbound())
      return $this->dest;
    if ($this->isInbound())
      return $this->origin;
    if ($this->dest)
      return '>' . $this->dest;
    return '?';
  }
  public function isBearingWithinThreshold($bo) {
    $a = abs($bo);
    return $a <= 90;
  }
  public function getBearingOffset($bearing) {
    $bo = $bearing - $this->dir;
    if ($bo > 180)
      $bo -= 360;
    else if ($bo < -180)
      $bo += 360;
    return $bo;
  }
  protected function setFields() {
    $p2p = static::$AREA->getPointToPoint($this);
    $this->mph = $this->getMph();
    $this->dist = $p2p->distance;
    $this->bearing = $p2p->bearing;
    $this->xt = $p2p->crossTrack;
    $this->_xt = static::format($this->xt);
    $this->_dir = Point::getCardinal($this->dir);  // . ' ' . $this->dir;
    $this->_mph = round($this->mph);
    $this->_bo = $this->getBearingOffset($this->bearing);
    $this->_targeted = $this->isBearingWithinThreshold($this->_bo);
    $this->at = $p2p->alongTrack;
    $this->_oa = $this->getOtherAirport();
    $this->_o = $this->isOutbound() ? '' : $this->origin;
    $this->_d = $this->isInbound() ? '' : $this->dest;
    $this->_special = $this->isSpecialAircraft();
    $this->_ft = $this->getFeedType();
    $orig = new Point($this->lat, $this->long);
    $this->_dest = $orig->toString() . ' ' . $this->Pos->toString();
    $this->_inbound = $this->isInbound();
    $this->_outbound = $this->isOutbound();
    $this->_bearing = Point::getCardinal($this->bearing) . ' ' . round($this->_bo);
    $this->_dist = static::format($this->dist);
    if (! $this->_targeted) {
      $this->at = -$this->at;
      $this->_dist = '-' . $this->_dist;
    }
    $this->_at = static::format($this->at);
    $mb = static::$AREA->bearingFromObs($this->Pos);
    $this->_mb = Point::getCardinal($mb) . ' ' . round($mb);
    $this->_ts = date("h:i", $this->timestamp);
    $this->_alt = round($this->alt / 100);
    $this->_close = abs($this->xt) < 5;
    if ($this->mph) {
      $this->ema = $this->getEma($this->at, $this->mph);
      if ($this->_targeted && $this->_close && $this->alt > 3000) {
        $this->_ema = static::format($this->ema, 2);
        if ($this->ema < 5)
          $this->_approach = 1;
      }
    } else {
      $this->ema = 0;
    }
    if ($this->isInbound() && isset($this->_approach))
      $this->_hilite = 1;
    $this->_sort = $this->getSort();
  }
  public function getSort() {
    $sort = abs($this->ema) + 1000;
    if ($this->isNotInterest())
      $sort += 1000;
    return number_format($sort, 2);
  }
  public function isNotInterest() {
    if ($this->mph == 0)
      return true;
    if (abs($this->xt) >= 20)
      return true;
    if ($this->at > 100 || $this->at < -20)
      return true;
    if ($this->isThrubound()) {
      if ($this->at < -10 || abs($this->xt) >= 10)
        return true;
    }
  }
  //
  static function fetchAll() {
    static::$AREA = Area_Home::create();
    return parent::fetchAll();
  }
  static function isValid($rec) {
    $valid = parent::isValid($rec) && ($rec->isInbound() || $rec->withinArea());
    if ($valid)
      $rec->setFields();
    return $valid;
  }
  static function format($num, $dec = 1) {
    return abs($num) < 1000 ? number_format($num, $dec) : number_format($num / 1000, 2) . 'K';
  }
}
