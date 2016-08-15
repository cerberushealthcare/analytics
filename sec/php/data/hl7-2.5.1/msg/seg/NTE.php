<?php
require_once 'php/data/hl7-2.5.1/msg/seg/_HL7Segment.php';
//
/**
 * Notes and Comments v2.5.1
 * @author Warren Hornsby
 */
class NTE extends HL7SequencedSegment {
  //
  public $segId = 'NTE';
  public $seq;  // Set ID - NTE (SI)
  public $source;  // Source of Comment (ID)
  public $comment;  // Comment (FT)
  public $type;  // Comment Type (CE)
  //
  static $_seq = 0;
}
