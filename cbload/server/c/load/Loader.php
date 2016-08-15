<?php
require_once 'Loader_Files.php';
//
class Loader {
  //
  static function run() {
    $recs = Chord_In::fetch();
    $recs = Chord_Out::create($recs);
    Chord_Json::create($recs);
  }
}