<?
require_once "php/data/LoginSession.php";
require_once 'inc/uiFunctions.php';
//
LoginSession::verify_forUser()->requires($login->Role->Report->any());
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Strict//EN'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
  <head>
    <?php HEAD('Graph Test', 'TestGraphPage') ?>
    <?php HEAD_UI('Graphing') ?>
    <style>
DIV.CanvasWrapper {
  border:1px solid #c0c0c0;
  background-color:white;
}
    </style>
  </head>
  <body>
    <?php BODY() ?>
      <h1>Graph Test</h1>
      <?php BOX() ?>
        <div id='tile'>
          <div class='spacer'>&nbsp;</div>
        </div>
      <?php _BOX() ?>
    <?php _BODY() ?>
  </body>
  <?php // CONSTANTS('X') ?>
  <?php START() ?>
</html>
