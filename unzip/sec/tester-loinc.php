<?php
ini_set('memory_limit', '1024M');

require_once 'php/data/LoginSession.php';
require_once 'php/cbat/loinc-to-ipc/LoincToIpc.php';
//
?>
<html>
  <head>
  </head>
  <body>
  <pre>
<?php 
LoincToIpc::exec();
?>
</html>