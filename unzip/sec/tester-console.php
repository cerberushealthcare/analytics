<?php
require_once 'php/data/LoginSession.php';
require_once "php/delegates/JsonDelegate.php";
require_once "php/data/Version.php";
require_once 'php/data/rec/sql/OrderEntry.php';
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
    $session = JsonDao::buildJSession($_GET["sid"], true);
    p_r($session);
    exit;
}
?>
</html>
