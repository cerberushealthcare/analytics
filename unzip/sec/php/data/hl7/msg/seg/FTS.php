<?php
require_once 'php/data/hl7/msg/seg/_HL7Segment.php';
//
/**
 * File Trailer 
 * @author Warren Hornsby
 */
class FTS extends HL7Segment {
  //
  public $segId = 'FTS';
  public $batch;  // File Batch Count (NM)
  public $comment;  // File Trailer Comment (ST)
}
