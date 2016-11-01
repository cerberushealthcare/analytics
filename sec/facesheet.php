<?
require_once "inc/requireLogin.php";
require_once "inc/uiFunctions.php";
require_once "php/dao/UserDao.php";
require_once "php/forms/utils/CommonCombos.php";

if ($myLogin->permissions->accessPatients == Permissions::ACCESS_NONE) {
  header("Location: welcome.php");
}
$lu_print = LookupDao::getPrintCustom();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Strict//EN">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <?php renderHead("Facesheet") ?>
    <link rel="stylesheet" type="text/css" href="css/clicktate.css?<?=Version::getUrlSuffix() ?>" />
    <link rel="stylesheet" type="text/css" href="css/schedule.css?<?=Version::getUrlSuffix() ?>" />
    <link rel="stylesheet" type="text/css" href="css/data-tables.css?<?=Version::getUrlSuffix() ?>" />
    <link rel="stylesheet" type="text/css" href="css/facesheet.css?<?=Version::getUrlSuffix() ?>" />
    <link rel="stylesheet" type="text/css" href="css/template-pops.css?<?=Version::getUrlSuffix() ?>" />
    <link rel="stylesheet" type="text/css" href="css/TabBar.css?<?=Version::getUrlSuffix() ?>" />
    <link rel="stylesheet" type="text/css" href="css/pop.css?<?=Version::getUrlSuffix() ?>" />
    <?php if (! $myLogin->vistaFonts || $myLogin->ie == "6") { ?>
    <link rel="stylesheet" type="text/css" href="css/clicktate-font.css?<?=Version::getUrlSuffix() ?>" />
    <link rel="stylesheet" type="text/css" href="css/schedule-font.css?<?=Version::getUrlSuffix() ?>" />
    <link rel="stylesheet" type="text/css" href="css/pop-font.css?<?=Version::getUrlSuffix() ?>" />
    <link rel="stylesheet" type="text/css" href="css/data-tables-font.css?<?=Version::getUrlSuffix() ?>" />
    <link rel="stylesheet" type="text/css" href="css/facesheet-font.css?<?=Version::getUrlSuffix() ?>" />
    <link rel="stylesheet" type="text/css" href="css/template-pops-font.css?<?=Version::getUrlSuffix() ?>" />
    <?php } ?>
<!--[if lte IE 6]>    
    <link rel="stylesheet" type="text/css" href="css/pop-ie6.css?<?=Version::getUrlSuffix() ?>" />
<![endif]-->    
    <script language="JavaScript1.2" src="js/facesheet.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/pop.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/ajax.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/yahoo-min.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/json.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/connection-min.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/ui.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/new-open.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/template-pops.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/engine-download.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/components/TableLoader.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/components/TabBar.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/components/ProfileLoader.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/pops/DocOpener.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/components/TemplateForm.js?<?=Version::getUrlSuffix() ?>"></script>
