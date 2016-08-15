<?php
require_once 'php/data/rec/group-folder/GroupFolder.php';
//
/**
 * GroupFolder_Scanning
 * @author Warren Hornsby
 */
class GroupFolder_Scanning extends GroupFolder {
  /**
   * @param GroupUpload_Scanning[] $uploads
   * @param int $fileIndex next file index
   */
  public function uploadAll($uploads, $fileIndex) {
    foreach ($uploads as &$upload) 
      $upload = self::upload($upload, $fileIndex++);
    return $uploads;
  }
  public function upload(/*GroupUpload*/$upload, $fileIndex) {
    $upload->setNewName($this->ugid, $fileIndex);
    $subfolder = GroupFile_Scanning::getSubfolder($upload->newName);
    $this->checkSubfolder($subfolder);
    $filename = $this->getCompleteFilename($upload->newName, $subfolder);
    $upload->move($filename);
    $dir = $subfolder ? $this->dir . "\\$subfolder" : $this->dir;
    AutoEncryptFile::create($dir, $upload->newName)->save();
    return $upload;
  }
  public function output(/*ScanFile*/$file, $maxh = 0, $maxw = 0) {
    $groupfile = GroupFile_Scanning::from($file->filename, $file->userGroupId);
    $contents = AutoEncryptFile::fromGroupFile($groupfile)->getContent();
    $groupfile->output($file->mime, $file->rotation, $maxh, $maxw, $contents);
  }
  public function delete(/*ScanFile*/$file) {
    parent::delete(GroupFile::from($this, $file->filename));
  }
  public function getCompleteFilename($filename, $subfolder = null) {
    if ($subfolder == null)
      $subfolder = GroupFile_Scanning::getSubfolder($filename);
    return $subfolder ? "$this->dir\\$subfolder\\$filename" : "$this->dir\\$filename";
  } 
  public function checkSubfolder($sf) {
    if ($sf) {
      $sf = "$this->dir\\$sf";
      logit_r($sf, 'sf');
      logit_r(is_dir($sf), 'is_dir');
      if (! is_dir($sf)) {
        if (! mkdir($sf, 0, true)) { 
          throw new GroupFolderException($sf, 'Unable to access directory');
        }
      }
    }
  }
  //
  static function /*static*/open($ugid = null) {
    return parent::open($ugid, 'scan');
  }
}
class GroupFile_Scanning extends GroupImageFile {
  //
  /**
   * @param string $filename
   * @return self
   */
  static function from($filename, $ugid = null) {
    $me = new static();
    $me->folder = GroupFolder_Scanning::open($ugid);
    $me->filename = $filename;
    $subfolder = static::getSubfolder($filename);
    if ($subfolder) {
      $me->filename = $subfolder . "\\$filename";
    }
    return $me;
  }
  static function getSubfolder($filename) {
    $subfolder = substr($filename, strpos($filename, 'S'), 5); /*extract subfolder from name, e.g. "G12S0002127.jpg" becomes "S0002\G125S0002127.jpg"*/
    if (strlen($subfolder) == 5 && is_numeric(substr($subfolder, 1))) {
      return $subfolder; 
    }
  }
}
class GroupUpload_Scanning extends GroupUpload {
  //
  public $name;     // 'original.jpg'
  public $type;     // 'image/jpeg'
  public $tmpName;  // 'C:\Windows\temp\phpE74.tmp'
  public $error;    // 0
  public $size;     // 23308
  //
  public $fileseq;  // 12
  public $newName;  // 'S00000012'
  public $ext;      // 'jpg'
  public $mime;     // 'image/jpeg'
  public $width;    // 306
  public $height;   // 205
  //
  public function validate() {
    parent::validate();
    $this->ext = $this->getExt();
    if (empty($this->ext)) 
      throw new GroupUploadException($this, "$this->name is an invalid type; only image and PDF files accepted");
  }
  public function setNewName($ugid, $i) {
    $this->fileseq = $i;
    $this->newName = "G$ugid" . sprintf("S%07d", $i) . "." . $this->getExt();
  }
  public function move($filename) {
    $result = move_uploaded_file($this->tmpName, $filename);
    $this->setImageInfo($filename);
  }
  //
  protected function setImageInfo($filename) {
    $info = getimagesize($filename);
    if (isset($info['mime'])) {
      $this->width = $info[0];
      $this->height = $info[1];
      $this->mime = $info['mime'];
    } else {
      $this->mime = $this->type;
    }
  }
  protected function getExt() {
    switch ($this->type) {
      case 'image/jpeg':
      case 'image/pjpeg':
        return 'jpg';
      case 'image/bmp':
      case 'image/x-windows-bmp':
        return 'bmp';
      case 'image/gif':
        return 'gif';
      case 'image/x-png':
      case 'image/png':
        return 'png';
      case 'application/pdf':
        return 'pdf';
    }
  }
}
class GroupUpload_ScanningXml extends GroupUpload_Scanning {
  //
  public function validate() {
  }
  //
  protected function getExt() {
    return 'xml';
  }
}
/**
 * Batch 
 */
class GroupFolder_Batch extends GroupFolder {
  //
  /**
   * @return GroupFile
   */
  public function upload() {
    $upload = GroupUpload_Batch::asUpload();
    return parent::upload($upload);
  }
  //
  /**
   * @return self
   */
  static function open($ugid = null) {
    return parent::open($ugid, 'scan-batch');
  }
}
class GroupUpload_Batch extends GroupUpload {
  //
  protected function getExt() {
    return 'pdf';
  }
  //
  static function asUpload() {
    logit_r('batch asUpload, max=' . static::getMaxSize());
    return parent::asUpload();
  }
  protected static function getValidTypes() {
    return array('application/pdf');
  }
  protected static function getInvalidTypeMessage() {
    return array('Batch file must be a PDF.');
  }
  protected static function getMaxSize() {
    return 50 * static::M;
  }
}
class GroupUpload_Split extends GroupUpload_Scanning {
  //
  public function __construct() {
    // no validate; guaranteed to have image files from bat job
  }
  public function move($to) {
    logit_r($to, 'move');
    rename($this->tmpName, $to);
  }
  //
  /**
   * @param GroupFile $file
   * @return array(self,..)
   */
  static function getAllFor($file) {
    $pattern = $file->folder->dir . "\\" . $file->filename . '-*';
    $filenames = glob($pattern);
    $mes = array();
    logit_r($filenames, 'filenames');
    foreach ($filenames as $filename)
      $mes[] = static::from($file->folder->dir, $filename);
    logit_r($mes, 'mes');
    return $mes;
  }
  static function from($dir, $filename) {
    $me = new static();
    $me->name = substr($filename, strlen($dir) + 1);
    $me->tmpName = $filename;
    $me->setImageInfo($filename);
    $me->type = $me->mime;
    return $me;
  }
}
