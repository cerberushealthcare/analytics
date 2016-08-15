<?php
require_once 'php/data/rec/sql/_UserRec.php';
//
/**
 * User Profile DAO
 * @author Warren Hornsby 
 */
class UserProfiles extends UserRec {
  //
  public function getMine() {
    $profile = Profile::getMine();
    return $profile;
  }
  public function saveUser($o) {
    global $login;
    $user = User_P::fetch($login->userId);
    if ($login->Role->Profile->license)
      $user->saveUi_nameLicense($o);
    else
      $user->saveUi_name($o);
    return Profile::getMine();
  }
  public function saveGroup($o) {
    global $login;
    $group = UserGroup_P::fetch($login->userGroupId);
    $group->saveUi($o);
    return Profile::getMine();
  }
  public function saveTimeout($min) {
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
  public /*BillInfo_P*/ $Bill;
  //
  static function getMine() {
    global $login;
    $me = new static();
    $me->User = User_P::fetch($login->userId);
    if ($login->Role->Profile->practice)
      $me->Group = UserGroup_P::fetch($login->userGroupId);
    if ($login->Role->Profile->billing)
      $me->Bill = BillInfo_P::fetch($login->userId);
    return $me;
  }
}
class User_P extends UserRec {
  //
  public $userId;
  public $uid;
  public $name;
  public $subscription;
  public $active;
  public $userGroupId;
  public $userType;
  public $licenseState;
  public $license;
  public $dea;
  public $npi;
  public $email;
  public $expiration;
  public $expireReason;
  public $roleType;
  public $mixins;
  public /*NcUser_P*/ $NcUser;
  //
  public function validate(&$rv) {
    $rv->requires('name', 'email');
  }
  public function saveUi_name($o) {
    $this->setUi_name($o);
    $this->save();
  }
  public function saveUi_nameLicense($o) {
    $this->setUi_name($o);
    $this->setUi_License($o);
    $this->save();
  }
  protected function setUi_name($o) {
    $this->name = $o->name;
    $this->email = $o->email;
  }
  protected function setUi_license($o) {
    $this->license = $o->license;
    $this->licenseState = $o->licenseState;
    $this->dea = $o->dea;
    $this->npi = $o->npi;
  }
  //
  static function fetch($id) {
    $c = new static($id);
    $c->NcUser = new NcUser_P();
    return static::fetchOneBy($c);
  }
}
class NcUser_P extends NcUser_Login {
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
}
class UserGroup_P extends UserGroupRec {
  //
  public $userGroupId;
  public $name;
  public $estTzAdj;
  public $sessionTimeout;
  public /*AddressUserGroup_P*/ $Address;
  //
  public function toJsonObject(&$o) {
    $o->Address->_estTzAdj = static::$TIMEZONES[$this->estTzAdj];
  }
  public function saveTimeout($min) {
    $min = intval($min);
    if ($min < 10 || $min > 60)
      $min = 60;
    $this->sessionTimeout = $min;
    $this->save();
  }
  public function saveUi($o) {
    $this->name = $o->name;
    $this->estTzAdj = $o->Address->estTzAdj;
    $this->save();
    $this->Address->saveUi($o->Address);
  }
  //
  static function fetch($id) {
    $c = new static($id);
    $c->Address = AddressUserGroup_P::asJoin();
    return static::fetchOneBy($c);
  }
}
class AddressUserGroup_P extends AddressUserGroup_Login {
  //
  public function saveUi($o) {
    $this->addr1 = $o->addr1;
    $this->addr2 = $o->addr2;
    $this->city = $o->city;
    $this->state = $o->state;
    $this->zip = $o->zip;
    $this->phone1 = $o->phone1;
    $this->phone2 = $o->phone2;
    $this->phone2Type = static::PHONE_TYPE_FAX;
    $this->save();
  }
}
class BillInfo_P extends BillInfoRec {
  //
  public $userId;
  public $name;
  public $address1;
  public $address2;
  public $city;
  public $state;
  public $zip;
  public $country;
  public $cardNumber;
  public $expMonth;
  public $expYear;
  public $phoneNum;
  public $phoneExt;
  public $cardType;
  public $billCode;
  //
}