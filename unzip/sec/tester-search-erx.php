<?php
require_once 'php/data/LoginSession.php';
require_once 'php/data/rec/sql/Clients.php';
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
    $recs = Clients::search('Hornsby', 'Warren');
    p_r($recs);
    exit;
}
?>
</html>