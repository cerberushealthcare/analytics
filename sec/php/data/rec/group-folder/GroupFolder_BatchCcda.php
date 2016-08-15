<?php
require_once 'php/data/rec/group-folder/GroupFolder.php';
//
/**
 * GroupFolder_BatchCcda
 * @author Warren Hornsby
 */
class GroupFolder_BatchCcda extends GroupFolder {
  //
  public function /*GroupFile_Bc*/save(/*ClinicalDocument*/$ccd, $batchId) {
    $file = GroupFile_Bc::asNew($ccd, $batchId);
    return $file->save($ccd->toXml(true));
  }
  public function download($filename) {
    $file = GroupFile_Bc::from($filename);
    $file->download($filename, self::MIME_XML);
  }
  //
  static function /*GroupFolder_BatchCcda*/open() {
    global $login;
    return parent::open($login->userGroupId, 'ccda-batch');
  }
}
//
class GroupFile_Bc extends GroupFile {
  //
  static function asNew($ccd, $bid) {
    $client = $ccd->getClient();
    $filename = ClientCcdaBatch::makeFilename($bid, $client->clientId);
    return self::from($filename);
  }
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
    readfile($this->getCompleteFilename(), true);
  }
  //
  protected static function openFolder() {
    return GroupFolder_BatchCcda::open();
  }
}
