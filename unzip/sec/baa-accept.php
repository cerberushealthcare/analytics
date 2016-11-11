<?php
require_once "php/data/LoginSession.php";
//
$version = MyEnv::$BAA_VERSION;
//
LoginSession::verify_forUser();
$redirect = false;
$errors = null;
if (isset($_POST['acc'])) {
  if ($_POST['acc'] == 'Yes, I accept, continue with login') {
    $errors = $login->acceptBaa($_POST);
    if ($errors == null)
      $redirect = true;
  } else {
    header("Location: index.php?logout=y");
  }
}
?>
<?php $title = 'Clicktate - Business Associate Agreement' ?>
<?php if (! $redirect) { ?>
<?php include "inc/hheader.php" ?>
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
  <form method="post" action="baa-accept.php">
  <div class="content center screen" style="padding-bottom:30px">
    <h1>Business Associate Agreement</h1>
    Please review and accept to continue with your login.
  </div>
  <div class="wm">
    <div class="tos">
      <?php include "baa/$version.html" ?>
    </div>
  </div>
    <div class="fm">
      <div class='fmb'>
        <?php require_once "inc/errors.php" ?>
        <div class='fmf'>
          <label>Initials</label><input name='init' id='init' type='text' size='4' value="<?=geta($_POST,'init') ?>"><br>
          <label>Name</label><input name='name' type='text' size='40' value="<?=geta($_POST,'name') ?>"><br>
          <label>Title</label><input name='title' type='text' size='15' value="<?=geta($_POST,'title') ?>"><br>
          <label>Medical License #</label><input name='lic' type='text' size='20' value="<?=geta($_POST,'lic') ?>"><br>
          <label>Date</label><input name='date' type='text' size='8' value="<?=geta($_POST, 'date', date("m/d/Y"))?>"><br>
        </div>
        <div class='fmd'>
        By entering your license number, you certify that the initials above constitute an electronic signature.
        Company warrants, represents, covenants, and agrees that it is duly organized, validly existing and in good standing and that this individual is authorized to make this agreement. 
        </div>
        <div class='fmc screen'>
          <input class='cb' type="submit" name="acc" value="Yes, I accept, continue with login" />
          <input class='cb' type="submit" name="acc" value="No, I do not accept, log out" />
          <a class="print" href="javascript:print()">Print this page</a>
        </div>
      </div>
    </div>
  </form>
</div>
  <?php if ($errors) { ?>
  <script>
  window.setTimeout(function() {
    window.scrollTo(0, document.documentElement.scrollHeight);
  }, 100)
  </script>
  <?php } ?>
<?php } else { ?>
<script>
window.location = 'welcome.php';
</script>
<?php } ?>
