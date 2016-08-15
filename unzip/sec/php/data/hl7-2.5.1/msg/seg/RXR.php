<?php
require_once 'php/data/hl7-2.5.1/msg/seg/_HL7Segment.php';
//
/**
 * Pharmacy/Treatment Route v2.5.1
 * @author Warren Hornsby
 */
class RXR extends HL7Segment {
  //
  public $segId = 'RXR';
  public $route;  // RXR-1: Route (CE) - HL70162
  public $site;  // RXR-2: Administration Site (CWE) optional - HL70163
  public $device;  // RXR-3: Administration Device (CE) optional
  public $method;  // RXR-4: Administration Method (CWE) optional
  public $instruction;  // RXR-5: Routing Instruction (CE) optional
  public $modifier;  // RXR-6: Administration Site Modifier (CWE) optional  
}
class RXR_VXU extends RXR {
  //
  static function loadIds($fs) {
    if ($fs->Immun_HL7) {
      HL70162::loadIds($fs->Immun_HL7->route);
      HL70163::loadIds($fs->Immun_HL7->site);
    }
  }
  static function from($imm) {
    $me = static::asEmpty();
    $me->route = CE_Route::fromImmun($imm);
    $me->site = CE_Site::fromImmun($imm);
    return $me;
  }
}
