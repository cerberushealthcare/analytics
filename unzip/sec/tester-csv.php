<?php
require_once 'php/data/LoginSession.php';
require_once 'bat/batch.php';
require_once 'php/cbat/csv-import/agbassi/InputFiles.php';
require_once 'php/cbat/csv-import/agbassi/CsvImport.php';
//
LoginSession::verify_forServer();
?>
<html>
  <head>
  </head>
  <body>
  <pre>
<?php 
switch ($_GET['t']) {
  case '1':
    $recs = PatientCsv::fetch()->recs;
    p_r($recs);
    exit;
  case '2':
    $file = PatientCsv::fetch();
    $file->download();
    exit;
  case '3':
    t();
    CsvImport::exec();
    t();
    exit;
  case '4':
    $a = '0';
    $b = ($a) ? 'true' : 'false';
    $c = (! is_null($a) && $a !== '') ? 'true' : 'false';
    p_r($b . ',' . $c, 'zero');
    $a = '';
    $b = ($a) ? 'true' : 'false';
    $c = (! is_null($a) && $a !== '') ? 'true' : 'false';
    p_r($b . ',' . $c, '""');
    $a = null;
    $b = ($a) ? 'true' : 'false';
    $c = (! is_null($a) && $a !== '') ? 'true' : 'false';
    p_r($b . ',' . $c, 'null');
    exit;
  case '5':
    global $login;
    p_r($login);
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
?>
</html>