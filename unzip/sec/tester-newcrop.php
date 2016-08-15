<?php
require_once 'php/data/LoginSession.php';
require_once 'php/newcrop/NewCrop.php';
//
LoginSession::verify_forServer();
//
?>
<html>
  <head>
  </head>
  <body>
  <pre>
<?php 
$nc = new NewCrop();
switch ($_GET['t']) {
  case '1':
    $now = strtotime(date("Y-m-d H:i:s"));
    p_r($now);
    $future = mktime(date('G'), date('i') + 30, 0);
    p_r(date('Y-m-d H:i:s', $future));
    p_r($future);
    //$nc->pullAcctStatusDetails();
    exit;
}

?>
</html>
