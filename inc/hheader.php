<?php
require_once "inc/getSecurePrefix.php";
$version = '7';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <!-- Copyright (c)2012 by LCD Solutions, Inc.  All rights reserved. -->
  <!-- http://www.clicktate.com -->
  <head>
    <title>
      <?=$title ?>
    </title>
    <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <meta http-equiv="Content-Script-Type" content="text/javascript" />
    <meta http-equiv="Content-Language" content="en-us" />
    <meta name="keywords" content="dictate, dictation, medical note, document generation, note generation, medical office notes, medical transcription, emr, ehr, medical documentation, progress notes, medical progress notes, soap notes, medical soap notes, medical note generation, medical notes, medical dictation, medical transcription, family practice notes, internal medicine notes, pediatric notes, urgent care notes, urgent care documentation, internal medicine documentation, pediatric documentation, family practice documentation, electronic medical record, electronic health record, certified emr, certified ehr, certified electronic medical record, certified electronic health record, practice management software, small office emr, small office ehr" />
    <meta name="description" content="Automated document generation." />
    <link rel="stylesheet" type="text/css" href="css/home.css?<?=$version ?>" media="screen" />
    <script language="JavaScript1.2" src="inc/tpop.js?<?=$version ?>"></script>
  </head>
  <body>
    <div id="head">
      <div class="content">
        <div id="nav">
          <table cellpadding="0" cellspacing="0">
            <tr>
              <td>
                <a href="index.php">Home</a>
                <span>|</span>
                <a href="tour.php">Take a Tour</a>
                <span>|</span>
                <a href="pricing.php">Pricing</a>
                <span>|</span>
                <a href="javascript:pop()">Free Trial Signup</a>
              </td>
              <td style="text-align:right">
                <a href="<?=getSecurePrefix() ?>" class="login">Secure Login for Clicktate Users ></a>
              </td>
            </tr>
          </table>
        </div>
      </div>
    </div>
    <div id='curtain'></div>
