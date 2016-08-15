<?php
require_once "php/data/csv/ip-country/Csv_IpCountry.php";
//
class IpValidator {
  //
  static function isBad() {
    if (MyEnv::isLocal())
      return;
    $ip = $_SERVER['REMOTE_ADDR'];
    if (static::isFlagged($ip))
      return true;
    if (! static::isEnglish($ip))
      return true;
  }
  //
  protected static function isFlagged($ip) {
    $ip3 = substr($ip, 0, strrpos($ip, '.'));    // e.g. 173.167.60
    $ip2 = substr($ip3, 0, strrpos($ip3, '.'));  // e.g. 173.167
    switch ($ip) {
      case '173.167.60.41':  // Amber Fulton
      case '173.67.165.81':  // Amber Fulton
        return true;
    }
    switch ($ip2) {
      case '117.192':  // India
      case '122.166':  // India
      case '41.251':  // Morocco
        return true;
    }
  }
  protected static function isEnglish($ip) {
    $rec = Csv_IpCountry::find($ip);
    if ($rec)
      return true;
  }
}
