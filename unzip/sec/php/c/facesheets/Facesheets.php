<?php
require_once 'Facesheet_Recs.php';
//
class Facesheets {
  //
  /** Get facesheet for visit summary */
  static function /*Facesheet_Visit*/asVisitSummary($cid, $dos) {
    return Facesheet_Visit::from($cid, $dos);    
  }
  /** Get facesheet for HL7 ADT pub health message */
  static function /*Facesheet_Hl7Adt_PubHealth*/asPubHealth($cid) {
    return Facesheet_Hl7Adt_PubHealth::from($cid);
  }
}