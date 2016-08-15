<?php
require_once 'php/data/rec/sql/_NcUserRec.php';
require_once 'php/newcrop/data/NCScript.php';
/**
 * ERX Users 
 * DAO for ErxUser, NCUser
 * @auther Warren Hornsby  
 */
class ErxUsers {
  /**
   * @return array(
   *   'me'=>ErxUser, 
   *   'lps'=>array(ErxUser,..),
   *   'lp'=>ErxUser, 
   *   'staff'=>ErxUser)
   */
  static function getMyGroup() {
    //return MethodCache::getset(__METHOD__, func_get_args(), function() {
      global $login;
      $me = ErxUsers::getMe();
      if ($me->NcUser) {
        $lps = ErxUser::fetchAllLps($login->userGroupId);
        $lp = ErxUsers::getMyLp($me, $lps);
        $staff = ErxUsers::getMyStaff($me);
        return array(
          'me' => $me,
          'lps' => $lps,
        	'lp' => $lp,
          'staff' => $staff);
      }
    //});
  }
  /**
   * @return ErxUser
   */
  static function get($id, $ugid) {
    //return MethodCache::getset(__METHOD__, func_get_args(), function() use ($id, $ugid) {
      $user = ErxUser::fetch($id, $ugid);
      if ($user->NcUser) {
        if ($user->NcUser->partnerId) 
          $user->Partner = ErxUser::fetch($user->NcUser->partnerId, $ugid);
        if ($user->NcUser->partnerId == null || $user->Partner == null) {  // try to default partner if not found
          if ($user->NcUser->isLp()) 
            $user->Partner = static::getMyStaff($user);
          else 
            $user->Partner = static::getMyLp($user);
        }
        $user->_lpId = ($user->NcUser->isLp()) ? $id : $user->NcUser->partnerId;
        $user->_lpName = ($user->NcUser->isLp()) ? $user->name : $user->Partner->name; 
      }  
      return $user;
    //});
  }
  static function getMe() {
    global $login;
    if ($login->userId)
      return self::get($login->userId, $login->userGroupId);
  }
  /**
   * @return array('Doc Name',..)
   */
  static function getMyLpNames() {
    //return MethodCache::getset(__METHOD__, func_get_args(), function() {
      global $login;
      $lps = ErxUser::fetchAllLps($login->userGroupId);
      $lpNames = array();
      foreach ($lps as $lp)
        $lpNames[] = $lp->name;
      return $lpNames;
    //});
  }
  //
  static function getMyLp($me, $lps = null) {
    if ($me->NcUser->isLp())
      return $me;
    if ($me->Partner && $me->Partner->NcUser->isLp())
      return $me->Partner;
    if ($lps)
      return current($lps);
    else
      return ErxUser::fetchLp($me->userGroupId);
  }
  //
  static function getMyStaff($me) {
    if ($me->NcUser->isStaff())
      return $me;
    if ($me->Partner && $me->Partner->NcUser->isStaff())
      return $me->Partner;
    return ErxUser::fetchStaff($me->userGroupId);
  }
}
/**
 * ERX User
 */
class ErxUser extends SqlRec implements NoAuthenticate {
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
  public /*ErxUser*/ $Partner;
  //
  public function getSqlTable() {
    return 'users';
  }
  //
  /**
   * @param int $userId
   * @return ErxUser
   */
  static function fetch($userId, $ugid) {
    $c = new ErxUser();
    $c->userId = $userId;
    $c->userGroupId = $ugid;
    $c->NcUser = new NcUser($userId);
    return parent::fetchOneBy($c);
  }
  /**
   * @param int $ugid
   * @param string $userType @see NCScript.UserType  
   * @return ErxUser 
   */
  static function fetchByType($ugid, $userType) {
    $rec = ErxUser::asCriteria($ugid, $userType);
    $rec = parent::fetchOneBy($rec);
    return $rec;
  }
  /**
   * @param int $ugid
   * @return ErxUser 
   */
  static function fetchLp($ugid) {
    return ErxUser::fetchByType($ugid, UserType::LP);
  }
  /**
   * @param int $ugid
   * @return array(ErxUser,..)
   */
  static function fetchAllLps($ugid) {
    $rec = ErxUser::asCriteria($ugid, UserType::LP);
    $recs = parent::fetchAllBy($rec);
    return $recs;
  } 
  /**
	 * @param int $ugid
   * @return ErxUser 
   */
  static function fetchStaff($ugid) {
    return ErxUser::fetchByType($ugid, UserType::STAFF);
  }
  //
  private static function asCriteria($ugid, $userType) {
    $rec = new ErxUser();
    $rec->userGroupId = $ugid;
    $rec->NcUser = CriteriaJoin::requires(NcUser::asCriteria($userType));
    return $rec;
  }
}
/**
 * NewCrop User
 */
class NcUser extends NcUserRec {
  //
  static $USER_TYPES = array(
    UserType::LP => 'Licensed Prescriber',
    UserType::MIDLEVEL => 'Midlevel Prescriber',
    UserType::STAFF => 'Staff');
  static $PROVIDER_USER_TYPES = array(
    UserType::LP => 'Licensed Prescriber',
    UserType::MIDLEVEL => 'Midlevel Prescriber');
  static $ROLE_TYPES = array(
    RoleType::DOCTOR => 'Doctor',
    RoleType::NURSE => 'Nurse',
    RoleType::NURSE_NO_RX => 'Nurse (No RX)',
    RoleType::MANAGER => 'Midlevel Prescriber');
  static $STAFF_ROLE_TYPES = array(
    RoleType::NURSE => 'Nurse',
    RoleType::NURSE_NO_RX => 'Nurse (No RX)');
  //
  public function isLp() {
    return $this->userType == UserType::LP;
  }
  public function isStaff() {
    return $this->userType == UserType::STAFF;
  }
  /**
   * @param NcScript.UserType $userType
   * @return NcUser
   */
  static function asCriteria($userType) {
    $c = new self();
    $c->userType = $userType;
    return $c;
  }
}
