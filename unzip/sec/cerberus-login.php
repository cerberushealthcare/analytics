<?
require_once "php/data/LoginSession.php";
require_once 'inc/uiFunctions.php';
//
LoginSession::verify_forUser()->requires($login->Role->Report->any());
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Strict//EN'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
  <head>
    <? HEAD('Cerberus Login') ?>
    <? // HEAD_X() ?>
<style>
DIV#spacer {
  line-height:330px;
  text-align:center;
  font-size:12pt;
  font-weight:bold;
  height:450px;
}
</style>    
  </head>
  <body>
    <? BODY() ?>
      <? BOX() ?>
        <div id='tile'>
          <div id='spacer' class='spacer'>
            Logging into Papyrus
          </div>
        </div>
      <? _BOX() ?>
    <? _BODY() ?>
  </body>
<script>
async(function() {
  CerberusLoginPage.start();
})
CerberusLoginPage = page = {
  //
  start:function(query) {
    _$('tile').working(function() {
      Ajax.get('Cerberus', 'login', null, function() {
        window.location.href = 'welcome.php';
      })
    })
  } 
}
</script>  
</html>
