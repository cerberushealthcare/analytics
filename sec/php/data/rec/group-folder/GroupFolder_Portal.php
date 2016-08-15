<?php
require_once 'php/data/rec/group-folder/GroupFolder.php';
//
/**
 * GroupFolder_Portal
 * @author Warren Hornsby
 */
class GroupFolder_Portal extends GroupFolder {
  /**
   * @param int $cid
   * @return string filename 'P7_Fred.jpeg'
   */
  public function upload($portalUserId) {
    $upload = GroupUpload_Portal::asUpload($portalUserId);
    parent::upload($upload);
    return $upload->name;
  }
  /**
   * @param string $filename
   */
  public function output($filename, $maxh = 0, $maxw = 0) {
    $file = GroupImageFile::from($this, $filename);
    $file->output(null, 0, $maxh, $maxw);
  }
  //
  static function open() {
    global $login;
    return parent::open($login->userGroupId, 'portal');
  }
  protected static function getRoot($ugid) {
    $root = parent::getRoot($ugid);
    if (static::cwd() == 'sec') 
      $root = "portal\\$root"; 
    return $root;
  }
}
/**
 * GroupUpload_Face
 */
class GroupUpload_Portal extends GroupUpload {
  /**
   * @return GroupUpload_Face
   */
  public static function asUpload($id) {
    $me = parent::asUpload();
    $me->name = "P$id-" . $me->name;
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