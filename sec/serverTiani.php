<?php
require_once "php/dao/_util.php";
require_once "php/dao/LoginDao.php";
require_once "php/dao/TianiDao.php";
require_once "php/data/json/JAjaxMsg.php"; 
//
if (LoginDao::authenticateSession() < 0) {
  $m = new JAjaxMsg("save-timeout", "null");
  echo $m->out();
  exit;
}
if (isset($_GET["action"])) {
  $_GP = &$_GET;
} else {
  $_GP = &$_POST;
}
$act = $_GP["action"];
if (isset($_GP["obj"])) {
  $_GP["obj"] = stripslashes($_GP["obj"]);
}
logit("serverTiani.php?" . implode_with_keys("&", $_GP));
$m = null;
//
switch ($act) {
  /**
   * Send PDF 
   */
//  case "sendPdf":    
//    $sid = $_GP["id"];
//    TianiDao::sendPdf($sid);
//    $m = new JAjaxMsg($act, '1');
//    break;
  /**
   * Send Clinical Document
   */
//  case "sendCd":
  case "sendPdf":    
    $sid = $_GP["id"];
    TianiDao::sendCd($sid);
    $m = new JAjaxMsg($act, '1');
    break;
  /**
   * Get document list
   */
  case "getDocList":    
    $cid = $_GP["id"];
    $docs = TianiDao::getDocList($cid);
    $m = new JAjaxMsg($act, jsonencode($docs));
    break;
}
if ($m) 
  echo $m->out();
?>