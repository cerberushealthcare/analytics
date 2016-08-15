<?php
require_once 'php/data/rec/group-folder/GroupFolder.php';
//
/**
 * GroupFolder_SessionImages
 * @author Warren Hornsby
 */
class GroupFolder_SessionImages extends GroupFolder implements AutoEncrypt {
  //
  public function /*GroupUpload_SessionImage*/upload($sid) {
    $upload = GroupUpload_SessionImage::asUpload($sid);
    parent::upload($upload);
    return $upload;
  }
  public function output($filename) {
    $file = GroupFile::from($this, $filename);
    return parent::output($file);
  }
  //
  static function open() {
    global $login;
    return parent::open($login->userGroupId, 'session-images');
  }
}
/**
 * GroupUpload_SessionImage
 */
class GroupUpload_SessionImage extends GroupUpload_Image {
  //
  public $name;     // 'S1001-0_original.jpg'
  public $type;     // 'image/jpeg'
  public $tmpName;  // 'C:\Windows\temp\phpE74.tmp'
  public $error;    // 0
  public $size;     // 23308
  //
  public $width;
  public $height;
  public $mime;     // 'image/jpeg'
  //
  public $src;      // 'session-image.php?id=S1001-0_original.jpg'
  public $ratio;    // 1.2 (h/w)
  //
  public function toJsonObject(&$o) {
    unset($o->tmpName);
    $o->src = 'session-image.php?id=' . $o->name;
    if ($o->width)
      $o->ratio = $o->height / $o->width;
  }
  public static function asUpload($sid) {
    $prefix = "S$sid";
    return parent::asUpload($prefix);
  }
}
