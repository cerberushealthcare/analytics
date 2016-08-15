<?php
require_once 'php/data/json/_util.php';
require_once 'php/data/json/JDataSyncGroup.php';

/*
 * Collection of "proc" DATA_SYNC records
 * DATA_SYNC organization:
 *   "cat"            ("pmhx") Selected procs (assigned to multi-option question defining the "proc table")
 *   "cat.proc.field" ("pmhx.Brain Cancer.type") Field value for a selected proc 
 */
class JDataSyncProcGroup {
  
  public $cat;
  public $procs;     // [proc,..] Selected procs
  public $records;   // [proc=>[field=>value,..],..]

  // Definitions
  private $fieldDefs; // FIELD_DEFS element for $cat
  
  /*
   * Categories
   */
  const CAT_MED = "pmhx";
  const CAT_SURG = "pshx";
   
  /*
   * Category field definitions
   * cat=>[
   *    field=>[label],..
   *   ]
   */
  public static $FIELD_DEFS = array(
    JDataSyncProcGroup::CAT_MED => array(
      'date'     => array('Date'),
      'type'     => array('Type'),
      'rx'       => array('Treatment'),
      'comment'  => array('Comment')),
    JDataSyncProcGroup::CAT_SURG => array(
      'date'     => array('Date'),
      'type'     => array('Type'),
      'comment'  => array('Comment'))
    );    
    
  /*
   * $procs:[proc,...]          // Selected procs (DATA_SYNC value of cat)
   * $values:[dsync=>value,..]  // All client DATA_SYNC field values for category (e.g. "pmhx.*") 
   */
  public function __construct($cat, $procs, $values) {
    $this->cat = $cat;
    $this->procs = $procs;
    $this->fieldDefs = JDataSyncProcGroup::$FIELD_DEFS[$cat];
    $this->records = array();
    if ($procs) {
      $fields = array_keys($this->fieldDefs);
      foreach ($procs as &$proc) {
        $prefix = $cat . "." . $proc . ".";
        $this->records[$proc] = new JDataSyncProcRec($prefix, $fields, $values);
      }
    }
  }  
  /*
   * Returns {
   *   "cat":cat,
   *   "fields":{field:[label],..}
   *   "recs":{proc:{field:value,..},..}
   *   }
   */
  public function out() {
    $out = "";
    $out = nqq($out, "cat", $this->cat);
    $out = nqqo($out, "procs", jsonencode($this->procs));
    $out = nqqo($out, "fields", jsonencode($this->fieldDefs));
    $out = nqqo($out, "recs", aarr($this->records));
    return cb($out);
  }
}

/*
 * Record object for JDataSyncProcGroup
 */
class JDataSyncProcRec {
  
  public $fieldValues;
  /*
   * $prefix:"cat.proc."
   * $fields:[field,..]
   * $values:[field=>value,..]
   */
  public function __construct($prefix, $fields, $values) {
    $this->fieldValues = array();
    foreach ($fields as &$field) {
      $dsync = $prefix . $field;
      $this->fieldValues[$field] = JDataSyncRecord::getValue($values, $dsync);
    }
  }
  /*
   * Returns
   *   {field:value,..}
   */
  public function out() {
    return aarro($this->fieldValues);
  }
}
?>