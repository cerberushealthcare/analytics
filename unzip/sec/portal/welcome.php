<?php
require_once "inc/tags.php";
set_include_path('../');
require_once 'inc/require-login.php';
require_once 'php/data/rec/sql/PortalMessaging.php'; 
?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <?php PHEAD('Welcome', 'welcome.css') ?>
  </head>
  <body>
    <?php PPAGEHEAD($me) ?>
    <div id='page'></div>
    <?php PPAGEFOOT() ?>
  </body>
  <?php PPAGE('WelcomePage', $me) ?>
  <script type='text/javascript'>
  C_Unread = <?=PortalMessaging::getMyUnreadCt() ?>;
  </script>
</html>
