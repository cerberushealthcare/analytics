<?php
require_once 'server.php';
require_once 'php/data/rec/sql/PortalFacesheets.php';
//
try {
  switch ($action) {
    //
    case 'get':
      $fs = PortalFacesheets::getMine();
      jam($fs);
  }
} catch (Exception $e) {
  jamerr($e);
}
?>
