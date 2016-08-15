<?php
require_once 'php/data/csv/_CsvFile.php';
//
/**
 * CSVImportFile for generating .SQL import files
 * @author Warren Hornsby
 * @example
 *   $file = SomeCsvImportFile::load();
 *   $file->outputSql();
 */
abstract class CsvImportFile extends CsvFile {
  //
  static $SQLIMPORT_CLASS;  // 'SqlRec_Import' (implements CsvImporter)
  //
  public function outputSql($lines = null) {
    if ($lines == null)
      $lines = $this->buildSqlLines();
    $filename = static::$FILENAME . '.sql';
    if (($handle = fopen($filename, 'w', true)) == false) 
      throw new Exception("Unable to open file $filename");
    foreach ($lines as $line) 
      fwrite($handle, static::getSqlLineOutput($line));
    fclose($handle);
    return $filename;
  }
  public function buildSqlLines() {
    $class = static::$SQLIMPORT_CLASS;
    $recs = $class::fromFile($this);
    $lines = $class::buildSqlLines($recs);
    return $lines;
  }
  //
  static function getSqlLineOutput($line) {
    return static::concatSemi($line) . "\n";
  }
  static function concatSemi($line) {
    if (substr($line, -1, 1) != ';')
      $line .= ';';
    return $line;
  }
}
abstract class SqlRec_Export extends SqlRec {
  /**
   * @param string[] $lines array('INSERT INTO...',..)
   */
  public function appendSqlInsertTo(&$lines) {
    $lines[] = CsvImportFile::concatSemi($this->getSqlInsert());
  }
  //
  /**
   * @param SqlRec_Import[] $recs
   * @return string[]
   */
  static function buildSqlLines($recs) {
    $lines = array();
    foreach ($recs as $rec)  
      $rec->appendSqlInsertTo($lines);
    return $lines;
  }
}
abstract class SqlRec_Import extends SqlRec_Export {
  //
  /**
   * @param CsvRec $rec
   * @return SqlRec_Import
   */
  abstract static function fromRec($csv);
  /**
   * @param CsvFile $file
   * @return array(SqlRec_Import,..)
   */
  static function fromFile($file) {
    $mes = array();
    foreach ($file->recs as $rec)
      $mes[] = static::fromRec($rec);
    return $mes; 
  }
}
