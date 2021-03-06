<?
require_once "inc/uiFunctions.php";
require_once "php/forms/ForgotUserForm.php";

import_request_variables("p", "p_");
$form = new ForgotUserForm();
if (! isset($p_email)) {
   $p_email = "";
} else {
  try {
    $form->setFromPost();
    $form->validate();
    $form->sendMail();
    $msg = "User ID email sent.";
    $form = new ForgotUserForm();
  } catch (ValidationException $e) {
    $errors = $e->getErrors();
  }
}
$focus="email";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <!-- Copyright (c)2010 by LCD Solutions, Inc.  All rights reserved. -->
  <!-- http://www.clicktate.com -->
  <head>
    <title>
      Clicktate - Forgot Login ID
    </title>
    <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <meta http-equiv="Content-Script-Type" content="text/javascript" />
    <meta http-equiv="Content-Language" content="en-us" />
    <meta name="keywords" content="dictate, dictation, medical note, document generation, note generation, medical office notes, medical transcription, emr, ehr, medical documentation, progress notes, medical progress notes, soap notes, medical soap notes, medical note generation, medical notes, medical dictation, medical transcription, family practice notes, internal medicine notes, pediatric notes, urgent care notes, urgent care documentation, internal medicine documentation, pediatric documentation, family practice documentation, small office emr, small office ehr" />
    <meta name="description" content="Automated document generation." />
    <link rel="stylesheet" type="text/css" href="css/home.css" media="screen" />
<!--[if lte IE 6]>    
    <link rel="stylesheet" type="text/css" href="css/home-ie6.css" media="screen" />
<![endif]-->    
    <script language="JavaScript1.2" src="js/ui.js"></script>
  </head>
  <body style='background-color:#000000;'>
    <div id="head">
      <div class="content">
        <div id="nav">
          <table cellpadding="0" cellspacing="0">
            <tr>
              <td>
                <a href="../index.php">Home</a>
                <span>|</span>
                <a href="../tour.php">Take a Tour</a>
                <span>|</span>
                <a href="../pricing.php">Pricing</a>
                <span>|</span>
                <a href="https://www.clicktate.com/sec/registerTrial.php">Free Trial Signup</a>
              </td>
              <td style="text-align:right">
                <a href="." class="login">Secure Login for Clicktate Users ></a>
              </td>
            </tr>
          </table>
        </div>
      </div>
    </div>
    <div id="body">
      <div class="content center">
        <h1 style="margin-top:2em">Forgot Login ID</h1>
        <div class="subh">
          Please enter the email address you registered with.<br/>Your ID will be sent to this address.
        </div>
        <div class="login">
          <?php require_once "inc/errors.php" ?>
        </div>
        <table border='0' cellpadding='0' cellspacing'0'>
          <tr>
            <td class='wm'>
              <?php renderBoxStart() ?>
                <div id="login">
                  <form id="frm" method="post" action="forgot-id.php">
                    <div class="l" style="margin-top:10px">
                      <label>Email Address</label><br/>
                      <input id="email" type="text" size="40" name="email" maxlength="50" value="<?=$form->email ?>" />
                    </div>
                    <div id="trial" style="padding-bottom:10px">
                      <a href="javascript:submit()" class="tour">Submit ></a>
                    </div>
                  </form>
                </div>
              <?php renderBoxEnd() ?>
            </td>
          </tr>
        </table>
        <div id="forgot">
          Forgot your <a href="forgot-pw.php">password</a>?
        </div>
      </div>
    </div>
    <?php require_once "inc/focus.php" ?>
    <div id="foot">
      <div class="content">
        <div class="foot-text">
          &copy; 2007-2010 LCD Solutions, Inc.<br/>
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
function submit() {
  document.getElementById('frm').submit();
}
</script>