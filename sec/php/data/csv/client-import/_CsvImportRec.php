<?php
require_once 'php/data/csv/_CsvRec.php';
require_once 'php/data/csv/client-import/ClientImport.php';
//
abstract class CsvImportRec extends CsvRec {
  //
  abstract public function getUgid();
  abstract public function asClientImport();
  //
  static function hasHeaderRow() {
    return true;
  }
  static function read($filename, $class, $step = 0, $batchSize = 99999) {
    $start = $step * $batchSize;
    $to = $start + $batchSize;
    $recs = parent::read($filename, $class, $start, $to);
    foreach ($recs as $rec) {
      $rec->Client = $rec->asClientImport(); 
      $rec->Match = ClientImport::fetchByCsv($rec);
    }
    return $recs;
  }
  static function export($recs, $max = 1000) {
    for ($i = 0; $i <= $max; $i++) {
      if (empty($recs))
        return null;
      $rec = array_shift($recs);
      $client = $rec->Client;
      $match = $rec->Match;
      if ($match) 
        $client->setFromMatch($match);
      $client->save();
      print_r($client);
    }
    return empty($recs) ? null : $recs;
  }
}
