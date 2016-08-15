<?php
require_once 'php/data/rec/sql/_SqlLevelRec.php';
//
abstract class LabXrefRec extends SqlGroupLevelRec {
  /*
  public $labId;
  public $type;
  public $fromId;
  public $userGroupId;
  public $fromText;
  public $toId;  // IPC
  */
  static $ID_FIELD_COUNT = 3;
  //
  const TYPE_PROC = 'P'; 
  //
  public function getSqlTable() {
    return 'lab_xref';
  }
} 
