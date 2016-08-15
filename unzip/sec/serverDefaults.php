<?php
require_once "php/dao/LoginDao.php";
require_once "php/dao/SessionDao.php";

if (LoginDao::authenticateSession() < 0) {
  exit;
}
$clientId = $_POST["cid"];
$templateId = $_POST["tid"];
$defaults = json_decode(stripslashes($_POST["a"]));
SessionDao::updateClientDefaults($clientId, $templateId, $defaults);
// No response from this server by design	
?>