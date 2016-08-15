<?php
require_once 'php/data/json/_util.php';
require_once 'php/data/json/JDataSyncGroup.php';

/*
 * Collection of DATA_SYNC records generated from injected cloneable pars
 * DATA_SYNC organization:
 *   "suid"                     ("famHx") Record selector assigned to multi-option question (the "suid" question)
 *   "suid.injector+puid.field" ("famHx.brother2+male.age") Value of a cloneable datasync (suid.puid injected by suid.injector)
 *  
 * Unlike the "cat" question of JDataSyncProcGroup, selected options of the "suid" question do not directly specify the selected records, for a couple reasons.
 * A single selected option can represent several records (e.g. "3 brothers" = 3 distinct brother records).
 * The rel+gender portion of the datasync ID comes not from option text of the "famHx" question, but from the cloneable paragraph injected for each selected rel.
 * For example, "famHx.brother2+male.age" was generated from "+male.age" datasync assigned within "+male" par, which had been injected by "famHx.brother2".
 */
class JDataSyncFamGroup {
  
  public $suid;
  public $sopts;        // [text,..] selected option text for suid question
  public $puids;        // [puid,..] selected injector+puids
  public $records;      // [puid=>[field=>value,..],..] 
  
  // Definitions
  private $fieldDefs;    // FIELD_DEFS element for $suid
  private $soptsToPuids; // STOPS_TO_PUIDS element for $suid
  private $puidTexts;    // PUIDS_TEXT element for $suid
  
  /*
   * Suids
   */
  const SUID_FAM = "famHx";
   
