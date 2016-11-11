<?php
header("Location: index.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <!-- Copyright (c)2006 by LCD Solutions, Inc.  All rights reserved. -->
  <!-- http://www.clicktate.com -->
  <head>
    <title>clicktate : faq</title>
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
      <?php include "inc/header.php" ?>
      <div class="content">
        <div class="abstract">
          <div id="breadcrumb">
            <a href="index.php">home</a> > faq
          </div>
          <h1 style="margin-bottom:10px">Frequently Asked Questions</h1>
          <ul>
            <li><a href="faq.php#trial">
              <span>I have signed up for my free trial, now what's the best way to get started?</span></a>
            </li>
            <li><a href="faq.php#manual">
              <span>Is there a user's manual available online?</span></a>
            </li>
            <li><a href="faq.php#popup">
              <span>I am creating a session, but nothing is happening.  What's going on?</span></a>
            </li>
            <li><a href="faq.php#HIPAA">
              <span>Is Clicktate HIPAA compliant?</span></a>
            </li>
            <li><a href="faq.php#appt">
              <span>My patient didn't keep their appointment for testing, or didn't complete their medication.  How do I document this?</span></a>
            </li>
            <li><a href="faq.php#treatment">
              <span>I would like to document my plans if a test or response to treatment is positive or negative.</span></a>
            </li>
            <li><a href="faq.php#testing">
              <span>My patient refused to follow my advice regarding testing.  How do I document this?</span></a>
            </li>
            <li><a href="faq.php#time">
              <span>I would like to document the time I spent with a patient.  How do I do this?</span></a>
            </li>
            <li><a href="faq.php#prior">
              <span>My patient has received care at another facility, or previously at my office.  How do I get this information into a note?</span></a>
            </li>
            <li><a href="faq.php#tylenol">
              <span>I would like to document when my patient had their last dose of Tylenol and/or Ibuprofen.  How do I do this?</span></a>
            </li>
            <li><a href="faq.php#MED">
              <span>There is a section labeled MED at the bottom of the left selection bar, it doesn't seem to have a function.  What is its purpose?</span></a>
            </li>
            <li><a href="faq.php#ADDMED">
              <span>When I'm generating a note, there is a section at the bottom that says ADD MED and DISCONTINUE MED, but I didn't add or discontinue a medication.  How do I delete this?</span></a>
            </li>
            <li><a href="faq.php#mult">
              <span>What if I want to add or discontinue more than one medication?</span></a>
            </li>
            <li><a href="faq.php#nomed">
              <span>I want to add or discontinue a medication, such as an over the counter medication,  which doesn't appear in the search function on the Medication Selection Screen.  How do I do this?</span></a>
            </li>
            <li><a href="faq.php#clipboard">
              <span>How can I put a note into my EMR system?</span></a>
            </li>
            <li><a href="faq.php#contract">
              <span>After my free month, what type of contract will I be asked to sign?</span></a>
            </li>
          </ul>
          <hr /><br />
          <ul class="list">
            <li><a name="trial">
              <span>I have signed up for my free trial, now what's the best way to get started?</span><br></a>
              <div class="indent">
              While Clicktate is easy-to-use and intuitive, there is a learning curve.  
              This is the reason for the free month trial.  
              We want you to learn about Clicktate and experience all its functionality without worrying about cost or obligation. 
              We recommend that you initially try Clicktate in the office with 2-3 patients per half-day and increase as you feel comfortable.  
              Also, try it at home, on vacation, at the airport, or wherever you have Internet access.  
              During your free month, we encourage you to experiment with the system, creating hundreds (or thousands) of notes.  
              See what Clicktate can do!  
              And of course, all of this is at no cost to you.
              </div>
            </li>
            <li><a name="manual">
              <span>Is there a user's manual available online?</span><br></a>
              <div class="indent">
              Yes, and you can view or download our <a target="_blank" href="/Clicktate-Users-Guide.pdf">user manual right here</a>.
              </div>
            </li>
            <li><a name="popup">
              <span>I am creating a session, but nothing is happening.  What's going on?</span><br></a>
              <div class="indent">
              Most likely your browser's <b>popup-blocker</b> is preventing Clicktate from displaying the created session.
              You must click Internet Explorer's yellow popup warning bar and allow Clicktate to display popups.
              For more information, refer to our <a target="_blank" href="/Clicktate-Users-Guide.pdf">user manual</a>.
              </div>
            </li>
            <li><a name="HIPAA">
              <span>Is Clicktate HIPAA compliant?</span><br></a>
              <div class="indent">
              Yes we are, and our HIPAA privacy contract is available upon request.
              Clicktate uses Verisign SSL (Secure Socket Layers) certification and 256-bit encryption.
              The SSL protocol is enforced from the user logon forward, meaning that all patient data is transmitted encrypted over the network.
              Our servers are in a secure site and are protected by a CISCO firewall.   
              </div>
            </li>
            <li><a name="appt">
              <span>My patient didn't keep their appointment for testing, or didn't complete their medication.  How do I document this?</span><br /></a>
              <div class="indent">
              Under the HPI section, select NONCOMPLIANCE WITH TESTING or NONCOMPLIANCE WITH TREATMENT.
              </div>
            </li>
            <li><a name="treatment">
              <span>I would like to document my plans if a test or response to treatment is positive or negative.</span><br /></a>
              <div class="indent">
              Under the PLAN section, select CONTINGENCY PLAN and DISCUSSION.
              </div>
            </li>
            <li><a name="testing">
              <span>My patient refused to follow my advice regarding testing.  How do I document this?</span><br /></a>
              <div class="indent">
              Under the PLAN section, select REFUSAL TO TEST.
              </div>
            </li>
            <li><a name="time">
              <span>I would like to document the time I spent with a patient.  How do I do this?</span><br /></a>
              <div class="indent">
              Under the TIME section, select DOCUMENTATION OF TIME.
              </div>
            </li>
            <li><a name="prior">
              <span>My patient has received care at another facility, or previously at my office.  How do I get this information into a note?</span><br /></a>
              <div class="indent">
              Under the HPI section, select PRIOR TREATMENT FOR AN ACUTE ILLNESS.
              </div>
            </li>
            <li><a name="tylenol">
              <span>I would like to document when my patient had their last dose of Tylenol and/or Ibuprofen.  How do I do this?</span><br /></a>
              <div class="indent">
              Under the MEDS section, select LAST IBUPROFEN DOSAGE or LAST TYLENOL DOSAGE.
              </div>
            </li>
            <li><a name="MED">
              <span>There is a section labeled MED at the bottom of the left selection bar, it doesn't seem to have a function.  What is its purpose?</span><br /></a>
              <div class="indent">
              This is a reminder icon to let you see where ADD MEDS and DISCONTINUE MEDS will appear in a note.  This button does not have user functionality.
              </div>
            </li>
            <li><a name="ADDMED">
              <span>When I'm generating a note, there is a section at the bottom that says ADD MED and DISCONTINUE MED, but I didn't add or discontinue a medication.  How do I delete this?</span><br /></a>
              <div class="indent">
              You don't.  Clicktate will remove this automatically if a med is not added or discontinued.  If you add a medication, but don't discontinue one, it will only remove the DISCONTINUE MED section and vice-versa.  This also works the same way when a medication selection icon appears in the document.
              </div>
            </li>
            <li><a name="mult">
              <span>What if I want to add or discontinue more than one medication?</span><br /></a>
              <div class="indent">
              Once one [add med] or [discontinue med] icon is used, another will appear. Only those that are used will appear in the document.
              </div>
            </li>
            <li><a name="nomed">
              <span>I want to add or discontinue a medication, such as an over the counter medication,  which doesn't appear in the search function on the Medication Selection Screen.  How do I do this?</span><br /></a>
              <div class="indent">
              Simply type the medication into the medication selection window, along with any other information you would like included, and hit the OK button.
              </div>
            </li>
            <li><a name="clipboard">
              <span>How can I put a note into my EMR system?</span><br /></a>
              <div class="indent">
              Just click the Clipboard Copy button at the top of the console, then go to the place in your EMR system where you can type in note text and press Ctrl-V
              (or use the standard Paste function, if there is one, of the EMR system's text editor).
              </div>
           </li>
            <li><a name="contract">
              <span>After my free month, what type of contract will I be asked to sign?</span><br></a>
              <div class="indent">
              You won't be asked to sign any contract.  
              Clicktate is paid monthly.   
              You can cancel anytime. (Although we don't think you will.)
              </div>
            </li>
          </ul>
      </div>
    </div>
  </body>
  <?php include "inc/footer.php" ?>
</html>

