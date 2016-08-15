<?php
require_once 'app/data/rec/BasicRec.php';
//
class FlightTrack_Area extends FlightTrack {
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
      return '>' . $this->dest;
    if ($this->isInbound())
      return $this->origin;
    if ($this->dest)
      return '=' . $this->dest;
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
    $this->viewangle = $this->getViewAngle();
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
    $this->_thrubound = $this->isThrubound();
    $this->_bearing = Point::getCardinal($this->bearing) . ' ' . round($this->_bo);
    $this->_dist = static::format($this->dist);
    if (! $this->_targeted) {
      $this->at = -$this->at;  
      $this->_dist = '-' . $this->_dist;
    }
    $this->_at = static::format($this->at);
    $mb = static::$AREA->bearingFromObs($this->Pos);
    $mbr = static::$AREA->Obs->bearingrad($this->Pos);
    $this->_mb = Point::getClock($mb) . ':' . $this->viewangle . '&deg; ' . ' ' . $mbr;  // ' ' . Point::getCardinal($mb) . round($mb);
    $this->_ts = date("h:i", $this->timestamp);
    $this->_alt = round($this->alt / 100);
    $this->_close = abs($this->xt) < 5;
    if ($this->mph) {
      $this->ema = $this->getEma($this->at, $this->mph);
      if ($this->_targeted && $this->_close) {
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
    return 90-$this->viewangle;
    
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
  protected function getViewAngle() {
    $d = $this->dist * 5280;
    $h = $this->alt;
    $rad = atan2($h, $d);
    $deg = rad2deg($rad);
    return round($deg); 
  }
  //   
  static function fetchAll($area) {
    static::$AREA = $area;
    return parent::fetchAll();
  }
  static function isValid($rec) {
    $valid = parent::isValid($rec) && ($rec->isInbound() || $rec->withinArea());
    if ($valid)
      $rec->setFields();
    return $valid;
  }
  static function format($num, $dec = 1) {
    if (abs($num) >= 1000) 
      return number_format($num / 1000, 2) . 'K';
    if ($dec > 1 || abs($num) < 10)
      return number_format($num, $dec);
    return number_format($num, 0);
  }
}
class Area {
  //
  public $radius;
  public /*Point*/$Obs;  // center of area
  public /*Airport*/$Airport;
  public $distObsToAirport;
  //
  public function contains(/*Point*/$point) {
    $dist = static::distanceToObs($point);
    return $dist <= $this->radius;
  }
  public function /*PointToPoint*/getPointToPoint(/*FlightTrack*/$track) {
    $rec = PointToPoint::create($track->Pos, $track->dir, $this->Obs);
    return $rec;
  }
  public function bearingFromObs(/*Point*/$point) {
    return $this->Obs->bearing($point);
  }
  public function distanceToObs(/*Point*/$point) {
    return $point->distance($this->Obs);
  }
  //
  static function create($airportCode, $obsLat = null, $obsLong = null, $radius = 60) {
    $me = new static();
    $me->Airport = Airport::from($airportCode);
    $me->radius = $radius;
    if ($obsLat == null) {
      $me->Obs = $me->Airport->Center;
      $me->distObsToAirport = 0;
    } else {
      $me->Obs = new Point($obsLat, $obsLong);
      $me->distObsToAirport = $me->Obs->distance($me->Airport->Center);
    }
    return $me;
  }
}
class Airport extends BasicRec {
  //
  public $id;
  public /*Point*/$Center;
  //
  protected static $AIRPORTS = array(
    'CVG' => array(39.056784, -84.664764),
    'SDF' => array(38.172016, -85.735588),
    'LEX' => array(38.034994, -84.606952),
    'ATL' => array(33.641276, -84.427799));
  //
  static function from($id) {
    $latlon = static::$AIRPORTS[$id];
    $me = static::create($id, $latlon[0], $latlon[1]);
    return $me;
  }
  static function create($id, $lat, $lon) {
    $me = new static();
    $me->id = $id;
    $me->Center = new Point($lat, $lon);
    return $me; 
  }
}
class Point extends BasicRec {
  //
  public $lat;
  public $long;
  //
  const R = 3960;  // Earth radius (mi) 
  protected static $CARDS = array('N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW', 'N');
  protected static $CLOCK = array(12, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12);
  //
  public function distance(/*Point*/$point) {
    $dLat = deg2rad($point->lat - $this->lat);
    $dLon = deg2rad($point->long - $this->long);
    $lat1 = deg2rad($this->lat);
    $lat2 = deg2rad($point->lat);
    $a = sin($dLat / 2) * sin($dLat / 2) + sin($dLon / 2) * sin($dLon / 2) * cos($lat1) * cos($lat2); 
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $miles = $c * static::R;  
    return $miles;
  }
  public function bearing(/*Point*/$point) {
    $lat1 = deg2rad($this->lat);
    $lat2 = deg2rad($point->lat);
    $dLon = deg2rad($point->long - $this->long);
    $y = sin($dLon) * cos($lat2);
    $x = cos($lat1) * sin($lat2) - sin($lat1) * cos($lat2) * cos($dLon);
    $rad = atan2($y, $x);
    $degrees = rad2deg($rad);
    if ($degrees < 0) 
      $degrees += 360;
    return $degrees;
  }
  public function bearingrad(/*Point*/$point) {
    $lat1 = deg2rad($this->lat);
    $lat2 = deg2rad($point->lat);
    $dLon = deg2rad($point->long - $this->long);
    $y = sin($dLon) * cos($lat2);
    $x = cos($lat1) * sin($lat2) - sin($lat1) * cos($lat2) * cos($dLon);
    $rad = atan2($y, $x);
    return $rad;
  }
  public function /*Point*/destination($dist, $bearing) { 
    $lat1 = deg2rad($this->lat);
    $lon1 = deg2rad($this->long);
    $bear = deg2rad($bearing);
    $dr = $dist / static::R;
    $lat2 = asin(sin($lat1) * cos($dr) + cos($lat1) * sin($dr) * cos($bear));
    $lon2 = $lon1 + atan2(sin($bear) * sin($dr) * cos($lat1), cos($dr) - sin($lat1) * sin($lat2));
    $point = new Point(rad2deg($lat2), rad2deg($lon2));
    return $point;
  }
  public function crossTrackDistance($bearing, /*Point*/$point, $distToPoint = null, $bearingToPoint = null) {
    if ($distToPoint == null)
      $distToPoint = $this->distance($point); 
    if ($bearingToPoint == null)
      $bearingToPoint = $this->bearing($point); 
    $dist13 = $distToPoint / static::R;
    $bear13 = deg2rad($bearingToPoint);
    $bear12 = deg2rad($bearing);
    $miles = asin(sin($dist13) * sin($bear13 - $bear12)) * static::R;
    return $miles;
  }
  public function alongTrackDistance($distToPoint, $crossTrackDistance) {
    $dist13 = $distToPoint / static::R;
    $distX = $crossTrackDistance / static::R;
    $miles = acos(cos($dist13) / cos($distX)) * static::R;
    return $miles;
  }
  public function toString() {
    return '(' . number_format($this->lat, 4) . ',' . number_format($this->long, 4) . ')';
  }
  //
  static function getCardinal($deg) {
    return static::$CARDS[round($deg / 45)];
  }
  static function getClock($deg) {
    return static::$CLOCK[round($deg / 30)];
  }
}
class PointToPoint {
  //
  public $bearing;
  public $distance;
  public $crossTrack;
  public $alongTrack;
  //
  public function toString() {
    return 'd=' . number_format($this->distance, 2) . ', xt=' . number_format($this->crossTrack, 2) . ', at=' . number_format($this->alongTrack, 2);
  }
  //
  static function create(/*Point*/$pointA, $pointABearing, /*Point*/$pointB) {
    $me = new static();
    $me->bearing = $pointA->bearing($pointB);
    $me->distance = $pointA->distance($pointB);
    $me->crossTrack = $pointA->crossTrackDistance($pointABearing, $pointB, $me->distance, $me->bearing);
    $me->alongTrack = $pointA->alongTrackDistance($me->distance, $me->crossTrack);
    return $me;
  }
}
