<?php
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
require_once 'server.php';
require_once 'php/data/LoginSession.php';
require_once 'php/data/rec/AjaxResponse.php'; 
//
$action = $_POST['action'];
$msg = null;
if (! empty($_FILES)) {
  try {
    switch ($action) {
      //
      case 'uploadScans':
        require_once 'php/data/rec/sql/Scanning.php';
        LoginSession::verify_forServer()->requires($login->Role->Artifact->scan);
        Scanning::upload();
        $msg = AjaxResponse::from($action);
        break;
      case 'uploadBatch':
        require_once 'php/data/rec/sql/Scanning.php';
        LoginSession::verify_forServer()->requires($login->Role->Artifact->scan);
        $filename = Scanning::uploadBatch();
        $msg = AjaxResponse::from($action, $filename);
        break;
      case 'uploadFace':
        require_once 'php/data/rec/sql/Clients.php';
        LoginSession::verify_forServer()->requires($login->Role->Patient->demo);
        Clients::uploadImage($_POST['cid']);
        $msg = AjaxResponse::from($action);
        break;
      case 'uploadSessionImage':
        require_once 'php/c/sessions/Sessions.php';
        LoginSession::verify_forServer()->requires($login->Role->Artifact->noteCreate);
        $upload = Sessions::uploadImage($_POST['sid']);
        $msg = AjaxResponse::from($action, $upload);
        break;
      case 'uploadLab':
        require_once 'php/data/rec/sql/HL7_Labs.php';
        LoginSession::verify_forServer()->requires($login->Role->Artifact->labs);
        HL7_Labs::import_fromUpload();
        $msg = AjaxResponse::from($action);
        break;
      case 'uploadClinical':
        require_once 'php/c/patient-import/clinical-xml/ClinicalImporter.php';
        LoginSession::verify_forServer()->requires($login->Role->Patient->create);
        $import = ClinicalImporter::import_asUpload();
        $msg = AjaxResponse::from($action, $import);
        break;
    }
  } catch (Exception $e) {
    $msg = AjaxResponse::fromException($e);
  }
  if ($msg)
    $msg = $msg->toJson();
?>
<script>
if (window.parent && window.parent.Html) {
  window.parent.Html.UploadForm.callback(<?=$msg?>);
}
</script>
<?
} else {
  if (isset($_POST['obj'])) {
    $_POST['obj'] = stripslashes($_POST['obj']);
    $obj = json_decode($_POST['obj']);
  }
  try {
    switch ($action) {
      //
      case 'updateClinical':
        require_once 'php/c/patient-import/clinical-xml/ClinicalImporter.php';
        LoginSession::verify_forServer()->requires($login->Role->Patient->create);
        $import = ClinicalImporter::import_asUpdate($obj->filename, $obj->cid);
        AjaxResponse::out($action, $import);
        break;
    }
  } catch (Exception $e) {
    AjaxResponse::exception($e);
  }        
}
