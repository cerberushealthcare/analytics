<?php
require_once 'lib/rec/BasicRec.php';
//
class FlightTrack_Area extends FlightTrack {
  //
  static /*Area*/$AREA;
  //
  static function fetchAll($area) {
    static::$AREA = $area;
    return parent::fetchAll();
  }
  //
  public $id;
  public $feed;
  public $lat;
  public $long;
  public $dir/*deg*/;
  public $alt/*feet*/;
  public $speed/*knots*/;
  public $projected;
  public $datatype;
  public $craft;
  public $reg;
  public $timestamp;
  public $origin;
  public $dest;
  public $code;
  //
  public $mph;
  public $dist;
  public $bearing;
  public $xt;
  public $at;
  public $ema;
  public $captions;
  public /*Point*/$Pos;
  public /*VPt[]*/$VPts;
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
      case 'A34':
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
  protected function setFields() {
    $p2p = static::$AREA->getPointToPoint($this);
    $this->mph = $this->getMph();
    $this->dist = $p2p->distance;
    $this->bearing = static::$AREA->bearingFromObs($this->Pos);
    $this->xt = $p2p->crossTrack;
    $this->at = $p2p->alongTrack;
    $this->ema = $this->mph ? $this->getEma($this->at, $this->mph) : 0;
    $this->rad = static::$AREA->Obs->bearingrad($this->Pos);
    $this->viewangle = $this->getViewAngle();
    $this->_special = $this->isSpecialAircraft();
    $this->_inbound = $this->isInbound();
    $this->_outbound = $this->isOutbound();
    $this->_thrubound = $this->isThrubound();
    $this->_alt = round($this->alt / 100);
    $this->_dir = Point::getCardinal($this->dir); 
    $this->_sort = $this->getSort();
    $this->captions = array(
      $this->id,
    	$this->craft . ' ' . $this->dest,
      $this->_alt . ' ' . $this->_dir);  
  }
  public function getSort() {
    return $this->alt;
  }
  protected function getViewAngle() {
    $d = $this->dist * 5280;
    $h = $this->alt;
    $rad = atan2($h, $d);
    $deg = rad2deg($rad);
    return round($deg); 
  }
  protected function /*Point*/getActualPos() {
    $point = new Point($this->lat, $this->long);
    $delay = $this->getFeedDelay();
    if ($delay > 0) 
      $point = $point->destination($this->dir, $this->getMph(), $delay); 
    return $point;
  }
  protected function /*VPt[]*/getVPts($every/*min*/, $count) {
    $vpts = array();
    for ($i = 0; $i < $count; $i++) 
      $vpts[] = $this->getVPt($this->Pos, $every * $i);
    return $vpts;
  }
  protected function getVPt(/*Point*/$pos, $minutes = 0) {
    if ($minutes > 0)
      $pos = $pos->destination($this->dir, $this->getMph(), $minutes);
    return VPt::create(static::$AREA->Obs, $pos, $this->alt);   
  }
  //
  protected static function isValid($rec) {
    if (parent::isValid($rec)) {
      $rec->Pos = $rec->getActualPos();
      if ($rec->withinArea()) {
        $rec->VPts = $rec->getVPts(1, 15);
        $rec->setFields();
        return true;
      }
    }
  }
  protected static function format($num, $dec = 1) {
    if (abs($num) >= 1000) 
      return number_format($num / 1000, 2) . 'K';
    if ($dec > 1 || abs($num) < 10)
      return number_format($num, $dec);
    return number_format($num, 0);
  }
}
class Area {
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
  //
  public $radius;
  public /*Point*/$Obs/*center of area*/;
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
}
class Airport extends BasicRec {
  //
  static function from($id) {
    $latlon = static::$AIRPORTS[$id];
    $me = static::create($id, $latlon[0], $latlon[1]);
    return $me;
  }
  //
  public $id;
  public /*Point*/$Center;
  //
  protected static function create($id, $lat, $lon) {
    $me = new static();
    $me->id = $id;
    $me->Center = new Point($lat, $lon);
    return $me; 
  }
  //
  protected static $AIRPORTS = array(
    'CVG' => array(39.056784, -84.664764),
    'SDF' => array(38.172016, -85.735588),
    'LEX' => array(38.034994, -84.606952),
    'ATL' => array(33.641276, -84.427799));
}
class Point extends BasicRec {
  //
  public $lat;
  public $long;
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
  public function /*Point*/destination($bearing, $mph, $min) { 
    $dist = $mph * $min / 60;
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
  //
  protected static $CARDS = array('N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW', 'N');
  protected static $CLOCK = array(12, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12);
  const R = 3960/*Earth radius (mi)*/;
}
class PointToPoint {
  //
  static function create(/*Point*/$pointA, $pointABearing, /*Point*/$pointB) {
    $me = new static();
    $me->bearing = $pointA->bearing($pointB);
    $me->distance = $pointA->distance($pointB);
    $me->crossTrack = $pointA->crossTrackDistance($pointABearing, $pointB, $me->distance, $me->bearing);
    $me->alongTrack = $pointA->alongTrackDistance($me->distance, $me->crossTrack);
    return $me;
  }
  //
  public $bearing;
  public $distance;
  public $crossTrack;
  public $alongTrack;
  //
  public function toString() {
    return 'd=' . number_format($this->distance, 2) . ', xt=' . number_format($this->crossTrack, 2) . ', at=' . number_format($this->alongTrack, 2);
  }
}
class VPt extends BasicRec {
  //
  static function create(/*Point*/$obs, /*Point*/$pos, $alt) {
    $dir = $obs->bearingrad($pos);
    $dist = $pos->distance($obs) * 5280;
    $angle = rad2deg(atan2($alt, $dist)) ;
    return new static($dir, $angle);
  }
  //
  public $dir/*direction, in radians*/;
  public $angle/*viewing angle, in degrees*/;
}
