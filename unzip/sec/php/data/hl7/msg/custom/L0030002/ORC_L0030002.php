<?php
//
class ORC_L0030002 extends ORC {
  //
  /* qtyTiming.priority */
  const PRIORITY_STAT = 'S';
  const PRIORITY_ASAP = 'A';
  const PRIORITY_ROUTINE = 'R';
  const PRIORITY_CALLBACK = 'C';
  const PRIORITY_TIMING_CRIT = 'T';
  static $PRIORITIES = array(
    self::PRIORITY_STAT => 'STAT',
    self::PRIORITY_ASAP => 'As Soon As Possible',
    self::PRIORITY_ROUTINE => 'Routine',
    self::PRIORITY_CALLBACK => 'Callback',
    self::PRIORITY_TIMING_CRIT => 'Timing Critical');  
}
