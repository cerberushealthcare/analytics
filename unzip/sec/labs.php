<?
require_once "php/data/LoginSession.php";
require_once 'inc/uiFunctions.php';
//
LoginSession::verify_forUser()->requires($login->Role->Artifact->labs);
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Strict//EN'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
  <head>
    <?php HEAD('Labs', 'LabsPage') ?>
    <?php HEAD_UI('Entry') ?>
    <?php HEAD_UI_CSSJS('LabMessagePop') ?>
    <?php HEAD_REC('Labs') ?>
    <link rel='stylesheet' type='text/css' href='css/labs.css?<?=Version::getUrlSuffix() ?>' />
  </head>
  <body>
    <?php BODY() ?>
      <table class='h'>
        <tr>
          <th>
            <h1 id='h1'>Labs</h1>
          </th>
          <td>
          </td>
        </tr>
      </table>
      <?php renderBoxStart('wide min-pad', null, null, 'box') ?>
        <div id='tile'>
          <div class='spacer'>&nbsp;</div>
        </div>
        <div id='info' style='display:none'>
          <div class='how'>
            How do I receive labs electronically?
          </div>
          <p>
            <b>Contact your lab representative!</b>
          </p>
          <p>
          We can interface with any lab capable of sending results electronically.
          Many providers are already enjoying the benefits of receiving lab results electronically, eliminating the need for tedious, error-prone manual entry.
          </p>
          <p>
          Ask your lab representative to contact us at 1-888-825-4258 or <a href='mailto:info@clicktatemail.info'>info@clicktatemail.info</a> 
          about interfacing with Clicktate and we'll get started on it right away!
          </p> 
        </div>
      <?php renderBoxEnd() ?>
    <?php _BODY() ?>
  </body>
  <?php CONSTANTS('Proc', 'OrderEntry', 'Doctors', 'Client') ?>
  <?php START() ?>
</html>
