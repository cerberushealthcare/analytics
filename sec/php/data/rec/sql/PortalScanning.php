<?php
require_once 'php/data/rec/group-folder/GroupFolder_Scanning.php';
require_once 'php/data/rec/sql/Scanning.php';
//
/**
 * Portal Scanning DAO
 * @author Warren Hornsby
 */
class PortalScanning {
  //
  /**
   * @param int $sfid
   * @return ScanFile
   */
  static function getFile($sfid) {
    return ScanFile_Portal::fetch($sfid);
  }
  /**
   * @param int $sfid
   */
  static function output($sfid) {
    $sess = PortalSession::get(); 
    $file = static::getFile($sfid);
    $folder = GroupFolder_Scanning::open($sess->userGroupId);
    $folder->output($file);
  }
}
class ScanFile_Portal extends ScanFile implements ReadOnly {
  //
  static function fetchAllIndexedTo($id) {
    $sess = PortalSession::get();
    $c = new static();
    $c->userGroupId = $sess->userGroupId;
    $c->scanIndexId = $id;
    $recs = static::fetchAllBy($c, new RecSort('seq'));
    return $recs;
  }
}