  /*
   * Field definitions
   * suid=>[
   *    field=>[label],..
   *   ]
   */
  public static $FIELD_DEFS = array(
    JDataSyncFamGroup::SUID_FAM => array(
      'status'   => array('Status'),
      'deathAge' => array('Age at Death'),
      'age'      => array('Current Age'),
      'history'  => array('History'),
      'comment'  => array('Comment'))
    );
  /*
   * Selected-suid-options to puids
   * suid=>[
   *    optionText=>[puid,..],..
   *   ]
   */
  public static $SOPTS_TO_PUIDS = array(
    JDataSyncFamGroup::SUID_FAM => array(
      'Father'               => array('relFather+male'),
      'Mother'               => array('relMother+female'),
      'Paternal Grandfather' => array('relPGF+male'),
      'Paternal Grandmother' => array('relPGM+female'),
      'Maternal Grandfather' => array('relMGF+male'),
      'Maternal Grandmother' => array('relMGM+female'),
      '1 Brother'            => array('relBrother1+male'),
      '2 Brothers'           => array('relBrother1+male','relBrother2+male'),
      '3 Brothers'           => array('relBrother1+male','relBrother2+male','relBrother3+male'),
      '4 Brothers'           => array('relBrother1+male','relBrother2+male','relBrother3+male','relBrother4+male'),
      '5 Brothers'           => array('relBrother1+male','relBrother2+male','relBrother3+male','relBrother4+male','relBrother5+male'),
      '6 Brothers'           => array('relBrother1+male','relBrother2+male','relBrother3+male','relBrother4+male','relBrother5+male','relBrother6+male'),
      '7 Brothers'           => array('relBrother1+male','relBrother2+male','relBrother3+male','relBrother4+male','relBrother5+male','relBrother6+male','relBrother7+male'),
      '8 Brothers'           => array('relBrother1+male','relBrother2+male','relBrother3+male','relBrother4+male','relBrother5+male','relBrother6+male','relBrother7+male','relBrother8+male'),
      '9 Brothers'           => array('relBrother1+male','relBrother2+male','relBrother3+male','relBrother4+male','relBrother5+male','relBrother6+male','relBrother7+male','relBrother8+male','relBrother9+male'),
      '10 Brothers'          => array('relBrother1+male','relBrother2+male','relBrother3+male','relBrother4+male','relBrother5+male','relBrother6+male','relBrother7+male','relBrother8+male','relBrother9+male','relBrother10+male'),
      '1 Sister'             => array('relSister1+female'),
      '2 Sisters'            => array('relSister1+female','relSister2+female'),
      '3 Sisters'            => array('relSister1+female','relSister2+female','relSister3+female'),
      '4 Sisters'            => array('relSister1+female','relSister2+female','relSister3+female','relSister4+female'),
      '5 Sisters'            => array('relSister1+female','relSister2+female','relSister3+female','relSister4+female','relSister5+female'),
      '6 Sisters'            => array('relSister1+female','relSister2+female','relSister3+female','relSister4+female','relSister5+female','relSister6+female'),
      '7 Sisters'            => array('relSister1+female','relSister2+female','relSister3+female','relSister4+female','relSister5+female','relSister6+female','relSister7+female'),
      '8 Sisters'            => array('relSister1+female','relSister2+female','relSister3+female','relSister4+female','relSister5+female','relSister6+female','relSister7+female','relSister8+female'),
      '9 Sisters'            => array('relSister1+female','relSister2+female','relSister3+female','relSister4+female','relSister5+female','relSister6+female','relSister7+female','relSister8+female','relSister9+female'),
      '10 Sisters'           => array('relSister1+female','relSister2+female','relSister3+female','relSister4+female','relSister5+female','relSister6+female','relSister7+female','relSister8+female','relSister9+female','relSister10+female'),
      '1 Son'                => array('relSon1+male'),
      '2 Sons'               => array('relSon1+male','relSon2+male'),
      '3 Sons'               => array('relSon1+male','relSon2+male','relSon3+male'),
      '4 Sons'               => array('relSon1+male','relSon2+male','relSon3+male','relSon4+male'),
      '5 Sons'               => array('relSon1+male','relSon2+male','relSon3+male','relSon4+male','relSon5+male'),
      '6 Sons'               => array('relSon1+male','relSon2+male','relSon3+male','relSon4+male','relSon5+male','relSon6+male'),
      '1 Daughter'           => array('relDaughter1+female'),
      '2 Daughters'          => array('relDaughter1+female','relDaughter2+female'),
      '3 Daughters'          => array('relDaughter1+female','relDaughter2+female','relDaughter3+female'),
      '4 Daughters'          => array('relDaughter1+female','relDaughter2+female','relDaughter3+female','relDaughter4+female'),
      '5 Daughters'          => array('relDaughter1+female','relDaughter2+female','relDaughter3+female','relDaughter4+female','relDaughter5+female'),
      '6 Daughters'          => array('relDaughter1+female','relDaughter2+female','relDaughter3+female','relDaughter4+female','relDaughter5+female','relDaughter6+female'),
      '1 Paternal Uncle'     => array('relPUncle1+male'),
      '2 Paternal Uncles'    => array('relPUncle1+male','relPUncle2+male'),
      '3 Paternal Uncles'    => array('relPUncle1+male','relPUncle2+male','relPUncle3+male'),
      '1 Maternal Uncle'     => array('relMUncle1+male'),
      '2 Maternal Uncles'    => array('relMUncle1+male','relMUncle2+male'),
      '3 Maternal Uncles'    => array('relMUncle1+male','relMUncle2+male','relMUncle3+male'),
      '1 Paternal Aunt'      => array('relPAunt1+female'),
      '2 Paternal Aunts'     => array('relPAunt1+female','relPAunt2+female'),
      '3 Paternal Aunts'     => array('relPAunt1+female','relPAunt2+female','relPAunt3+female'),
      '1 Maternal Aunt'      => array('relMAunt1+female'),
      '2 Maternal Aunts'     => array('relMAunt1+female','relMAunt2+female'),
      '3 Maternal Aunts'     => array('relMAunt1+female','relMAunt2+female','relMAunt3+female'))    
    );
  /*
   * Puid-prefixes to selected-suid-options  
   */
  public static $PFX_TO_SOPTS = array(
    JDataSyncFamGroup::SUID_FAM => array(
      'relFather'  => array('Father'),
      'relMother'  => array('Mother'),
      'relPGF'     => array('Paternal Grandfather'),
      'relPGM'     => array('Paternal Grandmother'),
      'relMGF'     => array('Maternal Grandfather'),
      'relMGM'     => array('Maternal Grandmother'),
      'relBrother' => array('1 Brother','2 Brothers','3 Brothers','4 Brothers','5 Brothers','6 Brothers','7 Brothers','8 Brothers','9 Brothers','10 Brothers'),
      'relSister'  => array('1 Sister','2 Sisters','3 Sisters','4 Sisters','5 Sisters','6 Sisters','7 Sisters','8 Sisters','9 Sisters','10 Sisters'),
      'relSon'     => array('1 Son','2 Sons','3 Sons','4 Sons','5 Sons','6 Sons'),
      'relDaughter' => array('1 Daughter','2 Daughters','3 Daughters','4 Daughters','5 Daughters','6 Daughters'),
      'relPUncle'  => array('1 Paternal Uncle','2 Paternal Uncles','3 Paternal Uncles'),
      'relMUncle'  => array('1 Maternal Uncle','2 Maternal Uncles','3 Maternal Uncles'),
      'relPAunt'   => array('1 Paternal Aunt','2 Paternal Aunts','3 Paternal Aunts'),
      'relMAunt'   => array('1 Maternal Aunt','2 Maternal Aunts','3 Maternal Aunts'))
    );
  /*
   * Descriptive text value for puids, if necessary
   */
  public static $PUIDS_TEXT = array(
    JDataSyncFamGroup::SUID_FAM => array(
      'relFather+male'     => 'Father',
      'relMother+female'   => 'Mother',
      'relPGF+male'        => 'Paternal Grandfather', 
      'relPGM+female'      => 'Paternal Grandmother', 
      'relMGF+male'        => 'Maternal Grandfather', 
      'relMGM+female'      => 'Maternal Grandmother', 
      'relBrother1+male'   => 'Brother 1',
      'relBrother2+male'   => 'Brother 2',
      'relBrother3+male'   => 'Brother 3',
      'relBrother4+male'   => 'Brother 4',
      'relBrother5+male'   => 'Brother 5',
      'relBrother6+male'   => 'Brother 6',
      'relBrother7+male'   => 'Brother 7',
      'relBrother8+male'   => 'Brother 8',
      'relBrother9+male'   => 'Brother 9',
      'relBrother10+male'  => 'Brother 10',
      'relSister1+female'  => 'Sister 1',
      'relSister2+female'  => 'Sister 2',
      'relSister3+female'  => 'Sister 3',
      'relSister4+female'  => 'Sister 4',
      'relSister5+female'  => 'Sister 5',
      'relSister6+female'  => 'Sister 6',
      'relSister7+female'  => 'Sister 7',
      'relSister8+female'  => 'Sister 8',
      'relSister9+female'  => 'Sister 9',
      'relSister10+female' => 'Sister 10',
      'relSon1+male'       => 'Son 1',
      'relSon2+male'       => 'Son 2',
      'relSon3+male'       => 'Son 3',
      'relSon4+male'       => 'Son 4',
      'relSon5+male'       => 'Son 5',
      'relSon6+male'       => 'Son 6',
      'relDaughter1+female' => 'Daughter 1',
      'relDaughter2+female' => 'Daughter 2',
      'relDaughter3+female' => 'Daughter 3',
      'relDaughter4+female' => 'Daughter 4',
      'relDaughter5+female' => 'Daughter 5',
      'relDaughter6+female' => 'Daughter 6',
      'relPUncle1+male'    => 'Paternal Uncle 1',
      'relPUncle2+male'    => 'Paternal Uncle 2',
      'relPUncle3+male'    => 'Paternal Uncle 3',
      'relMUncle1+male'    => 'Maternal Uncle 1',
      'relMUncle2+male'    => 'Maternal Uncle 2',
      'relMUncle3+male'    => 'Maternal Uncle 3',
      'relPAunt1+female'   => 'Paternal Aunt 1',
      'relPAunt2+female'   => 'Paternal Aunt 2',
      'relPAunt3+female'   => 'Paternal Aunt 3',
      'relMAunt1+female'   => 'Maternal Aunt 1',
      'relMAunt2+female'   => 'Maternal Aunt 2',
      'relMAunt3+female'   => 'Maternal Aunt 3')
    );
    
