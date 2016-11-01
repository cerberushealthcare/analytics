<?
require_once "inc/requireLogin.php";
require_once "inc/uiFunctions.php";
require_once 'php/data/rec/erx/ErxStatus.php';
require_once 'php/data/rec/sql/OrderEntry.php';
require_once 'php/data/rec/sql/Meds.php';
require_once 'php/data/rec/sql/Clients.php';
require_once 'php/data/rec/sql/Documentation.php';
require_once 'php/data/rec/sql/UserGroups.php';
require_once 'php/data/rec/sql/LookupAreas.php';
//
if ($myLogin->permissions->accessPatients == Permissions::ACCESS_NONE) {
  header("Location: welcome.php");
}
$pop = isset($_GET['pop']);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Strict//EN">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <?php renderHead("Facesheet") ?>
    <script language="JavaScript1.2" src="js/ui.js?<?=Version::getUrlSuffix() ?>"></script>
    <link rel='stylesheet' type='text/css' href='css/xb/_clicktate.css?<?=Version::getUrlSuffix() ?>' />
    <link rel="stylesheet" type="text/css" href="css/xb/facesheet.css?<?=Version::getUrlSuffix() ?>" />
    <link rel='stylesheet' type='text/css' href='css/xb/Pop.css?<?=Version::getUrlSuffix() ?>' />
    <script language="JavaScript1.2" src="js/pages/Facesheet.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/libs/FaceUi.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/pages/Pop.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language='JavaScript1.2' src='js/components/AnchorTab.js?<?=Version::getUrlSuffix() ?>'></script>
    <script language="JavaScript1.2" src="js/ajax.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/json.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/yui/yahoo-min.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/yui/event-min.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/yui/connection-min.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/template-pops.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/components/TableLoader.js?<?=Version::getUrlSuffix() ?>"></script>
  </head>
  <body onload="start()" <?php if ($pop) { ?>style='background-color:white'<?php } ?>>
    <div id="bodyContainer">
      <div id="curtain"></div>
      <?php include "inc/header.php" ?>
      <div id='bodyContent' class="content">
                  <div id="his-div">
                  </div>
      </div>
    </div>
  </body>
<script type="text/javascript">
C_TrackItem = <?=TrackItem::getStaticJson()?>;
C_Address = <?=Address::getStaticJson()?>;
C_Diagnosis = <?=Diagnosis::getStaticJson()?>;
C_Client = <?=Client::getStaticJson()?>;
C_DocStub = <?=DocStub::getStaticJson()?>;
C_Docs = <?=UserGroups::getDocsJsonList()?>;
C_Users = <?=UserGroups::getActiveUsersJsonList()?>;
C_Lookups = <?=LookupRec::getJsonLists(LookupAreas::get())?>;
C_Ipc = <?=Ipc::getStaticJson() ?>;
function start() {
  var query = <?=jsonencode($_GET)?>;
  page.load(query);
}
</script>      
</html>
