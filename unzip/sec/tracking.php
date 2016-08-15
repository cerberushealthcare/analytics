<?
ob_start('ob_gzhandler');
require_once "php/data/LoginSession.php";
require_once 'inc/uiFunctions.php';
require_once 'php/data/rec/sql/OrderEntry.php';
require_once 'php/data/rec/sql/LookupAreas.php';
require_once 'php/c/template-entry/TemplateEntry.php';
//
LoginSession::verify_forUser()->requires($login->Role->Patient->track);
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Strict//EN'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
  <head>
    <? HEAD('Tracking', 'TrackingPage') ?>
    <? HEAD_OrderEntry() ?>
    <? HEAD_DocPreview() ?>
    <script language='JavaScript1.2' src='js/ui.js?<?=Version::getUrlSuffix() ?>'></script>
    <link rel='stylesheet' type='text/css' href='css/xb/_clicktate.css?<?=Version::getUrlSuffix() ?>' />
    <link rel="stylesheet" type="text/css" href="css/xb/facesheet.css?<?=Version::getUrlSuffix() ?>" />
    <link rel='stylesheet' type='text/css' href='css/xb/Pop.css?<?=Version::getUrlSuffix() ?>' />
    <link rel='stylesheet' type='text/css' href='css/xb/EntryForm.css?<?=Version::getUrlSuffix() ?>' />
    <link rel='stylesheet' type='text/css' href='css/xb/template-pops.css?<?=Version::getUrlSuffix() ?>' />
    <link rel='stylesheet' type='text/css' href='css/data-tables.css?<?=Version::getUrlSuffix() ?>' />
    <link rel='stylesheet' type='text/css' href='css/TabBar.css?<?=Version::getUrlSuffix() ?>' />
    <link rel='stylesheet' type='text/css' href='css/TableLoader.css?<?=Version::getUrlSuffix() ?>' />
    <link rel='stylesheet' type='text/css' href='css/TemplateUi.css?<?=Version::getUrlSuffix() ?>' />
    <link rel="stylesheet" type="text/css" href="css/xb/_hover.css?<?=Version::getUrlSuffix() ?>" media="screen" />
    <script language='JavaScript1.2' src='js/pages/TrackingPage.js?<?=Version::getUrlSuffix() ?>'></script>
    <script language='JavaScript1.2' src='js/pages/Pop.js?<?=Version::getUrlSuffix() ?>'></script>
    <script language='JavaScript1.2' src='js/libs/ClientUi.js?<?=Version::getUrlSuffix() ?>'></script>
    <script language='JavaScript1.2' src='js/libs/DocUi.js?<?=Version::getUrlSuffix() ?>'></script>
    <script language='JavaScript1.2' src='js/libs/DateUi.js?<?=Version::getUrlSuffix() ?>'></script>
    <script language='JavaScript1.2' src='js/yui/yahoo-min.js?<?=Version::getUrlSuffix() ?>'></script>
    <script language='JavaScript1.2' src='js/yui/event-min.js?<?=Version::getUrlSuffix() ?>'></script>
    <script language='JavaScript1.2' src='js/yui/connection-min.js?<?=Version::getUrlSuffix() ?>'></script>
    <script language='JavaScript1.2' src='js/components/TableLoader.js?<?=Version::getUrlSuffix() ?>'></script>
    <script language='JavaScript1.2' src='js/components/TabBar.js?<?=Version::getUrlSuffix() ?>'></script>
    <script language='JavaScript1.2' src='js/components/CmdBar.js?<?=Version::getUrlSuffix() ?>'></script>
    <script language='JavaScript1.2' src='js/components/EntryForm.js?<?=Version::getUrlSuffix() ?>'></script>
    <script language='JavaScript1.2' src='js/components/DateInput.js?<?=Version::getUrlSuffix() ?>'></script>
  </head>
  <body onload='start()'>
    <div id='bodyContainer'>
      <div id='curtain'></div>
      <? include 'inc/header.php' ?>
      <div id='bodyContent' class='content'>
        <table class='h'>
          <tr>
            <th><h1>Tracking Sheet</h1></th>
            <td></td>
          </tr>
        </table>
        <? renderBoxStart('wide min-pad', null, null, 'box') ?>
          <div id='tracking-table-tile'>
            &nbsp; <!-- TrackingTable -->
          </div>
        <? renderBoxEnd() ?>
      </div>
      <div id='bottom'><img src='img/brb.png' /></div>
    </div>      
    <? include 'inc/footer.php' ?>
  </body>
  <? CONSTANTS('Tracking') ?>
<script type='text/javascript'>
C_Lookups = <?=LookupRec::getJsonLists(LookupAreas::get())?>;
function start() {
  var query = <?=jsonencode($_GET) ?>;
  TrackingPage.load(query);
}
</script>      
</html>
