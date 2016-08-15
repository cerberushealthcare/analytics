<?php
require_once 'php/data/csv/client-import/henein/HeneinRec.php';
//
$class = "HeneinRec";
$batchSize = 100;
//
require_once 'inc/requireLogin.php';
if (! $myLogin->admin)
  header('Location: welcome.php');
echo '<pre style="font-size:9pt">';
//
$step = intval(geta($_GET, 'step', 0));
$batch = intval(geta($_GET, 'batch', 0));
$next = false;
switch ($step) {
  case 0:
    $next = "Fetch $class";
    break;
  case '1':
    $recs = sc($class, 'read', $batch, $batchSize);
    print_r($recs);
    $next = "Upload $batchSize";
    break;
  case '2':
    $recs = sc($class, 'read', $batch, $batchSize);
    sc($class, 'export', $recs);
    if ($recs) {
      $batch++;
      $step = 0;
      $next = 'Fetch next batch';
    }
    break;
}
//
if ($next) {
  $step++;
  $rnd = rnd();
  echo "<div><br><a href='csv-import.php?step=$step&batch=$batch&$rnd'>$next</a></div>";
}
echo '</pre>';
?>
