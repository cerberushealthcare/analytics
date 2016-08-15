<?php
require_once 'php/data/csv/_CsvFile.php';
//
abstract class MassCsvFile extends CsvFile {
  //
  static $FILENAME;
  static $CSVREC_CLASS;
  static $HAS_FID_ROW;
}
abstract class MassCsvRec extends CsvRec {
  //
  public $email;
  //
  static $REPLACE_FIELDS;  // array('field',..)
  //
  public function shouldSend() {
    return ! empty($this->email);
  }
  public function getValue($field) {
    return $this->$field;
  }
}