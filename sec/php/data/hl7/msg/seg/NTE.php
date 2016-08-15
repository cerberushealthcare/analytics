<?php
require_once 'php/data/hl7/msg/seg/_HL7Segment.php';
//
/**
 * Notes and Comments
 * @author Warren Hornsby
 */
class NTE extends HL7Segment {
  //
  public $segId = 'NTE';
  public $seq;  // Set ID - NTE (SI)
  public $source;  // Source of Comment (ID)
  public $comment;  // Comment (FT)
  public $type;  // Comment Type (CE)
}
