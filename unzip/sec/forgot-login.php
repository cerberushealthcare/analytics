<?php 
require_once "inc/uiFunctions.php";
require_once "php/data/rec/sql/UserLogins.php";
//
$locked = isset($_GET['locked']);
$email = isset($_POST['email']) ? $_POST['email'] : null;
if ($email) {
  try {
    UserLogins::requestPasswordReset($email);
    $msg = "Email has been sent.<br>Please check your inbox.";
  } catch (LoginNotFoundException $e) {
    $errors = array('The email address you entered is not registered.');
    sleep(2);
  }
}
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Strict//EN'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
  <head>
    <title>
      Clicktate - Forgot Login
    </title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <meta http-equiv="Content-Script-Type" content="text/javascript" />
    <meta http-equiv="Content-Language" content="en-us" />
    <link rel="stylesheet" type="text/css" href="css/home.css" media="screen" />
    <script language="JavaScript1.2" src="js/_lcd_core.js?4"></script>
    <script language="JavaScript1.2" src="js/_lcd_html.js?4"></script>
    <style>
DIV#message-div {
  padding-top:45px;
  height:
}
    </style>
  </head>
  <body style='background-color:#000000;'>
  <div id="bodyContainer"> 
    <div id="curtain" class="cdark"></div>
    <div id="head">
      <div class="content">
        <div id="nav">
          <table cellpadding="0" cellspacing="0">
            <tr>
              <td>
              </td>
              <td style="text-align:right">
              </td>
            </tr>
          </table>
        </div>
      </div>
    </div>
    <div id="body">
      <div class="content center">
        <h1 style="margin-top:2em">Forgot Login</h1>
        <div class="subh">
          Enter your email address below.<br/>
          We will send you instructions for resetting your password.
        </div>
        <table border='0' cellpadding='0' cellspacing'0'>
          <tr>
            <td class='wm'>
              <? renderBoxStart() ?>
                <div id="login" style="height:160px; width:310px">
                  <?php require_once "inc/errors.php" ?>
                  <? if (! isset($msg)) { ?>
                  <form id="frm" method="post" action="forgot-login.php">
                    <div class="l" style="margin-top:10px">
                      <table border="0" cellpadding="0" cellspacing="0">
                        <tr>
                          <td>
                            <label>Email</label><br/>
                            <input id="email" type="text" size="35" name="email" /></td>
                          </td>
                        </tr>
                      </table>
                    </div>
                    <div id="trial" style="padding-bottom:10px">
                      <a id='asub' href="." class="tour">Submit ></a>
                    </div>
                  </form>
                  <? } ?>
                </div>
              <? renderBoxEnd() ?>
            </td>
          </tr>
        </table>
        <div id="forgot">
          <? if (! $locked) { ?>
            <? if (! isset($msg)) { ?>
              Never mind, <a class='gb' href="index.php">go back to login page</a>
            <? } else { ?>
              <a class='gb' href="index.php">Go back to login page</a>
            <? } ?>
          <? } ?>
        </div>
      </div>
    </div>
    <div id="foot">
      <div class="content">
        <div class="foot-text">
          &copy; 2012 LCD Solutions, Inc.<br/>
          All rights reserved.
        </div>
        <div>
          <a href="../privacy.php">Privacy Policy</a>
          <span>|</span>
          <a href="../terms.php">Terms of Service</a>
          <span>|</span>
          <a style="background:url(img/pdf.gif) no-repeat; padding-left:20px" href="../Clicktate-BAA-1.0.pdf">Business Associate Agreement</a>
          <span>|</span>
          <a href="../contact-us.php">Contact Us</a>
        </div>
      </div>
    </div>
  </body>
</html>
<script type='text/javascript'>
Html.Input.$('email').setFocus();
var f = _$('frm');  
var a = Html.AnchorSubmit.$('asub');
a.attach(f);
</script>