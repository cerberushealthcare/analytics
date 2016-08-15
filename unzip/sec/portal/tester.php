<?php
require_once 'inc/require-login.php';
set_include_path('../');
require_once 'php/data/rec/sql/PortalFacesheets.php';
require_once 'php/data/rec/sql/PortalMessaging.php';
//
?>
<html>
  <body>
<?php 
echo '<pre>';
switch ($_GET['t']) {
  case '0':
    $rec = PortalUser::fetchByUid('bb');
    print_r($rec);
    exit;
  case '1':
    $fs = PortalFacesheets::getMine();
    p_r($fs);
    exit;
  case '2':
    $i = PortalMessaging::getMyUnreadCt();
    p_r($i);
    exit;
  case '3':
    $recs = PortalMessaging::getMyInboxThreads();
    p_r($recs);
    exit;
  case '4':
    $rec = PortalMessaging::openThread(88);
    p_r($rec);
    exit;
  case '5':
    PortalMessaging::postReply('88', array('3'), 'This is a test');
    exit;
}
?>
  </body>
</html>