<?
require_once "php/data/LoginSession.php";
require_once 'inc/uiFunctions.php';
require_once 'php/data/rec/sql/IProcCodes.php';
//
LoginSession::verify_forUser()->requires($login->admin);
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Strict//EN'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
  <head>
    <?php renderHead('Drug Classification') ?>
    <link rel='stylesheet' type='text/css' href='css/xb/_clicktate.css?<?=Version::getUrlSuffix() ?>' />
    <link rel='stylesheet' type='text/css' href='css/xb/Pop.css?<?=Version::getUrlSuffix() ?>' />
    <link rel='stylesheet' type='text/css' href='css/xb/facesheet.css?<?=Version::getUrlSuffix() ?>' />
    <link rel='stylesheet' type='text/css' href='css/xb/EntryForm.css?<?=Version::getUrlSuffix() ?>' />
    <link rel='stylesheet' type='text/css' href='css/xb/template-pops.css?<?=Version::getUrlSuffix() ?>' />
    <link rel='stylesheet' type='text/css' href='css/data-tables.css?<?=Version::getUrlSuffix() ?>' />
    <link rel='stylesheet' type='text/css' href='css/TabBar.css?<?=Version::getUrlSuffix() ?>' />
    <link rel='stylesheet' type='text/css' href='css/TableLoader.css?<?=Version::getUrlSuffix() ?>' />
    <link rel='stylesheet' type='text/css' href='css/TemplateUi.css?<?=Version::getUrlSuffix() ?>' />
    <link rel="stylesheet" type="text/css" href="css/xb/_hover.css?<?=Version::getUrlSuffix() ?>" media="screen" />
    <script language='JavaScript1.2' src='js/ui.js?<?=Version::getUrlSuffix() ?>'></script>
    <script language='JavaScript1.2' src='js/pages/AdminDrugClassPage.js?<?=Version::getUrlSuffix() ?>'></script>
    <script language='JavaScript1.2' src='js/pages/Pop.js?<?=Version::getUrlSuffix() ?>'></script>
    <script language='JavaScript1.2' src='js/libs/DateUi.js?<?=Version::getUrlSuffix() ?>'></script>
    <script language='JavaScript1.2' src='js/yui/yahoo-min.js?<?=Version::getUrlSuffix() ?>'></script>
    <script language='JavaScript1.2' src='js/yui/event-min.js?<?=Version::getUrlSuffix() ?>'></script>
    <script language='JavaScript1.2' src='js/yui/connection-min.js?<?=Version::getUrlSuffix() ?>'></script>
    <script language='JavaScript1.2' src='js/components/TableLoader.js?<?=Version::getUrlSuffix() ?>'></script>
    <script language='JavaScript1.2' src='js/components/TabBar.js?<?=Version::getUrlSuffix() ?>'></script>
    <script language='JavaScript1.2' src='js/components/CmdBar.js?<?=Version::getUrlSuffix() ?>'></script>
    <script language='JavaScript1.2' src='js/components/EntryForm.js?<?=Version::getUrlSuffix() ?>'></script>
    <script language='JavaScript1.2' src='js/components/DateInput.js?<?=Version::getUrlSuffix() ?>'></script>
    <style>
TD.c2 A {
  color:green;
}
TD.c2 {
  padding-left:30px !important;
}
TD.c3 {
  padding-left:55px !important;
}
TD.c3 A {
  color:red;
}
A.new-folder {
  color:#606060 !important;
}
    </style>
  </head>
  <body onload="start()"> 
    <div id='bodyContainer'>
      <div id='curtain'></div>
      <?php include 'inc/header.php' ?>
      <div id='bodyContent' class='content'>
        <table class='h'>
          <tr>
            <th><h1>Drug Classification</h1></th>
            <td></td>
          </tr>
        </table>
        <?php renderBoxStart('wide min-pad', null, null, 'box') ?>
          <div id='tile'>
            <div class='spacer'>&nbsp;</div>
          </div>
        <?php renderBoxEnd() ?>
      </div>
      <div id='bottom'><img src='img/brb.png' /></div>
    </div>      
    <?php include 'inc/footer.php' ?>
  </body>
<script type='text/javascript'>
function start() {
  var query = <?=jsonencode($_GET) ?>;
  AdminDrugClassPage.load(query);
}
</script>      
</html>
