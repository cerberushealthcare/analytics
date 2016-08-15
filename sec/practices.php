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
    <? HEAD('Practice Database', 'PracticesPage') ?>
  </head>
  <body>
    <? BODY() ?>
      <? TITLE('Practice Database') ?>
      <? _TITLE() ?>
      <? BOX() ?>
        <div id='tile'>
          <div class='spacer'>&nbsp;</div>
        </div>
      <? _BOX() ?>
    <? _BODY() ?>
  </body>
  <? //CONSTANTS('Client') ?>
  <? START() ?>  
</html>
