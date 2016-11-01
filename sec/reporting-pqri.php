<?
require_once "php/data/LoginSession.php";
require_once 'inc/uiFunctions.php';
//
LoginSession::verify_forUser()->requires($login->Role->Report->pqri);
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Strict//EN'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
  <head>
    <?php HEAD('Clinical Quality Measures', 'PqriPage') ?>
    <?php HEAD_UserSelector() ?>
    <script language="JavaScript1.2" src="js/_ui/CcdDownloader.js"></script>
    <style>
DIV.PageTile {
  padding:10px;
}
DIV.PageTile TD {
  padding:4px;
  font-size:1.2em;
}
A.rep {
  width:90px;
  font-size:1.3em;
  font-weight:bold;
  height:20px;
  background-image:url(img/icons/graph.png);
  background-repeat:no-repeat;
  background-position:6px 6px;
  padding-left:30px;
  padding-top:2px;
  padding-bottom:6px;
}    
    </style>
  </head>
  <body onload='start()'>
    <?php BODY() ?>
      <h1>Clinical Quality Measures</h1>
      <?php renderBoxStart('wide min-pad', null, null, 'box') ?>
        <div id='tile'>
        </div>
      <?php renderBoxEnd() ?>
    <?php _BODY() ?>
  </body>
  <?php CONSTANTS('Doctors', 'Users') ?>
<script type='text/javascript'>
function start() {
  PqriPage.load();
}
</script>
</html>
