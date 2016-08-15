<?php
require_once 'php/data/rec/group-folder/GroupFolder.php';
//
/**
 * GroupFolder_Faces
 * @author Warren Hornsby
 */
class GroupFolder_Faces extends GroupFolder implements AutoEncrypt {
  /**
   * @param int $cid
   * @return string filename 'C12_Fred.jpeg'
   */
  public function upload($cid) {
    $upload = GroupUpload_Face::asUpload($cid);
    $file = parent::upload($upload);
    return $upload->name;
  }
  /**
   * @param string $filename
   */
  public function output($filename) {
    $file = GroupFile::from($this, $filename);
    logit_r($file, 'face file');
    return parent::output($file);
  }
  //
  static function open() {
    global $login;
    return parent::open($login->userGroupId, 'faces');
  }
}
/**
 * GroupUpload_Face
 */
class GroupUpload_Face extends GroupUpload {
  /**
   * @return GroupUpload_Face
   */
  public static function asUpload($cid) {
    $me = parent::asUpload();
    $me->name = "C$cid-" . $me->name;
    return $me;
  }
  //
  protected static function getValidTypes() {
    return self::$TYPE_IMAGES;
  }  
  protected static function getInvalidTypeMessage() {
    return 'File is not of an allowable type. Only image files (e.g. JPEG, GIF, PGN) may be used.';
  }
}
