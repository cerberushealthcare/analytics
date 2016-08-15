<?php
require_once 'php/data/rec/group-folder/GroupFolder.php';
//
/**
 * GroupFolder_Cqm
 * @author Warren Hornsby
 */
class GroupFolder_Cqm extends GroupFolder {
  //
  public function save($cd) {
    $file = GroupFile_Cqm::asNew($this, $cd);
    return $file->save($cd->toXml(true));
  }
  public function download($filename) {
    $file = GroupFile_Cqm::from($this, $filename);
    $file->download(self::MIME_XML);
  }
  public function zip($filenames, $cqm) {
    $dir = "$this->dir";
    $zipfilename = $cqm::$CQM . ".zip";
    $z = new ZipArchive;
    $r = $z->open("$dir\\$zipfilename", ZipArchive::CREATE | ZipArchive::OVERWRITE);
    foreach ($filenames as $filename) {
      $z->addFile("$dir\\$filename", $filename);
    }
    $z->close();
    return $zipfilename;
  }
  //
  static function open() {
    global $login;
    return parent::open($login->userGroupId, 'cqm');
  }
}
//
class GroupFile_Cqm extends GroupFile {
  //
  static function asNew($folder, $cd) {
    $filename = $cd->getFilename();
    return self::from($folder, $filename);
  }
}
