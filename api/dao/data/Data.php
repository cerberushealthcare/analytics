<?php
/*
 * Data: Static helper functions
 */
class Data {
  /*
   * Get array element by index, or return default value if not found
   */
  public static function get(&$arr, $ix, $default = null) {
    return isset($arr[$ix]) ? $arr[$ix] : $default;
  }
  /*
   * Return true if null string
   */
  public static function isBlank($s) {
    return (trim($s) == "");
  }
  /*
   * Returns '1' or '0'
   */
  public static function bool($test) {
    return ($test) ? '1' : '0';
  }
  /*
   * Returns '1966-11-23'
   */
  public static function ymd($date) {
    return date("Y-m-d", strtotime($date));
  }
}
?>