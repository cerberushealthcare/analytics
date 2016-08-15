<?php
require_once 'HealthMaint_Recs.php';
//
/**
 * Health Maintenance Procedures
 * @author Warren Hornsby
 */
class HealthMaint {
  //
  static function /*IpcHm[]*/getAll() {
    global $login;
    $recs = IpcHm::fetchTopLevels($login->userGroupId);
    return Rec::sort($recs, new RecSort('Ipc.cat', 'Ipc.name'));
  }
  static function /*IpcHm_Client*/getForClient($cid) { /* including inactive client-levels */
    global $login;
    $recs = IpcHm_Client::fetchAll($login->userGroupId, $cid);
    return $recs; 
  }
  static function /*IpcHm*/save($obj) {
    global $login;
    $rec = IpcHm::revive($obj, $login->userGroupId);
    $rec->save();
    return IpcHm_Client::fetchOne($login->userGroupId, $rec->clientId, $rec->ipc);
  }
  static function del($obj) {
    global $login;
    $rec = IpcHm::revive($obj, $login->userGroupId);
    $cid = $rec->clientId;
    $ipc = $rec->ipc;
    IpcHm::delete($rec);
    return IpcHm_Client::fetchOne($login->userGroupId, $cid, $ipc);
  }
  static function /*Ipc_Hm[]*/getAllIpcs() {
    global $login;
    $ugid = $login->userGroupId;
    $ipcs = Ipc_Hm::fetchAll($ugid);
    return Rec::sort($ipcs, new RecSort('name'));
  }
  static function /*Client_Rep[]*/getAllDueNow($ipc) {
    global $login;
    $ugid = $login->userGroupId;
    $recs = IpcHm::fetchAllDueNowClients($ipc, $ugid);
    $recs = Rec::sort($recs, new RecSort('lastName', 'firstName'));
    return $recs;
  }
  static function recordReminders(/*int[]*/$cids, $name) {
    require_once 'php/data/rec/sql/Procedures_Admin.php';
    Proc_CareReminder::recordAll($cids, $name);
  }
  //
  private static function mapByName($ugid) {
    static $map;
    if ($map == null)
      $map = Ipc::fetchMapByName($ugid);
    return $map;
  }
  private static function mapSurgByDesc($ugid) {
    static $map;
    if ($map == null)
      $map = Ipc::fetchSurgMap($ugid);
    return $map;
  } 
}