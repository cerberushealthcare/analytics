<?php $title = 'Clicktate - Tour' ?>
<?php include "inc/hheader.php" ?>
<div id="body">
  <div class="content center">
    <h1>Feature Tour and Screenshots</h1>
    <table class='c' cellpadding="0" cellspacing="0">
      <tr>
        <td><a href="javascript:pop()" class="trial">Free Trial</a></td>
        <td width=5></td>
        <td><a class="itour video" href="javascript:video()">Watch a 3-minute video of features</a></td>
      </tr>
    </table>
    <div class="ss">
      <h2>Patient Facesheet</h2>
      <img src="img/ss-facesheet.png" />
      <div class="sp"> 
        <p>
          The <b>facesheet</b> is both an overview of a patient's medical record and a "launching pad" for patient-specific functions, such as recording vitals, allergies, or starting a SOAP note.
        </p>
        <p>
          <b>Health data flows automatically</b> between the facesheet and the documentation you create for that patient, eliminating redundant data entry and keeping the patient's record up-to-date.
        </p>
      </div>
    </div>
    <div class="ss">
      <h2>Document Console</h2>
      <img src="img/ss-console.png" />
      <div class="sp">
        <p>
          The <b>console</b> is Clicktate's amazing document builder that quickly and easily builds your medical documentation in paragraph form.
          With over 2,000 physician-developed templates in our ever-expanding library, you'll find templates for most patient problems you encounter.    
        </p>
        <p>
          Documents built in Clicktate are saved to the patient's record and automatically update relevant portions of the patient's facesheet (current medications, for example).
        </p>
        <p>
          Documents can also be downloaded in Word format, fully formatted with page headers, or copied to the clipboard for pasting into other medical systems.
        </p>
      </div>
    </div>
    <div class="ss">
      <h2>Scheduling</h2>
      <img src="img/ss-scheduling.png" />
      <div class="sp">
        <p>
          Use our <b>scheduling</b> module for complete calendar management of availability time and patient appointments.
          Complete customization is available to set appointment types, duration, colors, and status.   
        </p>
      </div>
    </div>
    <h2 class="drop">Ready to try for yourself?</h2>
    <p>
      Take a <b>14-day no-risk</b> test drive.<br/>
      It's completely free without obligation.
    </p>
    <div id="trial">
      <table class='c' cellpadding="0" cellspacing="0">
        <tr>
          <td><a href="javascript:pop()" class="trial" style='width:200px'>Free Trial Sign Up ></a></td>
        </tr>
      </table>
      Sign up takes seconds
    </div>
  </div>
</div>
<script type="text/javascript">
function video() {
  window.open("video.php", null, "height=600,width=850,resizable=0,statusbar=0");
}
</script>
<?php include "inc/hfooter.php" ?>