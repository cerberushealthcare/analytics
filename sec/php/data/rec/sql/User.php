<?php
require_once 'php/data/rec/sql/_SqlRec.php';
/**
 * User Record
 */
class User extends SqlRec {
  //
  public $userId;
  public $uid;
  public $pw;
  public $name;
  public $admin;
  public $subscription;
  public $active;
  public $regId;
  public $name;
  // TODO 
  // TODO 
  // TODO 
  public $userGroupId;
  public $userType;
  //
  public function getSqlTable() {
    return 'users';
  }
  //
  /**
   * Static fetchers
   */
  public static function fetchByUid($uid) {
    $rec = new User();
    $rec->uid = $uid;
    return SqlRec::fetchOneBy($rec);
  }
}
