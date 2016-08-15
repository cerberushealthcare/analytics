<?php
require_once 'php/data/csv/_CsvImportFile.php';
//
class Papyrus_Import {
  //
  static function import() {
    $pclient = PclientFile::load();
    $map = $pclient->getMap();
    $papyrus = PapyrusFile::load();
    $xrefs = array();
    foreach ($papyrus->recs as $rec) {
      $client = geta($map, $rec->getKey());
      if ($client)
        $xrefs[] = ApiIdXref_Ex::from($rec, $client);
    }
    $lines = ApiIdXref_Ex::buildSqlLines($xrefs);
    foreach ($lines as $line)
      print_r(CsvImportFile::getSqlLineOutput($line));
    exit;
  }
}
//
class CsvPapyFile extends CsvFile {
  //
  static $CSVREC_CLASS = 'CsvPapyRec';
  static $HAS_FID_ROW = true;
}
class CsvPapyRec extends CsvRec {
  //
  public $patientId;
  public $practiceId;
  public $last;
  public $first;
  public $dob;
  //
  public function getKey() {
    $key = $this->last . ',' . $this->first . ',' . $this->dob;
    return $key;
  }
}
//
class PapyrusFile extends CsvPapyFile {
  //
  static $FILENAME = 'php/data/csv/papyrus-import/papyrus.csv';
}
class PclientFile extends CsvPapyFile {
  //
  static $FILENAME = 'php/data/csv/papyrus-import/pclients.csv';
  //
  public function getMap() {
    $map = array();
    foreach ($this->recs as $rec) 
      $map[$rec->getKey()] = $rec;
    return $map;
  }
}
//
class ApiIdXref_Ex extends SqlRec_Export {
  //
  public $partner;
  public $practiceId;
  public $type;
  public $externalId;
  public $internalId;
  //
  public function getSqlTable() {
    return 'api_id_xref';
  }
  //
  static function from($papyrus, $client) {
    $me = new static();
    $me->partner = 5001;  // CERBERUS
    $me->practiceId = $papyrus->practiceId;
    $me->type = 3;  // ID_TYPE_PATIENT
    $me->externalId = $papyrus->patientId;
    $me->internalId = $client->patientId;
    return $me;
  }
}