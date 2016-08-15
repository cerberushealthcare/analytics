<?php
class IpLookup {
  //
  public $status;
  public $msg;
  public $ip;
  public $countryAbbr;
  public $country;
  public $region;
  public $city;
  public $zip;
  public $latitude;
  public $longitude;
  public $timezone;
  //
  static function fetch($ip) {
    $url = "http://api.ipinfodb.com/v3/ip-city/?key=9e8b7eb2577988e90551b06e9bf1d125addbc9932e808f5791db96f9cb45bc5a&ip=$ip";
    $response = file_get_contents($url);
    return static::from($response);
  }
  static function from($response) {
    $a = explode(';', $response);
    $me = new static();
    $me->status = current($a);
    $me->msg = next($a);
    $me->ip = next($a);
    $me->countryAbbr = next($a);
    $me->country = next($a);
    $me->region = next($a);
    $me->city = next($a);
    $me->zip = next($a);
    $me->latitude = next($a);
    $me->longitude = next($a);
    $me->timezone = next($a);
    return $me;
  }
}