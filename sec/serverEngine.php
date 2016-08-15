<?php
require_once 'php/dao/LoginDao.php';
require_once 'php/dao/UserDao.php';
require_once 'php/dao/MsgDao.php';
require_once 'php/data/json/JAjaxMsg.php'; 

if (LoginDao::authenticateSession() < 0) {
  $m = new JAjaxMsg('save-timeout', 'null');
  echo $m->out();
  exit;
}
if (isset($_GET['action'])) {
  $action = $_GET['action'];
  $id = geta($_GET, 'id');
} else {
  $_POST['obj'] = stripslashes($_POST['obj']);
  $action = $_POST['action'];
  $obj = jsondecode($_POST['obj']);
  logit('serverEngine.php (posted)');
  logit_r($_POST);
}
switch ($action) {
  case '':
    break;
  default:
    $m = new JAjaxMsg('error', $action);
}
if ($m != null) {
  echo $m->out();
}
?>