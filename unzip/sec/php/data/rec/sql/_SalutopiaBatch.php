<?php
require_once 'php/data/rec/sql/_SqlRec.php';
//
class SalutopiaBatch extends SqlRec implements NoAuthenticate {
  //
  public $userGroupId;
  public $active;
  public $lastRun;
  //
  public function getSqlTable() {
    return 'salutopia_batch';
  }
  //
  static function /*bool*/isActive($ugid) {
    $me = static::fetch($ugid);
    return $me != null;
  }
  static function /*Self*/fetch($ugid) {
    $c = new static();
    $c->userGroupId = $ugid;
    $c->active = true;
    return static::fetchOneBy($c);
  }
  static function /*Self[]*/fetchAll() {
    $c = new static();
    $c->active = true;
    return static::fetchAllBy($c);
  }
  static function /*Self*/save_asLastRun($ugid) {
    $me = new static();
    $me->userGroupId = $ugid;
    $me->active = true;
    $me->lastRun = nowNoQuotes();
    return $me->save();
  }
}