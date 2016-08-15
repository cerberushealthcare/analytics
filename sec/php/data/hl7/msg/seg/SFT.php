<?php
require_once 'php/data/hl7/msg/seg/_HL7Segment.php';
//
/**
 * Software 
 * @author Warren Hornsby
 */
class SFT extends HL7Segment {
  //
  public $segId = 'SFT';
  public $vendor = 'XON';  // Software Vendor Organization (XON)
  public $version;  // Software Certified Version or Release Number (ST)
  public $name;  // Software Product Name (ST)
  public $binaryId;  // Software Binary ID (ST)
  public $productInfo;  // Software Product Information (TX)
  public $installDate = 'TS';  // Software Install Date (TS)
}
