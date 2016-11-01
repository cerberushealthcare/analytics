<?php
require_once "inc/tags.php";
require_once 'inc/require-login.php';
?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <?php HEAD('Messages') ?>
  </head>
  <body>
    <?php PAGEHEAD($me) ?>
    <div id='page'></div>
    <?php PAGEFOOT() ?>
  </body>
  <?php PAGE('MessagesPage', $me) ?>
</html>
