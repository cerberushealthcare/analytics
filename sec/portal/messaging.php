<?php
require_once "inc/tags.php";
require_once 'inc/require-login.php';
set_include_path('../');
require_once 'php/data/rec/sql/PortalMessaging.php';
?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <?php PHEAD('Messages', 'messaging.css') ?>
    <?php PHEAD_DATA('Messaging') ?>
  </head>
  <body>
    <?php PPAGEHEAD($me) ?>
    <div id='page'></div>
    <?php PPAGEFOOT() ?>
  </body>
  <?php JsonConstants::writeGlobals('MsgThread','MsgPost','MsgInbox','DocStub') ?>
  <script type='text/javascript'>
    C_MsgTypes = <?=jsonencode(PortalMessaging::getMyMsgTypes()) ?>;
  </script>
  <?php PPAGE('MessagingPage', $me) ?>
</html>
