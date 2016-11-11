<?
require_once "php/data/LoginSession.php";
require_once 'inc/uiFunctions.php';
require_once 'php/data/rec/erx/ErxPharm.php';
require_once 'php/data/rec/sql/ErxUsers.php';
//
LoginSession::verify_forUser()->requires($login->Role->erx);
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Strict//EN'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
  <head>
    <?php HEAD('ERX Pharmacy Requests', 'ErxPharmPage') ?>
    <link rel='stylesheet' type='text/css' href='css/xb/facesheet.css?$v' />
    <link rel='stylesheet' type='text/css' href='css/message.css?<?=Version::getUrlSuffix() ?>' />
    <script type='text/javascript' src='js/ui.js?<?=Version::getUrlSuffix() ?>'></script>
    <script type='text/javascript' src='js/ui/PatientSelector.js?<?=Version::getUrlSuffix() ?>'></script>
    <script language="JavaScript1.2" src="js/pages/NewCrop.js?<?=Version::getUrlSuffix() ?>"></script>
  </head>
  <body onload='start()'>
    <div id='bodyContainer'>
      <div id='curtain'></div>
      <?php include 'inc/header.php' ?>
      <div id='bodyContent' class='content'>
        <table class='h'>
          <tr>
            <th><h1>ERX Pharmacy Requests</h1></th>
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
                  <th style=''>Received</th>
                  <th style='40%'>Medication</th>
                  <th style=''>Doctor</th>
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
    <div id="pop-ep" class="pop" style='width:770px'>
      <div id="pop-ep-cap" class="pop-cap">
        <div id="pop-ep-cap-text">
          Pharmacy Request - Clicktate
        </div>
        <a href="javascript:Pop.close()" class="pop-close"></a>
      </div>
      <div class="pop-content">
        <table class='w100'>
          <tr>
            <td class='vtop' style='width:400px'>
              <div id='rx-request'>
                <div style='height:20px'>
                  <label>Received <span id='rx-rcv'></span>
                  <span>&nbsp;from&nbsp;</span></label>
                  <span id='rx-from' class='bold'></span>
                </div>
                <div style='height:90px'>
                  <h2><span id='rx-patient'></span></h2>
                  <label>DOB:</label> <span id='rx-dob' class='bold'></span>
                  <span>&nbsp;&bull;&nbsp;</span>
                  <label>Gender:</label> <span id='rx-sex' class='bold'></span>
                  <br/>
                  <label>Medication:</label> <span id='rx-med' class='bold'></span>
                  <br/>
                  <label>Qty:</label> <span id='rx-qty' class='bold'></span>
                  <span>&nbsp;&bull;&nbsp;</span>
                  <label>Sig:</label> <span id='rx-sig' class='bold'></span>
                </div>
              </div>
              <div id='matches' class='fstab' style='height:257px'>  
                <table id='matches-tbl' class='fsgr'>
                  <thead>
                    <tr id='matches-head' class='fixed head'>
                      <th style='50%'>Possible Match</th>
                      <th style=''>Birth</th>
                      <th style=''>ID</th>
                    <tr>
                  </thead>
                  <tbody id='matches-tbody'>
                  </tbody>
                </table>
              </div>
            </td>
            <td style='width:5px'></td>
            <td id='td-client2' style='width:350px'>  <!-- clientTile -->
              <div style='height:17px;text-align:center'>
                <a id='client-clear-a' href='javascript:ClientTile.clearClient()' class='action dele'>Clear</a>
              </div>
              <div id='div-client' style='height:350px;overflow-y:scroll;'>
                <div id='client-add' style='padding:100px 20px;margin:0'>
                  Possible matches for this pharmacy request are listed to the left.
                  <br/><br/>
                  Click one of these (or search for another) to view facesheet information here.
                </div>
                <div id='client-existing' style='margin:0;'>
                  <h2 id='h2-client'></h2>
                  <div style='margin-bottom:5px'>
                    <label>ID:</label> <span id='client-uid'></span><br/>
                    <label>DOB:</label> <a id='client-dob' href='javascript:ClientTile.editClient(1)'></a>
                  </div>
                  <div id='client-info'>
                    <h3>Contact</h3>
                    <div>
                      <a id='client-contact' href='javascript:ClientTile.editClient(2)'></a>
                    </div>
                    <!-- 
                    <h3>Emergency</h3>
                    <div>
                      <a id='client-emer' href='javascript:ClientTile.editClient(6)'></a>
                    </div>
                     -->
                    <h3>Allergies</h3>
                    <div id='allergies'></div>
                    <h3>Current Medications</h3>
                    <div id='meds'>
                    </div>
                    <h3>Pharmacy</h3>
                    <div>
                      <a id='client-rx' href='javascript:ClientTile.editClient(7)'></a>
                    </div>
                    <div id='client-links' style='padding-top:1em;padding-left:0'>
                    </div>
                  </div>
                </div>
              </div>
            </td>
          </tr>
        </table>
        <div class="pop-cmd">
          <a id='cmd-erx' href='javascript:page.pNewCrop()' class="cmd erx">Respond to Request...</a>
          <span>&nbsp;</span>
          <a href="javascript:Pop.close()" class="cmd none">Cancel</a>
        </div>
      </div>
    </div>
    <?php include 'inc/footer.php' ?>
  </body>
<script>
var C_Address = <?=Address::getStaticJson() ?>;
var C_Client = <?=Client::getStaticJson()?>;
function start() {
  var query = <?=jsonencode($_GET) ?>;
  var docs = <?=jsonencode(ErxUsers::getMyLpNames()) ?>;
  var cErxPharm = <?=ErxPharm::getStaticJson() ?>;
  ErxPharmPage.load(query, docs, cErxPharm);
}
</script>
</html>