  /*
   * $sopts:[text,..]           // Currently selected suid options
   * $values:[dsync=>value,..]  // All client DATA_SYNC field values for suid (e.g. "famHx.*")
   * $includeDefs               // if true, fieldDefs and soptsToPuids included in out() 
   * Examine $this->sopts after construction for calculated selected options of suid question; these may be different for example if data records are found yet suid option is unchecked 
   */
  public function __construct($suid, $sopts, $values, $includeDefs = false) {
    $fieldDefs = JDataSyncFamGroup::$FIELD_DEFS[$suid];
    if ($includeDefs) {
      $this->fieldDefs = $fieldDefs;
    }
    $this->suid = $suid;
    $this->puidTexts = JDataSyncFamGroup::$PUIDS_TEXT[$suid];
    $this->sopts = $sopts;
    $this->setPuidsBySopts($sopts);
    if (! empty($this->puids)) {
      $this->addPuidsFromValues($values);  // also calculates $this->sopts
      $this->records = array();
      if ($this->puids) {
        $fields = array_keys($fieldDefs);
        foreach ($this->puids as &$puid) {
          $prefix = $suid . "." . $puid . ".";
          $this->records[$puid] = new JDataSyncFamRec($prefix, $fields, $values);
        }
      }
    }
  }
  /*
   * Remove $puid from client's JDataSyncFamGroup and recalculate selected options
   * May require datasync reassigning, e.g. removing relSister2 from [relSister1,relSister2,relSister3] results in [relSister1, relSister2] with original relSister3 reassigned as relSister2
   * If so, returns puids to reassign:
   *   [oldPuid => newPuid] 
   * In all cases, examine $this->sopts for recalculated selection 
   */
  public function removePuid($puid) {
    $pfx = $this->getPuidPfx($puid);  // 'relSister'
    $puidsByPfx = $this->getPuidsByPfx($pfx);
    $puids = &$puidsByPfx[$pfx];  // ['relSister1+female','relSister2+female','relSister3+female']
    $ix = array_search($puid, $puids);   
    $reassigns = array();
    $j = count($puids) - 1;
    for ($i = $ix; $i < $j; $i++) {
      $reassigns[$puids[$i + 1]] = $puids[$i];
    }
    unset($puids[$ix]);
    $this->setPuidsAndSoptsByPfx($puidsByPfx);
    return $reassigns;
  }
  // Set puids from selected opts [text,..] 
  private function setPuidsBySopts($sopts) {
    $puids = array();
    if ($sopts) {
      foreach ($sopts as &$text) {
        if (isset(JDataSyncFamGroup::$SOPTS_TO_PUIDS[$this->suid][$text])) {
          $sels = JDataSyncFamGroup::$SOPTS_TO_PUIDS[$this->suid][$text];
          if ($sels) 
            $puids = array_merge($puids, $sels);
        }
      }
    }
    $this->puids = array_distinct($puids);
  }
  // Add any puids from existing data values [dsync=>value,..]  
  private function addPuidsFromValues($values) {
    $puids = array();
    foreach ($values as $dsync => &$value) {
      $puids[$this->extractPuid($dsync)] = 1;
    }
    $this->puids = array_distinct(array_merge($this->puids, array_keys($puids)));
    $this->setPuidsAndSoptsByPfx($this->getPuidsByPfx());  // run thru this to establish sopts  
  }
  // Return matching puid prefix (as defined by $PFX_TO_SOPTS) 
  private function getPuidPfx($puid) {
    foreach (JDataSyncFamGroup::$PFX_TO_SOPTS[$this->suid] as $pfx => &$sopts) {
      if (strncasecmp($puid, $pfx, strlen($pfx)) == 0) {
        return $pfx;
      }
    }
  }
  private function extractPuid($dsync) {  // return puid from suid.puid.field
    $a = explode(".", $dsync);
    return $a[1];
  }
  // Returns [pfx=>[puid,..]] for selected puids
  private function getPuidsByPfx() {
    $pfxs = array();
    foreach (JDataSyncFamGroup::$PFX_TO_SOPTS[$this->suid] as $pfx => &$sopts) {
      $pfxs[$pfx] = array();
    }
    foreach ($this->puids as &$puid) {
      $pfx = $this->getPuidPfx($puid);
      $pfxs[$pfx][] = $puid;
    }
    return $pfxs;
  }
  // Set puids and sopts from puidsByPfx [pfx=>[puid,..]]
  private function setPuidsAndSoptsByPfx($pfxs) {
    $this->sopts = array();
    $this->puids = array();
    foreach (JDataSyncFamGroup::$PFX_TO_SOPTS[$this->suid] as $pfx => &$sopts) {
      $puids = $pfxs[$pfx];  // selected puids for pfx, if any
      $ct = count($puids);
      if ($ct > 0) {
        $this->puids = array_merge($this->puids, $puids); 
        $this->sopts[] = $sopts[$ct - 1];
      } 
    }
  }
  /*
   * Returns {
   *   "suid":suid,
   *   "sopts":[text,..],
   *   "puids":[puid,..],
   *   "fields":{field:[label],..}
   *   "recs":{puid:{field:value,..},..}
   *   "soptsToPuids":{soptText:[puid,..],..}
   *   "puidTexts":{puid:text,..}
   *   }
   */
  public function out() {
    $out = "";
    $out = nqq($out, "suid", $this->suid);
    $out = nqqo($out, "sopts", jsonencode($this->sopts));
    $out = nqqo($out, "puids", jsonencode($this->puids));
    $out = nqqo($out, "recs", aarr($this->records));
    $out = nqqo($out, "fields", jsonencode($this->fieldDefs));
    $out = nqqo($out, "soptsToPuids", jsonencode($this->soptsToPuids));
    $out = nqqo($out, "puidTexts", jsonencode($this->puidTexts));
    return cb($out);
  }
}

/*
 * Record object for JDataSyncFamGroup
 */
class JDataSyncFamRec {
  
  public $fieldValues;
  
  /*
   * $prefix:"suid.injector+puid."
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