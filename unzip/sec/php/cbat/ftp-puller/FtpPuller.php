<?php
require_once 'php/data/ftp/FtpFolder.php';
require_once 'php/data/rec/sql/HL7_Labs.php';
//
class FtpPuller {
  //
  static function exec() {
    $labs = Lab::fetchAll_forSftpPull();
    foreach ($labs as $lab) 
      static::pull($lab);
  }
  protected static function pull($lab) {
    try {
      blog('1/4. Logging into ' . $lab->getLabel());
      $sftp = static::getSftp($lab);
      if ($sftp == null) {
        blog('1/4 ERROR. Login failed');
      } else {
        $folder = FtpFolder::from($lab);
        $files = $sftp->getFiles();
        blog('2/4. Files found: ' . count($files));
        $base = $folder->getPath_in();
        blog('3/4. Saving files to ' . $base);
        SfFile::saveAll($files, $base);
        blog('4/4. Deleting files from server: ' . count($files));
        $sftp->deleteFiles($files);
        blog('Finished');
      }
    } catch (Exception $e) {
      blog($e);
    }
  }
  protected static function getSftp($lab) {
    $uid = $lab->uid;
    $class = "Sftp_$uid";
    @include_once "$class.php";
    if (! class_exists($class, false))
      throw new SftpClassNotFound($path);
    $sftp = $class::login($lab->id, $lab->pw);
    return $sftp;
  }
}
//
class SftpClassNotFound extends Exception {}