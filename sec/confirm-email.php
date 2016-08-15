<?php
require_once "php/data/LoginSession.php";
//
LoginSession::verify_forUser();
$redirect = false;
$errors = null;
if (isset($_POST['acc'])) {
  $errors = $login->confirmEmail($_POST);
  if ($errors == null)
    $redirect = true;
} else {
  $_POST['email'] = $login->User->email;
}
?>
<? $title = 'Clicktate - Confirm Email' ?>
<? if (! $redirect) { ?>
<? include "inc/hheader.php" ?>
<style>
BODY {font-family:Arial}
TABLE {font-size:10pt}
P {font-size:10pt}
H3 {margin-top:1.5em}
INPUT.cb {height:40px;padding:0 10px}
UL {font-family:Arial;font-size:10pt;margin:0.8em 0}
OL {font-family:Arial;font-size:10pt}
LI {margin:0.5em 0;
  font-family:Arial;
  font-size:10pt;
  line-height:13pt;
}
B {font-size:11pt}
LI B {font-family:Arial;font-size:10pt;color:black}
P B {font-size:10pt}
DIV.fm {
padding-bottom:30px;
}
DIV.fmb {
width:600px;
border:4px solid #008C7B;
margin:0 auto;
padding:20px;
background-color:#D2E3E0;
}
DIV.fmc {
padding-top:10px;
text-align:center;
}
DIV.fmd {
padding-top:10px;
font-style:italic;
font-size:9pt;
}
DIV.fmf LABEL {
width:140px;
text-align:right;
display:inline-block;
}
A.print {
margin-left:20px;
display:inline-block;
}
@media print {
  DIV.screen {
    display:none;
  }
}
</style>
<div id="body" style="background:white">
  <form method="post" action="confirm-email.php">
  <div class="content center screen" style="padding-bottom:30px">
    <h1>Confirm Your Email</h1>
    It is important that we have a correct email address on file.
    Please review and make any corrections to continue with your login.
  </div>
  <div class="wm">
    <div class="fm">
    <br>
      <div class='fmb'>
        <?php require_once "inc/errors.php" ?>
        <div class='fmf'>
          <label>Email Address&nbsp;</label><input name='email' id='email' type='text' size='40' value="<?=geta($_POST,'email') ?>"><br>
        </div>
        <div class='fmd'>
        </div>
        <div class='fmc screen'>
          <input class='cb' type="submit" name="acc" value="Save and continue with login" />
        </div>
      </div>
    </div>
  </div>
  </form>
</div>
  <? if ($errors) { ?>
  <script>
  window.setTimeout(function() {
    window.scrollTo(0, document.documentElement.scrollHeight);
  }, 100)
  </script>
  <? } ?>
<? $focus = 'email'; ?>
<?php require_once "inc/focus.php" ?>
<? } else { ?>
<script>
window.location = 'welcome.php';
</script>
<? } ?>
