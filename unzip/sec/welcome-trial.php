<?
require_once "php/data/LoginSession.php";
//
LoginSession::verify_forUser();
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Strict//EN'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
  <head>
    <? HEAD('Trial Setup') ?>
    <style>
DIV#left {
  font-family:Arial;
  font-weight:bold;
  font-size:18pt;
  color:#059082; 
}
    </style>
  </head>
  <body>
    <? BODY() ?>
      <div style='padding:20px 40px 40px 40px'>
      <? renderBoxStart('wide min-pad', null, null, 'box') ?>
        <div class='cj' style='margin:4em 0'>
          <h2>
            Please wait while we complete your trial setup...
          </h2>
          <div id='working' style='visibility:hidden;border:4px solid #1A6A62;background-color:white;padding:4px 4px 1px 4px;width:210px;margin:20px auto 0 auto'>
            <img src='img\icons\working-bar.gif' height='25px' width='200px' />
          </div>
          <div id='left' style='visibility:hidden'>&nbsp;</div>
        </div>
      <? renderBoxEnd() ?>
      </div>
    <? _BODY() ?>
  </body>
</html>
<script>
Header.hideNavMenu();
pause(1, function() {
  _$('working').visible();
  _$('left').visible();
  setup();
})
function setup() {
  Ajax.get('Trial', 'setup', null, function(left) {
    if (left > 0) {
      _$('left').setText(left);
      setup();
    } else {
      _$('left').setText('Finished!');
      pause(1, function() {
        Page.go('welcome.php');
      })
    }
  })
}
</script>