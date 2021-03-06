<?
require_once "php/data/LoginSession.php";
require_once 'inc/uiFunctions.php';
require_once 'php/data/rec/sql/Messaging.php';
require_once 'php/data/rec/sql/PortalUsers.php';
require_once 'php/c/template-entry/TemplateEntry.php';
//
LoginSession::verify_forUser()->requires($login->Role->Message->general);
$id = geta($_GET, 'id');
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Strict//EN'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
  <head>
    <?php HEAD('Messaging Center', 'MessagePage') ?>
    <?php HEAD_Messaging() ?>
    <link rel='stylesheet' type='text/css' href='css/message.css?<?=Version::getUrlSuffix() ?>' />
    <script language="JavaScript1.2" src="js/pages/NewCrop.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language='JavaScript1.2' src='js/_ui/PortalUserEntry.js?<?=Version::getUrlSuffix() ?>'></script>
  </head>
  <body onload='start()'>
    <?php BODY() ?>
      <table border='0' cellpadding='0' cellspacing='0' style='width:100%'>
        <tr>
          <td>
            <h1 id='h1'>Message Center</h1>
          </td>
          <td style='text-align:right; padding-right:5px; vertical-align:bottom;'>
            <a href='messages.php' class='icon big back'>Back to Inbox</a>
          </td>
        </tr>
      </table>
      <?php renderBoxStart('wide min-pad', null, 'box') ?>
        <div id='message-working'>&nbsp;</div>  <!-- workingTile -->
        <div id='thread' style='visibility:hidden'>  <!-- pageTile -->
          <table id='thread-cols'>
            <tr>
              <td id='td-posts' style='width:80%;padding:0 7px 0 0'>
                <div id='thread-head'>
                  <table style='width:100%'>
                    <tr>
                      <td>
                        <h2 id='h2' class='thread'></h2>
                      </td>
                      <td style='text-align:right'>
                      </td>
                    </tr>
                  </table>
                </div>
                <div id='thread-head-tag'></div>
                <div id='new-thread'>  <!-- newThreadTile -->
                  <ul id='new-thread-ul'>
                  </ul>
                </div>
                <div id='posts'>  <!-- postsTile -->
                </div>
                <div id='new-post'>  <!-- newPostTile -->
                  <div id='templates'>
                    <div id='tchooser'>
                      <label>Choose a Template:</label>
                      <span id='tchooser-atabs'>
                      </span>
                    </div>
                    <div id='tuis'>
                    </div>
                  </div>
                  <div id='post-freetext' class='post-entry' style='margin-bottom:0'>
                    <div class='pcap'>
                      <table><tr><th>Free Text Area</th></tr></table>
                    </div>
                    <div class='pfree'>
                      <textarea id='post-free' onkeydown='taAutosize(this)' onkeyup='taAutosize(this)' onkeypress='taAutosize(this)'></textarea>
                    </div>
                  </div>
                  <div id='post-attach'></div>
                  <table class='w100' style='margin-top:10px'>
                    <tr>
                      <td style='width:60%;padding:10px;border:1px solid #c0c0c0'>
                        <div id='send-tile'></div>
                      </td>
                      <td width=5 nowrap='nowrap'>&nbsp;</td>
                      <td style='width:40%;padding:10px;border:1px solid #c0c0c0; vertical-align:bottom'>
                        <div class='pop-cmd'>
                          <a id='cmd-complete' href='javascript:' onclick='page.pComplete()' class='cmd lock'>Save and Mark Complete</a>
                        </div>
                      </td>
                    </tr>
                  </table>
                  <div class='pop-cmd' style='display:none'>
                    <a id='cmd-send' href='javascript:' onclick='page.pSend()' class='cmd send'>Send Message</a><a id='cmd-save-complete' href='javascript:' onclick='page.pComplete()' class='cmd lock'>Save a Complete</a>
                    <span>&nbsp;</span>
                    <a href='javascript:' onclick='page.pCancel()' class='cmd none'>Cancel</a>
                  </div>
                </div>
                <div id='send-post' style='display:none'>  <!-- sendingTile -->
                </div>
              </td>
              <td id='td-client' style='width:250px'>  <!-- clientTile -->
                <div id='client-clear'>
                  <a id='client-clear-a' class='x' href='javascript:page.pClearClient()'>X</a>
                  <a id='client-edit-a' class='pencil' href='javascript:page.pEditClient()'>Edit</a>
                </div>
                <div id='client-add'>
                  <ul id='client-add-ul'>
                  </ul>
                </div>
                <div id='client-existing'>
                  <h2 id='h2-client'></h2>
                  <div style='margin-bottom:5px'>
                    <label>ID:</label> <span id='client-uid'></span><br/>
                    <label>DOB:</label> <a id='client-dob' href='javascript:page.pEditClient(1)'></a>
                  </div>
                  <div id='client-info'>
                    <h3>Contact</h3>
                    <div>
                      <a id='client-contact' href='javascript:page.pEditClient(2)'></a>
                    </div>
                    <h3>Emergency</h3>
                    <div>
                      <a id='client-emer' href='javascript:page.pEditClient(6)'></a>
                    </div>
                    <h3>Allergies</h3>
                    <div id='allergies'></div>
                    <h3>Current Medications</h3>
                    <div id='meds'>
                    </div>
                    <h3>Pharmacy</h3>
                    <div>
                      <a id='client-rx' href='javascript:page.pEditClient(7)'></a>
                    </div>
                    <h3 id='h3-vitals'>Vitals</h3>
                    <div id='vitals'></div>
                  </div>
                  <div id='client-links' style='padding-top:1em'>
                  </div>
                </div>
              </td>
            </tr>
          </table>
        </div>
      <?php renderBoxEnd() ?>
    <?php _BODY() ?>
  </body>
<?php CONSTANTS('Templates', 'Users') ?>
<?php JsonConstants::writeGlobals('Address','Client','PortalUser','DocStub','ScanIndex','MsgInbox','MsgPost','MsgThread') ?>
<script>
C_Docs = <?=UserGroups::getDocsJsonList()?>;
Lu_Recips = <?=jsonencode(LookupDao::getRecips())?>;
function start() {
  var query = <?=jsonencode($_GET) ?>;
  var dao = <?=Messaging::getListsAsJson() ?>;
  page.load(query, dao);
}
</script>
</html>