<style>
@media screen {
  DIV#hm-div {
    margin-bottom:10px;
  }
  DIV.print-only {
    visibility:hidden;
    position:absolute;
    top:-1000px;
    left:-1000px;
  }
  DIV#hx-screen {
    margin-bottom:10px;
  }
  DIV#hx-print {
    visibility:hidden;
    position:absolute;
    top:-1000px;
    left:-1000px;
  }
}
@media print {
  DIV#hm-div {
    display:none;
  }
  DIV#hx-screen {
    display:none;
  }
  A.fscap {
    background:none;
  }
}   
    </style>
  </head>
  <body>
    <div id="curtain"></div>
    <form id="frm" method="post" action="facesheet.php">
      <div id="bodyContainer">
        <?php include "inc/header.php" ?>
        <div class="content">
          <div id="print">
            <table border="0" cellpadding="0" cellspacing="0" style="width:100%">
              <tr>
                <td>
                  <h1>Facesheet for <span style="color:#004B52;" id="h1-name"></span></h1>
                </td>
                <td style="text-align:right; vertical-align:bottom; padding:0 2px 1px 0;">
                  <a href="javascript:window.print()" class="icon big print">Print</a>
                  &nbsp;
                  <a href="javascript:showPatientSelector(2, true);" class="icon big view">Search for patient</a>
                  <!-- <a href="javascript:showCustomProfile()" class="icon big custom">Customize</a> -->
                </td>
              </tr>
            </table>
          </div>
          <div id="fs-refresh" style="display:none">
            <a href="javascript:refreshFs()">Facesheet has been updated. Click to refresh page.</a>
          </div>
          <?php renderBoxStart("wide min-pad") ?>
            <div id="print">
              <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                  <td style="vertical-align:top; padding-right:2px;">
                    <table border="0" cellpadding="0" cellspacing="0">
                      <tr>
                        <td style="width:70%; vertical-align:top">  
                          <div id="portrait">
                            <table border="0" cellpadding="0" cellspacing="0">
                              <tr>
                                <td>
                                </td>
                                <td style="text-align:right">
                                  <a class="fsview" title="Expand this section" href="javascript:" onclick="showDemo()" style="position:relative;top:3px;left:-3px;">Expand</a>
                                </td>
                              </tr>
                            </table>
                            <ul class="entry ro" style="margin:0 0 0 90px;">
                              <li>
                                <label class="first">
                                  ID:
                                </label>
                                <span id="dem-cid" class="ro">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                                <label class="spad">
                                  DOB:
                                </label>
                                <span id="dem-dob" class="ro">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                                <label class="spad">
                                  Age:
                                </label>
                                <span id="dem-age" class="ro">&nbsp;&nbsp;</span>
                              </li>
                              <li>
                                <label id="dem-lbl-addr" class="first">
                                  Address:<br/><br/><br/>
                                </label>
                                <span id="dem-addr" class="ro" style="width:180px"> 
                                  &nbsp;<br/>
                                  &nbsp;<br/>
                                  &nbsp;<br/>
                                  &nbsp;
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
                          </div>
                        </td>
                      </tr>
                    </table>
                  </td>
                  <td id="td-note" style="width:30%;vertical-align:top;">
                    <div id="notepad">
                      <div id="notepad-empty">
                        <a class="pencil" href="javascript:" onclick="javascript:editNotepad()">Add Notes</a>
                      </div>
                      <a id="notepad-text" href="javascript:" onclick="javascript:editNotepad()" title="Edit this note">
                      </a>
                    </div>
                  </td>
                </tr>
              </table>
            </div>
            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-top:10px">
              <tr>
                <td id="td-wf-1" style="vertical-align:top;">
                  <h2 style="margin-bottom:4px">Workflow</h2>
                  <!-- <div style="border:1px solid #c0c0c0; background-color:white; padding:5px;"> -->
                    <table border="0" cellpadding="0" cellspacing="0">
                      <tr> 
                        <td>
                          <div id="wf-appt" class="qpanel">
                          </div>
                        </td>
                      </tr>
                      <tr><td style="height:5px"></td></tr>
                      <tr>
                        <td>
                          <div id="wf-vit" class="qpanel">
                          </div>
                        </td>
                      </tr>
                      <tr><td style="height:5px"></td></tr>
                      <tr>
                        <td>
                          <div class="qpanel">
                            <ul id="wf-doc-ul">
                            </ul>
                          </div>
                        </td>
                      </tr>
                    </table>
                  <!-- </div> -->
                </td>
                <td id="td-wf-2" class="w5"></td>
                <td id="td-all" nowrap="nowrap">
                  <div id="print">
                    <a href="javascript:" onclick="showFspAll()" class="fscap">Active Allergies</a>
                    <div id="all-div" class="fstab noscroll">
                      <table id="all-tbl" class="fsr" style="height:85px">
                        <tbody id="all-tbody">
                          <tr>
                            <td>&nbsp;</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                  <!-- 
                  <div class="pop-cmd" style="margin-top:5px">
                    <a id="all-cmd-add" href="javascript:" class="cmd new disabled" disabled="disabled">Add an Allergy...</a>
                  </div>
                  -->
                </td>
              </tr>
            </table>
          <?php renderBoxEnd() ?>
          <table border="0" cellpadding="0" cellspacing="0" width="100%" class="push">
            <tr>
              <td style="width:38%; vertical-align:top">
                <?php renderBoxStart("wide min-pad", "", "dia-box", "dia-boxc") ?>
                  <div id="print">
                    <a href="javascript:" onclick="showFspDia()" class="fscap">Diagnoses</a>
                    <div id="dia-div" class="fstab noscroll">
                      <table id="dia-tbl" class="fsy">
                        <tbody id="dia-tbody">
                          <tr>
                            <td>&nbsp;</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                <?php renderBoxEnd() ?>
              </td>
              <td style="width:7px;" nowrap="nowrap"></td>
              <td style="width:62%; vertical-align:top">
                <?php renderBoxStart("wide min-pad", "", "med-box") ?>
                  <a href="javascript:" onclick="showFspMed()" class="fscap">Current Meds</a>
                  <div id="med-div" class="fstab noscroll">
                    <table id="med-tbl" class="fsb">
                      <tbody id="med-tbody">
                        <tr>
                          <td>&nbsp;</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                  <!-- 
                  <div class="pop-cmd" style="margin-top:5px">
                    <label>
                      With checked:
                    </label>
                    <a id="med-cmd-toggle" href="javascript:" class="cmd none disabled" disabled="disabled">Set Active/Inactive</a>
                    <a id="med-cmd-rx" href="javascript:" class="cmd none disabled" disabled="disabled">Refill RX</a>
                    <span>&nbsp;</span>
                    <span>&nbsp;</span>
                    <span>&nbsp;</span>
                    <a id="med-cmd-add" href="javascript:showMed()" class="cmd new disabled" disabled="disabled">Add a Med...</a>
                    <span>&nbsp;</span>
                    <span>&nbsp;</span>
                    <span>&nbsp;</span>
                    <span>&nbsp;</span>
                  </div>
                  -->
                <?php renderBoxEnd() ?>
              </td>
            </tr>
          </table>
          <div id="hx-screen">
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
              <tr>
                <td style="width:36%; text-align:center">
                  <?php renderBoxStart("wide min-pad push", null, "medhx-box") ?>
                    <a href="javascript:" onclick="showFspHx(0)" class="fscap">Medical / Surgical History</a>
                    <table>
                      <tr>
                        <td><div id="fshx-sum" class="hxsum"></div></td>
                      </tr>
                    </table>
                  <?php renderBoxEnd() ?>
                </td>
                <td style="width:5px" nowrap="nowrap"></td>
                <td style="width:28%; text-align:center">
                  <?php renderBoxStart("wide min-pad push", null, "famhx-box") ?>
                    <a href="javascript:" onclick="showFspFamhx()" class="fscap">Family History</a>
                    <table>
                      <tr>
                        <td><div id="famhx-sum" class="hxsum"></div></td>
                      </tr>
                    </table>
                  <?php renderBoxEnd() ?>
                </td>
                <td style="width:5px" nowrap="nowrap"></td>
                <td style="width:36%; text-align:center">
                  <?php renderBoxStart("wide min-pad push", null, "sochx-box") ?>
                    <a href="javascript:" onclick="showFspSochx()" class="fscap">Psycho-Social History</a>
                    <table>
                      <tr>
                        <td><div id="sochx-sum" class="hxsum"></div></td>
                      </tr>
                    </table>
                  <?php renderBoxEnd() ?>
                </td>
              </tr>
            </table>
          </div>
          <div id="print" class="print-only">
            <h2 id="medhx-prt-h2" style="margin-top:10px">Medical History</h2>
            <div id="medhx-prt-div" class="fstab noscroll">
              <table class="fsp single">
                <tbody id="medhx-prt-tbody">
                </tbody>
              </table>
            </div>
          </div>
          <div id="print" class="print-only">
            <h2 id="surghx-prt-h2" style="margin-top:10px">Surgical History</h2>
            <div id="surghx-prt-div" class="fstab noscroll">
              <table class="fsb single">
                <tbody id="surghx-prt-tbody">
                </tbody>
              </table>
            </div>
          </div>
          <div id="print" class="print-only">
            <h2 id="sochx-prt-h2" style="margin-top:10px">Psycho-Social History</h2>
            <div id="sochx-prt-div" class="fstab noscroll">
              <table class="fsy single">
                <tbody id="sochx-prt-tbody">
                </tbody>
              </table>
            </div>
          </div>
          <?php renderBoxStart("wide min-pad push", "", "hm-box") ?>
            <div id="print" class="no-widow">
              <a id="hmcap" href="javascript:" onclick="showFspHm()" class="fscap">Health Maintenance / Recurring Tests / Procedures</a>
            </div>
            <div id="hm-div" class="fstab noscroll nbb">
              <table id="hm-tbl" class="fsp single grid">
                <thead>
                  <tr class="head">
                    <th>Test/Procedure</th>
                    <th>Last&nbsp;Date</th>
                    <th style="width:50%">Last Results</th>
                    <th>Next&nbsp;Due</th>
                  </tr>
                </thead>
                <tbody id="hm-tbody">
                  <tr>
                    <td colspan="4">&nbsp;</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div id="print" class="print-only">
              <div id="hmprt-div" class="fstab noscroll">
                <table class="fsp">
                  <tbody id="hmprt-tbody">
                    <tr>
                      <td>&nbsp;</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <!-- 
            <div id="hm-cmd" class="pop-cmd" style="margin-top:5px">
              <a href="javascript:editHm()" class="cmd new disabled" disabled="disabled">Add Health Maintenance...</a>
            </div>
            -->
          <?php renderBoxEnd() ?>
          <table border="0" cellpadding="0" cellspacing="0" width="100%" class="push">
            <tr>
              <td id="td-his-1" style="width:60%; vertical-align:top">
                <?php renderBoxStart("wide min-pad", "", "his-box") ?>
                  <a href="javascript:" onclick="showFspHis()" class="fscap">Documentation / Visit History</a>
                  <div id="his-div" class="fstab doc-vit">
                    <table id="his-tbl" class="fsgr single">
                      <tbody id="his-tbody">
                      </tbody>
                    </table>
                  </div>
                <?php renderBoxEnd() ?>
              </td>
              <td id="td-his-2" style="width:7px;" nowrap="nowrap"></td>
              <td id="td-vit" style="width:40%; vertical-align:top">
                <?php renderBoxStart("wide min-pad", "", "vit-box", "vit-boxc") ?>
                  <div id="print" class="no-widow">
                    <a href="javascript:" onclick="showFspVit()" class="fscap">Vitals</a>
                  </div>
                  <div id="vit-div" class="fstab doc-vit">
                    <div id="print">
                      <table id="vit-tbl" class="fsg">
                        <tbody id="vit-tbody">
                        </tbody>
                      </table>
                    </div>
                  </div>
                <?php renderBoxEnd() ?>
              </td>
            </tr>
          </table>
        </div>
      </div>      
      <div id="fsp-med" class="pop" onmousedown="event.cancelBubble = true">
        <div id="fsp-med-cap" class="pop-cap">
          <div id="fsp-med-cap-text">
            Medications
          </div>
          <a href="javascript:closeOverlayPop()" class="pop-close"></a>
        </div>
        <div class="pop-content">
          <div class="pop-frame">
            <h1>Current Medications</h1>
            <div class="pop-frame-content" style="width:700px">
              <div id="fsp-med-div" class="fstab" style="height:137px">
                <table id="fsp-med-tbl" class="fsb single">
                  <tbody id="fsp-med-tbody">
                    <tr>
                      <td>&nbsp;</td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                  <td nowrap="nowrap">
                    <div class="pop-cmd">
                      <label>
                        With checked:
                      </label>
                      <a id="med-cmd-toggle" href="javascript:deleteMeds()" class="cmd delete-red">Remove from List</a>
                    </div>
                  </td>
                  <td style="width:100%">
                    <div class="pop-cmd cmd-right">
                      <a id="med-cmd-rx" href="javascript:fsRx()" class="cmd rx">Print...</a>
                      <span>&nbsp;</span>
                      <a id="med-cmd-add" href="javascript:showMed()" class="cmd new">Add a Med...</a>
                    </div>
                  </td>
                </tr>
              </table>
            </div>
          </div>
          <div class="pop-frame" style="margin-top:10px;">
            <h1>History</h1>
            <div class="pop-frame-content" style="width:700px">
              <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                  <td style="vertical-align:top;">
                    <ul id="medh-filter-ul" class="filter fwidth" style="width:110px"></ul>
                  </td>
                  <td style="padding-left:5px; vertical-align:top; width:100%">
                    <div id="fsp-medh-div-1" class="fstab" style="height:260px;">
                      <table id="fsp-medh-tbl-1" class="fsb single">
                        <thead>
                          <tr class="fixed head">
                            <th>Date</th>
                            <th>Source</th>
                            <th>Action: Medication</th>
                            <th>RX</th>
                          </tr>
                        </thead>
                        <tbody id="fsp-medh-tbody-1">
                        </tbody>
                      </table>
                    </div>
                    <div id="fsp-medh-div-2" class="fstab" style="height:260px; display:none">
                      <table id="fsp-medh-tbl-2" class="fsb single">
                        <thead>
                          <tr class="fixed head">
                            <th>Medication</th>
                            <th>Date</th>
                            <th>Action</th>
                            <th>Source</th>
                            <th>RX</th>
                          </tr>
                        </thead>
                        <tbody id="fsp-medh-tbody-2">
                        </tbody>
                      </table>
                    </div>
                  </td>
                </tr>
              </table>
            </div>
            <div class="pop-cmd cmd-right">
              <a href="javascript:closeOverlayPop()" class="cmd none">&nbsp;&nbsp;&nbsp;Exit&nbsp;&nbsp;&nbsp;</a>
            </div>
          </div>
        </div>
      </div>
      <div id="fsp-all" class="pop" onmousedown="event.cancelBubble = true">
        <div id="fsp-all-cap" class="pop-cap">
          <div id="fsp-all-cap-text">
            Allergies
          </div>
          <a href="javascript:closeOverlayPop()" class="pop-close"></a>
        </div>
        <div class="pop-content">
          <div class="pop-frame">
            <h1>Active Allergies</h1>
            <div class="pop-frame-content" style="width:700px">
              <div id="fsp-all-div" class="fstab" style="height:137px">
                <table id="fsp-all-tbl" class="fsr single">
                  <tbody id="fsp-all-tbody">
                    <tr>
                      <td>&nbsp;</td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                  <td nowrap="nowrap">
                    <div class="pop-cmd">
                      <label>
                        With checked:
                      </label>
                      <a id="all-cmd-toggle" href="javascript:deleteAllergies()" class="cmd delete-red">Remove from List</a>
                    </div>
                  </td>
                  <td style="width:100%">
                    <div class="pop-cmd cmd-right">
                      <a id="all-cmd-add" href="javascript:editAllergy()" class="cmd new">Add an Allergy...</a>
                    </div>
                  </td>
                </tr>
              </table>
            </div>
          </div>
          <div class="pop-frame" style="margin-top:10px">
            <h1>Documented History</h1>
            <div class="pop-frame-content" style="width:700px">
              <div id="fsp-allh-div" class="fstab" style="height:200px">
                <table id="fsp-allh-tbl" class="fsr single">
                  <thead>
                    <tr id="allh-head" class="fixed head">
                      <th>Date</th>
                      <th>Document</th>
                      <th>Agent: Reactions</th>
                    </tr>
                  </thead>
                  <tbody id="fsp-allh-tbody">
                  </tbody>
                </table>
              </div>
            </div>
            <div class="pop-cmd cmd-right">
              <a href="javascript:closeOverlayPop()" class="cmd none">&nbsp;&nbsp;Exit&nbsp;&nbsp;</a>
            </div>
          </div>
        </div>
      </div>
      <div id="fsp-dia" class="pop" onmousedown="event.cancelBubble = true">
        <div id="fsp-dia-cap" class="pop-cap">
          <div id="fsp-dia-cap-text">
            Diagnoses
          </div>
          <a href="javascript:closeOverlayPop()" class="pop-close"></a>
        </div>
        <div class="pop-content" style="width:700px">
          <div class="pop-frame">
            <h1>Active Diagnoses</h1>
            <div class="pop-frame-content">
              <div id="fsp-dia-div" class="fstab" style="height:137px">
                <table id="fsp-dia-tbl" class="fsy single">
                  <tbody id="fsp-dia-tbody">
                    <tr>
                      <td>&nbsp;</td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                  <td nowrap="nowrap">
                    <div class="pop-cmd">
                      <label>
                        With checked:
                      </label>
                      <a id="dia-cmd-toggle" href="javascript:deleteDia()" class="cmd delete-red">Remove from List</a>
                    </div>
                  </td>
                  <td style="width:100%">
                    <div class="pop-cmd cmd-right">
                      <a id="dia-cmd-add" href="javascript:editDiagnosis()" class="cmd new">Add a Diagnosis...</a>
                    </div>
                  </td>
                </tr>
              </table>
            </div>
          </div>
          <div class="pop-frame" style="margin-top:10px">
            <h1>Documented History</h1>
            <div class="pop-frame-content">
              <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                  <td style="vertical-align:top">
                    <ul id="diah-filter-ul" class="filter fwidth"></ul>
                  </td>
                  <td style="padding-left:5px; vertical-align:top">
                    <div id="fsp-diah-div" class="fstab" style="height:240px">
                      <table id="fsp-diah-tbl" class="fsy single">
                        <thead>
                          <tr id="diah-head" class="fixed head">
                            <th>Date</th>
                            <th>Document</th>
                            <th>Diagnosis</th>
                          </tr>
                        </thead>
                        <tbody id="fsp-diah-tbody">
                        </tbody>
                      </table>
                    </div>
                  </td>
                </tr>
              </table>
            </div>
            <div class="pop-cmd cmd-right">
              <a href="javascript:closeOverlayPop()" class="cmd none">&nbsp;&nbsp;Exit&nbsp;&nbsp;</a>
            </div>
          </div>
        </div>
      </div>
      <div id="fsp-his" class="pop" onmousedown="event.cancelBubble = true">
        <div id="fsp-his-cap" class="pop-cap">
          <div id="fsp-his-cap-text">
            Documentation/Visit History
          </div>
          <a href="javascript:closeOverlayPop()" class="pop-close"></a>
        </div>
        <div class="pop-content" style="width:720px;padding:0">
          <div class="tabbar">
          </div>
          <div class="tabpanels">
            <div class="tabpanel"> 
              <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                  <td style="width:150px; vertical-align:top">
                    <ul id="his-filter-ul" class="filter"></ul>
                  </td>
                  <td style="padding-left:5px; vertical-align:top">
                    <div id="fsp-his-div" class="fstab" style="height:420px">
                      <table id="fsp-his-tbl" class="fsgr single grid">
                        <thead>
                          <tr id="fsp-his-head" class="fixed head">
                            <th>Date</th>
                            <th style="width:80%">Document</th>
                          </tr>
                        </thead>
                        <tbody id="fsp-his-tbody">
                        </tbody>
                      </table>
                    </div>
                  </td>
                </tr>
              </table>
            </div>
            <div class="tabpanel"> 
              <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                  <td style="width:150px; vertical-align:top">
                    <ul id="hisa-filter-ul" class="filter"></ul>
                  </td>
                  <td style="padding-left:5px; vertical-align:top">
                    <div id="fsp-hisa-div" class="fstab" style="height:420px">
                      <table id="fsp-hisa-tbl" class="fsgr single grid">
                        <thead>
                          <tr id="fsp-hisa-head" class="fixed head">
                            <th>Date</th>
                            <th style="width:80%">Appointment</th>
                          </tr>
                        </thead>
                        <tbody id="fsp-hisa-tbody">
                        </tbody>
                      </table>
                    </div>
                  </td>
                </tr>
              </table>
            </div>
            <div class="tabpanel"> 
              <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                  <td style="width:150px; vertical-align:top">
                    <ul id="hism-filter-ul" class="filter"></ul>
                  </td>
                  <td style="padding-left:5px; vertical-align:top">
                    <div id="fsp-hism-div" class="fstab" style="height:420px">
                      <table id="fsp-hism-tbl" class="fsgr single grid">
                        <thead>
                          <tr id="fsp-hism-head" class="fixed head">
                            <th>Date</th>
                            <th style="width:80%">Subject</th>
                          </tr>
                        </thead>
                        <tbody id="fsp-hism-tbody">
                        </tbody>
                      </table>
                    </div>
                  </td>
                </tr>
              </table>
            </div>
            <div class="pop-cmd cmd-right">
              <a href="javascript:closeOverlayPop()" class="cmd none">&nbsp;&nbsp;Exit&nbsp;&nbsp;</a>
            </div>
          </div>
        </div>
      </div>
      <div id="fsp-hx" class="pop" onmousedown="event.cancelBubble = true">
        <div id="fsp-hx-cap" class="pop-cap" style="width:770px">
          <div id="fsp-hx-cap-text">
            History
          </div>
          <a href="javascript:closeOverlayPop()" class="pop-close"></a>
        </div>
        <div class="pop-content" style="width:772px;padding:0">
          <div class="tabbar">
            <table border="0" cellpadding="0" cellspacing="0" style="width:100%">
              <tr>
                <th>
                  <h2>Medical History</h2>
                  <h2>Surgical History</h2>
                  <h2>Family History</h2>
                  <h2>Psycho-Social History</h2>
                </th>
                <td style="text-align:right">
                  <a href="javascript:showTab('fsp-hx',0)">Medical</a>
                  &nbsp;&bull;&nbsp;
                  <a href="javascript:showTab('fsp-hx',1)">Surgical</a>
                  &nbsp;&bull;&nbsp;
                  <a href="javascript:showTab('fsp-hx',2)">Family</a>
                  &nbsp;&bull;&nbsp;
                  <a href="javascript:showTab('fsp-hx',3)">Psycho-Social</a>
                </td>
              </tr>
            </table>
          </div>
          <div class="tabpanels">
            <div class="tabpanel">
              <div id="fsp-medhx-div" class="fstab" style="margin-top:0;height:400px;">
                <table class="fsp single grid">
                  <thead>
                    <tr class="head">
                      <th style="width:35%">Diagnosis</th>
                      <th>Date</th>
                      <th style="width:25%">Type</th>
                      <th style="width:25%">Treatment</th>
                    </tr>
                  </thead>
                  <tbody id="fsp-medhx-tbody">
                  </tbody>
                </table>
              </div>
              <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                  <td>
                    <div class="pop-cmd cmd-right">
                      <a href="javascript:getProcQuestion(fs.medhx)" class="cmd new">Add Diagnosis...</a>
                      <span>&nbsp;</span>
                      <a href="javascript:closeOverlayPop()" class="cmd none">&nbsp;&nbsp;Exit&nbsp;&nbsp;</a>
                    </div>
                  </td>
                </tr>
              </table>
            </div>
            <div class="tabpanel">
              <div id="fsp-surghx-div" class="fstab" style="margin-top:0;height:400px;">
                <table class="fsb single grid">
                  <thead>
                    <tr class="fixed head">
                      <th style="width:35%">Procedure</th>
                      <th>Date</th>
                      <th style="width:50%">Type</th>
                    </tr>
                  </thead>
                  <tbody id="fsp-surghx-tbody">
                  </tbody>
                </table>
              </div>
              <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                  <td>
                    <div class="pop-cmd cmd-right">
                      <a href="javascript:getProcQuestion(fs.surghx)" class="cmd new">Add Procedure...</a>
                      <span>&nbsp;</span>
                      <a href="javascript:closeOverlayPop()" class="cmd none">&nbsp;&nbsp;Exit&nbsp;&nbsp;</a>
                    </div>
                  </td>
                </tr>
              </table>
            </div>
            <div class="tabpanel">
              <div id="fsp-famhx-div" class="fstab" style="margin-top:0;height:400px;">
                <table id="fsp-famhx-tbl" class="fsgr grid">
                  <thead>
                    <tr id="fsp-famhx-head" class="fixed head">
                      <th>Relative</th>
                      <th style="text-align:right">&nbsp;</th>
                      <th style="width:70%">&nbsp;</th>
                    </tr>
                  </thead>
                  <tbody id="fsp-famhx-tbody">
                  </tbody>
                </table>
              </div>
              <div class="pop-cmd cmd-right">
                <a href="javascript:getFamQuestion()" class="cmd new">Add Relative...</a>
                <span>&nbsp;</span>
                <a href="javascript:closeOverlayPop()" class="cmd none">&nbsp;&nbsp;Exit&nbsp;&nbsp;</a>
              </div>
            </div>
            <div class="tabpanel">
              <div id="fsp-sochx-div" class="fstab" style="margin-top:0;height:400px;">
                <table id="fsp-sochx-tbl" class="fsy grid">
                  <thead>
                    <tr id="fsp-sochx-head" class="fixed head">
                      <th>Topic</th>
                      <th style="text-align:right">&nbsp;</th>
                      <th style="width:70%">&nbsp;</th>
                    </tr>
                  </thead>
                  <tbody id="fsp-sochx-tbody">
                  </tbody>
                </table>
              </div>
              <div class="pop-cmd cmd-right">
                <a href="javascript:closeOverlayPop()" class="cmd none">&nbsp;&nbsp;Exit&nbsp;&nbsp;</a>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div id="pop-hxe" class="pop" onmousedown="event.cancelBubble = true">
        <div id="pop-hxe-cap" class="pop-cap">
          <div id="pop-hxe-cap-text">
            Clicktate - History Entry
          </div>
          <a href="javascript:closeOverlayPop()" class="pop-close"></a>
        </div>
        <div class="pop-content" style="width:640px">
          <ul class="entry">
            <li style="margin-bottom:1em">
              <h4 id="hxe-proc"></h4>
            </li>
          </ul>
          <ul id="ul-hxe-fields" class="entry">
          </ul>
          <div class="pop-cmd push">
            <a href="javascript:hxeSave()" class="cmd save">Save Changes</a>
            <span id="hxe-delete-span">
              <span>&nbsp;</span>
              <a href="javascript:hxeDelete()" class="cmd delete-red">Delete</a>
            </span>
            <span>&nbsp;</span>
            <a href="javascript:closeOverlayPop()" class="cmd none">Cancel</a>
          </div>
          <div id="pop-hxe-errors" class="pop-error" style="display:none"></div>
        </div>
      </div>      
      <div id="pop-fhxe" class="pop" onmousedown="event.cancelBubble = true">
        <div id="pop-fhxe-cap" class="pop-cap">
          <div id="pop-fhxe-cap-text">
            Clicktate - Family History Entry
          </div>
          <a href="javascript:closeOverlayPop()" class="pop-close"></a>
        </div>
        <div class="pop-content" style="width:640px">
          <ul class="entry">
            <li style="margin-bottom:1em">
              <h4 id="fhxe-rel"></h4>
            </li>
          </ul>
          <ul id="ul-fhxe-fields" class="entry">
          </ul>
          <div class="pop-cmd push">
            <a href="javascript:fhxeSave()" class="cmd save">Save Changes</a>
            <span id="fhxe-delete-span">
              <span>&nbsp;</span>
              <a href="javascript:fhxeDelete()" class="cmd delete-red">Delete</a>
            </span>
            <span>&nbsp;</span>
            <a href="javascript:closeOverlayPop()" class="cmd none">Cancel</a>
          </div>
          <div id="pop-fhxe-errors" class="pop-error" style="display:none"></div>
        </div>
      </div>      
      <div id="pop-she" class="pop" onmousedown="event.cancelBubble = true">
        <div id="pop-she-cap" class="pop-cap">
          <div id="pop-she-cap-text">
            Clicktate - Social History Entry
          </div>
          <a href="javascript:sheClose()" class="pop-close"></a>
        </div>
        <div class="pop-content" style="width:550px">
          <ul class="entry">
            <li style="margin-bottom:1em">
              <h4 id="she-topic">Topic</h4>
            </li>
          </ul>
          <ul id="ul-she-fields" class="entry">
          </ul>
          <div class="pop-cmd push">
            <a href="javascript:sheSave()" class="cmd save">Save Changes</a>
            <span>&nbsp;</span>
            <a href="javascript:sheClose()" class="cmd none">Cancel</a>
          </div>
          <div id="pop-she-errors" class="pop-error" style="display:none"></div>
        </div>
      </div>
      <div id="fsp-hm" class="pop" onmousedown="event.cancelBubble = true">
        <div id="fsp-hm-cap" class="pop-cap">
          <div id="fsp-hm-cap-text">
            Health Maintenance
          </div>
          <a href="javascript:closeOverlayPop()" class="pop-close"></a>
        </div>
        <div class="pop-content" style="width:720px">
          <table border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
              <td style="width:170px; vertical-align:top">
                <ul id="hm-filter-ul" class="filter"></ul>
                <div class="pop-cmd" style="margin:5px 0 0 18px;padding:0;text-align:left;">
                  <a href="javascript:showProcPick()" class="cmd new">Add...</a>
                </div>
              </td>
              <td style="padding-left:5px">
                <div id="hm-one">
                  <div class="pop-frame-content" style="margin-top:5px;">
                    <table border="0" cellpadding="0" cellspacing="0" style="width:100%">
                      <tr> 
                        <td>
                          <h4 id="hm-one-proc"></h4>
                          <span id="hm-one-proc-desc" style="color:#494949"></span>
                        </td>
                        <td style="vertical-align:top">
                          <div class="pop-cmd cmd-right" id="fsp-hm-deactivate" style="margin:0;padding:0">
                            <a href="javascript:deleteBlankHm()" class="cmd delete-red">Remove Test/Procedure</a>
                          </div>
                        </td>
                      </tr>
                    </table>
                    <ul id="hm-face-entry" class="entry q">
                      <li>
                        <span id="hme-next-info" class="warn">
                        </span>
                      </li>
                      <li class="push2">
                        <label>Interval</label>
                        <span class="q qd"><a id="hme-interval" href="javascript:" onclick="editHmeInterval()" class="df">_______</a></span>
                        <label>Next Due</label>
                        <?php renderQuestionField("hme-next-due", "hmNextDateCallback", null, "q qd") ?>
                      </li>
                    </ul>
                  </div>
                </div>
                <div id="fsp-hma-1" class="push5">
                  <div id="fsp-hma-div" class="fstab" style="height:360px;">
                    <table id="fsp-hma-tbl" class="fsp single grid">
                      <thead>
                        <tr id="fsp-hma-head" class="fixed head">
                          <th>Test/Procedure</th>
                          <th>Last&nbsp;Date</th>
                          <th style="width:50%">Last Results</th>
                          <th>Next&nbsp;Due</th>
                        </tr>
                      </thead>
                      <tbody id="fsp-hma-tbody">
                      </tbody>
                    </table>
                  </div>
                  <table border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                      <td>
                        <div class="pop-cmd cmd-right">
                          <a id="hma-cmd-add" href="javascript:showProcPick()" class="cmd new">Add a Test/Procedure...</a>
                          <span>&nbsp;</span>
                          <a href="javascript:closeOverlayPop()" class="cmd none">&nbsp;&nbsp;Exit&nbsp;&nbsp;</a>
                        </div>
                      </td>
                    </tr>
                  </table>
                </div>
                <div id="fsp-hma-2" class="push5 pop-frame-content">
                  <h2>History</h2>
                  <div id="fsp-hm-div" class="fstab" style="height:200px;">
                    <table id="fsp-hm-tbl" class="fsp single grid">
                      <thead>
                        <tr id="fsp-hm-head" class="fixed head">
                          <th class="check">&nbsp;</th>
                          <th>&nbsp;</th>
                          <th>Date</th>
                          <th style="width:50%">Results</th>
                        </tr>
                      </thead>
                      <tbody id="fsp-hm-tbody">
                      </tbody>
                    </table>
                  </div>
                  <table border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                      <td nowrap="nowrap">
                        <div class="pop-cmd">
                          <span id="fsp-hm-delete">
                            <label>
                              With checked:
                            </label>
                            <a id="hm-cmd-toggle" href="javascript:deleteHms()" class="cmd delete-red">Delete</a>
                          </span>
                        </div>
                      </td>
                      <td style="width:100%">
                        <div class="pop-cmd cmd-right">
                          <a id="hm-cmd-add" href="javascript:editHm()" class="cmd note">Add History Item...</a>
                          <span>&nbsp;</span>
                          <a href="javascript:setFspHmProc()" class="cmd none">&nbsp;Return&nbsp;</a>
                        </div>
                      </td>
                    </tr>
                  </table>
                </div>
              </td>
            </tr>
          </table>
        </div>
      </div>
      <div id="fsp-hmcint" class="pop" onmousedown="event.cancelBubble = true">
        <div id="fsp-hmcint-cap" class="pop-cap">
          <div id="fsp-hmcint-cap-text">
            Set Interval
          </div>
          <a href="javascript:closeOverlayPop()" class="pop-close"></a>
        </div>
        <div class="pop-content">
          <ul class="entry">
            <li>
              <label>Every</label>
              <input id="hmcint-every" type="text" size="1" />
              <select id="hmcint-int">
              </select>
            </li>
          </ul>
          <div class="pop-cmd" style="margin-top:20px">
            <a href="javascript:hmcintOk()" class="cmd ok">OK</a>
            <span>&nbsp;</span>
            <a class="cmd delete" href="javascript:hmcintClear()">Clear (Use Default)</a>
            <span>&nbsp;</span>
            <a href="javascript:closeOverlayPop()" class="cmd none">Cancel</a>
          </div>
        </div>
      </div> 
      <div id="fsp-hmcp" class="pop" onmousedown="event.cancelBubble = true">
        <div id="fsp-hmcp-cap" class="pop-cap">
          <div id="fsp-hmcp-cap-text">
            Tests/Procedure Customization
          </div>
          <a href="javascript:closeOverlayPop()" class="pop-close"></a>
        </div>
        <div class="pop-content" style="width:720px">
          <div id="hmcp-div" class="fstab" style="height:410px">
            <table id="hmcp-tbl" class="fsgr grid smallpad">
              <thead>
                <tr class="fixed head bottom">
                  <th>Active</th>
                  <th>Name</th>
                  <th class="center">Auto<br/>Apply</th>
                  <th class="center">Gender</th>
                  <th class="center">Age<br/>Start</th>
                  <th class="center">Age<br/>Up To</th>
                  <th class="center">Frequency</th>
                </tr>
              </thead>
              <tbody id="hmcp-tbody" onclick="hmcpClick()">
              </tbody>
            </table>
          </div>
          <div class="pop-cmd">
            <a href="javascript:hmcpSave()" class="cmd save">Save Changes</a>
            <span>&nbsp;</span>
            <!-- <a href="javascript:hmcpReset()" class="cmd none">Reset to Default</a>
            <span>&nbsp;</span> -->
            <a href="javascript:closeOverlayPop()" class="cmd none">Cancel</a>
          </div>
        </div>
      </div>
      <div id="fsp-hcp" class="pop" onmousedown="event.cancelBubble = true">
        <div id="fsp-hcp-cap" class="pop-cap">
          <div id="fsp-hcp-cap-text">
            Customize History Items
          </div>
          <a href="javascript:closeOverlayPop()" class="pop-close"></a>
        </div>
        <div class="pop-content" style="width:350px">
          <div id="hcp-div" class="fstab" style="height:410px">
            <table id="hcp-tbl" class="fsgr grid smallpad">
              <thead>
                <tr class="fixed head bottom">
                  <th>Active</th>
                  <th>Name</th>
                </tr>
              </thead>
              <tbody id="hcp-tbody" onclick="hcpClick()">
              </tbody>
            </table>
          </div>
          <div class="pop-cmd">
            <a href="javascript:hcpSave()" class="cmd save">Save Changes</a>
            <span>&nbsp;</span>
            <!-- <a href="javascript:hcpReset()" class="cmd none">Reset to Default</a>
            <span>&nbsp;</span> -->
            <a href="javascript:closeOverlayPop()" class="cmd none">Cancel</a>
          </div>
        </div>
      </div>
      <div id="fsp-vit" class="pop" onmousedown="event.cancelBubble = true">
        <div id="fsp-vit-cap" class="pop-cap">
          <div id="fsp-vit-cap-text">
            Vitals
          </div>
          <a href="javascript:closeOverlayPop()" class="pop-close"></a>
        </div>
        <div class="pop-content" style="width:700px">
          <div id="fsp-vith-div" class="fstab" style="height:300px">
            <table id="fsp-vith-tbl" class="fsg single grid bigpad">
              <thead>
                <tr class="fixed head">
                  <th>Date</th>
                  <th>Pulse</th>
                  <th>Resp</th>
                  <th>BP</th>
                  <th>Temp</th>
                  <th>Wt</th>
                  <th>Ht</th>
                  <th>BMI</th>
                  <th>WC</th>
                  <th>HC</th>
                  <th>O2</th>
                </tr>
              </thead>
              <tbody id="fsp-vith-tbody">
                <tr>
                  <td colspan="11">&nbsp;</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="pop-cmd cmd-right">
            <a id="vit-cmd-add" href="javascript:editVitals()" class="cmd new">Add Today's Vitals...</a>
            <span>&nbsp;</span>
            <a href="javascript:closeOverlayPop()" class="cmd none">&nbsp;&nbsp;Exit&nbsp;&nbsp;</a>
          </div>
        </div>
      </div>
      <div id="pop-cn" class="pop" onmousedown="event.cancelBubble = true">
        <div id="pop-cn-cap" class="pop-cap">
          <div id="pop-cn-cap-text">
            Clicktate - Patient Notepad
          </div>
          <a href="javascript:closeOverlayPop()" class="pop-close"></a>
        </div>
        <div class="pop-content">
          <textarea id="pop-cn-text" rows="10"></textarea>
          <div class="pop-cmd">
            <a href="javascript:cnSave()" class="cmd save">Save Changes</a>
            <span id="cn-delete-span">
              <span>&nbsp;</span>
              <a href="javascript:cnDelete()" class="cmd delete-red">Clear Note</a>
            </span>
            <span>&nbsp;</span>
            <a href="javascript:closeOverlayPop()" class="cmd none">Cancel</a>
          </div>
        </div>
      </div>
      <div id="pop-de" class="pop" onmousedown="event.cancelBubble = true">
        <div id="pop-de-cap" class="pop-cap">
          <div id="pop-de-cap-text">
            Clicktate - Diagnosis Entry
          </div>
          <a href="javascript:closeOverlayPop()" class="pop-close"></a>
        </div>
        <div class="pop-content">
          <div class="pop-frame">
            <h1>Active Diagnosis</h1>
            <div class="pop-frame-content" style="width:680px">
              <ul class="entry">
                <li>
                  <label class="first">Description</label>
                  <input id="de-desc" type="text" size="80" />
                  <a href="javascript:deIcd()" class="find">Lookup...</a>
                </li>
                <li>
                  <label class="first">ICD</label>
                  <input id="de-icd" type="text" size="5" />
                </li>
              </ul>
            </div>
          </div>
          <div class="pop-cmd">
            <a href="javascript:deSave()" class="cmd save">Save Changes</a>
            <span id="de-delete-span">
              <span>&nbsp;</span>
              <a href="javascript:deDelete()" class="cmd delete-red">Delete</a>
            </span>
            <span>&nbsp;</span>
            <a href="javascript:closeOverlayPop()" class="cmd none">Cancel</a>
          </div>
        </div>
      </div>
      <div id="pop-ve" class="pop" onmousedown="event.cancelBubble = true">
        <div id="pop-ve-cap" class="pop-cap">
          <div id="pop-ve-cap-text">
            Clicktate - Vitals Entry
          </div>
          <a href="javascript:closeOverlayPop()" class="pop-close"></a>
        </div>
        <div class="pop-content" style="width:660px">
          <ul class="entry q">
            <li>
              <label class="first">Date</label>
              <?php renderCalendar("ve-date") ?>
            </li>
          </ul>
          <ul id="ul-ve-fields" class="entry">
          </ul>
          <div class="pop-cmd push">
            <a href="javascript:veSave()" class="cmd save">Save Changes</a>
            <span id="ve-delete-span">
              <span>&nbsp;</span>
              <a href="javascript:veDelete()" class="cmd delete-red">Delete</a>
            </span>
            <span>&nbsp;</span>
            <a href="javascript:closeOverlayPop()" class="cmd none">Cancel</a>
          </div>
        </div>
      </div>
      <div id="pop-pp" class="pop" onmousedown="event.cancelBubble = true">
        <div id="pop-pp-cap" class="pop-cap">
          <div>
            Clicktate - Test/Procedure Selection
          </div>
          <a href="javascript:closeOverlayPop()" class="pop-close"></a>
        </div>
        <div class="pop-content">
          <div class="pop-frame">
            <div class="pop-frame-head">
              <h1>Select a Test/Procedure</h1>
              <a class="pencil custom" href="javascript:showCustomProc()">Customize</a>
            </div>
            <div class="pop-frame-content" style="width:600px">
              <div id="pp-div" class="fstab" style="height:360px">
                <table id="pp-tbl" class="fsgr grid">
                  <thead>
                    <tr class="fixed head">
                      <th>Name</th>
                      <th>Auto?</th>
                      <th>Gender</th>
                      <th>Age Range</th>
                      <th>Frequency</th>
                    </tr>
                  </thead>
                  <tbody id="pp-tbody">
                  </tbody>
                </table>
              </div>
              <div class="pop-cmd">
                <a href="javascript:closeOverlayPop()" class="cmd none">Cancel</a>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div id="pop-hme" class="pop" onmousedown="event.cancelBubble = true">
        <div id="pop-hme-cap" class="pop-cap">
          <div id="pop-hme-cap-text">
            Clicktate - Health Maintenance Entry
          </div>
          <a href="javascript:closeOverlayPop()" class="pop-close"></a>
        </div>
        <div class="pop-content" style="width:650px">
          <ul class="entry q">
            <li>
              <h4 id="hme-proc"></h4>
            </li>
          </ul>
          <ul id="ul-hme-fields" class="entry">
          </ul>
          <div class="pop-cmd push">
            <a href="javascript:hmeSave()" class="cmd save">Save Changes</a>
            <span id="hme-delete-span">
              <span>&nbsp;</span>
              <a href="javascript:hmeDelete()" class="cmd delete-red">Delete</a>
            </span>
            <span>&nbsp;</span>
            <a href="javascript:closeOverlayPop()" class="cmd none">Cancel</a>
          </div>
        </div>
      </div>
    </form>
    <?php include "inc/footer.php" ?>
    <?php include "inc/ajax-pops/new-open.php" ?>
    <?php include "inc/ajax-pops/calendar.php" ?>
    <?php include "inc/ajax-pops/working-confirm.php" ?>
    <?php include "inc/ajax-pops/doc-viewer.php" ?>
    <?php include "inc/ajax-pops/template-pops.php" ?>
    <?php include "inc/engine-download-form.html" ?>
  </body>
