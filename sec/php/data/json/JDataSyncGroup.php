<?php
require_once 'php/data/json/_util.php';

/*
 * Generic collection object of JDataSyncRecords
 * DATA_SYNC organization:
 *   "group.rec.field" ("sochx.alcohol.amt") Field value for a proc 
 */
class JDataSyncGroup {
  
  public $records; 
  
  public $nonNullRecs;  // [recName,..]
  
  // Group names
  const GROUP_SOCHX = 'sochx';

  /*
   * Group record definitions
   * groupName=>[
   *    recName=>[
   *       dsync=>label
   *      ]
   *   ]
   */
  const COMBO = '';
  public static $DEFS = array(
    JDataSyncGroup::GROUP_SOCHX => array(
      'Alcohol' => array(
        'sochx.alcohol.uses' => 'Uses', 
        'sochx.alcohol.amt' => 'Amount', 
        'sochx.alcohol.intox' => 'Intoxication'),
      'Current Drug Use' => array(
        'sochx.recDrug' => 'Current Use',
        'sochx.recDrugsUsed' => 'Substances'),
      'Drug Use History' => array(
        'sochx.drugs' => JDataSyncGroup::COMBO),
    	'Education' => array(
        'sochx.edu.attained' => 'Attained',
        'sochx.edu.ged' => 'GED'),
      'Household' => array(
        'sochx.household.members' => 'Members',
        'sochx.household.familyStructure' => 'Family Structure',
        'sochx.household.daycare' => 'Daycare'),
      'Marital' => array(
        'sochx.marital.status' => 'Status'),
      'Occupation' => array(
        'sochx.occ.current' => 'Current'),
      'Past Occupations' => array(
        'sochx.occs' => JDataSyncGroup::COMBO),
      'Religion' => array(
        'sochx.religion.attends' => 'Attends', 
        'sochx.religion.affil' => 'Affiliation'),
      'Seat Belt' => array(
        'sochx.sb.uses' => 'Uses'),
      'Sexual' => array(
        'sochx.sex.orient' => 'Orientation',
        'sochx.sex.active' => 'Active',
        'sochx.sex.lsp' => 'Lifetime Partners',
        'sochx.sex.lspMale' => 'Male Partners',
        'sochx.sex.lspFemale' => 'Female Partners',
        'sochx.sex.anal' => 'Anal Recep'),
      'Tobacco' => array(
        'sochx.tob.uses' => 'Uses',
        'sochx.tob.freq' => 'Frequency',
        'sochx.tob.neverStatus' => 'Never',
        'sochx.tob.ppd' => 'PPD',
        'sochx.tob.exposure' => 'Exposed',
        'sochx.tob.smokeless' => 'Smokeless',  
        'sochx.tob.recode' => 'Recode (MU)',
        'sochx.tob.status' => 'Status-1 (MU)',
        'sochx.tob.status2' => 'Status-2 (MU)')  
      )
    );
    
  /*
   * Create collection of JDataSyncRecords for group name
   * $values:[dsync=>value,..]  // DATA_SYNC values for client and entire group (e.g. "sochx.*") 
   */
  public function __construct($groupName, $values) {
    $this->records = array();
    $this->nonNullRecs = array(); 
    $defRecs = JDataSyncGroup::$DEFS[$groupName];
    foreach ($defRecs as $recName => &$dsyncs) {
      $rec = new JDataSyncRecord($dsyncs, $values);
      if ($rec->nonNull) {
        $this->nonNullRecs[] = $recName;
      }
      $this->records[$recName] = $rec;
    }
  }
  
  /*
   * Returns
   *   {recName:JDataSyncRecord,...}
   */
  public function out() {
    return aarr($this->records);
  }
}

class JDataSyncRecord {

  public $fields;
  
  public $nonNull = false;  // boolean

  /*
   * Construct record from data sync definition array and data rows
   * $dsyncs:[dsync=>label,..]   // dsyncs that define this record
   * $values:[dsyncId=>value],..]  // DATA_SYNC values for client   
   */
  public function __construct($dsyncs, $values) {
    $this->fields = array();
    foreach ($dsyncs as $dsync => $label) {
      if ($label == JDataSyncGroup::COMBO) {
        foreach ($values as $dsyncId => $value) {
          if (substr($dsyncId, 0, strlen($dsync)) == $dsync) {
            $this->fields[$dsyncId] = JDataSyncRecord::jsonize($value, $label, $dsync);
            $this->nonNull = true;
          }
        }
      } else {
        $value = JDataSyncRecord::getValue($values, $dsync);
        if ($value != null) {
          $this->nonNull = true;
        }
        $this->fields[$dsync] = JDataSyncRecord::jsonize(JDataSyncRecord::getValue($values, $dsync), $label);
      }
    }
  }
  
  /*
   * Return the combo instance values for dsync
   */
  private static function extractComboValues($values, $dsync) {
    $vals = array();
    foreach ($values as $dsyncId => $value) {
      if (substr($dsyncId, 0, strlen($dsync)) == $dsync) {
        $vals[$dsyncId] = $value;
      }
    }
    return $vals;
  }
  
  /*
   * Returns 
   *   {dsync:{"v":value,"l":label},..}  
   */
  public function out() {
    return aarro($this->fields);
  }
  
  /*
   * Static: Retrieve datasync value from array, treat "[]" as NULL 
   */
  public static function getValue($values, $dsync) {
    $value = geta($values, $dsync);
    if ($value == "[]") {
      $value = null;
    }
    return $value;
  }
  
  // Create label/value JSON
  private static function jsonize($value, $label, $dsync = null) {
    return '{"v":' . (($value) ? $value : "null") 
        . (($label) ? ',"l":"' . $label. '"}' : ',"d":"' . $dsync . '"}');   
  }
}
?>