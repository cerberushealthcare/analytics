<?php
ob_start('ob_gzhandler');
require_once "php/data/LoginSession.php";
require_once 'inc/uiFunctions.php';
LoginSession::verify_forUser()->requires($login->Role->Patient->any());
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Strict//EN'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
  <head>
    <?php HEAD('Patient Database', 'PatientsPage'); ?>
    <style>
TABLE.tpage {
  width:100%;
}
TABLE.tpage TD {
  vertical-align:top;
} 
TABLE.tpage TD.tdfilter {
  width:150px;
} 
    </style>
  </head>
  <body>
    <?php BODY() ?>
      <?php TITLE('Patient Database') ?>
        <a href="javascript:" onclick="PatientSelector.pop();" class="icon search">Search for patient</a>
      <?php _TITLE() ?>
      <?php BOX() ?>
        <div id='test'></div>
        <table class='tpage'>
          <tr>
            <td class='tdfilter'>
              <div id='filterbox' class='filter'></div>
            </td>
            <td>
              <div id='tile'>
                <div class='spacer'>&nbsp;</div>
              </div>
            </td>
          </tr>
        </table>
      <?php _BOX() ?>
    <?php _BODY() ?>
  </body>
  <?php CONSTANTS('Client') ?>
  <?php START() ?>  
</html>

