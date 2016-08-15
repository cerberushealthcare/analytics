<?php
require_once 'php/data/rec/sql/_SqlRec.php';
/**
 * User Group Base Class
 * @author Warren Hornsby
 */
abstract class UserGroupRec extends SqlRec {
  //
  /*
  public $userGroupId;
  public $parentId;
  public $name;
  public $usageLevel;
  public $estTzAdj;
  public $sessionTimeout;
  public $demo;
  */
  //
  const USAGE_LEVEL_BASIC = '0';
  const USAGE_LEVEL_PREMIUM = '1';
  const USAGE_LEVEL_ERX = '2';
  const USAGE_LEVEL_SUPERGROUP = '3';
  //
  static $TIMEZONES = array(
    "0" => "US/Eastern",
    "-1" => "US/Central",
    "-2" => "US/Mountain",
    "-3" => "US/Pacific",
    "-4" => "US/Alaska",
    "-5" => "US/Hawaii");
  static $TIMEZONES_BY_STATE = array(
	  "AK" => "-4",
    "AL" => "-1",
    "AR" => "-1",
    "AZ" => "-2",
    "CA" => "-3",
    "CO" => "-2",
    "CT" => "0",
    "DC" => "0",
    "DE" => "0",
    "FL" => "0",
    "GA" => "0",
    "HI" => "-5",
    "IA" => "-1",
    "ID" => "-2",
    "IL" => "-1",
    "IN" => "0",
    "KS" => "-1",
    "KY" => "0",
    "LA" => "-1",
    "MA" => "0",
    "MD" => "0",
    "ME" => "0",
    "MI" => "0",
    "MN" => "-1",
    "MO" => "-2",
    "MS" => "-1",
    "MT" => "-2",
    "NC" => "0",
    "ND" => "-1",
    "NE" => "-1",
    "NH" => "0",
    "NJ" => "0",
    "NM" => "-2",
    "NY" => "0",
    "NV" => "-3",
    "OH" => "0",
    "OK" => "-1",
    "OR" => "-3",
    "PA" => "0",
    "RI" => "0",
    "SC" => "0",
    "SD" => "-1",
    "TN" => "0",
    "TX" => "-1",
    "UT" => "-2",
    "VA" => "0",
    "VT" => "0",
    "WA" => "-3",
    "WI" => "-1",
    "WV" => "0",
    "WY" => "-2");
  public function getSqlTable() {
    return 'user_groups';
  }
  public function isErx() {
    return $this->usageLevel == self::USAGE_LEVEL_ERX;
  }
  public function isSuper() {
    return $this->usageLevel == self::USAGE_LEVEL_SUPERGROUP;
  }
}
