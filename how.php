<?php
header("Location: index.php");
?>
<?php
require_once "inc/getSecurePrefix.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <!-- Copyright (c)2006 by LCD Solutions, Inc.  All rights reserved. -->
  <!-- http://www.clicktate.com -->
  <head>
    <title>clicktate : how to join us</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <meta http-equiv="Content-Script-Type" content="text/javascript" />
    <meta http-equiv="Content-Language" content="en-us" />
    <meta name="keywords" content="dictate, dictation, medical note, document generation, note generation" />
    <meta name="description" content="Automated document generation." />
    <link rel="stylesheet" type="text/css" href="css/clicktate.css" media="screen" />
  </head>
  <body>
    <div id="bodyContainer">
      <? include "inc/header.php" ?>
      <div class="content">
        <div id="columns">
          <div id="col1">
            <br>
            <ul class="list">
              <li>
                <b>More info?</b><br>
                Take a closer look at what Clicktate can do.<br>
                <a href="what.php">Learn more</a>
              </li>
              <li>
                <b>Pricing</b><br>
                Did you know using Clicktate can save your practice thousands of dollars?<br>
                <a href="pricing.php">More about pricing</a>
              </li>
              <li>
                <b>Got questions?</b><br>
                We've got answers. Take a quick look at our <a href="faq.php">FAQ</a>.
              </li>
            </ul>
          </div>
          <div id="col2">
            <div id="breadcrumb">
              <a href="index.php">home</a> > how to join us
            </div>
            <h1>Ready to get started?</h1>
            Find out for yourself just how amazing Clicktate is.
            Sign up now for a free trial account, which gives you full access to Clicktate's features for thirty days, with no obligation.<br><br>
            <div class="centered">
              <a class="button" href="<?=getSecurePrefix() ?>registerTrial.php">Sign up for a free trial ></a>
              <br><br><br>
            </div>
            <table width="100%" class="box" cellpadding=0 cellspacing=0>
              <tr>
                <td class="tl"></td>
                <td class="t"></td>
                <td class="tr"></td>
              </tr>
              <tr>
                <td class="l" nowrap></td>
                <td class="content">
                  <ul class="list">
                    <li>
                      <b>Am I under any obligation once my trial is up?</b><br>
                      No.
                    </li>
                    <li>
                      <b>What are the system requirements?</b><br>
                      Any PC with Internet Explorer 6 or 7 connected to the Internet will do.
                      We have had great success operating Clicktate on Tablet PCs.
                    </li>
                    <li>
                      <b>Do I have to be a computer guru to operate Clicktate?</b><br>
                      Not at all. And with our handy <a target="_blank" href="/Clicktate-Users-Guide.pdf">user manual</a> at your side, you'll become a pro in no time.
                    </li>
                    <li>
                      <b>Once I fall in love with your product and my thirty days are up, what happens?</b><br>
                      When your trial period expires, you will be asked for billing information to convert your trial to a full subscription.
                      <a href="pricing.php">More about pricing</a>
                    </li>
                  </ul>
                </td>
                <td class="r" nowrap></td>
              </tr>
              <tr>
                <td class="bl"></td>
                <td class="b"></td>
                <td class="br"></td>
              </tr>
            </table>
          </div>
        </div>
      </div>
    </div>
    <? include "inc/footer.php" ?>
  </body>
</html>
