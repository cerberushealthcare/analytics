<?php
set_include_path('../../');
require_once 'php/dao/_util.php';
require_once 'batch/_batch.php';
require_once 'php/data/ftp/FtpFolder.php';
require_once 'php/data/rec/sql/HL7_Labs.php';
//
blog('Start lab-sftp.php');
FtpImporter::exec();
blog('Finish lab-sftp.php');
//
/**
 * Import lab messages dropped into SFTP folders
 * @author Warren Hornsby
 */
class FtpImporter {
  //
  static function exec() {
    blog('exec');
    $labs = Lab::fetchAll_forSftpPolling();
    foreach ($labs as $lab) {
      $folder = FtpFolder::from($lab);
      if ($folder == null) {
        blog('folder empty');
      } else {
        blog('root='.$folder->root);
      }
      blog('Opening ' . $lab->getLabel());
      static::import_fromFtpFolder($folder);
    }
  }
  protected static function import_fromFtpFolder($folder) {
    blog('import');
    $files = $folder->getIncoming();
    blog('files='.$files);
    if (! empty($files)) {
      blog('Files count: ' . count($files));
      foreach ($files as $file)
        static::import_fromFtpFile($file, $folder);
    }
  }
  protected static function import_fromFtpFile($file, $folder) {
    try {
      HL7_Labs::import_fromFtpFile($file);
      $folder->moveToOut($file);
      blog('Successful import');
    } catch (Exception $e) {
      blog($e);
    }
  }
}
