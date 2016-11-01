<?
ob_start('ob_gzhandler');
require_once "php/data/LoginSession.php";
require_once 'php/data/rec/sql/LookupAreas.php';
require_once 'php/c/template-entry/TemplateEntry.php';
require_once 'inc/uiFunctions.php';
//
LoginSession::verify_forUser()->requires($login->Role->Artifact->markReview);
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Strict//EN'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
  <head>
    <?php HEAD('Item Review', 'ReviewPage') ?>
    <?php HEAD_UI('DocHistory', 'Scanning') ?>
    <?php HEAD_PortalUserEntry() ?>
 </head>
  <body>
    <?php BODY() ?>
      <h1>Item Review</h1>
      <?php renderBoxStart('wide min-pad', null, null, 'box') ?>
        <div id='tile'>
          <div class='spacer'>&nbsp;</div>
        </div>
      <?php renderBoxEnd() ?>
    <?php _BODY() ?>
  </body>
  <?php CONSTANTS('DocHistory', 'Scanning', 'Templates') ?>
  <?php START() ?>
</html>
