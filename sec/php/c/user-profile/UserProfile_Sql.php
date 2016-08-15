<?php
require_once 'php/data/rec/sql/_UserRec.php';
require_once 'php/data/rec/sql/_BillingRecs.php';
//
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
  public function validate(&$rv) {
    $rv->requires('name');
  }
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
  static $FRIENDLY_NAMES = array(
    'addr1' => 'Address (Line 1)',
    'phone1' => 'Phone',
    'phone2' => 'Fax');
  //
  public function validate(&$rv) {
    $rv->requires('addr1', 'city', 'state', 'zip', 'phone1', 'phone2');
  }
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
class BillStatus_P extends BillStatusRec implements ReadOnly {
  //
  public $userId;
  public $planId;
  public $sourceId;
  public $active;
  public $inactiveCode;
  public /*BillPlan_P*/ $BillPlan;
  public /*BillSource_P*/ $BillSource;
  //
  public function unsetCardInfo($userId) {
    if ($this->BillSource && $this->isSharingSource())
      $this->BillSource->unsetCardInfo();
  }
  //
  static function fetch($userId) {
    $c = static::asCriteria($userId);
    $me = static::fetchOneBy($c);
    if ($me)
      $me->unsetCardInfo($userId);
    return $me;
  }
  static function asCriteria($userId) {
    $c = new static($userId);
    $c->BillPlan = BillPlan_P::asRequiredJoin('planId');
    $c->BillSource = BillSource_P::asRequiredJoin('sourceId');
    return $c;
  }
}
class BillPlan_P extends BillPlanRec {
  //
  public $billPlanId;
  public $desc;
}
class BillSource_P extends BillSourceRec {
  //
  public $userId;
  public $type;
  public $name;
  public $addr1;
  public $addr2;
  public $city;
  public $state;
  public $zip;
  public $country;
  public $phone;
  public $cardType;
  public $cardNumber;
  public $expMonth;
  public $expYear;
  public $email;
  //
  static $FRIENDLY_NAMES = array(
    'addr1' => 'Address (Line 1)',
    'expMonth' => 'Expiration Month', 
    'expYear' => 'Expiration Year');
  //
  public function validate(&$rv) {
    $rv->requires('name', 'addr1', 'city', 'state', 'zip', 'phone', 'cardType', 'cardNumber', 'expMonth', 'expYear', 'email');
  }
  public function toJsonObject(&$o) {
    $o->_label = $this->getLabel();
  }
  public function getLabel() {
    switch ($this->type) {
      case static::TYPE_CARD:
        return static::getLabelCard();
      case static::TYPE_INVOICE:
        return static::getLabelInvoice();
    }
  }
  protected function getLabelCard() {
    if ($this->cardType && $this->cardNumber) {
      $label = static::$CARD_TYPES[$this->cardType] . ' ...' . substr($this->cardNumber, -4);
      if ($this->expMonth && $this->expYear)
        $label .= " Exp $this->expMonth/$this->expYear";
    } else {
      $label = $this->name;
    }
    return $label;
  }
  protected function getLabelInvoice() {
    $label = 'Invoice: ' . $this->name;
    return $label;
  }
  public function unsetCardInfo() {
    $this->cardType = null;
    $this->cardNumber = null;
    $this->expMonth = null;
    $this->expYear = null;
  }
  public function saveUi($o) {
    $this->name = $o->name;
    $this->addr1 = $o->addr1;
    $this->addr2 = $o->addr2;
    $this->city = $o->city;
    $this->state = $o->state;
    $this->zip = $o->zip;
    $this->phone = $o->phone;
    $this->cardType = $o->cardType;
    $this->cardNumber = $o->cardNumber;
    $this->expMonth = $o->expMonth;
    $this->expYear = $o->expYear;
    $this->email = $o->email;
    $this->save();
  }
}
