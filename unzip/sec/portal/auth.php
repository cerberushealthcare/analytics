<?php
require_once "inc/tags.php";
set_include_path('../');
require_once 'php/data/rec/sql/PortalUsers_Session.php'; 
?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <?php HEAD('Patient Portal', 'AuthPage') ?>
    <?php HEAD_DATA('PortalSession') ?>
    <link rel="stylesheet" type="text/css" href="css/login.css" />
  </head>
  <body onload='start()'>
    <div id='loginc'>
      <div id='logina'>
        <h1>Patient Portal</h1>
        <p>
          To continue, please answer the following security questions.
          If you have any problems blah blah
          If you have any problems blah blah
        </p>
        <?php BOX(null, 'tile') ?>
          <div>
            <label>What is your name?</label>
            <input type='text' size='20' id='id' name='id' />
          </div>
        <?php _BOX() ?>
      </div>
    </div>
  </body>
<script>
<?php JsonConstants::writeGlobals('PortalUser') ?>
function start() {
  //AuthPage.load();
}
</script>
</html>
