<?php
require_once 'php/data/LoginSession.php';
require_once 'php/c/template-entry/TemplateEntry.php';
//
LoginSession::verify_forServer();
//
switch ($_GET['t']) {
  case '1':
    t();
    $par = TemplateEntry::getPar(3183);
    t();
    $json = jsonencode($par);
    t();
    echo "length=" . strlen($json) . "<br>";
    echo $json;
    exit;
  case '2':
    t();
    $par = TemplateEntry::getPar(3183);
    t();
    $json = jsonencode2($par);
    t();
    echo "length=" . strlen($json) . "<br>";
    echo $json;
    exit;
  case '3':
    $q = TemplateEntry::getPmhxQuestion();
    p_r($q);
    exit;
  case '4':
    require_once 'php/data/rec/sql/Templates_Map.php';
    $map = Templates_Map::get(1, '2011-01-01');
    p_r($map);
    exit;
}
function t() {
  static $x;
  static $last;
  static $total;
  $now = microtime(true);
  if ($last == null)
    $last = $now;
  $elapsed = $now - $last; 
  $total += $elapsed;
  $x++;
  echo "$x: " . date("Y-m-d H:i:s") . " (+" . sprintf("%01.2f", $elapsed) . ") (" . sprintf("%01.2f", $total) . ")<br>";
}
