<?
ob_start('ob_gzhandler');
require_once "php/data/LoginSession.php";
require_once "inc/uiFunctions.php";
require_once 'php/data/rec/erx/ErxStatus.php';
require_once 'php/data/rec/sql/OrderEntry.php';
require_once 'php/data/rec/sql/Meds.php';
require_once 'php/data/rec/sql/Clients.php';
require_once 'php/data/rec/sql/Documentation.php';
require_once 'php/data/rec/sql/UserGroups.php';
require_once 'php/data/rec/sql/LookupAreas.php';
require_once 'php/c/health-maint/HealthMaint_Recs.php';
require_once 'php/data/rec/sql/PortalUsers.php';
require_once 'php/data/rec/sql/Templates_IolEntry.php';
//
LoginSession::verify_forUser()->requires($login->Role->Patient->facesheet);
//
if (isset($_GET['aid'])) {
  global $login;
  $practiceId = $login->cerberus; 
  if ($practiceId) {
    require_once 'php/data/rec/sql/_ApiIdXref.php';
    $cid = ApiIdXref_Cerberus::lookupClientId($practiceId, $_GET['aid']);
    $_GP = array('id' => $cid);
  }
} else if (isset($_GET['id'])) { 
  $_GP = &$_GET;
} else { 
  $_GP = &$_POST; 
}
$pop = isset($_GP['pop']);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Strict//EN">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <?php HEAD('Facesheet', 'FacesheetPage') ?>
    <link rel="stylesheet" type="text/css" href="css/xb/facesheet.css?<?=Version::getUrlSuffix() ?>" />
    <script language="JavaScript1.2" src="js/rx-writer.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/template-pops.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/pages/NewCrop.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/libs/FaceUi.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/pops/FaceHx.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/pops/FaceHm.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/pops/FaceMeds.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/pops/FaceDiagnoses.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/pops/FaceVitals.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/pops/FaceImmun.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/pops/FaceAllergies.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/pops/FaceTrack.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/pops/PatientEditor.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/_ui/ReportBuilder.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/_ui/SnomedPop.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/old-ajax.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/json.js?<?=Version::getUrlSuffix() ?>"></script>
    <script src="js/_rec/InfoButton.js?<?=Version::getUrlSuffix() ?>"></script>
    <?php HEAD_UI('Facesheet') ?>
    <style>
