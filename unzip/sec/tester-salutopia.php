<?php
require_once 'php/data/LoginSession.php';
require_once 'php/cbat/salutopia/Salutopia.php';
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
    $groups = SalutopiaBatch::fetchAll();
    $group = current($groups);
    p_r($group);
    $cids = AuditMru_Sal::fetchCids($group->userGroupId, $group->lastRun);
    p_r($cids);
    exit;    
  case '2':
    $groups = SalutopiaBatch::fetchAll();
    $group = current($groups);
    $ccds = Salutopia::buildCcds($group->userGroupId, $group->lastRun);
    $ccd = current($ccds);
    $xml = $ccd->toXml();
    p_r($ccd);
    exit;
    $response = Curl_Salutopia::send($xml);
    exit;    
  case '3':
    $groups = SalutopiaBatch::fetchAll();
    $group = current($groups);
    p_r($group, 'before');
    SalutopiaBatch::save_asLastRun($group->userGroupId);
    $groups = SalutopiaBatch::fetchAll();
    $group = current($groups);
    p_r($group, 'after');
    exit;
}
?>
</html>
