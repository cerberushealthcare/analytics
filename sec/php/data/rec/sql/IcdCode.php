<?php
require_once 'php/data/rec/sql/_SqlRec.php';
/**
 * ICD Code 
 */
class IcdCode extends SqlRec implements ReadOnly {
  //
  public $icdCode;
  public $icdDesc;
  public $synonyms;
  public $includes;
  public $excludes;
  public $notes;
  public $icd3;
  //
  public function toJsonObject() {
    $o = new stdClass();
    $o->code = utf8_encode($this->icdCode);
    $o->desc = utf8_encode($this->icdDesc);
    $o->synonyms = utf8_encode($this->synonyms);
    $o->includes = utf8_encode($this->includes);
    $o->excludes = utf8_encode($this->excludes);
    $o->notes = utf8_encode($this->notes);
    return $o;
  }
  public function getSqlTable() {
    return 'icd_codes';
  }
}
