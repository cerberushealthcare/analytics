<?php
require_once 'php/data/rec/_Search.php';
require_once 'php/data/rec/sql/_SqlRec.php';
//
/**
 * CPT Codes
 * DAO for CptCode
 * @author Warren Hornsby
 */
class CptCodes {
  
}
/**
 * CPT Code 
 */
class CptCode extends SqlRec implements ReadOnly {
  //
  public $code;
  public $desc;
  //
  public function getSqlTable() {
    return 'cpts';
  }
}
