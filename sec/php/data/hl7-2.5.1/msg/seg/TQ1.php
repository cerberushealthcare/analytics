<?php
require_once 'php/data/hl7-2.5.1/msg/seg/_HL7Segment.php';
//
/**
 * Timing/Quantity v.2.5.1
 * @author Warren Hornsby
 */
class TQ1 extends HL7Segment {
  //
  public $segId = 'TQ1';
  public $seq;  // 1: Set ID - TQ1 (SI) optional
  public $qty;  // 2: Quantity (CQ) optional
  public $repeat;  // 3: Repeat Pattern (RPT) optional repeating
  public $timeExplicit;  // 4: Explicit Time (TM) optional repeating
  public $timeRelative;  // 5: Relative Time and Units (CQ) optional repeating
  public $duration;  // 6: Service Duration (CQ) optional
  public $start = 'TS';  // 7: Start date/time (TS) optional
  public $end = 'TS';  // 8: End date/time (TS) optional
  public $priority = 'CWE';  // 9: Priority (CWE) optional repeating
  public $condition;  // 10: Condition text (TX) optional
  public $instruction;  // 11: Text instruction (TX) optional
  public $conjunction;  // 12: Conjunction (ID) optional
  public $durationOccur;  // 13: Occurrence duration (CQ) optional
  public $totalOccur;  // 14: Total occurrence's (NM) optional 
}
