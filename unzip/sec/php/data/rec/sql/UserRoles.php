<?php
require_once 'php/data/rec/_Rec.php';
//
/**
 * Role
 */
abstract class Role extends Rec {
  //
  /* Perms */
  public /*Perms_Profile*/ $Profile;
  public /*Perms_Account*/ $Account;
  public /*Perms_Patient*/ $Patient;
  public /*Perms_Artifact*/ $Artifact;
  public /*Perms_Report*/ $Report;
  public /*Perms_Message*/ $Message;
  public /*Perms_Cerberus*/ $Cerberus;
  //
  protected static function byName($name) {
    $me = new static();
    $me->_name = $name;
    foreach ($me->getPermFids() as $fid) {
      $class = "Perms_$fid";
      $me->$fid = $class::byRoleName($name);
    }
    return $me;
  }
  //
  protected function getPermFids() {
    return array_keys($this->getPerms());
  }
  protected function getPerms() {
    $perms = get_object_vars($this);
    foreach ($perms as $fid => $value)
      if (substr($fid, 0, 1) == '_')
        unset($perms[$fid]);
    return $perms;
  }
}
/**
 * User Role
 */
class UserRole extends Role {
  //
  const TYPE_TRIAL = 0;
  const TYPE_PROVIDER_PRIMARY = 10;
  const TYPE_PROVIDER = 11;
  const TYPE_CLINICAL = 20;
  const TYPE_CLERICAL = 30;
  const TYPE_EMER = 88;
  const TYPE_PATIENT_PORTAL = 99;
  static $TYPES = array(
    self::TYPE_TRIAL => 'Provider (Trial)',
    self::TYPE_PROVIDER_PRIMARY => 'Provider (Primary)',
    self::TYPE_PROVIDER => 'Provider',
    self::TYPE_CLINICAL => 'Clinical',
    self::TYPE_CLERICAL => 'Clerical',
    self::TYPE_EMER => 'Emergency'
  );
  static $SUPPORT_TYPES = array(
    self::TYPE_CLINICAL => 'Clinical',
    self::TYPE_CLERICAL => 'Clerical',
    self::TYPE_EMER => 'Emergency'
  );
  //
  public function isPrimaryProvider() {
    return $this->_type == static::TYPE_PROVIDER_PRIMARY;
  }  
  public function isAdmin() {
    return $this->_role->isAdmin();
  }
  public function toJsonObject(&$o) {
    unset($o->_role);
  }
  /**
   * @param UserLogin $user
   * @return Role 
   */
  static function from($user, $cerberus = 'fetch') {
    if ($cerberus == 'fetch') {
      global $login;
      $cerberus = $login->cerberus;
    }
    $type = static::getRoleType($user);
    $role = ($user->active) ? static::getActiveRole($type, $user->mixins) : static::getInactiveRole($type);
    logit_r($role, '111 role');
    $me = static::fromRole($role);
    logit_r($me, '111 me');
    if ($cerberus == null) {
      $me->Cerberus = Perms_Cerberus::asNone();
    } else {
      $me->Patient->create = 0;
    }
    if (isset($user->UserGroup))
      $me->erx = $user->UserGroup->isErx() && $user->canErx();
    $me->_type = $type;
    return $me;
  }
  static function getRoleType($user) {
    if ($user->isOnTrial())
      return UserRole::TYPE_TRIAL;
    if ($user->roleType)
      return intval($user->roleType);
    switch ($user->userType) {
      case 1:
        return static::TYPE_PROVIDER_PRIMARY;
      case 2:
        return static::TYPE_CLINICAL;
      case 88:
        return static::TYPE_EMER;
      default:
        return static::TYPE_CLERICAL;
    }
  }
  //
  protected static function fromRole($role) {
    $me = new static();
    $me->_role = $role;
    foreach ($role->getPerms() as $fid => $perm)
      $me->$fid = $perm;
    return $me;
  }
  protected static function getActiveRole($type, $mixinIds) {
    $mixins = MixinRole::fromIdString($mixinIds);
    switch ($type) {
      case static::TYPE_PROVIDER_PRIMARY:
        return PrimaryRole::asPrimaryProvider();
      case static::TYPE_PROVIDER:
        return PrimaryRole::asProvider($mixins);
      case static::TYPE_CLINICAL:
        return PrimaryRole::asClinical($mixins);
      case static::TYPE_CLERICAL:
        return PrimaryRole::asClerical($mixins);
      case static::TYPE_TRIAL:
        return PrimaryRole::asTrial();
      case static::TYPE_PATIENT_PORTAL:
        return PrimaryRole::asInactive();
      case static::TYPE_EMER:
        return PrimaryRole::asInactive();
      default:
        return PrimaryRole::asInactive();
    }
  }
  protected static function getInactiveRole($type) {
    switch ($type) {
      case static::TYPE_PROVIDER_PRIMARY:
        return PrimaryRole::asInactivePrimaryProvider();
      default:
        return PrimaryRole::asInactive();
    }
  }
}
/**
 * Primary Roles
 */
