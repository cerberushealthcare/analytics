<?php
require_once 'php/data/rec/sql/_ClientRec.php';
/**
 * Clients Administration
 * @author Warren Hornsby
 */
class Clients_Admin extends Clients {
  /**
   * Merge client records (correct and dupe) 
   * Correct client will absorb dupe client data; dupe will be deactiated
   * @param int $cid of correct record
   * @param int $cidDupe of dupe record
   */
  static function merge($cid, $cidDupe) {
    query("UPDATE sessions SET client_id=$cid WHERE client_id=$cidDupe");
    query("UPDATE scheds SET client_id=$cid WHERE client_id=$cidDupe");
    query("UPDATE data_allergies SET client_id=$cid WHERE client_id=$cidDupe AND source IS NULL");
    query("UPDATE data_diagnoses SET client_id=$cid WHERE client_id=$cidDupe");
    query("UPDATE data_hm SET client_id=$cid WHERE client_id=$cidDupe AND session_id=0 AND active=1");
    query("UPDATE data_immuns SET client_id=$cid WHERE client_id=$cidDupe");
    query("UPDATE data_meds SET client_id=$cid WHERE client_id=$cidDupe AND source IS NULL");
    query("UPDATE data_vitals SET client_id=$cid WHERE client_id=$cidDupe");
    query("UPDATE clients SET user_group_id=0 WHERE client_id=$cidDupe");
    self::mergeAddresses($cid, $cidDupe);
  }
  private static function mergeAddresses($cidt, $cidDupe) {
    $client = ClientDemo::fetch($cid);
    $clientDupe = ClientDemo::fetch($cidDupe);
    $adds = $client->getAddresses();
    $addsDupe = $clientDupe->getAddresses();
    foreach ($adds as $type => $add) {
      $addDupe = $addsDupe[$type];
      if ($add == null && $addDupe) {
        $addDupe->tableId = $client->clientId;
        $addDupe->save();
      }
    }
    if (count($client->ICards) == 0)
      query("UPDATE client_icards SET client_id=$client->clientId WHERE client_id=$clientDupe->clientId");
  }
}
