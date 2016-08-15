<?php
require_once 'php/data/LoginSession.php';
require_once 'php/c/info-button/InfoButton_Query.php';
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
    $icd = '250.33';
    $json = InfoButtonQuery::searchDiag($icd);
    p_r($json);
    exit;
  case '2':
    $icd = '272.1';
    $json = InfoButtonQuery::searchDiag($icd);
    p_r($json);
    exit;
}
?>
</html>