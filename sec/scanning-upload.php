<?
require_once "php/data/LoginSession.php";
//
LoginSession::verify_forUser()->requires($login->Role->Artifact->scan);
//
$uploaded = false;
$upException = null;
$asXml = isset($_POST['XML']);
/*
if ($_FILES) {
  $uploaded = true;
  try {
    if ($asXml)
      Scanning::uploadXml();
    else 
      Scanning::upload();
  } catch (Exception $e) {
    $upException = ExceptionRec::from($e);
  }
}
*/
?>
<body>
This is a test.
</body>
<script>
alert(window.parent.name);
window.parent.Html.UploadForm.callback(true);
</script>