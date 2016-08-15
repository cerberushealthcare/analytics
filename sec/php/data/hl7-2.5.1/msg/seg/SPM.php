<?php
require_once 'php/data/hl7-2.5.1/msg/seg/_HL7Segment.php';
//
/**
 * Specimen v.2.5.1 
 * @author Warren Hornsby
 */
class SPM extends HL7Segment {
  //
  public $segId = 'SPM';
  public $seq;  // 1: Set ID _ SPM (SI) optional
  public $id;  // 2: Specimen ID (EIP) optional
  public $parentId;  // 3: Specimen Parent IDs (EIP) optional repeating
  public $type = 'CWE';  // 4: Specimen Type (CWE)
  public $typeMod;  // 5: Specimen Type Modifier (CWE) optional repeating
  public $additives;  // 6: Specimen Additives (CWE) optional repeating
  public $method;  // 7: Specimen Collection Method (CWE) optional
  public $site;  // 8: Specimen Source Site (CWE) optional
  public $siteMod;  // 9: Specimen Source Site Modifier (CWE) optional repeating
  public $siteCollect;  // 10: Specimen Collection Site (CWE) optional
  public $role;  // 11: Specimen Role (CWE) optional repeating
  public $amt;  // 12: Specimen Collection Amount (CQ) optional
  public $count;  // 13: Grouped Specimen Count (NM) optional
  public $desc;  // 14: Specimen Description (ST) optional repeating
  public $handling;  // 15: Specimen Handling Code (CWE) optional repeating
  public $risk;  // 16: Specimen Risk Code (CWE) optional repeating
  public $collected = 'DR';  // 17: Specimen Collection Date/Time (DR) optional
  public $received;  // 18: Specimen Received Date/Time (TS) optional
  public $expiration;  // 19: Specimen Expiration Date/Time (TS) optional
  public $avail;  // 20: Specimen Availability (ID) optional
  public $reject;  // 21: Specimen Reject Reason (CWE) optional repeating
  public $quality;  // 22: Specimen Quality (CWE) optional
  public $approp;  // 23: Specimen Appropriateness (CWE) optional
  public $condition = 'CWE';  // 24: Specimen Condition (CWE) optional repeating
  public $qtyCurrent;  // 25: Specimen Current Quantity (CQ) optional
  public $containers;  // 26: Number of Specimen Containers (NM) optional
  public $contType;  // 27: Container Type (CWE) optional
  public $contCond;  // 28: Container Condition (CWE) optional
  public $childRole;  // 29: Specimen Child Role (CWE) optional
}