class PrimaryRole extends Role {
  //
  const NAME_TRIAL = 'Trial';
  const NAME_PROVIDER = 'Provider';
  const NAME_CLINICAL = 'Clinical';
  const NAME_CLERICAL = 'Clerical';
  const NAME_INACTIVE_PRIMARY = 'InactivePrimaryProvider';   
  const NAME_INACTIVE = 'Inactive';
  //
  public function isAdmin() {
    return MixinRole::hasAdmin($this->_mixins);
  }
  //
  static function asTrial() {
    return static::byName(static::NAME_TRIAL);
  }
  static function asPrimaryProvider() {
    $mixins = MixinRole::asPrimaryProvider();
    return static::asProvider($mixins);
  }
  static function asInactivePrimaryProvider() {
    return static::byName(static::NAME_INACTIVE_PRIMARY);
  }
  static function asProvider($mixins) {  
    return static::byName(static::NAME_PROVIDER, $mixins);
  }
  static function asClinical($mixins) {
    return static::byName(static::NAME_CLINICAL, $mixins);
  }
  static function asClerical($mixins) {
    return static::byName(static::NAME_CLERICAL, $mixins);
  }
  static function asInactive() {
    return static::byName(static::NAME_INACTIVE);
  }
  //
  protected static function byName($name, $mixins = null) {
    $me = parent::byName($name);
    $me->_mixins = $mixins;
    if ($mixins)
      $me->blend($mixins);
    return $me;
  }
  protected function blend($mixins) {
    foreach ($mixins as $role) {
      foreach ($role->getPerms() as $fid => $perm)
        if ($perm) 
          $this->$fid = $this->$fid->blend($perm);
    }
    return $this;
  }
}
/**
 * Additional (Mixin) Roles
 */
class MixinRole extends Role {
  //
  const NAME_ADMIN = 'Admin';  // office administrator
  const NAME_PAYER = 'Payer';  // manage office billing
  const NAME_AUDITOR = 'Auditor';  // run audit reports
  const NAME_BUILDER = 'ReportBuilder';  // build reports
  const NAME_BILLER = 'Biller';  // manage patient billing
  //
  static $NAME_BY_ID = array(
    'AD' => self::NAME_ADMIN,
    'PA' => self::NAME_PAYER,
    'AU' => self::NAME_AUDITOR,
    'RB' => self::NAME_BUILDER,
    'BI' => self::NAME_BILLER);
  //
  public function isAdmin() {
    return $this->_name == static::NAME_ADMIN; 
  }
  /**
   * @param string $s 'AD|RB'
   * @return array(MixinRole,..)
   */
  static function fromIdString($s) {
    $recs = array();
    if (! empty($s)) {
      $ids = explode('|', $s);
      foreach ($ids as $id)
        $recs[] = self::byId($id);
    }
    return $recs;
  }
  static function getIdString($isAdmin, $isPayer, $isAuditor, $isBuilder, $isBiller) {
    $a = array();
    if ($isAdmin) 
      $a[] = 'AD';
    if ($isPayer) 
      $a[] = 'PA';
    if ($isAuditor) 
      $a[] = 'AU';
    if ($isBuilder) 
      $a[] = 'RB';
    if ($isBiller) 
      $a[] = 'BI';
    return implode('|', $a);
  }
  static function asPrimaryProvider() {
    return array(
      static::byName(self::NAME_ADMIN),
      static::byName(self::NAME_PAYER),
      static::byName(self::NAME_AUDITOR),
      static::byName(self::NAME_BUILDER),
      static::byName(self::NAME_BILLER));
  }
  static function hasAdmin($mixins) {
    if (! empty($mixins))
      foreach ($mixins as $me)
        if ($me->isAdmin())
          return true;
  }
  //
  protected static function byId($id) {
    $name = self::$NAME_BY_ID[$id];
    $role = static::byName($name);
    return $role;
  }
}
/** 
 * Permissions
 */
