<?
require_once 'php/data/rec/sql/Documentation.php';
require_once "php/data/LoginSession.php";
require_once 'inc/uiFunctions.php';
require_once 'php/data/rec/sql/Scanning.php';
require_once 'php/data/rec/sql/Messaging.php';
require_once 'php/data/rec/sql/LookupAreas.php';
require_once 'php/data/rec/sql/_AddressRec.php';
require_once 'php/data/rec/sql/UserGroups.php';
require_once 'php/data/rec/ExceptionRec.php';
//
LoginSession::verify_forUser()->requires($login->Role->Artifact->scan);
//
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Strict//EN'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
  <head>
    <?php HEAD('Scanning') ?>
    <?php HEAD_Scanning() ?>
    <link rel='stylesheet' type='text/css' href='css/scanning.css?<?=Version::getUrlSuffix() ?>' />
    <script language="JavaScript1.2" src="js/pages/ScanningPage.js?4<?=Version::getUrlSuffix() ?>"></script>
  </head>
  <body>
    <?php BODY() ?>
      <table class='h'>
        <tr>
          <th><h1 id='h1'>Scanning Center</h1></th>
          <td></td>
        </tr>
      </table>
      <?php renderBoxStart('wide min-pad', null, null, 'box') ?>
        <div id='scan-indexing'>
          <div class='spacer'>&nbsp;</div>
        </div>
      <?php renderBoxEnd() ?>
      <div id="pop-upload-xml" class="pop">
        <div class="pop-cap">
          <div>
            Clicktate - Upload Electronic Clinical Document
          </div>
          <a href="javascript:Pop.close()" class="pop-close"></a>
        </div>
        <div class="pop-content">
          <div class="pop-frame">
            <div class="pop-frame-content">
              <form id="frm-upload-xml" enctype="multipart/form-data" action="scanning.php" method="post">
                <input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
                <input type="hidden" name="XML" value="1" />
                <input class='file' name="scanfile[]" type="file" /><br>
                <div style='padding-top:4px;padding-left:1em'>
                  <?php renderLabelCheck('lc_enc', 'Encrypted?', false, null, 'showDecrypt()') ?>
                  <span id='dec' style='visibility:hidden;margin-left:1em'>
                    <label style='color:black'>Password</label>&nbsp;<input id='pw' name='pw' type='password' size='30' style='vertical-align:bottom'/>
                  </span>
                </div>
              </form>
            </div>
          </div>
          <div class="pop-cmd">
            <a href="javascript:uploadXml()" onclick="" class="cmd save">Upload Now</a>&nbsp;
            <a href="javascript:Pop.close()" class="cmd none">Cancel</a>
          </div>
        </div>
      </div>
    <?php _BODY() ?>
  </body>
  <?php CONSTANTS('Scanning') ?>
<script type='text/javascript'>
function uploadXml() {
  $('frm-upload-xml').submit();
}
function showDecrypt() {
  var checked = _$('lc_enc').checked;
  _$('dec').visibleIf(checked); 
  if (checked)
    _$('pw').setFocus();
}
ScanningPage.start();
C_Recips = <?=Messaging::getMyRecipsAsJson()?>;
</script>      
</html>