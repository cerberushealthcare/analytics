<?php
require_once 'php/data/rec/sql/_UserRec.php';
require_once 'php/data/rec/sql/ErxUsers.php';
//
/**
 * Account/Security Management (for users)    
 * @author Warren Hornsby 
 */
class UserManager {
  //
  static function getMine() {
    global $login;
    return User_M::fetchAll($login->userGroupId);
  }
  static function save($o) {
    global $login;
    $user = User_Mui::save_from($login->userGroupId, $o);
    return $user;
  }
  static function deactivate($userId) {
    $user = User_M::deactivate($userId);
    return $user;
  }
  static function activate($userId) {
    $user = User_M::activate($userId);
    return $user;
  }
  static function saveErx($o) {
    if ($o->userId) {
      $user = User_M::fetch($o->userId); 
      $ncuser = NcUser_Mui::save_from($user, $o);
      return $ncuser;
    }
  }
  static function removeErx($id) {
    $id = NcUser_M::remove($id);
    return $id;
  }
}
class User_M extends UserRec {
  //
  public $userId;
  public $uid;
  public $pw;
  public $name;
  public $active;
  public $subscription;
  public $userGroupId;
  public $userType;
  public $email;
  public $roleType;
  public $pwExpires;
  public $mixins;
  public /*NcUser_P*/ $NcUser;
  //
  public function getJsonFilters() {
    return array(
    	'active' => JsonFilter::boolean(),
      'pw' => JsonFilter::omit(),
    	'pwExpires' => JsonFilter::omit());
  }
  public function toJsonObject(&$o) {
    $o->roleType = UserRole::getRoleType($this);
    $o->lookup('roleType', UserRole::$TYPES);
    unset($o->userType);
  }
  //
  static function deactivate($userId) {
    if ($userId) {
      $rec = static::fetch($userId);
      $rec->active = false;
      $rec->save();
      return $rec;
    }
  }
  static function activate($userId) {
    if ($userId) {
      $rec = static::fetch($userId);
      $rec->active = true;
      $rec->save();
      return $rec;
    }
  }
  static function fetch($id) {
    global $login;
    $c = static::asCriteria($login->userGroupId, $id);
    return static::fetchOneBy($c);
  }
  static function fetchAll($ugid) {
    $c = static::asCriteria($ugid);
    return static::fetchAllBy($c, new RecSort('-active', 'roleType', 'name'));
  }
  static function fetchAllActive($ugid) {
    $c = static::asCriteria($ugid);
    $c->active = true;
    return static::fetchAllBy($c, new RecSort('roleType', 'name'));
  }
  static function asCriteria($ugid, $userId = null) {
    $c = new static();
    $c->userGroupId = $ugid;
    $c->userId = $userId;
    $c->NcUser = new NcUser_M();
    return $c;
  }
}
class User_Mui extends User_M {
  //
  public function validate(&$rv) {
    $rv->requires('uid', 'name', 'email');
  }
  public function apply($o) {
    $this->name = $o->name;
    $this->email = $o->email;
    $this->roleType = $o->roleType;
    $this->mixins = static::mixins_from($o);
  }
  //
  static function save_from($ugid, $o) {
    $rec = static::from($ugid, $o);
    $rec->save();
    return $rec;
  }
  static function from($ugid, $o) {
    if (empty($o->userId)) {
      $me = User_MuiNew::from($ugid, $o);
    } else {
      $me = static::fetch($o->userId);
      $me->apply($o);
    }
    return $me;
  } 
  protected static function mixins_from($o) {
    return MixinRole::getIdString(
      $o->mix_ad,
      $o->mix_pa,
      $o->mix_au,
      $o->mix_rb,
      get($o, 'mix_bi'));
  }
}
class User_MuiNew extends User_Mui {
  //
  static $FRIENDLY_NAMES = array(
    'pw' => 'Password');
  //
  public function validate(&$rv) {
    $rv->requires('uid', 'pw', 'name', 'email');
  }
  //
  static function from($ugid, $o) {
    $me = new static();
    $me->uid = $o->uid;
    $me->active = true;
    $me->userType = static::TYPE_SUPPORT;
    $me->subscription = static::SUBSCRIPTION_FREE;  
    $me->userGroupId = $ugid;
    $me->setPassword_asTemporary($o->pw);
    $me->apply($o);
    return $me;
  }
}
class NcUser_M extends NcUser {
  //
  public $userId;
  public $userType;
  public $roleType;
  public $partnerId;
  public $nameLast;
  public $nameFirst;
  public $nameMiddle;
  public $namePrefix;
  public $nameSuffix;
  public $freeformCred;
  //
  public function toJsonObject(&$o) {
    $o->namePrefix = array_search(get($o, 'namePrefix'), static::$PREFIXES);
    $o->nameSuffix = array_search(get($o, 'nameSuffix'), static::$SUFFIXES);
  } 
  static function remove($id) {
    $me = static::fetch($id);
    if ($me) {
      static::delete($me);
      return $id;
    }
  }
}
class NcUser_Mui extends NcUser_M {
  //
  public function validate(&$rv) {
    $rv->requires('nameLast', 'nameFirst');
  }
  //
  static function save_from($user, $o) {
    $rec = static::from($user, $o);
    $rec->saveAsInsertOnDupeUpdate();
    return $rec;
  }
  static function from($user, $o) {
    $me = new static();
    $me->userId = $o->userId;
    $me->nameLast = $o->nameLast;
    $me->nameFirst = $o->nameFirst;
    $me->nameMiddle = $o->nameMiddle;
    $me->namePrefix = geta(static::$PREFIXES, $o->namePrefix);
    $me->nameSuffix = geta(static::$SUFFIXES, $o->nameSuffix);
    $me->freeformCred = $o->freeformCred;
    $me->partnerId = get($o, 'partnerId');
    if ($user->isDoctor()) { 
      $me->userType = $o->userType;
      $me->roleType = 'doctor'; 
    } else {
      $me->userType = 'Staff';
      $me->roleType = $o->roleType;
    }
    return $me;
  } 
}
