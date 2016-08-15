<?php
require_once 'php/data/rec/sql/_SqlRec.php';
require_once 'php/data/rec/sql/UserErx.php';
require_once 'php/newcrop/data/NCScript.php';
/**
 * NewCrop User Record
 */
class NcUser extends SqlRec implements NoUserGroup {
  //
  public $userId;
  public /*NCScript.UserType*/ $userType;
  public /*NCScript.RoleType*/ $roleType;
  //
  public static $USER_TYPES = array(
    UserType::LP => 'Licensed Prescriber',
    UserType::MIDLEVEL => 'Midlevel Prescriber',
    UserType::STAFF => 'Staff');
  public static $ROLE_TYPES = array(
    RoleType::DOCTOR => 'Doctor',
    RoleType::NURSE => 'Nurse',
    RoleType::NURSE_NO_RX => 'Nurse (No RX)',
    RoleType::MANAGER => 'Midlevel Prescriber');
  //
  public function getSqlTable() {
    return 'nc_users';
  }
  //
  /**
   * Static fetchers
   */
  public static function fetch($userId) {
    return SqlRec::fetch($userId, 'NcUser');
  }
  /**
   * @param int $ugid
   * @return array(
   *    userType=>[UserErx,..],..
   *  )
   */
  public static function fetchUsersInTypes($ugid) {
    $usersInTypes = array(
      UserType::LP => array(),
      UserType::MIDLEVEL => array(),
      UserType::STAFF => array());
    $users = UserErx::fetchAllByUgid($ugid);
    foreach ($users as &$user) {
      $user->loadNcUser();
      if ($user->NcUser) 
        $usersInTypes[$user->NcUser->userType][] = $user;
    }
    return $usersInTypes;
  }
}
