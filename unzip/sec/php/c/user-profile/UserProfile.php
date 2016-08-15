<?php
require_once 'php/c/user-profile/UserProfile_Sql.php';
//
/**
 * User Profile 
 * @author Warren Hornsby 
 */
class UserProfile {
  //
  static function /*Profile*/getMine() {
    $profile = Profile::getMine();
    return $profile;
  }
  static function /*Profile*/saveUser($o) {
    global $login;
    $user = User_P::fetch($login->userId);
    if ($login->Role->Profile->license)
      $user->saveUi_nameLicense($o);
    else
      $user->saveUi_name($o);
    return Profile::getMine();
  }
  static function /*Profile*/saveGroup($o) {
    global $login;
    $group = UserGroup_P::fetch($login->userGroupId);
    $group->saveUi($o);
    return Profile::getMine();
  }
  static function /*Profile*/saveBilling($o) {
    global $login;
    $bill = BillSource_P::fetch($login->userId);
    $bill->saveUi($o);
    return Profile::getMine();
  }
  static function /*Profile*/saveTimeout($min) {
    global $login;
    $group = UserGroup_P::fetch($login->userGroupId);
    $group->saveTimeout($min);
    $login->refresh();
    return Profile::getMine();
  }
}
//
class Profile extends Rec {
  //
  public /*User_P*/ $User;
  public /*UserGroup_P*/ $Group;
  public /*BillStatus_P*/ $Bill;
  //
  static function getMine() {
    global $login;
    $me = new static();
    $me->User = User_P::fetch($login->userId);
    if ($login->Role->Profile->practice)
      $me->Group = UserGroup_P::fetch($login->userGroupId);
    //if ($login->Role->Profile->billing)
    //  $me->Bill = BillStatus_P::fetch($login->userId);
    return $me;
  }
}
