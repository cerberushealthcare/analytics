<?php
require_once 'php/data/LoginSession.php';
require_once 'php/data/rec/sql/IcdCodes10.php';
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
    $text = "Diabetes";
    $a = IcdCodes10::search($text);
    p_r($a);
    exit;
}
?>
</html>