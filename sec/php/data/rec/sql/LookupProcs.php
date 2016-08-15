<?php
require_once 'php/data/rec/sql/_SqlRec.php';
require_once 'php/dao/LookupDao.php';
//
/**
 * Lookup Procs DAO
 */
class LookupProcs {
  
}
class LuProc extends Rec {
  //
  public $procId;
  public $active;
  //
  public function getLookupTableId() {
    return 18;
  }
}

