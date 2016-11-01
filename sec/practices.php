<?
ob_start('ob_gzhandler');
require_once "php/data/LoginSession.php";
require_once 'inc/uiFunctions.php';
//
LoginSession::verify_forUser()->requires($login->super);
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Strict//EN'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
  <head>
    <?php HEAD('Practice Database', 'PracticesPage') ?>
  </head>
  <body>
    <?php BODY() ?>
      <?php TITLE('Practice Database') ?>
      <?php _TITLE() ?>
      <?php BOX() ?>
        <div id='tile'>
          <div class='spacer'>&nbsp;</div>
        </div>
      <?php _BOX() ?>
    <?php _BODY() ?>
  </body>
  <?php //CONSTANTS('Client') ?>
  <?php START() ?>  
</html>
