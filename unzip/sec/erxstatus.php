<?
require_once "php/data/LoginSession.php";
require_once 'inc/uiFunctions.php';
require_once 'php/data/rec/erx/ErxStatus.php';
//
LoginSession::verify_forUser()->requires($login->Role->erx);
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Strict//EN'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
  <head>
    <?php HEAD('ERX Status Review', 'ErxStatusPage') ?>
    <link rel='stylesheet' type='text/css' href='css/xb/facesheet.css?<?=Version::getUrlSuffix() ?>' />
    <script type='text/javascript' src='js/ui.js?<?=Version::getUrlSuffix() ?>'></script>
    <script language="JavaScript1.2" src="js/pages/NewCrop.js?<?=Version::getUrlSuffix() ?>"></script>
  </head>
  <body onload='start()'>
    <div id='bodyContainer'>
      <div id='curtain'></div>
      <?php include 'inc/header.php' ?>
      <div id='bodyContent' class='content'>
        <table class='h'>
          <tr>
            <th><h1>ERX Status Review</h1></th>
            <td></td>
          </tr>
        </table>
        <?php renderBoxStart('wide min-pad', null, null, 'box') ?>
          <div id='topbar' class='mb5'>
            <ul id='topbar-filter' class='topfilter'></ul>
          </div>
          <div id='results' class='fstab' style='height:450px'>  <!-- ResultsTile -->
            <table id='results-tbl' class='fsb'>
              <thead>
                <tr id='results-head' class='fixed head'>
                  <th style='15%'>Patient</th>
                  <th style=''>Date</th>
                  <th style='40%'>Medication</th>
                  <th style=''>Provider / Staff</th>
                <tr>
              </thead>
              <tbody id='results-tbody'>
              </tbody>
            </table>
          </div>
        <?php renderBoxEnd() ?>
      </div>
      <div id='bottom'><img src='img/brb.png' /></div>
    </div>      
    <?php include 'inc/footer.php' ?>
  </body>
<script>
function start() {
  var query = <?=jsonencode($_GET) ?>;
  var cErxStatus = <?=ErxStatus::getStaticJson() ?>; 
  ErxStatusPage.load(query, cErxStatus);
}
</script>
</html>
