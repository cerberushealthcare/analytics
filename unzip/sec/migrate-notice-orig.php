<?php
require_once "php/data/LoginSession.php";
//
LoginSession::verify_forUser();
$redirect = false;
$errors = null;
if (isset($_POST['acc'])) {
  if ($_POST['acc'] == 'Continue with login') {
    //$errors = $login->acceptBaa($_POST);
    $redirect = true;
  } else {
    header("Location: index.php?logout=y");
  }
}
?>
<? $title = 'Clicktate - Migration Notice' ?>
<? if (! $redirect) { ?>
<? include "inc/hheader.php" ?>
<style>
BODY {font-family:Arial}
TABLE {font-size:10pt}
P {font-size:10pt}
H3 {margin-top:1.5em}
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
INPUT.cb {height:40px;padding:0 10px}
@media print {
  DIV.screen {
    display:none;
  }
}
</style>
<div id="body" style="background:white">
  <form method="post" action="migrate-notice.php">
    <div class="content center" style="padding-bottom:30px">
      <h1>Important Information</h1>
      Regarding Your Clicktate&reg; Account
    </div>
    <div class="wm">
      <div class="tos">
<p><b>This weekend (April 20 and 21) we will be migrating all customer accounts to our certified electronic medical record system.</b> 
This migration is necessary to provide all users the full benefits of the Clicktate&reg; electronic medical record, including drug-drug interaction checking, drug-allergy interaction checking, patient summary reports, patient education materials and reporting capabilities. 
Most importantly, this move ensures that Clicktate&reg; will continue to provide the best product possible to help our customers provide the best patient care. </p>
<p>The ONC certified Clicktate&reg; 4.0 system includes all original functionality as well as additional features necessary to meet meaningful use (MU) requirements in 2013.
We are also diligently working towards certification for MU stage 2, which goes into effect in 2014.</p> 
<p>We have attempted to contact all affected customers by both phone and email over the past several months. 
(If we were not successful in contacting you, please update your practice information including your phone and email address in the Practice section of your Profile page.)
If you decide to transition to another EMR system, or do not wish to take advantage of the features available by Clicktate&reg; 4.0, please contact us by phone at 1-888-825-4258 ext 802 to cancel your account.
You will continue to have access to your data in read-only format.</p>
<p><b>In order to ensure everyone has adequate time to acclimate to Clicktate&reg; 4.0, migrated customers will remain at their current monthly fee until their regular May billing.</b>
In May, all certified users will be billed at our standard rate of $139 per month.
This is not a decision we take lightly. For over five years we have done our best to keep provider costs from increasing, even as the standard rate for new customers has steadily increased. 
However, with increasing government regulations including EMR certification and security requirements, and due to increasing administrative fees including server and storage costs and electronic prescribing fees, it has become necessary for us to consolidate our userbase onto a single, certified system.</p>
</p>
<p>For customers who wish to prepay one year in advance, we can offer a cost of $1500 per provider for the first year, which represents a 10% discount off the standard monthly fee. <b>If you are interested in taking advantage of this discounted rate,</b> please contact us by <a href="mailto:info@clicktatemail.info">replying to this email</a>. 
<h3>Activating all services provided by Clicktate&reg; 4.0</h3>
<p>Please note that in order to take advantage of <b>all</b> services offered by Clicktate&reg; 4.0, there are some additional steps.</p>
<p><b>Electronic Prescribing</b></p>
<ul>
<li>Fax to 1-888-825-4258 (or email to <a href='mailto:info@clicktatemail.info'>info@clicktatemail.info</a>) a copy of your valid medical license and valid DEA certificate.
We will also need your fax number and NPI number. 
</li>
<li>Sign up for a training session webinar by calling our support line 1-888-825-4258 or <a href='mailto:info@clicktatemail.info'>email</a>.
Training sessions typically last about an hour and are provided on a first-come first-serve basis. Each webinar has space for 24 customers. Available webinar times:</li>
<ul>
<li>Tuesday, April 16 at 7AM Eastern</li>
<li>Thursday, April 18 at 9PM Eastern</li>
<li>Friday, April 26 at 8AM Eastern</li>
<li>Friday, April 26 at 1PM Eastern</li>
<li>Saturday, April 27 at 1PM Eastern</li>
</ul>
</ul>
<p><b>Laboratory Interface</b></p>
<ul>
<li>Contact your laboratory representative to notify the laboratory that you would like an interface for importing results electronically into Clicktate&reg;.</li>
<li>Ask your laboratory representative to contact us at 888-825-4258 ext. 802 to begin the interface process.</li>
<li>Let us know that you have requested a lab interface. Laboratory interfaces typically take two to four weeks to be up and running, depending upon the laboratory.</li>   
</ul>
<p><br>
We are certain that you will find the features of Clicktate&reg; to be an asset to your practice in terms of patient care, office efficiency and your bottom line.
We know you have many choices, and we truly appreciate your continued use of Clicktate&reg;.
<BR><BR>
</p>        
<div class="fm screen">
  <div class='fmb'>
    <div class='fmc'>
      <input class='cb' type="submit" name="acc" value="Continue with login" />
      <a class="print" href="javascript:print()">Print this page</a>
    </div>
  </div>
</div>
    </div>
  </div>
  </form>
</div>
<? } else { ?>
<script>
window.location = 'welcome.php';
</script>
<? } ?>
