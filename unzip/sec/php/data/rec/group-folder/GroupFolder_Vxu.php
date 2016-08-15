<?php
require_once 'php/data/rec/group-folder/GroupFolder.php';
//
/**
 * GroupFolder_Vxu
 * @author Warren Hornsby
 */
class GroupFolder_Vxu extends GroupFolder {
  /**
   * @param VXUMessage $vxu
   * @param string $password for encryption (optional)
   * @return GroupFile_Vxu
   */
  public function save($vxu, $password = null) {
    $file = GroupFile_Vxu::asNew($this, $vxu);
    return $file->save($vxu->toHl7(), $password);
  }
  /**
   * @param string $filename
   */
  public function download($filename) {
    $file = GroupFile_Vxu::from($this, $filename);
    $file->download(self::MIME_XML);
  }
  //
  static function open() {
    global $login;
    return parent::open($login->userGroupId, 'vxu');
  }
}
/**
 * GroupFile_Vxu
 */
class GroupFile_Vxu extends GroupFile {
  /**
   * @param VXUMessage $vxu
   * @return GroupFile_Vxu
   */
  static function asNew($folder, $vxu) {
    $client = $vxu->_fs->Client;
    $filename = $client->lastName . '_' . $client->uid . '_VXU.hl7';
    return self::from($folder, $filename);
  }
}