class Perms extends Rec {
  //
  /* Flags */
  /**
   * @param (Perm,..)
   * @return static set by augmenting supplied perms  
   */
  public function blend() {
    $perms = func_get_args();
    foreach ($perms as $perm)
      foreach ($perm->getFlags() as $flag => $set)
        if ($set)
          $this->$flag = 1;
    return $this;
  }
  /**
   * @return true if user has access to at least one perm
   */
  public function any() {
    foreach ($this->getFlags() as $flag => $set)
      if ($set)
        return true;
  }
  //
  /**
   * @param string $name 'Provider'
   * @return static
   */
  static function byRoleName($name) {
    $method = "as$name";
    if (method_exists(get_called_class(), $method))
      return static::$method();
  }
  //
  static function asNone() {
    return new static();
  }
  private function getFlags() {
    return get_object_vars($this);
  }
}
class Perms_Profile extends Perms {
  public $name;      // can update
  public $license;   // can update
  public $practice;  // can update
  public $billing;   // can update
  //
  static function asTrial() {
    $me = new static();
    $me->name = 1;
    $me->license = 1;
    $me->practice = 1;
    $me->billing = 1;
    return $me;
  }
  static function asProvider() {
    $me = new static();
    $me->name = 1;
    $me->license = 1;
    return $me;
  }
  static function asClinical() {
    return static::asClerical();
  }
  static function asClerical() {
    $me = new static();
    $me->name = 1;
    return $me;
  }
  static function asInactivePrimaryProvider() {
    $me = new static();
    $me->billing = 1;
    return $me;
  }
  static function asInactive() {
    return static::asNone();
  }
  // Mixins 
  static function asAdmin() {
    $me = new static();
    $me->name = 1;
    $me->practice = 1;
    return $me;
  }
  static function asPayer() {
    $me = new static();
    $me->billing = 1;
    return $me;
  }
}
class Perms_Account extends Perms {
  public $manage;   // manage provider/support accounts 
  public $portal;   // manage portal accounts
  //
  static function asTrial() {
    return static::asNone();
  }
  static function asProvider() {
    return static::asClinical();
  }
  static function asClinical() {
    return static::asClerical();
  }
  static function asClerical() {
    $me = new static();
    $me->portal = 1;
    return $me;
  }
  static function asInactivePrimaryProvider() {
    return static::asNone();
  }
  static function asInactive() {
    return static::asNone();
  }
  // Mixins 
  static function asAdmin() {
    $me = new static();
    $me->manage = 1;
    $me->portal = 1;
    return $me;
  }
}
class Perms_Patient extends Perms {
  public $demo;       // record and search
  public $create;     // create
  public $facesheet;  // open
  public $vitals;     // record
  public $diagnoses;  // record
  public $immuns;     // record
  public $track;      // record
  public $history;    // record
  public $cds;        // customize ipcs and ipchms
  public $sched;      // record
  //
  static function asTrial() { 
    return static::asClinical();
  }
  static function asProvider() {
    return static::asClinical();
  }
  static function asClinical() {
    $me = new static();
    $me->vitals = 1;
    $me->diagnoses = 1;
    $me->immuns = 1;
    $me->cds = 1;
    $me->history = 1;
    return $me->blend(static::asClerical());
  } 
  static function asClerical() {
    $me = new static();
    $me->create = 1;
    $me->facesheet = 1;
    $me->demo = 1;
    $me->track = 1;
    $me->sched = 1;
    return $me;
  }
  static function asInactivePrimaryProvider() {
    return static::asInactive();
  }
  static function asInactive() {
    $me = new static();
    $me->facesheet = 1;
    return $me;
  }
}
class Perms_Artifact extends Perms {
  public $markReview;    // for all artifacts
  public $markPortal;    // for all artifacts
  public $noteRead;      // medNote: can read mine    
  public $noteCreate;    // medNote: can read all and create
  public $noteSign;      // medNote
  public $noteAddendum;  // medNote
  public $noteReplicate;
  public $documents;     // can manage all non-medNote
  public $templates;     // can manage
  public $scan;          // scan and manage providers/facilities
  public $labs;          // reconcile  
  public $hl7;           // create and download
  //
  static function asTrial() {
    $me = new static();
    $me->markReview = 1;
    $me->noteRead = 1;
    $me->noteCreate = 1;
    $me->noteSign = 1;
    $me->noteAddendum = 1;
    $me->scan = 1;
    $me->labs = 1;
    $me->hl7 = 1;
    return $me;
  }
  static function asProvider() {
    $me = new static();
    $me->markReview = 1;
    $me->noteSign = 1;
    $me->noteAddendum = 1;
    return $me->blend(static::asClinical(), static::asClerical());
  }
  static function asClinical() {
    $me = new static();
    $me->markPortal = 1;
    $me->noteCreate = 1;
    $me->noteReplicate = 1;
    return $me->blend(static::asClerical());
  }
  static function asClerical() {
    $me = new static();
    $me->templates = 1;
    $me->noteRead = 1;
    $me->scan = 1; 
    $me->labs = 1; 
    $me->hl7 = 1;
    return $me;
  }
  static function asInactivePrimaryProvider() {
    $me = new static();
    $me->noteRead = 1;
    return $me;
  }
  static function asInactive() {
    return static::asNone();
  }
} 
class Perms_Report extends Perms {
  public $patient;  // can
  public $cds;      // can
  public $audit;    // can
  public $builder;  // can
  public $pqri;     // can
  //
  static function asTrial() {
    $me = new static();
    $me->patient = 1;
    $me->cds = 1;
    $me->audit = 1;
    $me->builder = 1;
    $me->pqri = 1;
    return $me;
  }
  static function asProvider() {
    return static::asClinical();
  }
  static function asClinical() {
    return static::asClerical();
  }
  static function asClerical() {
    $me = new static();
    $me->patient = 1;
    $me->cds = 1;
    $me->pqri = 1;
    return $me;
  }
  // Mixins 
  static function asReportBuilder() {
    $me = new static();
    $me->builder = 1;
    return $me;
  }
  static function asAuditor() {
    $me = new static();
    $me->audit = 1;
    return $me;
  } 
  static function asInactivePrimaryProvider() {
    return static::asNone();
  }
  static function asInactive() {
    return static::asNone();
  }
}
class Perms_Cerberus extends Perms {
  public $insurance;  // can update
  public $superbill;  // can open and update
  //
  static function asProvider() {
    return static::asBiller();
  }
  static function asClinical() {
    return static::asClerical();
  }
  static function asClerical() {
    $me = new static();
    $me->insurance = 1;
    return $me;
  } 
  // Mixins
  static function asBiller() {
    $me = new static();
    $me->superbill = 1;
    return $me;
  }
}
class Perms_Message extends Perms { 
  public $general;  // general messages
  public $patient;  // patient messages
  public $portal;   // portal messages
  //
  static function asTrial() {
    $me = new static();
    $me->general = 1;
    $me->patient = 1;
    return $me;
  }
  static function asProvider() {
    return static::asClinical();
  }
  static function asClinical() {
    $me = new static();
    $me->portal = 1;
    return $me->blend(static::asClerical());
  } 
  static function asClerical() {
    $me = new static();
    $me->general = 1;
    $me->patient = 1;
    return $me;
  }
  static function asInactivePrimaryProvider() {
    return static::asNone();
  }
  static function asInactive() {
    return static::asNone();
  }
}
class Perms_NewCrop extends Perms {
  public $can;  // NewCrop user type
  //
  static function asErx() {
    $me = new static();
    $me->can = 1;
    return $me;
  }
  static function asNone() {
    return null;
  }
}
