<?php
require_once 'php/data/rec/sql/_ProcRec.php';
require_once 'php/data/json/JDataSyncFamGroup.php';
//
class Procedures_SocHx {
  //
  static function clear($cid) {
    global $login;
    $ugid = $login->userGroupId;
    Proc_SocHx::removeAll($ugid, $cid);
  }
  static function saveAll($cid, $obj/*{'shx':[123456,..]}}*/) {
    if ($obj) {
      foreach ($obj as $puid => $ipcs) {
        static::save($cid, $ipcs);
      }
    }
  }
  static function save($cid, /*int[]*/$ipcs) {
    global $login;
    $ugid = $login->userGroupId;
    Proc_SocHx::removeAll($ugid, $cid);
    Proc_SocHx::saveAll($ugid, $cid, $ipcs);
  }
}
class Procedures_FamHx {
  //
  static function clear($cid) {
    global $login;
    $ugid = $login->userGroupId;
    Proc_FamHx::removeAll($ugid, $cid);
  }
  static function saveAll($cid, $obj/*{'relSister1+female':[123456],..}}*/) {
    if ($obj) {
      foreach ($obj as $puid => $ipcs) {
        $member = geta(JDataSyncFamGroup::$PUIDS_TEXT[JDataSyncFamGroup::SUID_FAM], $puid, 'Unknown');
        static::save($cid, $ipcs, $member);
      }
    }
  }
  static function save($cid, /*int[]*/$ipcs, $member/*'Sister 1'*/) {
    global $login;
    $ugid = $login->userGroupId;
    Proc_FamHx::removeAll($ugid, $cid, $member);
    Proc_FamHx::saveAll($ugid, $cid, $ipcs, $member);
  }
}
abstract class Proc_Hx extends ProcRec {
  //
  static $TYPE = 'Must override';
  //
  static function removeAll($ugid, $cid, $subtype = null) {
    if ($ugid == null || $cid == null)
      return;
    $c = new static();
    $c->userGroupId = $ugid;
    $c->clientId = $cid;
    $c->location = static::makeLocation($subtype);
    if ($subtype == null) 
      $c->location = CriteriaValue::startsWith($c->location);
    $recs = static::fetchAllBy($c);
    static::deleteAll($recs);
  }
  static function saveAll($ugid, $cid, $ipcs, $subtype = null) {
    foreach ($ipcs as $ipc) {
      $rec = static::from($ugid, $cid, $ipc, $subtype);
      $rec->save();
    }
  }
  static function from($ugid, $cid, $ipc, $subtype = null) {
    $me = new static();
    $me->userGroupId = $ugid;
    $me->clientId = $cid;
    $me->date = nowNoQuotes();
    $me->ipc = $ipc;
    $me->location = static::makeLocation($subtype);   
    return $me;
  }
  static function makeLocation($subtype) {
    if ($subtype) 
      return static::$TYPE . ": " . $subtype;
    else
      return static::$TYPE;
  }
}
class Proc_FamHx extends Proc_Hx {
  //
  static $TYPE = 'Family History';
}
class Proc_SocHx extends Proc_Hx {
  //
  static $TYPE = 'Social History';
} 