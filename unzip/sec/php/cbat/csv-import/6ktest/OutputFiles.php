<?php
require_once 'php/data/file/_File.php';
//
class OutputSql extends RecFile {
  //
  static $FILENAME = 'output.sql';
}
class OutputFalloutsCsv extends TextFile {
  //
  static $FILENAME = 'output-fallouts.csv';
}
class Output2Sql extends RecFile {
  //
  static $FILENAME = 'output2.sql';
}
