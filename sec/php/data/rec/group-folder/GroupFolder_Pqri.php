<?php
require_once 'php/data/rec/group-folder/GroupFolder.php';
//
/**
 * GroupFolder_Pqri
 * @author Warren Hornsby
 */
class GroupFolder_Pqri extends GroupFolder {
  /**
   * @param PqriMessage $msg
   * @return GroupFile_Pqri
   */
  public function save($xml) {
    $file = GroupFile_Pqri::asNew($this, $xml);
    return $file->save($xml->toXml(true));
  }
  /**
   * @param string $filename
   */
  public function download($filename) {
    $file = GroupFile_Pqri::from($this, $filename);
    $file->download(self::MIME_XML);
  }
  //
  static function open() {
    global $login;
    return parent::open($login->userGroupId, 'pqri');
  }
}
/**
 * GroupFile_Pqri
 */
class GroupFile_Pqri extends GroupFile {
  /**
   * @param PqriMessage $msg
   * @return GroupFile_Pqri
   */
  static function asNew($folder, $xml) {
    $filename = 'pqri.xml';
    return self::from($folder, $filename);
  }
}
