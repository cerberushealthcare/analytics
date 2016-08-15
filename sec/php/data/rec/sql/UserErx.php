<?php
require_once 'php/data/rec/sql/_SqlRec.php';
/**
 * User ERX 
 */
class UserErx extends SqlRec {
  //
  public $userId;
  public $uid;
  public $name;
  public $userGroupId;
  public $userType;
  public $licenseState;
  public $license;
  public $dea;
  public $npi;
  public /*NcUser*/ $NcUser;
  //
  public function getSqlTable() {
    return 'users';
  }
  //
  public function loadNcUser() {
    $this->NcUser = NcUser::fetch($this->userId);
  }
  /**
   * Static fetchers
   */
  public static function fetchByUid($uid) {
    $rec = new UserErx();
    $rec->uid = $uid;
    return SqlRec::fetchOneBy($rec);
  }
  public static function fetchAllByUgid($ugid) {
    $rec = new UserErx();
    $rec->userGroupId = $ugid;
    return SqlRec::fetchAllBy($rec);
  }
  public static function fetchMe() {
    global $myUserId;
    $rec = new UserErx($myUserId);
    $rec->NcUser = new NcUser();
    return SqlRec::fetchOneBy($rec);
  }
}
