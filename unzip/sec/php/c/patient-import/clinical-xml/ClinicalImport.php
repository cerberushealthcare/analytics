<?php
require_once 'ClinicalImport_Sql.php';
require_once 'ClinicalImport_Ccr.php';
//
/**
 * Clinical Data Import
 * @author Warren Hornsby
 */
class ClinicalImport {
  //
  /** Build client from CCR
      @throws RecValidator, Dupe (unless cid supplied) */
  static function /*Client_Ci*/fromCcr(/*string*/$xml, $cid = null/*to update*/) {
    global $login;
    $ccr = ContinuityOfCareRecord::fromXml($xml);
    $client = $cid ? Client_Ci_Ccr::fetch($cid) : Client_Ci_Ccr::create($login->userGroupId);
    $client->saveDemo($ccr);
    $client->import($ccr);
    return $client;
  }
}