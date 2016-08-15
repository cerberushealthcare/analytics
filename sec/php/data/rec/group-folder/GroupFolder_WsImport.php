<?php
require_once 'php/data/rec/group-folder/GroupFolder.php';
//
/**
 * GroupFolder_WsImport
 * @author Warren Hornsby
 */
class GroupFolder_WsImport extends GroupFolder implements AutoEncrypt {
  //
  public function /*GroupFile*/upload() {
    $upload = GroupUpload::asUpload();
    $file = parent::upload($upload);
    return $file;
  }
  //
  static function open() {
    global $login;
    return parent::open($login->userGroupId, 'ws-import');
  }
}
