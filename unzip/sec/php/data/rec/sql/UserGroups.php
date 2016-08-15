<?php
require_once 'php/data/rec/sql/_UserRec.php';
require_once 'php/data/rec/sql/_UserGroupRec.php';
require_once 'php/data/rec/sql/_AddressRec.php';
require_once 'php/data/rec/sql/ErxUsers.php';
/**
 * User Groups DAO
 * @author Warren Hornsby
 */
class UserGroups {
  //
  static $first;
  //
  static function /*UserGroup*/getMine() {
    global $login;
    return UserGroup::fetch($login->userGroupId);
  }
  static function /*UserGroup*/getMineWithAddress() {
    global $login;
    return UserGroup_Doctors::fetch($login->userGroupId);
  }
  static function /*User_Any[id]*/getUserMap() {
    static $map;
    if ($map == null) {
      global $login;
      if ($login->super)
        $map = User_Any::fetchMap(null);
      else
        $map = User_Any::fetchMap($login->userGroupId);
    }
    return $map;
  }
  static function /*User_Any[]*/getAllUsers() {
    return MethodCache::getset(__METHOD__, func_get_args(), function() {
      global $login;
      $recs = User_Any::fetchAll($login->userGroupId);
      return Rec::sort($recs, new RecSort('userType', 'name'));
    });
  }
  static function /*User_Any[]*/getActiveUsers() {
    return MethodCache::getset(__METHOD__, func_get_args(), function() {
      global $login;
      $recs = User_Any::fetchActive($login->userGroupId);
      return $recs;
    });
  }
  static function /*User_Admin[]*/getAllAdmins() {
    global $login;
    $recs = User_Admin::fetchAll($login->userGroupId);
    return $recs;
  }
  static function /*User_Doctor[]*/getDocs() {
    return MethodCache::getset(__METHOD__, func_get_args(), function() {
      global $login;
      if ($login->admin)
        return User_Doctor::fetchAll($login->userGroupId);
      else
        return User_Doctor::fetchAllNonAdmin($login->userGroupId);
    });
  }
  static function /*string({id:'name',..})*/getDocsJsonList() {
    return MethodCache::getset(__METHOD__, func_get_args(), function() {
      $docs = UserGroups::getDocs();
      $list = array();
      foreach ($docs as $doc) 
        $list[$doc->userId] = $doc->name;
      return jsonencode($list);
    });
  }
  static function /*string({id:'name',..})*/getUsersJsonList() {
    return MethodCache::getset(__METHOD__, func_get_args(), function() {
      $users = UserGroups::getAllUsers();
      $list = array();
      foreach ($users as $user) 
        $list[$user->userId] = $user->name;
      return jsonencode($list);
    });
  }
  static function /*string({id:'name',..})*/getActiveUsersJsonList() {
    return MethodCache::getset(__METHOD__, func_get_args(), function() {
      $users = UserGroups::getActiveUsers();
      $list = array();
      foreach ($users as $user) 
        $list[$user->userId] = $user->name;
      return jsonencode($list);
    });
  }
  static function /*UserGroup[]*/getChildren($withAddress = false) {
    global $login;
    return UserGroup_Children::fetchAll($login->userGroupId, $withAddress);
  }
  static function getChildren_withAddress() {
    return static::getChildren(true);
  }
  static function /*string([group,..])*/getChildrenJsonList() {
    return '[]';
    return MethodCache::getset(__METHOD__, func_get_args(), function() {
      $groups = UserGroups::getChildren();
      return jsonencode($groups);
    });
  }
  static function /*int[]*/getChildrenUgids() {
    return MethodCache::getset(__METHOD__, func_get_args(), function() {
      global $login;
      $sql = "SELECT user_group_id FROM user_groups WHERE parent_id=$login->userGroupId";
      return Dao::fetchValues($sql);
    });
  }
  static function /*User_Doctor*/getFirstDoc() {
    if (static::$first == null)
      static::$first = current(static::getDocs());
    return static::$first;
  }
  static function /*string[]*/lookupUsers(/*int[]*/$ids) {
    $users = static::getUserMap();
    foreach ($ids as &$id) 
      $id = $users[$id]->name;
    return $ids;
  }
  static function /*string*/lookupUser($id) {
    $users = static::getUserMap();
    return $users[$id]->name;
  }
}
class UserGroup extends UserGroupRec {
  //
  public $userGroupId;
  public $parentId;
  public $name;
  public $usageLevel;
  public $estTzAdj;
  public $sessionTimeout;
  //
}
class UserGroup_Doctors extends UserGroup { 
  //
  static function fetch($ugid) {
    $me = parent::fetch($ugid);
    $me->Address = UserGroupAddress::fetch($ugid);
    $me->Doctors = User_Doctor::fetchMapWithErx($ugid);
    return $me;
  }
  //
  public function getDoctor($id) {
    if ($this->Doctors) {
      return geta($this->Doctors, $id);
    }
  }
}
class UserGroup_Children extends UserGroupRec implements NoAuthenticate {
  //
  public $userGroupId;
  public $parentId;
  public $name;
  //
  public function toJsonObject(&$o) {
    unset($o->parentId);
  }
  public function addCounts() {
    $this->_providers = count($this->Users);
    $this->_patients = number_format(Dao::fetchValue("SELECT COUNT(*) FROM clients WHERE user_group_id=$this->userGroupId"));
    $this->_lastUpdate = formatTimestamp(Dao::fetchValue("SELECT MAX(date) FROM audit_mrus WHERE user_group_id=$this->userGroupId"));
  }
  //
  static function fetchAll($ugid, $withAddress) {
    $c = new static();
    $c->parentId = $ugid;
    if ($withAddress) {
      $c->Address = UserGroupAddress::asJoin();
      $c->Users = User_Doctor::asJoinAll();
    } 
    $recs = static::fetchAllBy($c, new RecSort('name'));
    if ($withAddress) 
      $recs = static::addAllCounts($recs);
    return $recs;
  }
  protected static function addAllCounts($recs) {
    foreach ($recs as &$rec)
      $rec->addCounts();
    return $recs;
  }
}
class User_Any extends UserRec implements ReadOnly, NoAuthenticate {
  //
  public $userId;
  public $name;
  public $active;
  public $userGroupId;
  public $userType;
  //
  static function fetchMap($ugid) {
    $c = static::asCriteria($ugid);
    return static::fetchMapBy($c, 'userId');
  }
  static function fetchAll($ugid) {
    $c = static::asCriteria($ugid);
    return static::fetchAllBy($c);
  }
  static function fetchActive($ugid) {
    $c = static::asCriteria($ugid);
    $c->active = true;
    return static::fetchAllBy($c);
  }
  //
  static function asCriteria($ugid) {
    $c = new static();
    $c->userGroupId = $ugid;
    return $c;
  }
}
class User_Admin extends User_Any implements ReadOnly {
  //
  public $uid;
  public $name;
  public $admin;
  public $subscription;
  public $active;
  public $trialExpdt;
  public $userGroupId;
  public $userType;
  public $roleType;
  public $mixins;
  //
  static function fetchAll($ugid) {
    $users = static::fetchActive($ugid);
    $admins = array();
    foreach ($users as $user) {
      $role = UserRole::from($user);
      if ($role->isAdmin())
        $admins[] = $user;
    }
    return $admins;
  }
  
}
class User_Doctor extends User_Any implements ReadOnly {
  //
  public $userId;
  public $name;
  public $active;
  public $userGroupId;
  public $userType;
  //
  static function fetchAll($ugid, $active = true) {
    $c = static::asCriteria($ugid);
    $c->active = $active;
    return static::fetchAllBy($c);
  }
  static function fetchAllNonAdmin($ugid, $active = true) {
    $c = static::asCriteria($ugid);
    $c->active = $active;
    $c->admin = '0';
    return static::fetchAllBy($c);
  }
  static function fetchMapWithErx($ugid) {
    $c = static::asCriteria($ugid);
    $c->NcUser = new NcUser();
    return static::fetchMapBy($c, 'userId');
  }
  static function asJoin($fid = null) {
    $c = new static();
    $c->NcUser = new NcUser();
    return CriteriaJoin::requires($c, $fid); 
  }
  static function asOptionalJoin($fid = null) {
    $c = new static();
    $c->NcUser = new NcUser();
    return CriteriaJoin::optional($c, $fid); 
  }
  //
  static function asCriteria($ugid) {
    $c = parent::asCriteria($ugid);
    $c->userType = static::TYPE_DOCTOR;
    return $c;
  }
  static function asJoinAll() {
    $c = static::asCriteria(null);
    return CriteriaJoin::requiresAsArray($c); 
  }
}
