<?php 
require_once 'lib/rec/RecSort.php';
require_once 'Tracking_Recs.php';
//
class Tracking {
  //
  static function fetch($airportCode, $obsLat = null, $obsLong = null) {
    $area = Area::create($airportCode, $obsLat, $obsLong);
    $recs = FlightTrack_Area::fetchAll($area);
    $recs = RecSort::sort($recs, '-_sort');
    return /*FlightTrack_Area[]*/$recs;
  }
}
//
class FlightTrack {
  //
  static function /*FlightTrack[]*/fetchAll() {
    $json = static::fetchJson();
    return static::all_fromJson($json);
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
        $min = 0;
        break;
      default:
        $min = 5.5;
    }
    return $min;
  }
  public function getMph() {
    return $this->speed * 1.150779;
  }
  //
  protected static function all_fromJson($json) {
    $map = json_decode($json);
    $us = array();
    foreach ($map as $id => $array) {
      $rec = static::from($id, $array);
      if (static::isValid($rec)) 
        $us[] = $rec;
    }
    return $us;
  }
  protected static function isValid($rec) {
    if (! empty($rec) && $rec->alt > 0)
      return true; 
  }
  protected static function from($id, $array) {
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
      $me->unk3 = next($array);
      $me->id = next($array);
      return $me;
    }
  }
  protected static function fetchJson() {
    $json = file_get_contents(static::$URL);
    return $json;
  }
}
