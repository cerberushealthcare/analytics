<?php
require_once 'php/data/LoginSession.php';
require_once "php/dao/LookupDao.php";
require_once "php/data/json/JAjaxMsg.php";
require_once "php/data/rec/sql/LookupAreas.php";
//
if (isset($_GET["action"])) {
  $action = $_GET["action"];
  $id = geta($_GET, 'id');
  logit("serverLookup.php?" . implode_with_keys("&", $_GET));
} else {
  $action = $_POST["action"];
  if (geta($_POST, 'obj')) {
    $_POST['obj'] = stripslashes($_POST['obj']);
    $obj = jsondecode($_POST['obj']);
    $id = $obj->id;
    $value = $obj->value;
  } else {
    $_POST["value"] = stripslashes($_POST["value"]);
    $id = $_POST["id"];
    $value = $_POST["value"];
  }
  logit("serverLookup.php (posted)");
  logit_r($_POST);
}
try {
  LoginSession::verify_forServer();
  $m = new JAjaxMsg("null", null);  // default to return nothing
  switch ($action) {
    case "saveLookupMap":
      LookupDao::saveMyTemplateMap($id, $value);
      break;
    case "deleteLookupMap":
      LookupDao::removeMyTemplateMap($id);
      break;
    case "saveDefaultTemplate":
      LookupDao::saveMyDefaultTemplateId($value);
      break;
    case "saveDefaultSendTo":
      LookupDao::saveMyDefaultSendTo($value);
      break;
    case "getReplicateOverrideFs":
      $m = new JAjaxMsg($action, LookupDao::getReplicateOverrideFs());
      break;
    case "getVacChart":
      $m = new JAjaxMsg($action, jsonencode(LookupDao::getVacChart()));
      break;
    case "saveReplicateOverrideFs":
      LookupDao::saveMyReplicateOverrideFs($value);
      break;
    case "getPrintTemplateCustoms":
      $template = LookupDao::getAllTemplateCustoms();
      $print = LookupDao::getPrintCustom();
      $customs = array('template' => $template, 'print' => $print);
      $m = new JAjaxMsg($action, jsonencode($customs));
      break;
    case "saveConsoleRx":
      LookupDao::saveMyConsoleRx($value);
      break;
    case "getClientSearchCustom":
      $m = new JAjaxMsg($action, LookupDao::getClientSearchCustomAsJson());
      break;
    case "saveClientSearchCustom":
      LookupDao::saveMyClientSearchCustom($value);
      break;
    case "deleteClientSearchCustom":
    case "resetClientSearchCustom":
      $m = new JAjaxMsg("removeMyClientSearchCustom", LookupDao::removeMyClientSearchCustom());
      break;
    case "saveSchedProfile":
      LookupDao::saveSchedProfile($id, $value);
      $m = new JAjaxMsg("schedProfile", null);
      break;
    case "deleteSchedProfile":
      LookupDao::removeSchedProfile($id);
      $m = new JAjaxMsg("schedProfile", null);
      break;
    case "saveConsoleCustom":
      LookupDao::saveMyConsoleCustom($value);
      break;
    case "saveApptTypes":
      LookupDao::saveOurApptTypes($value);
      $m = new JAjaxMsg($action, null);
      break;
    case "deleteApptTypes":
      $m = new JAjaxMsg("removeApptTypes", LookupDao::removeOurApptTypes());
      break;
    case "saveSchedStatus":
      LookupDao::saveOurSchedStatus($value);
      $m = new JAjaxMsg($action, null);
      break;
    case "deleteSchedStatus":
      $m = new JAjaxMsg("removeSchedStatus", LookupDao::removeOurSchedStatus());
      break;
    case 'saveRecips':
      LookupDao::saveRecips($obj);
      $m = new JAjaxMsg($action, null);
      break;
    default:
      $m = new JAjaxMsg("error", $action);
  }
} catch (Exception $e) {
  $m = JAjaxMsg::constructError($e);
}
echo $m->out();
?>