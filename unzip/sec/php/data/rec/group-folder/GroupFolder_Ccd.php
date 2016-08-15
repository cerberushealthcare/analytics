<?php
require_once 'php/data/rec/group-folder/GroupFolder.php';
//
/**
 * GroupFolder_Ccd
 * @author Warren Hornsby
 */
class GroupFolder_Ccd extends GroupFolder {
  /**
   * @param ClinicalDocument $ccd
   * @param string $password for encryption (optional)
   * @return GroupFile_Ccd
   */
  public function save($ccd, $password = null) {
    $file = GroupFile_Ccd::asNew($ccd);
    return $file->save($ccd->toXml(true), $password);
  }
  /**
   * @param string $filename
   */
  public function download($filename) {
    $file = GroupFile_Ccd::from($filename);
    logit_r($file, 'file');
    $file->download($filename, self::MIME_XML);
  }
  //
  static function open() {
    global $login;
    return parent::open($login->userGroupId, 'ccd');
  }
}
/**
 * GroupFile_Ccd
 */
class GroupFile_Ccd extends GroupFile {
  /**
   * @param ClinicalDocument $ccd
   * @return self
   */
  static function asNew($ccd) {
    $client = $ccd->getClient();
    $filename = $client->lastName . '_' . $client->clientId . '_CCD.xml';
    return self::from($filename);
  }
  /**
   * @param string $filename
   * @return self
   */
  static function from($filename) {
    $me = new static();
    $me->folder = static::openFolder();
    $me->filename = $filename;
    return $me;
  }
  public function download($mime = null, $password = null) {
    $mime = ($mime) ? $mime : GroupFolder::getMime($this->filename);
    ob_clean();
    header("Pragma: ");
    header("Cache-Control: ");
    header("Content-type: $mime");
    header('Content-Disposition: attachment; filename="' . $this->filename . '"');
    $contents = static::readContents($password);
    //echo $contents; todo: why doesn't this work? had to restore readfile
    readfile($this->getCompleteFilename(), true);
  }
  //
  protected static function openFolder() {
    return GroupFolder_Ccd::open();
  }
}
