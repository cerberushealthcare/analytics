<?php
require_once "inc/tags.php";
set_include_path('../');
require_once 'php/data/rec/sql/_SqlRec.php';
require_once 'php/data/rec/sql/PortalUsers_Session.php';
if (isset($_GET['logout'])) {
  @session_start();
  session_destroy();        
  session_unset();     
  session_regenerate_id();
}
?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <?php PHEAD('Patient Portal', 'login.css') ?>
    <?php PHEAD_DATA('PortalSession') ?>
  </head>
  <body>
    <div id='page'>
      <div id='loginc'>
        <div id='login'>
          <h1>Patient Login</h1>
          <?php PBOX() ?>
            <div>
              <label>User ID</label>
              <input type='text' size='20' id='id' name='id' />
            </div>
            <div>
              <label>Password</label>
              <input type='password' size='20' id='pw' name='pw' />
            </div>
            <div>
              <a class='big' id='alog' href="javascript:login_onclick()">Login ></a>
            </div>
          <?php _PBOX() ?>
          <div id='links'>
            <div style='display:none'>
              Forgot your <a href='.'>login ID</a> or <a href='.'>password</a>?
             </div>
          </div>
        </div>
        <div id='tiles'>
          <div id='auth' style='display:none'>
            <h1>Patient Portal</h1>
            <p id='auth-msg'>To continue with your login, please answer the following security questions.</p>
            <?php PBOX() ?>
              <div id='auth-tile'></div>
            <?php _PBOX() ?>
          </div>
          <div id='pass' style='display:none'>
            <h1>Patient Portal</h1>
            <p id='auth-msg'>A new password is required to continue.</p>
            <?php PBOX() ?>
              <div id='pass-tile'></div>
            <?php _PBOX() ?>
          </div>
          <div id='tos' style='display:none'>
            <h1>Patient Portal</h1>
            <h2>Terms of Service</h2>
            <div id='tos-text'>
              <p>Clicktate Online Access is a medical information and information exchange tool only. It is designed to assist patients by allowing them to review their medical information, to receive communications from their healthcare provider and to allow patients to communicate information to and request information from their healthcare provider. It is not meant to replace the knowledge, experience, sound clinical judgment, and expertise of a medical provider. It is not a diagnostic tool, nor is it meant to suggest a diagnosis or treatment for any disease, malady or condition.  It is not a tool to assist in or suggest a course of therapy.</p>  
              <p>Nothing contained in Clicktate Online Access is intended to be instructional for medical diagnosis or treatment. The information in Clicktate Online Access should not be considered complete, nor should it be relied on to suggest a course of treatment for a particular individual. It should not be used in place of a visit, call, consultation or the advice of a physician or other qualified health care provider. Information obtained in Clicktate Online Access is not exhaustive and does not cover all diseases and physical conditions or their treatment.</p> 
              <p>Information contained in Clicktate Online Access is �AS ENTERED� by the physician, provider and medical office staff of a patient.  The information is not obtained by Clicktate Online Access or clicktate.com from any other source, and as such Clicktate Online Access is not responsible for the accurateness or completeness of the information.  The information contained in Clicktate Online Access is NOT considered a part of a patient�s medical record and information contained therein should not be used to decide on a course of treatment without first assuring its accuracy.  Patient access to Clicktate Online Access may however be retained by the physician office as a part of the medical record and for audit purposes.</p>
              <p>The information from or to Clicktate Online Access and associated sites is provided "AS IS" and "AS AVAILABLE" and all warranties, expressed or implied, are disclaimed (including but not limited to the disclaimer of any implied warranties or merchantability and fitness for a particular purpose). The information may contain errors, problems, or other limitations. LCD Solutions, Inc., Clicktate.com and Clicktate Online Access make no warranty as to the reliability, accuracy, timeliness, usefulness, adequacy, suitability or completeness for any purpose. LCD Solutions, Inc., Clicktate.com and Clicktate Online Access cannot and do not warrant against human and machine errors, omissions, delays, interruptions or losses, including loss of data. Users of Clicktate Online Access acknowledge and agree that LCD Solutions, Inc., Clicktate.com and Clicktate Online Access do not run or control the internet.  As such, LCD Solutions, Inc., Clicktate.com and Clicktate Online Access cannot control all variables that might arise from use of the internet.  LCD Solutions, Inc., Clicktate.com and Clicktate Online Access also have no control over internet usage habits of users of Clicktate Online Access.  Clicktate.com and Clicktate Online Access links and searches for the term Clicktate may inadvertently and unintentionally lead to sites containing information that some people may find inappropriate or offensive.  Clicktate.com and Clicktate Online Access links and searches for the terms Clicktate and Clicktate Online Access may also inadvertently and unintentionally lead to sites which contain inaccurate information, false or misleading advertising, or information which violates copyright, libel or defamation laws. Clicktate.com, Clicktate Online Access and affiliated sites cannot and do not guarantee or warrant that files available for downloading from these online sites will be free of infection by viruses, worms, Trojan horses or other code that manifest contaminating or destructive properties. All responsibility and liability for any damages caused by viruses contained within the electronic files of these sites are disclaimed.  Clicktate.com, Clicktate Online Access and Information Providers do not warrant or guarantee that the functions or Services performed in Clicktate Online Access will be uninterrupted or error-free or that defects in Clicktate Online Access will be corrected. Providers who supply Clicktate Online Access are responsible for implementing and maintaining adequate procedures and checkpoints to satisfy their particular requirements for accuracy of data input and output. Customers who utilize Clicktate Online Access are responsible for maintaining security of their personal log-in information including user IDs and Passwords.</p>
              <p>Clicktate.com does not and cannot review all communications and materials posted to or uploaded to Clicktate.com and Clicktate Online Access, and LCD Solutions, Inc., Clicktate.com, Clicktate Online Access, its officers, directors, employees, agents, and information providers are not responsible for the content of these communications and materials. However, LCD Solutions, Inc., Clicktate.com and Clicktate Online Access reserve the right to block or remove communications or materials that it determines, in their sole discretion, to be (a) abusive, libelous, defamatory or obscene, (b) fraudulent, deceptive, or misleading, (c) in violation of a copyright or trademark, other intellectual property right of another or (d) offensive or otherwise unacceptable to LCD Solutions and/or Clicktate.com and/or Clicktate Online Access.</p>
              <p>Users of Clicktate Online Access agree to: (a) maintain all equipment required for access to and use of Clicktate Online Access; (b) maintain the security of user identification, password and other confidential information relating to user�s Clicktate Online Access account; and (c) be responsible for all charges resulting from use of user�s Clicktate Online Access account, including unauthorized use prior to notifying LCD Solutions, Inc., Clicktate.com and Clicktate Online Access of such use and taking steps to prevent further occurrence by appropriate password changes.</p> 
              <p>Users of Clicktate Online Access agree to indemnify, defend and hold harmless LCD Solutions, Inc., Clicktate.com, Clicktate Online Access, it�s officers, directors, employees, agents, and information providers from and against all losses, expenses, damages and costs, including reasonable attorney�s fees, resulting from any violation of this agreement or any activity related to your account by you or any other person accessing Clicktate Online Access on your behalf or using your service account.  You further agree that neither LCD Solutions, Inc., Clicktate.com, Clicktate Online Access, its officers, directors, employees, agents and information providers shall have any liability to you under any theory of liability or indemnity in connection with your use of Clicktate Online Access.  You hereby release and forever waive any and all claims you may have against LCD Solutions, Inc., Clicktate.com, Clicktate Online Access, its officers, directors, employees, agents, and information providers, including but not limited to claims based upon negligence of LCD Solutions, Inc., Clicktate.com, Clicktate Online Access, it�s officers, directors, employees, agents, and information providers for losses or damages you sustain in connection with your use of Clicktate Online Access.</p>
              <p>Notwithstanding the foregoing paragraph, the sole and entire maximum liability of LCD Solutions, Inc., Clicktate.com, Clicktate Online Access, its officers, directors, employees, agents, and information providers, if any, for any inaccurate information and losses or damages for any reason, and users sole and exclusive remedy for any cause whatsoever, shall be limited to the amount paid by the customer for the information received (if any). In no event shall LCD Solutions, Inc., Clicktate.com, Clicktate Online Access,  its officers, directors, employees, agents, and information providers be liable to you for any losses or damages other than the amount referenced above.  LCD Solutions, Inc., Clicktate.com, Clicktate Online Access, its officers, directors, employees, agents, and information providers are not liable for any indirect, special, incidental, or consequential damages (including damages for loss of business, loss of profits, litigation, or the like), whether based on breach of contract, breach of warranty, tort (including negligence), product liability, or otherwise, even if LCD Solutions, Inc., Clicktate.com, Clicktate Online Access, its officers, directors, employees, agents, and information providers advised of the possibility of such damage. The limitations of damages set forth above are fundamental elements of the basis of the bargain between LCD Solutions, Inc., Clicktate.com, Clicktate Online Access and you. We would not provide these sites and information without such limitations. No representations, warranties or guarantees whatsoever are made as to the accuracy, adequacy, reliability, currentness, completeness, suitability or applicability of the information to a particular situation.</p>
              <p>Clicktate Online Access is a medical information and information exchange tool only. It is designed to assist patients by allowing them to review their medical information, to receive communications from their healthcare provider and to allow patients to communicate information to and request information from their healthcare provider. It is not meant to replace the knowledge, experience, sound clinical judgment, and expertise of a medical provider. It is not a diagnostic tool, nor is it meant to suggest a diagnosis or treatment. It is not a tool to assist in or suggest a course of therapy.</p>  
              <p>LCD Solutions, Inc., Clicktate.com and Clicktate Online Access cannot guarantee the availability or response time of providers to patient requests.  No guarantee is made or implied of a response time for any patient request made from patients to providers utilizing Clicktate Online Access. Providers may, at their discretion, make and/or publish statements regarding response time to requests, and any such statement or warranty, expressed or implied, is the sole responsibility of the provider to provide.</p>
              <p>LCD Solutions, Inc., Clicktate.com, Clicktate Online Access, its officers, directors, employees, agents, and information providers are not liable for any injury, damage, omission, or loss which occurs directly or indirectly from the use of this system. In addition, LCD Solutions, Inc., Clicktate.com and Clicktate Online Access are not responsible for any errors or omissions which may be perceived to be a part of the system.</p> 
              <p><b>Clicktate Online Access is NOT an emergency response system.  By signing up for service and agreeing to these terms and conditions, customers acknowledge that NO emergent, urgent, life-threatening or other requests constituting a healthcare or any other type of  emergency should be conveyed using Clicktate Online Access.</b></p>
              <p>As with any printed material or computer program or system, sound clinical judgment by a licensed and competent provider of medical care should be the ultimate guide to patient care, diagnosis, and therapy. This product is not designed to replace this care. </p>
              <p>Either you or Clicktate.com may terminate your right to use Clicktate Online Access at any time, with or without cause, upon notice. Clicktate.com also reserves the right to terminate or suspend your Clicktate Online Access membership without prior notice, but LCD Solutions and Clicktate.com will confirm such termination or suspension by subsequent notice.</p>
            </div>
            <div id='tos-cb'></div>
          </div>
        </div>
      </div>
    </div>
  </body>
  <?php JsonConstants::writeGlobals('PortalUser') ?>
  <?php PPAGE('LoginPage') ?>
</html>
