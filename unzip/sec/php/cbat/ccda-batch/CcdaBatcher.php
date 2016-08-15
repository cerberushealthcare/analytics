<?php
require_once 'php/c/patient-list/PatientList.php'; 
require_once 'php/data/xml/ccd/ClinicalDocument.php';
require_once 'php/data/rec/group-folder/GroupFolder_BatchCcda.php';
require_once 'CcdaBatch_Recs.php';
//
class CcdaBatcher {
  //
  static function start($synchronous = false) {
    global $login;
    $clients = PatientList::getAllActive();
    CcdaBatch::stopAllActive($login->userGroupId);
    $batch = CcdaBatch::create($login->userGroupId, $clients);
    if ($synchronous)
      static::next($batch->batchId, $batch->hash);
    return $batch;
  }
  static function next($bid, $hash) {
    $batch = CcdaBatch::fetchActive($bid, $hash);
    if ($batch) {
      $batch->next();
      $cba = $batch->getItem();
      static::processItem($batch, $cba);
      if ($batch->hasMore()) {
        static::next($bid, $hash);
      }
    }
  }
  static function clear() {
    global $login;
    $batch = CcdaBatch::fetchLast($login->userGroupId);
    $batch->clear();
  }
  //
  protected static function processItem($batch, $cba) {
    if ($cba) {
      $ccda = ClinicalDocument::fetch($cba->clientId);
      $folder = GroupFolder_BatchCcda::open();
      $folder->save($ccda, $batch->batchId);
      $batch->setItemComplete($cba);
    }  
  }
}
