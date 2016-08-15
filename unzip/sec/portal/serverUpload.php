<?php
require_once 'server.php';
require_once 'php/data/rec/sql/PortalUsers_Session.php';
require_once 'php/data/rec/group-folder/GroupFolder_Portal.php'; 
//
$msg = null;
$sess = PortalSession::get();
try {
  switch ($action) {
    case 'upload':
      $folder = GroupFolder_Portal::open();
      $filename = $folder->upload($sess->portalUserId);
      $msg = new JAjaxMsg($action, $filename);
      break;
  }
} catch (Exception $e) {
	$ex = ExceptionRec::from($e);
  $msg = new JAjaxMsg('error', $ex);
}
?>
<script>
if (window.parent && window.parent.Html)
  window.parent.Html.UploadForm.callback(<?=jsonencode($msg)?>);
</script>
