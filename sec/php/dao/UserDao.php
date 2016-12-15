<?php
p_i('UserDao');
/*
 * User functions and profiles
 */
class UserDao {
  
  const WITH_CHILDREN = true;

  public static function getMyUser() {
    global $login;
    $user = UserDao::getUser($login->userId, UserDao::WITH_CHILDREN);
    $user->includeLicLine();
    $user->userGroup->address->includeAddrLine();
    return $user;
  }
  
  public static function getMyUserAsJson() {
    global $login;
    $user = UserDao::getMyUser();
    UserDao::eraseSensitive($user);
    $user->includeLicLine();
    $user->userGroup->address->includeAddrLine();
    return $user->out(JUser::WITH_USER_GROUP);
  }
  
  public static function eraseSensitive(&$user) {
    $user->pw = null;
    if (! $user->admin)
      $user->admin = null;
    $user->subscription = null;
    $user->expiration = null;
    $user->expireReason = null;
    $user->active = null;
    $user->trialExpDt = null;
    if ($user->userGroup) {
      $user->userGroup->usageLevel = null;
    }
  }
  
  public static function getUser($userId, $withChildren) {
    global $login;
    $user = UserDao::buildUser(fetch("SELECT user_id, uid, pw, name, admin, subscription, active, reg_id, trial_expdt, user_group_id, user_type, license_state, license, dea, npi, email, expiration, expire_reason FROM users WHERE user_id=" . $userId));
    if ($user != null) {
      LoginDao::authenticateUserGroupId($user->userGroupId);
      if ($withChildren) {
        $user->userGroup = UserDao::getUserGroup($user->userGroupId, UserDao::WITH_CHILDREN);
        if (! $login->isPapyrus())
          $user->billInfo = BillingDao::getBillInfo($userId, UserDao::WITH_CHILDREN);
      }
    }
    return $user;
  }
  public static function getSupportUser($userId) {
    return UserDao::getUser($userId, false);
  }
  
