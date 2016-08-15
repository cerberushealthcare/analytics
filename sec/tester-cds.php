<?php
require_once 'php/data/LoginSession.php';
require_once 'php/c/health-maint/HealthMaint.php';
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
    $id = 3814/*Snow, Madelynn Ainsley*/;
    $recs = HealthMaint::getForClient($id);
    p_r($recs);
    exit;
}
?>
</html>