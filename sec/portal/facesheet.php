<?php
require_once "inc/tags.php";
require_once 'inc/require-login.php';
?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <?php PHEAD('Facesheet', 'facesheet.css') ?>
    <?php PHEAD_DATA('PortalFacesheet') ?>
  </head>
  <body>
    <?php PPAGEHEAD($me) ?>
    <div id='page'></div>
    <?php PPAGEFOOT() ?>
  </body>
  <?php PPAGE('FacesheetPage', $me) ?>
</html>
