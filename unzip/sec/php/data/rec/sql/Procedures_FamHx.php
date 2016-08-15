<?php
require_once 'php/data/rec/sql/_ProcRec.php';
require_once 'php/data/json/JDataSyncFamGroup.php';
//
class Procedures_FamHx {
  //
  static function clear($cid) {
    global $login;
    $ugid = $login->userGroupId;
    Proc_FamHx::removeAll($ugid, $cid);
  }
  static function save($cid, /*int[]*/$ipcs, $member/*'Sister 1'*/) {
    global $login;
    $ugid = $login->userGroupId;
    Proc_FamHx::removeAll($ugid, $cid, $member);
    Proc_FamHx::saveAll($ugid, $cid, $ipcs, $member);
  }
  static function saveAll($cid, $obj) {
    if ($obj) {
      foreach ($obj as $puid => $ipcs) {
        logit_r($puid, 'puid');
        $member = geta(JDataSyncFamGroup::$PUIDS_TEXT[JDataSyncFamGroup::SUID_FAM], $puid, 'Unknown');
        static::save($cid, $ipcs, $member);
      }
    }
  }
}
class Proc_FamHx extends ProcRec {
  //
  static function removeAll($ugid, $cid, $member) {
    if ($ugid == null || $cid == null)
      return;
    $c = new static();
    $c->userGroupId = $ugid;
    $c->clientId = $cid;
    $c->location = $member;
    $recs = static::fetchAllBy($c);
    static::deleteAll($recs);
  }
  static function saveAll($ugid, $cid, $ipcs, $member) {
    foreach ($ipcs as $ipc) {
      $rec = static::from($ugid, $cid, $ipc, $member);
      $rec->save();
    }
  }
  static function from($ugid, $cid, $ipc, $member) {
    $me = new static();
    $me->userGroupId = $ugid;
    $me->clientId = $cid;
    $me->date = nowNoQuotes();
    $me->ipc = $ipc;
    $me->location = $member;
    return $me;
  }
}