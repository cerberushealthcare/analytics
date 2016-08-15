<?php
require_once 'php/data/file/_File.php';
//
class OutputSql extends RecFile {
  //
  static $FILENAME = 'output.sql';
}
class InsuranceOnlySql extends TextFile {
  //
  static $FILENAME = 'output-insurance-only.sql';
  //
  public function load($clients) {
    $lines = static::extractICards($clients);
    return parent::load($lines);
  }
  //
  protected static function extractICards($clients) {
    $lines = array();
    foreach ($clients as $client) {
      if ($client->ICard1)
        $lines[] = $client->ICard1->toString($client);
      if ($client->ICard2)
        $lines[] = $client->ICard2->toString($client);
    }
    return $lines;
  }
}
class OutputFalloutsCsv extends TextFile {
  //
  static $FILENAME = 'output-fallouts.csv';
}