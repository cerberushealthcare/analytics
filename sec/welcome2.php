<?
require_once "inc/requireLogin.php";
require_once "inc/uiFunctions.php";
require_once "php/dao/UserDao.php";
global $myLogin;
p_r($myLogin);
exit;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <?php renderHead("Welcome") ?>
    <link rel="stylesheet" type="text/css" href="css/xb/_clicktate.css?<?=Version::getUrlSuffix() ?>" media="screen" />
    <link rel="stylesheet" type="text/css" href="css/xb/welcome.css?<?=Version::getUrlSuffix() ?>" media="screen" />
    <link rel="stylesheet" type="text/css" href="css/xb/Pop.css?<?=Version::getUrlSuffix() ?>" media="screen" />
    <link rel="stylesheet" type="text/css" href="css/xb/template-pops.css?<?=Version::getUrlSuffix() ?>" />
    <script language="JavaScript1.2" src="js/pop.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/ajax.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/yahoo-min.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/json.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/connection-min.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/ui.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/Pages/Pop.js?<?=Version::getUrlSuffix() ?>"></script>
  </head>
  <body>
    <div id="curtain"></div>
    <form id="frm" method="post" action="welcome.php">
      <div id="bodyContainer">
        <?php include "inc/header.php" ?>
        <div id='bodyContent' class="content">
          <div class="abstract">
            <h1>Welcome, <?=$myUser->name ?>!</h1>
            <table border="0" cellpadding="0" cellspacing="0">
              <tr>
                <td>
                  <h2 class="mt5 mb10">
                    <?=$myUser->userGroup->name ?>
                    <?php if ($myUser->userGroup->address != null) { ?>
                      &bull; <?=$myUser->userGroup->address->city ?>, <?=$myUser->userGroup->address->state ?>
                    <?php } ?>
                  </h2>
                </td>
                <td width="10"></td>
                <td>
                </td>
              </tr>
            </table>
          </div>
          <div class="profile">
            <table cellpadding="0" cellspacing="0" width="100%">
              <tr>
                <td width="33%" valign="top">
                  <?php renderBoxStart("wide small-pad") ?>
                    <div class="welcome-box">
                      <div class="welcome-head">
                        <a class="patients" href="patients.php">Patients</a>
                      </div>
                      <?php if ($myLogin->permissions->accessPatients > Permissions::ACCESS_NONE) { ?>
                        <ul>
                          <li>
                            <a class="icon go" href="patients.php">List all patients</a>
                          </li>
                          <li style="padding-top:1em;">
                            <label>Search by Last Name</label>
                            <table border="0" cellpadding="0" cellspacing="0">
                              <tr>
                                <td>
                                  <input type="text" id="last_name" size="18" onkeyup="testCr('last_name')" />
                                </td>
                                <td valign="top">
                                  <a class="icon go" href="javascript:search('last_name')">Go</a>
                                </td>
                              </tr>
                            </table>
                            <label>Search by ID</label>
                            <table border="0" cellpadding="0" cellspacing="0">
                              <tr>
                                <td>
                                  <input type="text" id="uid" size="12" onkeyup="testCr('uid')" />
                                </td>
                                <td valign="top">
                                  <a class="icon go" href="javascript:search('uid')">Go</a>
                                </td>
                              </tr>
                            </table>
                          </li>
                          <?php if ($myLogin->permissions->accessPatients > Permissions::ACCESS_READ) { ?>
                            <li style="padding-top:1em">
                              <a class="icon go" href="patients.php?pn=1">Create a new patient...</a>
                            </li>
                          <?php } ?>
                        </ul>
                      <?php } else { ?>
                        <ul>
                          <li style="padding-bottom:10px">
                            <span style="text-align:center">
                              <br/>This feature is not available<br/>for this account.<br/><br/>
                            </span>
                          </li>
                        </ul>
                      <?php } ?>
                    </div>
                  <?php renderBoxEnd() ?>
                </td>
                <td width="10" nowrap="nowrap"></td>
                <td width="33%" valign="top">
                  <?php renderBoxStart("wide small-pad") ?>
                    <div class="welcome-box">
                      <div class="welcome-head">
                        <a class="documents" href="documents.php">Documents</a>
                      </div>
                      <h5>Patient Notes</h5>
                      <?php if ($myLogin->permissions->accessMyNotes > Permissions::ACCESS_NONE) { ?>
                        <ul>
                          <?php if ($myLogin->permissions->canSignNotes) { ?>
                            <li>
                              <a class="icon go" href="documents.php?u=<?=$myUserId ?>&pf1=closed&pfv1=0&pfe2=2">List unsigned notes for me</a>
                            </li>
                          <?php } ?>
                          <?php if ($myLogin->permissions->accessOfficeNotes > Permissions::ACCESS_NONE) { ?>
                            <li>
                              <a class="icon go" href="documents.php">List all notes</a>
                            </li>
                          <?php } ?>
                          <?php if (! $myLogin->permissions->canSignNotes) { ?>
                            <li>
                              <a class="icon go" href="documents.php?u=<?=$myUserId ?>">List all notes addressed to me</a>
                            </li>
                          <?php } ?>
                          <?php if ($myLogin->permissions->accessMyNotes >= Permissions::ACCESS_INSERT) { ?>
                            <li>
                              <a class="icon go" href="documents.php?pop=0">Start a new note for patient...</a>
                            </li>
                          <?php } ?>
                        </ul>
                        <?php if ($myLogin->permissions->accessTemplates > Permissions::ACCESS_READ) { ?>
                          <h5 style="margin-top:1.5em">Custom Templates</h5>
                          <ul>
                            <li>
                              <a class="icon go" href="documents.php?v=1">Manage my templates</a>
                            </li>
                          </ul>
                        <?php } ?>
                      <?php } else { ?>
                        <ul>
                          <li style="padding-bottom:10px">
                            <span style="text-align:center">
                              <br/>This feature is not available<br/>for this account.<br/><br/>
                            </span>
                          </li>
                        </ul>
                      <?php } ?>
                    </div>
                  <?php renderBoxEnd() ?>
                </td>
                <td width="10" nowrap></td>
                <td width="33%" valign="top">
                  <?php renderBoxStart("wide small-pad") ?>
                    <div class="welcome-box">
                      <div class="welcome-head">
                        <a class="scheduling" href="schedule.php">Scheduling</a>
                      </div>
                      <ul style="padding-top:0.5em">
                        <?php if ($myLogin->permissions->accessSchedule > Permissions::ACCESS_NONE) { ?>
                          <?php if ($myLogin->isBasic()) { ?>
                            <li style="padding-bottom:10px">
                              <b>Note</b>
                              <span>
                                Scheduling features are available only to Clicktate EMR subscribers.<br/><br/>
                              </span>
                            </li>
                          <?php } ?>
                          <li>
                            <a class="icon go" href="schedule.php">Open today's schedule</a>
                          </li>
                          <li style="margin-top:0.5em">
                            <a class="icon go" href="schedule.php?v=1">Open this week's schedule</a>
                          </li>
                          <li style="margin-top:0.5em">
                            <a class="icon go" href="schedule.php?pc=1">Open for date...</a>
                          </li>
                        <?php } else { ?>
                          <li style="padding-bottom:10px">
                            <span style="text-align:center">
                              <br/>This feature is not available<br/>for this account.<br/><br/>
                            </span>
                          </li>
                        <?php } ?>
                      </ul>
                    </div>
                  <?php renderBoxEnd() ?>
                </td>
              </tr>
                <tr>
                  <td colspan="5" style="padding:10px 0 0 0">
                    <?php renderBoxStart("wide small-pad center") ?>
                      <div class="welcome-box" style="height:auto">
                        <table border="0" cellpadding="0" cellspacing="0" style="margin:0 auto">
                          <tr>
                            <td>
                              <div class="welcome-head welcome-left">
                                <a class="profile" href="profile.php">My Profile</a>
                              </div>
                            </td>
                            <?php if ($myLogin->permissions->accessProfile > Permissions::ACCESS_NONE) { ?>
                              <td>
                                <ul>
                                  <li>
                                    <a class="icon go" href="profile.php">My info</a>
                                  </li>
                                  <li>
                                    <a class="icon go" href="profile.php">Practice info</a>
                                  </li>
                                </ul>
                              </td>
                              <td style="padding-left:20px">
                                <ul>
                                  <li>
                                    <a class="icon go" href="profile.php?cp=1">Change my password</a>
                                  </li>
                                  <li>
                                    <a class="icon go" href="profile.php">Manage support logins</a>
                                  </li>
                                </ul>
                              </td>
                            <?php } else { ?>
                              <td style="padding-left:20px">
                                <ul>
                                  <li>
                                    <a class="icon go" href="profile.php?cp=1">Change my password</a>
                                  </li>
                                </ul>
                              </td>
                            <?php } ?>
                          </tr>
                        </table>
                      </div>
                    <?php renderBoxEnd() ?>
                  </td>
                </tr>
            </table>
          </div>
        </div>
        <div id='bottom'><img src='img/brb.png' /></div>
      </div>
      <?php include "inc/footer.php" ?>
    </form>
  </body>
<script type="text/javascript">
Page.setEvents();
<?php if (isset($_GET["qh"])) { ?>
quickhelp();
<?php } ?>
function testCr(field) {
  var kc = event.keyCode;
  if (kc == 13) {
    search(field);
  }
}
function search(field) {
  window.navigate("patients.php?pf1=" + field + "&pfv1=" + value(field));
}
function quickhelp(notAsEmail) {
  //var n = (! notAsEmail) ? "&n=<?=$myLogin->name ?>" : "";
  //window.frames["help"].location.href = "../email/quick-help.php?t=<?=$myLogin->subscription ?>" + n;
  //Pop.zoom("pop-help");
}
function printThis() {
  window.frames["help"].focus();
  window.frames["help"].print();
}
<?php if ($myLogin->showReqs) { ?>
Pop.show('pop-not');
  <?php $myLogin->showReqs = false ?>
<?php } ?>
</script>
</html>
