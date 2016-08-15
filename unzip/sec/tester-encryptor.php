<?php
require_once 'php/data/LoginSession.php';
require_once 'php/cbat/encryptor/Encryptor.php';
require_once 'php/cbat/encryptor/hdata/HdCreator.php';
//
//LoginSession::verify_forServer();
?>
<html>
  <head>
  </head>
  <body>
  <pre>
<?php 
switch ($_GET['t']) {
  case '1':
    $ugid = 1;
    $sets = Encryptor::getClientSets($ugid);
    p_r($sets);
    exit;
  case '2':
    $ugid = 1;
    $sql = Encryptor::getSessionSets($ugid);
    p_r($sql);
    exit;
  case '3':
    $ugid = 1;
    $sql = Encryptor::getProcSets($ugid);
    p_r($sql);
    exit;
  case '4':
    $ugid = 1;
    $sql = Encryptor::getDataSyncSets($ugid);
    p_r($sql);
    exit;
  case '5':
    $ugid = 1;
    $sql = Encryptor::getAuditSets($ugid);
    p_r($sql);
    exit;
  case '6':
    $ugid = 1;
    $sql = Encryptor::getPortalUserSets($ugid);
    p_r($sql);
    exit;
  case '10':
    $ugid = 1;
    HdCreator::exec(1);
    exit;
}
?>
</html>
