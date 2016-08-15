<?php 
require_once "inc/uiFunctions.php";
require_once "php/data/rec/sql/UserLogins.php";
//
$hash = isset($_GET['h']) ? $_GET['h'] : null;
if ($hash) {
  try {
    UserLogins::completePasswordReset($hash);
    header("Location: index.php?cp=1");
  } catch (LoginNotFoundException $e) {
    // continue rendering 'expired request'page
  }
}
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Strict//EN'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
  <head>
    <title>
      Clicktate
    </title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <meta http-equiv="Content-Script-Type" content="text/javascript" />
    <meta http-equiv="Content-Language" content="en-us" />
    <link rel="stylesheet" type="text/css" href="css/home.css" media="screen" />
    <script language="JavaScript1.2" src="js/_lcd_core.js"></script>
    <script language="JavaScript1.2" src="js/_lcd_html.js"></script>
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
      <div class="content center" style='padding-top:100px;padding-bottom:100px'>
        <table border='0' cellpadding='0' cellspacing'0'>
          <tr>
            <td class='wm'>
              <? renderBoxStart() ?>
                <div style="height:160px; width:380px; font-family:Arial;">
                  <h2 style='margin-bottom:1em'>This reset password request has expired.</h2>
                  <p style='font-size:10pt'>
                  For security, reset password requests stop working after your password has been reset.
                  </p>
                  <p style='font-size:10pt'>
                  If you still need to reset your password, <a class='gb' href="index.php">go back to login page</a> and click the "forgot password" link. 
                  </p>
                </div>
              <? renderBoxEnd() ?>
            </td>
          </tr>
        </table>
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
</script>