DIV.EntryFolderList {
  border:1px solid #c0c0c0;
  height:200px;
  background-color:white;
  overflow-y:scroll;
  padding:4px;
  margin-top:10px;
}
    </style>
  </head>
  <body onload="start()" <?php if ($pop) { ?>style='background-color:white'<?php } ?>>
    <div id="bodyContainer">
      <div id="curtain"></div>
      <?php include "inc/header.php" ?>
      <div id='bodyContent' class="content">
        <table class='h'>
          <tr>
            <th>
              <h1><span id="h1-name" class='tf-heading'></span>&nbsp;</h1>
            </th>
            <td>
              <?php if ($pop) { ?>
                <a href="javascript:window.close()" class="icon big mt10">Close Window</a>
              <?php } else { ?>
                <a href="javascript:" onclick="page.pDownload()" class="icon download">Download/Print</a>
                <span class='psearch'>
                  &nbsp;
                  <a href="javascript:" onclick="PatientSelector.pop()" class="icon search">Search for patient</a>
                </span>
              <?php } ?>
            </td>
          </tr>
        </table>
        <div id="fs-refresh" style="display:none">  <!-- refreshTile -->
          <a href="javascript:page.pRefresh()">Facesheet has been updated. Click to refresh page.</a>
        </div>
        <?php renderBoxStart("wide min-pad") ?>
          <table class='w100'>
            <tr>
              <td class='vtop' style="padding-right:4px;width:75%">
                <table class='w100'>
                  <tr>
                    <td class='vtop'>  
                      <div id="portrait">  <!-- demoTile -->
                        <table class='h'>
                          <tr>
                            <th style='padding:3px;width:85px;vertical-align:top'>
                              <img id='photo' style='display:none' onclick="popUpload()" />
                              <div id="empty-photo" style='visibility:hidden' onclick="popUpload()">
                              </div>
                            </th>
                            <th style='vertical-align:top'>
                              <ul class="entry">
                                <li>
                                  <label>
                                    ID:
                                  </label>
                                  <span id="dem-cid" class="ro"></span>
                                  <label class="spad">
                                    DOB:
                                  </label>
                                  <span id="dem-dob" class="ro"></span>
                                  <label class="spad">
                                    Age/Sex:
                                  </label>
                                  <span id="dem-age" class="ro">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                                </li>
                              </ul>
                              <ul id="ul-dem" class="entry ro" style='margin-top:5px'>
                                <li>
                                  <label id="dem-lbl-addr" class="first">
                                  </label>
                                  <span id="dem-addr" style='width:300px' class="ro"> 
                                  </span>
                                </li>
                                <li>
                                  <label id="dem-lbl-flags" class="first"></label>
                                  <span id="dem-flags" class="ro red"></span>
                                </li>
                                <li>
                                  <label id="dem-lbl-dnr" class="first"></label>
                                  <span id="dem-dnr" class="ro red"></span>
                                </li>
                              </ul>
                            </td>
                            <td style='vertical-align:top;padding:3px 2px 0 0'>
                              <a id='show-demo' class="fsedit demo" title="Open/edit this section" href="javascript:" onclick="page.pPopDemo()">Open</a>
                            </td>
                          </tr>
                        </table>
                      </div>
                    </td>
                  </tr>
                </table>
              </td>
              <td class='vtop' style="width:25%;">
                <div id="notepad">  <!-- notepadTile -->
                  <div id="notepad-empty">
                    <a class="fsedit" href="javascript:" onclick="page.pPopNotepad()">Add Notes</a>
                  </div>
                  <a id="notepad-text" href="javascript:" onclick="page.pPopNotepad()" title="Edit this note">
                  </a>
                </div>
              </td>
            </tr>
          </table>
          <table class='w100 mt10'>
            <tr>
              <td id="td-wf-1" class='vtop' style='width:65%'>  <!-- workflowTile -->