  public static function getNewSupportUser() {
    return new JUser(null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
  }
  
  public static function getMyUserGroup() {
    global $login;
    $userGroupId = $login->userGroupId;
    return MethodCache::getset(__METHOD__, func_get_args(), function() use ($userGroupId) {
      return UserDao::getUserGroup($userGroupId, true);
    });
  }
  
  public static function getUserGroup($userGroupId, $withChildren, $fromApi = false) {
    $userGroup = UserDao::buildUserGroup(fetch("SELECT user_group_id, name, usage_level, est_tz_adj, session_timeout FROM user_groups WHERE user_group_id=" . $userGroupId));
    if ($userGroup != null) {
      if (! $fromApi) LoginDao::authenticateUserGroupId($userGroupId);
      if ($withChildren) {
        $address = UserDao::getAddress(Address0::TABLE_USER_GROUPS, $userGroupId, Address0::ADDRESS_TYPE_SHIP);
        if ($address == null) {
          $address = new JAddress(null, "G", $userGroupId, "0", null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null);
        }
        $userGroup->address = $address;
      }
    }
    return $userGroup;
  }

  public static function getDocsOfGroupAsJson() {
    global $login;
    $docs = UserDao::getDocsOfGroup($login->userGroupId);
    return jsonencode($docs);   
  }
  
  public static function getDocsOfGroup($userGroupId) {
    //return MethodCache::getset(__METHOD__, func_get_args(), function() use ($userGroupId) {
      LoginDao::authenticateUserGroupId($userGroupId);
      $sql = "SELECT user_id, uid, pw, name, admin, subscription, active, reg_id, trial_expdt, user_group_id, user_type, license_state, license, dea, npi, email, expiration, expire_reason FROM users WHERE active=1 AND user_type=1 AND user_group_id=" . $userGroupId . " ORDER BY name";
      $res = query($sql);
      $dtos = array();
      while ($row = Dao::fetchRowFromResource($res)) { //mysql_fetch_array($res, MYSQL_ASSOC)) {
        $user = UserDao::buildUser($row);
        $user->includeLicLine();
        UserDao::eraseSensitive($user);
        $dtos[$row["user_id"]] = $user;
      }
      return $dtos;
    //});
  }

  public static function getUsersOfMyGroup($where = "") {
    $dtos = array();
    $res = UserDao::getUsersOfMyGroupAsRows($where);
    while ($row = Dao::fetchRowFromResource($res)) { //mysql_fetch_array($res, MYSQL_ASSOC)) {
      $dto = UserDao::buildUser($row);
      $dtos[] = $dto;
    }
    return $dtos;
  }
  
  public static function getNonDocsOfMyGroup() {
    return UserDao::getUsersOfMyGroup("user_type>1");
  }
  
  public static function getUsersOfMyGroupAsRows($where = "") {
    global $login;
    $andWhere = "";
    if ($where != "") {
      $andWhere = " AND " . $where;
    }
    LoginDao::authenticateUserGroupId($login->userGroupId);
    $sql = "SELECT user_id, uid, pw, name, admin, subscription, active, reg_id, trial_expdt, user_group_id, user_type, license_state, license, dea, npi, email, expiration, expire_reason FROM users WHERE user_group_id=" . $login->userGroupId . $andWhere . " ORDER BY active desc, user_type, name";
    return query($sql);
  }
  
  public static function getAddresses($table, $tableId) {
    $sql = "SELECT address_id, table_code, table_id, type, addr1, addr2, addr3, city, state, zip, country, phone1, phone1_type, phone2, phone2_type, phone3, phone3_type, email1, email2, name FROM addresses WHERE table_code=" . quote($table) . " AND table_id=" . $tableId . " ORDER BY type";
    $res = query($sql);
    $dtos = array();
    while ($row = Dao::fetchRowFromResource($res)) { //mysql_fetch_array($res, MYSQL_ASSOC)) {
      $dto = UserDao::buildAddress($row);
      $dtos[] = $dto;
    }
    return $dtos;
  }
  
  public static function getAddress($table, $tableId, $type) {
    return UserDao::buildAddress(fetch("SELECT address_id, table_code, table_id, type, addr1, addr2, addr3, city, state, zip, country, phone1, phone1_type, phone2, phone2_type, phone3, phone3_type, email1, email2, name FROM addresses WHERE table_code=" . quote($table) . " AND table_id=" . $tableId . " AND type=" . $type));
  }

  public static function getAddressById($addressId) {
    if ($addressId == null) return null;
    return UserDao::buildAddress(fetch("SELECT address_id, table_code, table_id, type, addr1, addr2, addr3, city, state, zip, country, phone1, phone1_type, phone2, phone2_type, phone3, phone3_type, email1, email2, name FROM addresses WHERE address_id=" . $addressId));
  }

  public static function updateMyUser($user) {
    MethodCache::clearAll(__CLASS__);
    LoginDao::authenticateUserId($user->id);
    $sql = "UPDATE users SET ";
    $sql .= "name=" . quote($user->name);
    $sql .= ", email=" . gquote($user, "email");
    $sql .= ", license_state=" . gquote($user, "licenseState");
    $sql .= ", license=" . gquote($user, "license");
    $sql .= ", dea=" . gquote($user, "dea");
    $sql .= ", npi=" . gquote($user, "npi");
    $sql .= " WHERE user_id=" . $user->id;
    return query($sql);
  }
  
  // $user = {id:user_id, cpw:"current", pw:"new"}
  /*
  public static function updateMyPw($user) {
    MethodCache::clearAll(__CLASS__);
    global $login;
    $id = get($user, "id", $login->userId);
    $cpw = get($user, "cpw", $login->ptpw);
    LoginDao::authenticateUserId($id);
    return LoginDao::changePw($id, $cpw, $user->pw);
  }
  */
  
  public static function updateMyUserGroup($userGroup, $fromApi = false) {
    MethodCache::clearAll(__CLASS__);
    if (! $fromApi) LoginDao::authenticateUserGroupId($userGroup->id);
    $sql = "UPDATE user_groups SET ";
    $sql .= "name=" . quote($userGroup->name);
    $sql .= ", est_tz_adj=" . quote($userGroup->estAdjust);
    $sql .= " WHERE user_group_id=" . $userGroup->id;
    query($sql);
    if (! $fromApi) {
      if ($userGroup->address != null) {
        if ($userGroup->address->id == null ) {
          SchedDao::addAddress($userGroup->address);
        } else {
          SchedDao::updateAddress($userGroup->address);
        }
      }
    }
  }
  
  public static function updateSupportUser($user) {
    MethodCache::clearAll(__CLASS__);
    if (get($user, "id") == null) {
      UserDao::insertSupportUser($user);
      return;
    }
    $other = UserLogin::fetchByEmail($user->email);
    if ($other && $other->userId != $user->id)
      throw new AddUserException('Email is already in use by another account.');
    LoginDao::authenticateSupportUserId($user->id);
    $sql = "UPDATE users SET ";
    $sql .= "name=" . quote($user->name);
    $sql .= ", email=" . quote($user->email);
    $sql .= ", user_type=" . quote($user->userType);
    $sql .= ", active=" . toBoolInt($user->active);
    $sql .= " WHERE user_id=" . $user->id;
    return query($sql);
  }
  
  public static function insertSupportUser($user) {
    MethodCache::clearAll(__CLASS__);
    global $login;
    $email = get($user, 'email');
    if ($email) {
      $other = UserLogin::fetchByEmail($email);
      if ($other)
        throw new AddUserException('Email is already in use.');
    } else {
      throw new AddUserException('Email is required.');
    }
    $user->admin = false;
    $user->subscription = User0::SUBSCRIPTION_FREE;
    $user->userGroupId = $login->userGroupId;
    $sql = "INSERT INTO users VALUES(NULL";
    $sql .= ", " . quote($user->uid);
    $sql .= ", " . quote(LoginDao::generateHash($user->pw));
    $sql .= ", " . quote($user->name);
    $sql .= ", " . toBoolInt($user->admin);
    $sql .= ", " . $user->subscription;
    $sql .= ", " . toBoolInt($user->active);
    $sql .= ", NULL";  // reg ID
    $sql .= ", NULL";  // trial exp dt
    $sql .= ", " . $user->userGroupId;
    $sql .= ", " . $user->userType;
    $sql .= ", NULL";  // date created
    $sql .= ", " . quote(get($user, 'licenseState'));
    $sql .= ", " . quote(get($user, 'license'));
    $sql .= ", NULL";  // DEA
    $sql .= ", NULL";  // NPI
    $sql .= ", " . quote($email);
    $sql .= ", NULL";  // expiration
    $sql .= ", NULL";  // expire reason
    $sql .= ", " . now();  // pw_expires
    $sql .= ", NULL";  // tos_accepted
    $sql .= ", NULL";  // role_type
    $sql .= ", NULL";  // mixins
    $sql .= ", NULL";  // reset_Hash
    $sql .= ")";
    insert($sql);
  }
  
  // Data builders
  public static function buildUser($row) {
    if (! $row) return null;
    return new JUser($row["user_id"], $row["uid"], $row["pw"], $row["name"], $row["admin"], $row["subscription"], $row["active"], $row["reg_id"], $row["trial_expdt"], $row["user_group_id"], $row["user_type"], null, $row["license_state"], $row["license"], $row["dea"], $row["npi"], $row["email"], $row["expiration"], $row["expire_reason"]);
  }
  public static function buildUserGroup($row) {
    if (! $row) return null;
    return new JUserGroup($row["user_group_id"], $row["name"], $row["usage_level"], $row["est_tz_adj"], $row['session_timeout']);
  }
  public static function buildAddress($row) {
    return SchedDao::buildJAddress($row);
  }
}
//
require_once "php/dao/_util.php";
require_once "php/dao/BillingDao.php";
require_once "php/dao/SchedDao.php";
require_once "php/data/db/User.php";
require_once "php/data/json/JUser.php";
require_once "php/data/json/JUserGroup.php";
require_once "php/data/json/JAddress.php";
?>