</html>
<?
function renderVitalQuestion($prop, $next = null) {
  $next = ($next == null) ? null : "ve-$next";
  renderQuestionField("ve-$prop", "vitalQuestionCallback", $next);
}
function renderQuestionField($id, $callback, $next = null, $className = "q") {
  echo "<span class='$className'><a id='$id' ";
  if ($next) {
    echo "next='$next' ";
  }
  echo "href='javascript:' onclick='showQuestionField(this, null, $callback)' class='df'>_______</a></span>";
}
?>
<script type="text/javascript">
document.onselectstart = testSelect;
document.onkeyup = onKeyUp;
attachWindowFocusBlur(pageFocus, pageBlur);
function onKeyUp() {
  if (event.keyCode == 27) {
    closeOverlayPop();
  }
}
var cid = <?=$_GET["id"] ?>;
var popedit = <?=Form::getFormVariable("pe", 0) ?>;
var me = <?=UserDao::getMyUserAsJson() ?>;
var lu_custom = <?=jsonencode($lu_print) ?>;
var lu_tcustoms = <?=LookupDao::getAllTemplateCustomsAsJson() ?>;
var perm = {"sn":<?=toString($myLogin->permissions->canSignNotes) ?>};
new TabBar('fsp-his', ['Documentation History','Appointment History','Message History'], ['Documents','Appointments','Messages']);
setTimeout("getFacesheet(<?=$_GET["id"] ?>)", 50);
var fs;
loadSelect($("hmcint-int"), intComboArray(), null, "");
// registerBodyFocus(pageFocus);
<?php timeoutCallbackJs("overlayWorking(false);") ?>
function showClient(id) {
  window.location.href = "facesheet.php?id=" + id;
}
</script>      
