<?php
require_once 'php/data/csv/_CsvImportFile.php';
//
class Csv_IpCountry extends CsvFile {
  //
  static $FILENAME = 'php/data/csv/ip-country/GeoIPCountryWhois-US.csv';
  static $CSVREC_CLASS = 'IpCountryRec';
  static $HAS_FID_ROW = true;
  //
  static function find($ip) {
    $iplong = sprintf("%u", ip2long($ip));
    $me = static::load();
    $rec = $me->bsearch($iplong);
    return $rec;
  }
}
class IpCountryRec extends CsvRec {
  //
  public $fromIp;
  public $toIp;
  public $from;
  public $to;
  public $country;
  public $name;
  //
  public function compare($iplong) {
    if ($iplong < $this->from)
      $c = 1;
    else if ($iplong > $this->to)
      $c = -1;
    else
      $c = 0;
    return $c;
  }
}
