<?php
require_once 'php/data/rec/sql/_SqlRec.php';
require_once 'php/data/rec/sql/Address.php';

//
class UserReport extends SqlRec implements ReadOnly {
  //
  public $userId;
  public $name;
  public $subscription;
  public $active;
  public $userGroupId;
  public $userType;
  public $licenseState;
  public $email;
  //
  public function getSqlTable() {
    return 'users';
  }
}
/**
 * Address Record
 */
class AddressReport extends SqlRec implements NoUserGroup {
  //
  public $tableCode;
  public $tableId;
  public $type;
  public $addr1;
  public $addr2;
  public $addr3;
  public $city;
  public $state;
  public $zip;
  public $country;
  public $phone1;
  public $phone1Type;
  public $email1;
  //
  public function getSqlTable() {
    return 'addresses';
  }
  //
  public static function fetchByTable($tableCode, $tableId, $type = Address::TYPE_SHIP) {
    $rec = new Address();
    $rec->tableCode = $tableCode;
    $rec->tableId = $tableId;
    $rec->type = $type;
    return SqlRec::fetchOneBy($rec);
  }
}
class UsageDetails extends SqlRec implements ReadOnly, NouserGroup {
  public $userId;
  public $sessionId;
  public $usageType;
  public $date;
  public $cid;
  //
  public function getSqlTable() {
    return 'usage_details';
  }
  //
  public static function getUsageYTD($userId) {
    $c = new UsageDetails();
    $c->userId = $userId;
    $c->usageType = '0';
    $c->date = CriteriaValue::greaterThan('2011-01-01');
    $recs = SqlRec::fetchAllBy($c);
    return count($recs);
  } 
}
