<?php 
require_once 'app/data/rec/RecSort.php';
//
class Polling {
  //
  static function fetch() {
    $recs = FlightTrack_SDF::fetchAll();
    $recs = RecSort::sort($recs, '_outbound', 'ema');
    return /*FlightTrack[]*/$recs;
  }
}
//
class FlightTrack {
  //
  public $id;
  public $unk1;
  public $lat;
  public $long;
  public $dir;
  public $alt;
  public $speed;
  public $projected;
  public $datatype;
  public $craft;
  public $reg;
  public $timestamp;
  public $origin;
  public $dest;
  public $code;
  public $unk2;
  //
  const FEED_REALTIME = 'A';
  const FEED_DELAY_5M = 'F';
  //
  static $URL = 'http://www.flightradar24.com/zones/full_all.json';
  //
  public function getFeedType() {
    return substr($this->feed, 0, 1);
  }
  public function getFeedDelay() {
    switch ($this->getFeedType()) {
      case static::FEED_REALTIME:
        return 0;
      default:
        return 5;
    }
  }
  //
  static function fetchAll() {
    $json = static::fetchJson();
    return static::all_fromJson($json);
  }
  static function all_fromJson($json) {
    $map = json_decode($json);
    $us = array();
    foreach ($map as $id => $array) {
      $rec = static::from($id, $array);
      if (static::isValid($rec))
        $us[] = $rec;
    }
    return $us;
  }
  static function isValid($rec) {
    return ! empty($rec) && $rec->alt > 0;
  }
  static function from($id, $array) {
    if (is_array($array)) {
      $me = new static();
      $me->id = $id;
      $me->feed = current($array);
      $me->lat = number_format(next($array), 4);
      $me->long = number_format(next($array), 4);
      $me->dir = next($array);
      $me->alt = next($array);
      $me->speed = next($array);
      $me->projected = next($array);
      $me->datatype = next($array);
      $me->craft = next($array);
      $me->reg = next($array);
      $me->timestamp = next($array);
      $me->origin = next($array);
      $me->dest = next($array);
      $me->code = next($array);
      $me->unk2 = next($array);
      return $me;
    }
  }
  protected static function fetchJson() {
    $json = file_get_contents(static::$URL);
    return $json;
  }
}
class Area {
  //
  public $latMin;
  public $latMax;
  public $longMin;
  public $longMax;
  //
  public function contains($lat, $long) {
    return $lat >= $this->latMin && $lat <= $this->latMax && $long >= $this->longMin && $long <= $this->longMax;
  }
}
class Area_SDF extends Area {
  //
  public $latMin = 37.85;
  public $latMax = 38.38;
  public $longMin = -86.00;
  public $longMax = -85.03;
}
class Point {
  //
  public $lat;
  public $long;
  //
  public function __construct($lat, $long){ 
    $this->lat = $lat;
    $this->long = $long;
  }
}
class Airport {
  //
  public $id;
  public /*Point*/$center;
}
class Airport_SDF extends Airport {
  //
  public $id = 'SDF';
  //
  static function create() {
    //
    $me = new static();
    $me->center = new Point(38.17, -85.74);
    return $me;
  }
}
class FlightTrack_SDF extends FlightTrack {
  //
  public $dist;
  public $bearing;
  public $_mph;
  public $_ema;
  public $_interest;
  public $_inbound;
  public $_outbound;
  public $_dist;
  public $_ts;
  //
  static /*Airport*/$AIRPORT;
  static /*Area*/$AREA;
  static $LAT_ME = 38.25;
  static $LONG_ME = -85.66;
  //
  public function isOutbound() {
    return $this->origin == static::$AIRPORT->id;
  }
  public function isInbound() {
    return $this->dest == static::$AIRPORT->id;
  }
  public function withinArea() {
    return static::$AREA->contains($this->lat, $this->long);
  }
  protected function setFields() {
    $lat1 = $this->lat;
    $lon1 = $this->long;
    $lat2 = static::$LAT_ME;
    $lon2 = static::$LONG_ME;
    $R = 3959; 
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $lat1 = deg2rad($lat1);
    $lat2 = deg2rad($lat2);
    $a = sin($dLat / 2) * sin($dLat / 2) + sin($dLon / 2) * sin($dLon / 2) * cos($lat1) * cos($lat2); 
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a)); 
    $this->dist = $R * $c;
    $y = sin($dLon) * cos($lat2);
    $x = cos($lat1) * sin($lat2) - sin($lat1) * cos($lat2) * cos($dLon);
    $bear = rad2deg(atan2($y, $x));
    if ($bear < 0) 
      $bear += 360;
    $this->bearing = $bear;
    $this->_mph = round($this->speed * 1.150779);
    $this->_ema = '';
    $this->ema = 0;
    if ($this->_mph) {
      $ema = $this->dist / $this->_mph * 60;
      $ema -= $this->getFeedDelay();  
      if ($ema < 60) {
        $this->_ema = number_format($ema, 2);
        if (abs($this->bearing - $this->dir) < 20) {
          if ($this->alt < 19000 && $ema > -1 && $ema < 5)
            $this->_hilite = 1;
        }
      }
      $this->ema = $ema;
    }
    if ($this->isInbound()) 
      if ($bear > 270 && $bear <= 360 && $this->dist < 1000)
        $this->_interest = 1; 
    $this->_inbound = $this->isInbound();
    $this->_outbound = $this->isOutbound();
    $this->_bearing = number_format($bear);
    $this->_dist = $this->dist < 1000 ? number_format($this->dist, 1) : number_format($this->dist / 1000, 2) . 'K';
    $this->_ts = date("h:i", $this->timestamp);
    $this->_lat = number_format($this->lat, 2);
    $this->_long = number_format($this->long, 2);
    $this->_alt = round($this->alt / 100);
  }
  //   
  static function fetchAll() {
    static::$AIRPORT = new Airport_SDF();
    static::$AREA = new Area_SDF();
    return parent::fetchAll();
  }
  static function isValid($rec) {
    return parent::isValid($rec) && ($rec->isInbound() || $rec->withinArea()); 
  }
  static function from($id, $array) {
    $me = parent::from($id, $array);
    if ($me) 
      $me->setFields();
    return $me;
  }
}
class Nav {
  //
  static function distance($lat1, $lon1, $lat2, $lon2) {
    $R = 3959; 
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $lat1 = deg2rad($lat1);
    $lat2 = deg2rad($lat2);
    $a = sin($dLat / 2) * sin($dLat / 2) + sin($dLon / 2) * sin($dLon / 2) * cos($lat1) * cos($lat2); 
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a)); 
    return $R * $c;
  }
  static function bearing($lat1, $lon1, $lat2, $lon2) {
    $dLon = deg2rad($lon2 - $lon1);
    $y = sin($dLon) * cos($lat2);
    $x = cos($lat1) * sin($lat2) - sin($lat1) * cos($lat2) * cos($dLon);
    $bear = rad2deg(atan2($y, $x));
    if ($bear < 0) 
      $bear += 360;
    return $bear;
  }
}
