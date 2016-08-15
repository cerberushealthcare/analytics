<?php
require_once 'CsvFile_G3.php';
//
class Importer {
  //
  static function import(/*GroupFile*/$gf) {
    $file = CsvFile_G3::from($gf);
    logit_r($file->getRecs());
  }
}