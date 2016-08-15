<?php
require_once 'php/data/LoginSession.php';
require_once 'bat/batch.php';
require_once 'php/cbat/csv-export/papyrus/CsvExport.php';
//
?>
<html>
  <head>
  </head>
  <body>
  <pre>
<?php 
switch ($_GET['t']) {
  case '1':
    t();
    CsvExport::exec();
    t();
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
?>
</html>