<!-- ****** crs 6/29/2016
                <table border="0" cellpadding="0" cellspacing="0">
                  <tr> 
                    <td style='padding-left:4px'>
                      <div style='display:inline' id="wf-appt" class="qpanel">
                      </div>
                      <div style='display:inline' id="wf-vit" class="qpanel">
                      </div>
                    </td>
                  </tr>
                  <tr><td class='h5'></td></tr>
                  <tr>
                    <td style='padding-left:4px'>
                      <div class="qpanel">
                        <ul id="wf-doc-ul">
                        </ul>
                      </div>
                    </td>
                  </tr>
                </table>
          ****** crs 6/29/2016 -->
              </td>
              <td id="td-wf-2" class="w5"></td>
              <td id="td-all" style='width:35%'>  <!-- allerTile -->
                <a href="javascript:" onclick="page.pPopAllergies()" title="Open this section" class="fscap">Active Allergies</a>
                <div id="all-div" class="fstab noscroll">
                  <table id="all-tbl" class="fsr" style="height:85px">
                    <tbody id="all-tbody">
                      <tr>
                        <td>&nbsp;</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </td>
            </tr>
          </table>
        <?php renderBoxEnd() ?>
        <div class="mt5">
          <table id='dia-med' class="w100">
            <tr>
              <td id='td-dia' class='vtop' style="width:38%;">  <!-- diagTile -->
                <?php renderBoxStart("wide min-pad", "", "dia-box", "dia-boxc") ?>
                  <a href="javascript:" onclick="page.pPopDiagnoses()" title="Open this section" class="fscap">Diagnoses</a>
                  <div id="DiagTile"></div>
                <?php renderBoxEnd() ?>
              </td>
              <td class='w5' nowrap='nowrap'></td>
              <td id='td-med' class='vtop' style="width:62%;">  <!-- medTile -->
                <?php renderBoxStart("wide min-pad", "", "med-box") ?>
                  <a href="javascript:" onclick="page.pPopMeds()" title="Open this section" class="fscap">Medications</a>
                  <div id="MedTile"></div>
                <?php renderBoxEnd() ?>
              </td>
            </tr>
          </table>
        </div>
        <div id="hx">  <!-- hxTile -->
          <div class='screen'>
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
              <tr>
               <!-- ****** crs 6/29/2016 <td class='cj' style="width:36%">  ****** crs 6/29/2016 -->
                <td class='cj' style="width:50%">
                  <?php renderBoxStart("wide min-pad mt5", null, "medhx-box") ?>
                    <a href="javascript:" onclick="page.pPopMedSurgHx()" title="Open this section" class="fscap">Medical / Surgical History</a>
                    <table>
                      <tr>
                        <td><div id="fshx-sum" class="hxsum"></div></td>
                      </tr>
                    </table>
                  <?php renderBoxEnd() ?>
                </td>
                <td class='w5' nowrap='nowrap'></td>
              <!-- ****** crs 6/29/2016   <td class='cj' style="width:28%"> ****** crs 6/29/2016 -->
                <td class='cj' style="width:50%">
                  <?php renderBoxStart("wide min-pad mt5", null, "famhx-box") ?>
                    <a href="javascript:" onclick="page.pPopFamHx()" title="Open this section" class="fscap">Family History</a>
                    <table>
                      <tr>
                        <td><div id="famhx-sum" class="hxsum"></div></td>
                      </tr>
                    </table>
                  <?php renderBoxEnd() ?>
                </td>
                <td class='w5' nowrap='nowrap'></td>
                <td class='cj' style="width:36%">
          <!-- ****** crs 6/29/2016
                  <?php renderBoxStart("wide min-pad mt5", null, "sochx-box") ?>
                    <a href="javascript:" onclick="page.pPopSocHx()" class="fscap">Psycho-Social History</a>
                    <table>
                      <tr>
                        <td><div id="sochx-sum" class="hxsum"></div></td>
                      </tr>
                    </table>
                  <?php renderBoxEnd() ?>
             ****** crs 6/29/2016  -->
                </td>
              </tr>
            </table>
          </div>
        </div>
        <div class="mt5 screen">
          <table class="w100">
            <tr>
              <td class='vtop' style="width:50%;">  
                <?php renderBoxStart("wide min-pad", "", "imm-box") ?>
                  <a href="javascript:" onclick="page.pPopImmun()" class="fscap">Immunizations</a>
                  <table>
                    <tr>
                      <td><div id="imm-sum" class="hxsum"></div></td>
                    </tr>
                  </table>
                <?php renderBoxEnd() ?>
              </td>
              <td class='w5' nowrap='nowrap'></td>
              <td class='vtop' style="width:50%;">  
          <!-- ****** crs 6/29/2016
                <?php renderBoxStart("wide min-pad", "", "track-box") ?>
                  <a href="javascript:" onclick="page.pPopTrack()" class="fscap">Order Entry & Tracking</a>
                  <table>
                    <tr>
                      <td><div id="trk-sum" class="hxsum"></div></td>
                    </tr>
                  </table>
                <?php renderBoxEnd() ?>
             ****** crs 6/29/2016  -->
              </td>
            </tr>
          </table>
        </div>
        <div id="hm">  <!-- hmTile -->
          <div class='screen'>
            <?php renderBoxStart("wide min-pad mt5", "", "hm-box") ?>
              <a id="hmcap" href="javascript:" onclick="page.pPopHm()" class="fscap">Clinical Decision Support</a>
              <div id="CdsTile"></div>
            <?php renderBoxEnd() ?>
          </div>
        </div>
        <div class="screen">
          <table class="w100 mt5">
            <tr>
              <td id="td-his-1" class='vtop' style="width:60%;">  <!-- docTile -->
                <?php renderBoxStart("wide min-pad", "", "his-box") ?>
                  <a href="javascript:" onclick="page.pPopDocHx()" class="fscap">Clinical Data and Documentation</a>
                  <div id="DocTile"></div>
                <?php renderBoxEnd() ?>
              </td>
              <td id="td-his-2" class='w5' nowrap='nowrap'></td>
              <td id="td-vit" class='vtop' style="width:40%;">  <!-- vitalTile -->
                <?php renderBoxStart("wide min-pad", "", "vit-box", "vit-boxc") ?>
                  <a href="javascript:" onclick="page.pPopVitals()" class="fscap">Vital Signs</a>
                  <div id="vit-div" class="fstab doc-vit">
                    <table id="vit-tbl" class="fsg">
                      <tbody id="vit-tbody">
                      </tbody>
                    </table>
                  </div>
                <?php renderBoxEnd() ?>
              </td>
            </tr>
          </table>
        </div>
        <table class="w100 mt5">
          <tr>
          <!-- ****** crs 6/29/2016
            <td class='vtop' style="width:30%;"> 
             ****** crs 6/29/2016  -->
            <td class='vtop' style="width:100%;">
              <?php renderBoxStart("wide min-pad", "", "proc-box") ?>
                <a href="javascript:" onclick="page.pPopProcs()" class="fscap">All Procedures / Results</a>
                <div id="ProcTile"></div>
              <?php renderBoxEnd() ?>
            </td>
            <td class='w5' nowrap='nowrap'></td>
            <td class='vtop' style="width:40%;"> 
          <!-- ****** crs 6/29/2016
              <?php renderBoxStart("wide min-pad", "", "portal-box") ?>
                <a href="javascript:" onclick="page.pPopPortal()" class="fscap">Patient Portal Access</a>
                <div id="PortalTile"></div>
              <?php renderBoxEnd() ?>
             ****** crs 6/29/2016  -->
            </td>
            <td class='w5' nowrap='nowrap'></td>
            <td class='vtop' style="width:30%;"> 
          <!-- ****** crs 6/29/2016
              <?php renderBoxStart("wide min-pad", "", "bill-box") ?>
                <a href="javascript:" onclick="page.pPopBilling()" class="fscap">Billing</a>
                <div id="BillingTile"></div>
              <?php renderBoxEnd() ?>
             ****** crs 6/29/2016  -->
            </td>
          </tr>
        </table>
      </div>
      <div id='bottom'><img src='img/brb.png' /></div>
    </div>      
    <div id="pop-cn" class="pop" style='width:600px'>
      <div id="pop-cn-cap" class="pop-cap">
        <div id="pop-cn-cap-text">
          Clicktate - Patient Notepad
        </div>
        <a href="javascript:Pop.close()" class="pop-close"></a>
      </div>
      <div class="pop-content">
        <textarea id="pop-cn-text" class="w100" rows="13"></textarea>
        <div class="pop-cmd">
          <a href="javascript:" onclick="NotepadTile.pSave()" class="cmd save">Save Changes</a>
          <span id="cn-delete-span">
            <span>&nbsp;</span>
            <a href="javascript:" onclick="NotepadTile.pDelete()" class="cmd delete-red">Clear Note</a>
          </span>
          <span>&nbsp;</span>
          <a href="javascript:Pop.close()" class="cmd none">Cancel</a>
        </div>
      </div>
    </div>
    <?php include "inc/ajax-pops/template-pops.php" ?>
    <?php if (! $pop) { ?>
      <?php include "inc/footer.php" ?>
    <?php } else { ?>
    <div id="page-includes"></div>
    <?php } ?>
  </body>
  <?php CONSTANTS('Face') ?>
<script type="text/javascript">
function start() {
  var query = <?=jsonencode($_GP)?>;
  page.load(query);
}
function popUpload() {
  <?php if (1==0 && $login->User->isOnTrial()) { ?>
  alert("This feature is only available for registered users.");
  <?php } else { ?>
  FaceUploadPop.pop(page.fs).bubble('oncomplete', page.refreshFacesheet);
  <?php } ?>
}
</script>      
</html>
