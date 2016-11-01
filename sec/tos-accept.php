<?php
require_once "php/data/LoginSession.php";
//
$version = MyEnv::$TOS_VERSION;
//
LoginSession::verify_forUser();
$redirect = false;
if (isset($_GET['acc'])) {
  if ($_GET['acc'] == 'Yes, I accept, continue with login') {
    $login->acceptTerms();
    $redirect = true;
  } else {
    header("Location: index.php?logout=y");
  }
}
?>
<?php $title = 'Clicktate - Terms of Service' ?>
<?php if (! $redirect) { ?>
<?php include "inc/hheader.php" ?>
<style>
P {font-size:10pt}
H3 {margin-top:1.5em}
INPUT.cb {height:40px;padding:0 10px}
</style>
<div id="body" style="background:white">
  <form method="get" action="tos-accept.php">
  <div class="content center" style="padding-bottom:30px">
    <h1>Terms of Service</h1>
    Our terms of service have been updated.<br>
    Please review and accept to continue with your login.
  </div>
  <div class="wm">
    <div class="tos">
      <?php include "tos/$version.html" ?>
    </div>
  </div>
    <div style="text-align:center;padding-bottom:30px">
      <input class='cb' type="submit" name="acc" value="Yes, I accept, continue with login" />
      <input class='cb' type="submit" name="acc" value="No, I do not accept, log out" />
    </div>
  </form>
</div>
<?php } else { ?>
<script>
window.location = 'welcome.php';
</script>
<?php } ?>
