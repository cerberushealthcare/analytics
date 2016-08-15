<?php
require_once 'php/data/rec/group-folder/GroupFolder.php';
//
/**
 * GroupFolder_ClinicalImport
 * @author Warren Hornsby
 */
class GroupFolder_ClinicalImport extends GroupFolder implements AutoEncrypt {
  //
  public function /*ClinicalFile*/upload() {
    $upload = GroupUpload_Xml::asUpload();
    $file = parent::upload($upload, true);
    return $file;
  }
  public function /*ClinicalFile*/getFile($filename) {
    $file = ClinicalFile::from($this, $filename);
    return $file;
  }
  //
  static function open() {
    global $login;
    return parent::open($login->userGroupId, 'clinical-import');
  }
}
class ClinicalFile extends AutoEncryptFile {
  //
}
