<?php
require_once 'php/data/rec/sql/_UserRec.php';
/**
 * User Stub
 */
class UserStub extends UserRec implements ReadOnly {
  //
  public $userId;
  public $userGroupId;
  public $uid;
  public $name;
  //
  /**
   * @param string $uid
   * @return UserStub
   */
  public static function fetchByUid($uid) {
    $rec = new UserStub();
    $rec->uid = $uid;
    return parent::fetchOneBy($rec);
  }
}
