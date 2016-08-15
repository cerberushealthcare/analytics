<?php
//
require_once "TemplateEntry.php";
abstract class TQuestion_Hx extends TQuestion {
  //
  const TID = 1;
  //
  public function load($userId) {
    parent::load();
    $this->setOthers($userId);
  }
  public function setOthers($userId) {
    $values = TOther::fetchValueArray(
      $userId, 
      static::TID, 
      $this->Par->sectionId,
      $this->Par->uid,
      $this->uid);
    if ($values)
      $this->Others = $values;
  } 
  //
  static function fetch($uid, $par, $userId) {
    $c = new static();
    $c->uid = $uid;
    $c->Par = $par;
    $me = static::fetchOneBy($c);
    $me->load($userId);
    return $me;
  } 
}
abstract class TPar_Hx extends TPar {
  //
  const TID = 1;
  const SID = 36;  // PMHX
  //
  public function toJsonObject(&$o) {
    parent::toJsonObject($o);
    $o->tid = static::TID;
    $o->sid = static::SID;
  }
  static function asRequires($uid) {
    $c = new static();
    $c->sectionId = static::SID;
    $c->uid = $uid;
    $c->current = 1;
    return CriteriaJoin::requires($c);
  }
}
class TQuestion_Pmhx extends TQuestion_Hx {
  //
  const QUID = 'pmHx';
  //
  static function fetch($userId) {
    return parent::fetch(static::QUID, TPar_Pmhx::asRequires(), $userId);
  }
}
class TPar_Pmhx extends TPar_Hx {
  //
  const PUID = 'pmHx';
  //
  static function asRequires() {
    return parent::asRequires(static::PUID);
  }
}
class TQuestion_Pshx extends TQuestion_Hx {
  //
  const QUID = 'pastSurgHx';
  //
  static function fetch($userId) {
    return parent::fetch(static::QUID, TPar_Pshx::asRequires(), $userId);
  }
}
class TPar_Pshx extends TPar_Hx {
  //
  const PUID = 'pastSurgHx';
  //
  static function asRequires() {
    return parent::asRequires(static::PUID);
  }
}
class TQuestion_DsyncSoc extends TQuestion_Hx {
  //
  static function /*TQuestion[]*/fetchAll($dsync, $userId) {
    $c = new static();
    $c->dsyncId = CriteriaValue::startsWith($dsync);
    $c->Par = TPar_DsyncSoc::asRequires();
    $us = static::fetchAllBy($c);
    static::loadAll($us, $userId);
    return $us;
  }
  protected static function loadAll(&$us, $userId) {
    foreach ($us as &$me)
      $me->load($userId);
    return $us;
  }
}
class TPar_DsyncSoc extends TPar_Hx {
  //
  const SID = 31;
  //
  static function asRequires() {
    return parent::asRequires(null);
  }
}
class TQuestion_DsyncFam extends TQuestion_Hx {
  //
  static function /*TQuestion[]*/fetchAll($dsync, $userId) {
    $c = new static();
    $c->dsyncId = CriteriaValue::startsWith($dsync);
    $c->Par = TPar_DsyncFam::asRequires();
    $us = static::fetchAllBy($c);
    static::loadAll($us, $userId);
    return $us;
  }
  protected static function loadAll(&$us, $userId) {
    foreach ($us as &$me)
      $me->load($userId);
    return $us;
  }
}
class TPar_DsyncFam extends TPar_Hx {
  //
  const SID = 27;
  //
  static function asRequires() {
    return parent::asRequires(null);
  }
}
