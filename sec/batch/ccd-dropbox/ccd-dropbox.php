<?php
set_include_path('../../');
require_once 'php/dao/_util.php';
require_once 'batch/_batch.php';
require_once 'php/data/Dropbox/DropboxFolder.php';
require_once 'php/data/rec/sql/Practice_CCDs.php';
//
blog('Start ccd-dropbox.php');
DropboxImporter::exec();
blog('Finish ccd-dropbox.php');
//
/**
 * Import ccd messages dropped into Dropbox folders
 * @author Chuck Sauer
	Last change: CS 7/29/2016 10:03:11 AM
 */
class DropboxImporter {
  //
  static function exec() {
    blog('exec');
    $practices = Practice::fetchAll_forDropboxPolling();
    foreach ($practices as $practice) {
      $folder = DropboxFolder::from($practice);
      if ($folder == null) {
        blog('folder empty');
      } else {
        blog('root='.$folder->root);
      }
      blog('Opening ' . $practice->getLabel());
      static::import_fromDropboxFolder($folder);
    }
  }
  protected static function import_fromDropboxFolder($folder) {
    blog('import');
    $files = $folder->getIncoming();
    blog('files='.$files);
    if (! empty($files)) {
      blog('Files count: ' . count($files));
      foreach ($files as $file)
        static::import_fromDropboxFile($file, $folder);
    }
  }
  protected static function import_fromDropboxFile($file, $folder) {
    try {
      CCD_Practices::import_fromDropboxFile($file);
      $folder->moveToOut($file);
      blog('Successful import');
    } catch (Exception $e) {
      blog($e);
    }
  }
}
