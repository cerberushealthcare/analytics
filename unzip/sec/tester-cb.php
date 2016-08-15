<?php
require_once 'php/data/LoginSession.php';
require_once 'php/cbat/ccda-batch/CcdaBatcher.php';
require_once 'php/c/patient-list/PatientList.php';
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
    //$batch = CcdaBatcher::start();
    p_r($batch);
    exit;
  case '2':
    $bid = 16;
    $hash = '516610e81878f662316b1c357f74ffdec8eeee4d';
    CcdaBatcher::next($bid, $hash);
    exit;
  case '3':
    $batch = CcdaBatcher::start(true);
    p_r($batch);
    exit;
  case '10':
    $page = PatientList::page();
    p_r($page);
    exit;
  case '11':
    $batch = CcdaBatch::fetchLast(200);
    p_r($batch);
    exit;
}
?>
</html>