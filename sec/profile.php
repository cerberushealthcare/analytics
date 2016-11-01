<?php 
require_once "php/data/LoginSession.php";
require_once "inc/uiFunctions.php";
require_once "php/forms/ProfileForm.php";
require_once "php/forms/utils/CommonCombos.php";
//
LoginSession::verify_forUser();  // ->requires($login->Role->Profile->any());
$form = new ProfileForm();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <?php renderHead("My Profile") ?>
    <link rel="stylesheet" type="text/css" href="css/xb/_clicktate.css?<?=Version::getUrlSuffix() ?>" media="screen" />
    <link rel="stylesheet" type="text/css" href="css/xb/profile.css?<?=Version::getUrlSuffix() ?>" media="screen" />
    <link rel="stylesheet" type="text/css" href="css/xb/Pop.css?<?=Version::getUrlSuffix() ?>" media="screen" />
    <link rel="stylesheet" type="text/css" href="css/data-tables.css?<?=Version::getUrlSuffix() ?>" media="screen" />
    <link rel="stylesheet" type="text/css" href="css/xb/EntryForm.css?<?=Version::getUrlSuffix() ?>" />
    <link rel="stylesheet" type="text/css" href="css/xb/_hover.css?<?=Version::getUrlSuffix() ?>" media="screen" />
    <script language="JavaScript1.2" src="js/Pages/Pop.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/old-ajax.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/yahoo-min.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/json.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/connection-min.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/ui.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/components/CmdBar.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/components/EntryForm.js?<?=Version::getUrlSuffix() ?>"></script>
    <style>
TABLE.prof {
  width:100%;
  margin-bottom:10px;
}
TABLE.prof TH {
  width:120px;
  vertical-align:top;
  text-align:left;
}
H6 {
  margin:0;
  font-size:13pt;
}
    </style>
    <?php HEAD_UI('PasswordEntry') ?>
  </head>
  <body onload='load()'>
    <div id="curtain"></div>  
    <form id="frm" method="post" action="profile.php">
      <div id="bodyContainer">
        <?php include "inc/header.php" ?>
        <div id='bodyContent' class="content">
          <?php require_once "inc/errors.php" ?>
          <div class="abstract">
            <h1 class="sched">My Profile</h1>
          </div>
          <?php renderBoxStart("wide small-pad") ?>
          <table class='prof'>
            <tr>
              <th>
                  <h6>Personal<br>Data</h6></th><td>
                  <ul class="entry" style="margin-bottom:0;">
                    <li>
                      <label class="first">Name<br/>Email</label>
                      <span class="ro" style="width:195px; height:35px">
                        <?=$form->htmlMyNameEmail ?>
                      </span>
                      <?php if ($login->Role->Profile->license) { ?>
                        <a class="pencil smar" href="javascript:showEditProfile(0)">Edit</a>
                      <?php } ?>
                    </li>
                    <li>
                      <label class="first">Password</label>
                      <span class="ro" style="width:195px">
                        ********
                      </span>
                      <a class="pencil smar" href="javascript:" onclick="ChangePasswordPop.pop(<?=$login->userId?>)">Edit</a>
                    </li>
                    <?php if ($login->Role->Profile->license) { ?>
                      <li>
                        <label class="first">License: State<br/>DEA<br/>NPI</label>
                        <span class="ro" style="width:195px; height:52px">
                          <?=$form->htmlLicense ?>
                        </span>
                        <a class="pencil smar" href="javascript:showEditProfile(2)">Edit</a>
                      </li>
                    <?php } ?>
                  </ul>
                <?php if ($login->Role->Profile->practice) { ?>
              </td>
            </tr>
          </table>
          <table class='prof'>
            <tr>
                <th>
                    <h6>Practice<br/>Information</h6></th><td>
                    <ul class="entry" style="margin-bottom:0;">
                      <li>
                        <label class="first">Name</label>
                        <span class="ro" style="height:35px; width:195px;">
                          <?=$form->practice->name ?>
                        </span>
                        <a class="pencil smar" href="javascript:showEditProfile(3)">Edit</a>
                      </li>
                      <li>
                        <label class="first">Address<br/><br/><br/><br/>Phone<br/><br/><br/>Timezone</label>
                        <span class="ro" style="width:195px; height:125px">
                          <?=$form->htmlPracticeAddress ?>
                        </span>
                        <a class="pencil smar" href="javascript:showEditProfile(4)">Edit</a>
                      </li>
                      <li>
                        <label class="first">Session<br>Timeout</label>
                        <span class="ro" style="height:25px; width:195px;">
                          <?=$form->practice->sessionTimeout ?> minutes
                        </span>
                        <a class="pencil smar" href="javascript:TimeoutPop.pop({'timeout':'<?=$form->practice->sessionTimeout ?>'})">Edit</a>
                      </li>
                    </ul>
              </td>
            </tr>
          </table>
          <table class='prof'>
            <tr>
                <th>
                    <h6>Providers</h6></th><td>
                    <ul class="entry" style="margin-bottom:0;">
                      <li>
                        <label class="first">&nbsp;</label>
                        <span class="ro bold" style="width:550px;">
                          <table cellpadding="0" cellspacing="0" class="list">
                            <?php foreach ($form->docAccts as $doc) { ?>
                              <tr>
                                <?php if ($doc != null) { ?>
                                  <td><?=$doc->name ?></td>
                                <?php } else { ?>
                                  <td>&nbsp;</td>
                                <?php } ?>
                              </tr>
                            <?php } ?>
                          </table>
                        </span>
                      </li>
                    </ul>
                  <?php } ?>
                <?php if ($login->Role->Account->manage) { ?>
              </td>
            </tr>
          </table>
          <table class='prof'>
            <tr>
                <th>
                    <h6>Support<br/>Accounts</h6></th><td>
                    <ul class="entry" style="margin-bottom:0;">
                      <li>
                        <label class="first">&nbsp;</label>
                        <span class="ro" style="width:550px;">
                          <table cellpadding="0" cellspacing="0" class="list" width="100%">
                            <tr>
                              <th>ID</th>
                              <td width="5"></td>
                              <th>Name</th>
                              <td width="5"></td>
                              <th>Type</th>
                            </tr>
                            <tr>
                              <td height="4"></td>
                            </tr>
                            <?php foreach ($form->supportAccts as $acct) { ?>
                              <tr>
                                <?php if ($acct != null) { ?>
                                  <td style="vertical-align:top">
                                    <a href="javascript:showEditAccount(<?=$acct->id ?>)" class="icon <?=echoIf($acct->active, "user bold", "user-fade strike") ?>">
                                      <?=$acct->uid ?>
                                    </a>
                                  </td>
                                  <td></td>
                                  <td style="vertical-align:top">
                                    <?=$acct->name ?>
                                  </td>
                                  <td></td>
                                  <td style="vertical-align:top">
                                    <?=echoIf($acct->active, $acct->userTypeDesc, "[Inactive]") ?>
                                  </td>
                                <?php } else { ?>
                                  <td>&nbsp;</td>
                                <?php } ?>
                              </tr>
                            <?php } ?>
                          </table>
                        </span>
                      </li>
                    </ul>
                    <div class="pop-cmd" style="text-align:left; margin:0; padding-left:90px">
                      <a href="javascript:showEditAccount()" class="cmd none">New Support Account...</a>
                    </div>
              </td>
              <?php } ?>
              <?php if ($login->Role->Account->portal) { ?>
            </tr>
          </table>
          <table class='prof'>
            <tr>
                <th>
                    <h6>Portal<br/>Accounts</h6></th><td>
                    <div class="pop-cmd" style="text-align:left; margin:0; padding-left:90px">
                    <a href='accounts.php'>Update Portal Accounts...</a>
                    </div>
                </td>
              <?php } ?>
              <?php if ($login->Role->Profile->billing) { ?>
              </td>
            </tr>
          </table>
          <table class='prof'>
            <tr>
              <th>
                    <h6>Subscription</h6></th><td>
                    <ul class="entry">
                      <li>
                        <label class="first">&nbsp;</label>
                        <span class="ro" style="width:550px; height:120px; padding:5px 7px">
                          <?=$form->htmlSubscription ?>
                        </span>
                      </li>
                    </ul>
                    <?php if ($form->me->billInfo != null) { ?>
              </td>
            </tr>
          </table>
          <table class='prof'>
            <tr>
              <th>
                      <h6>Billing Info</h6></th><td>
                      <ul class="entry" style="margin-bottom:0;">
                        <li>
                          <label class="first">Card<br/>Expires<br/>Address<br/><br/><br/><br/>Phone</label>
                          <span class="ro" style="width:200px; height:117px">
                            <?=$form->htmlBillInfo ?>
                          </span>
                          <a class="pencil smar" href="registerCard.php">Edit</a>
                        </li>
                       </ul>
                     <?php } ?>
                   <?php } ?>
            </tr>
          </table>
          <?php renderBoxEnd() ?>
        </div>
        <div id='bottom'><img src='img/brb.png' /></div>
      </div>
      <?
      // Edit Support Account dialog
      // showEditAcct
      ?>
      <div id="pop-ea" class="pop" onmousedown="event.cancelBubble = true">
        <div id="pop-ea-cap" class="pop-cap">
          <div id="pop-ea-cap-text">
            Edit Support Account
          </div>
          <a href="javascript:Pop.close()" class="pop-close"></a>
        </div>
        <div id="pop-ea-content" class="pop-content" onkeypress="return ifCrClick('pop-ea-save')">
          <ul class="entry">
            <li>
              <label class="first">ID</label>
              <input id="pop-ea-id" size="20" />
            </li>
            <li id="li-sup-pass">
              <label class="first">Password</label>
              <input id="pop-ea-pw" size="22" />
            </li>
            <li>
              <label class="first">Account Type</label>
              <select id="pop-ea-type">
                <option value="<?=User0::USER_TYPE_OFFICE_EDITOR ?>">
                  <?=User0::getUserTypeDesc(User0::USER_TYPE_OFFICE_EDITOR) ?> 
                </option>
                <option value="<?=User0::USER_TYPE_OFFICE_READER ?>">
                  <?=User0::getUserTypeDesc(User0::USER_TYPE_OFFICE_READER) ?> 
                </option>
              </select>
            </li>
            <li style="margin-top:0.5em">
              <label class="first">Name</label>
              <input id="pop-ea-name" size="30" />
            </li>
            <li>
              <label class="first">Email</label>
              <input id="pop-ea-email" size="35" />
            </li>
            <li style="margin-top:0.5em">
              <label class="first">Active?</label>
              <?php renderLabelCheck("pop-ea-active", "Yes") ?>
            </li>
          </ul>
          <div class="pop-cmd">
            <a id="pop-ea-save" href="javascript:" onclick="saveAcct(); return false" class="cmd save">Save Changes</a>
            <span>&nbsp;</span>
            <a href="javascript:Pop.close()" class="cmd none">Cancel</a>
          </div>
          <div id="pop-ea-errors" class="pop-error">
          </div>
        </div>
      </div>
<script type="text/javascript">
function load() {
  //if ($('pf-right-box'))
  //  syncHeights(['pf-left-box', 'pf-right-box']);
  Page.setEvents();
}
var eaNew;
var eaUser;
function showEditAccount(id) {  // id=null for new acct
  eaNew = (id == null);
  $("pop-ea").style.width = "";
  showIf(eaNew, "li-sup-pass");
  hide("pop-ea-errors");
  Pop.Working.show();
  if (eaNew) {
    setText("pop-ea-cap-text", "New Support Account");
    setText("pop-ea-save", "Create Account");
    sendRequest(4, "action=newSupportUser");
  } else {
    setText("pop-ea-cap-text", "Edit Support Account");
    setText("pop-ea-save", "Save Changes");
    sendRequest(4, "action=getSupportUser&id=" + id);
  }
}
function getSupportUserCallback(user) {
  Pop.Working.close();
  eaUser = user;
  if (eaNew) {
    eaUser.active = true; 
  }
  setValue("pop-ea-id", user.uid).disabled = ! eaNew;
  setValue("pop-ea-pw", user.pw);
  setValue("pop-ea-name", user.name);
  setValue("pop-ea-type", user.userType);
  setValue("pop-ea-email", user.email);
  setCheck("pop-ea-active", user.active).disabled = eaNew;
  Pop.show("pop-ea", (eaNew) ? "pop-ea-id" : "pop-ea-pw");
}
function saveAcct() {
  if (isValidAcct()) {
    eaUser.uid = value("pop-ea-id");
    eaUser.pw = value("pop-ea-pw");
    eaUser.name = value("pop-ea-name");
    eaUser.email = value("pop-ea-email");
    eaUser.userType = value("pop-ea-type");
    eaUser.active = isChecked("pop-ea-active");
    postRequest(
        4,
        "action=updateSupportUser" + 
        "&obj=" + jsonUrl(eaUser));
  }
}
function isValidAcct() {
  hide("pop-ea-errors");
  var errs = [];
  validateRequired(errs, "pop-ea-id", "ID");
  if (eaNew) validateRequired(errs, "pop-ea-pw", "Password");
  validateRequired(errs, "pop-ea-name", "Name");
  validateRequired(errs, "pop-ea-email", "Email");
  if (errs.length > 0) {
    showErrors("pop-ea-errors", errs);
    focus(errs[0].id);
    return false;
  }
  return true;
}
function updateSupportUserCallback(dupe) {
  if (dupe) {
    showError("pop-ea-errors", "<b>The ID entered is already in use.</b><br/>Please choose another.");
    focus("pop-ea-id");
    return;
  } 
  Pop.Working.close();
  refreshPage();
}
</script>
      <?
      // Edit Profile dialog
      // showEditProfile(type) 0=my name/email, 1=my pw, 2=license,
      //                       3=prac name, 4=prac address
      //                       5=bill info, 6=bill address
      // editProfileCallback(updatedJsonObject) 
      ?>
      <div id="pop-ep" class="pop" onmousedown="event.cancelBubble = true">
        <div id="pop-ep-cap" class="pop-cap">
          <div id="pop-ep-cap-text">
            Edit Profile
          </div>
          <a href="javascript:Pop.close()" class="pop-close"></a>
        </div>     
        <div id="pop-ep-content" class="pop-content" onkeyup="ifCrClick('pop-ep-save')">
          <ul id="pop-ep-myNameEmail" class="entry">
            <li>
              <label class="first">Name</label>
              <input id="pop-ep-myName" size="30" />
            </li>
            <li>
              <label class="first">Email</label>
              <input id="pop-ep-myEmail" size="40" />
            </li>
          </ul>
          <ul id="pop-ep-myPw" class="entry">
            <li>
              <label class="first2">Current Password</label>
              <input id="pop-ep-curPw" type="password" size="24" />
            </li>
            <li style="margin-top:1em">
              <label class="first2">New Password</label>
              <input id="pop-ep-newPw" type="password" size="24" />
            </li>
            <li style="margin-bottom:1.5em">
              <label class="first2">New Password<br/>(Repeat)</label>
              <input id="pop-ep-newPw2" type="password" size="24" />
            </li>
          </ul>
          <ul id="pop-ep-myLic" class="entry ro">
            <li>
              <label class="first">License: State</label>
              <?php renderCombo("pop-ep-myLicState", CommonCombos::states()) ?>
              <input id="pop-ep-myLicense" size="18" />
            </li>
            <li>
              <label class="first">DEA</label>
              <input id="pop-ep-myDea" size="20" />
            </li>
            <li>
              <label class="first">NPI</label>
              <input id="pop-ep-myNpi" size="20" />
            </li>
          </ul>
          <ul id="pop-ep-myPracName" class="entry">
            <li>
              <label class="first">Practice Name</label>
              <textarea id="pop-ep-pName" cols="50" rows="2"></textarea>
            </li>
          </ul>
          <ul id="pop-ep-myPracAddress" class="entry">
            <li>
              <label class="first">Address</label>
              <input id="pop-ep-pAdd1" size="35" />
            </li>
            <li>
              <label class="first">&nbsp;</label>
              <input id="pop-ep-pAdd2" size="35" />
            </li>
            <li>
              <label class="first">&nbsp;</label>
              <input id="pop-ep-pAdd3" size="35" />
            </li>
            <li style="margin-top:0.5em">
              <label class="first">City</label>
              <input id="pop-ep-pCity" size="30" />
            </li>
            <li>
              <label class="first">State, Zip</label>
              <?php renderCombo("pop-ep-pState", CommonCombos::states(), "", "onchange='settz()'") ?>
              <input id="pop-ep-pZip" size="10" />
            </li>
            <li>
              <label class="first">Office Phone</label>
              <input id="pop-ep-pPhone" size="22" />
            </li>
            <li>
              <label class="first">Phone</label>
              <input id="pop-ep-pPhone2" size="22" />
              <?php renderCombo("pop-ep-pPhone2Type", CommonCombos::phoneTypes()) ?>
            </li>
            <li>
              <label class="first">Phone</label>
              <input id="pop-ep-pPhone3" size="22" />
              <?php renderCombo("pop-ep-pPhone3Type", CommonCombos::phoneTypes()) ?>
            </li>
            <li style="margin-top:0.5em">
              <label class="first">Timezone</label>
              <?php renderCombo("pop-ep-tz", CommonCombos::timezones()) ?>
            </li>
          </ul>
          <div class="pop-cmd">
            <a id="pop-ep-save" href="javascript:" onclick="saveProfile(); return false" class="cmd save">Save Changes</a>
            <span>&nbsp;</span>
            <a href="javascript:Pop.close()" class="cmd none">Cancel</a>
          </div>
          <div id="pop-ep-errors" class="pop-error">
          </div>
        </div>      
      </div>
<script type="text/javascript">
<?php if ($form->popMsg != null) { ?>
  Pop.Msg.showInfo("<?=$form->popMsg ?>");
<?php } else if ($form->popEdit != null) { ?>
  ChangePasswordPop.pop(<?=$login->userId?>);
<?php } ?>
var epType;
var epUser;
var epUserGroup;
var s2tz = <?=jsonencode(CommonCombos::timezonesByState()) ?>;
function settz() {
  setValue("pop-ep-tz", s2tz[value("pop-ep-pState")]);
}
function showEditProfile(type) {
  epType = type;
  $("pop-ep").style.width = "";
  showHideEpForms();
  sendEpAjaxRequest();
}
function showHideEpForms() {
  hide("pop-ep-errors");
  showIf(epType == 0, "pop-ep-myNameEmail");
  showIf(epType == 1, "pop-ep-myPw");
  showIf(epType == 2, "pop-ep-myLic");
  showIf(epType == 3, "pop-ep-myPracName");
  showIf(epType == 4, "pop-ep-myPracAddress");
}
function sendEpAjaxRequest() {
  Pop.Working.show();
  if (epType == 0 || epType == 1 || epType == 2) {
    sendRequest(4, "action=getMyUser");
  } else if (epType == 3 || epType == 4) {
    sendRequest(4, "action=getMyUserGroup");
  }
}
function getMyUserCallback(user) {
  Pop.Working.close();
  epUser = user;
  if (epType == 0) {
    setValue("pop-ep-myName", user.name);
    setValue("pop-ep-myEmail", user.email);
    Pop.show("pop-ep", "pop-ep-myName");
  } else if (epType == 1) {
    setValue("pop-ep-curPw", "");
    setValue("pop-ep-newPw", "");
    setValue("pop-ep-newPw2", "");
    Pop.show("pop-ep", "pop-ep-curPw");
  } else if (epType == 2) {
    setValue("pop-ep-myLicState", user.licenseState);
    setValue("pop-ep-myLicense", user.license);
    setValue("pop-ep-myDea", user.dea);
    setValue("pop-ep-myNpi", user.npi);
    Pop.show("pop-ep", "pop-ep-myLicState");
  }
}
function getMyUserGroupCallback(ug) {
  Pop.Working.close();
  epUserGroup = ug;
  if (epType == 3) {
    setValue("pop-ep-pName", ug.name);
    Pop.show("pop-ep", "pop-ep-pName");
  } else {
    setValue("pop-ep-pAdd1", ug.address.addr1);
    setValue("pop-ep-pAdd2", ug.address.addr2);
    setValue("pop-ep-pAdd3", ug.address.addr3);
    setValue("pop-ep-pCity", ug.address.city);
    setValue("pop-ep-pState", ug.address.state);
    setValue("pop-ep-pZip", ug.address.zip);
    setValue("pop-ep-pPhone", ug.address.phone1);
    setValue("pop-ep-pPhone2", ug.address.phone2);
    setValue("pop-ep-pPhone2Type", ug.address.phone2Type);
    setValue("pop-ep-pPhone3", ug.address.phone3);
    setValue("pop-ep-pPhone3Type", ug.address.phone3Type);
    setValue("pop-ep-tz", ug.estAdjust);
    Pop.show("pop-ep", "pop-ep-pAdd1");
  }
}
function saveProfile() {
  if (isValidProfile()) {
    Pop.Working.show();
    if (epType == 1) {
      epUser.cpw = value("pop-ep-curPw");
      epUser.pw = value("pop-ep-newPw");
      postRequest(
          4,
          "action=updateMyPw" + 
          "&obj=" + jsonUrl(epUser));      
    } else if (epType == 0 || epType == 2) {
      if (epType == 0) {
        epUser.name = value("pop-ep-myName");
        epUser.email = value("pop-ep-myEmail");
      } else {
        epUser.licenseState = value("pop-ep-myLicState");
        epUser.license = value("pop-ep-myLicense");
        epUser.dea = value("pop-ep-myDea");
        epUser.npi = value("pop-ep-myNpi");
      }
      postRequest(
          4,
          "action=updateMyUser" + 
          "&obj=" + jsonUrl(epUser));
    } else if (epType == 3 || epType == 4) {
      if (epType == 3) {
        epUserGroup.name = value("pop-ep-pName");
      } else {
        epUserGroup.address.addr1 = value("pop-ep-pAdd1");
        epUserGroup.address.addr2 = value("pop-ep-pAdd2");
        epUserGroup.address.addr3 = value("pop-ep-pAdd3");
        epUserGroup.address.city = value("pop-ep-pCity");
        epUserGroup.address.state = value("pop-ep-pState");
        epUserGroup.address.zip = value("pop-ep-pZip");
        epUserGroup.address.phone1 = value("pop-ep-pPhone");
        epUserGroup.address.phone1Type = "0";
        epUserGroup.address.phone2 = value("pop-ep-pPhone2");
        epUserGroup.address.phone2Type = value("pop-ep-pPhone2Type");
        epUserGroup.address.phone3 = value("pop-ep-pPhone3");
        epUserGroup.address.phone3Type = value("pop-ep-pPhone3Type");
        epUserGroup.estAdjust = value("pop-ep-tz");
      }
      postRequest(
          4,
          "action=updateMyUserGroup" + 
          "&obj=" + jsonUrl(epUserGroup));
    }
  }
}
function isValidProfile() {
  hide("pop-ep-errors");
  var errs = [];
  if (epType == 0) {
    errs = validateMyNameEmail(errs); 
  } else if (epType == 1) {
    errs = validateMyPw(errs);
  }
  if (errs.length > 0) {
    showErrors("pop-ep-errors", errs);
    focus(errs[0].id);
    return false;
  }
  return true;
}
function validateMyNameEmail(errs) {
  validateRequired(errs, "pop-ep-myName", "Name");
  return errs;
}
function validateMyPw(errs) {
  validateRequired(errs, "pop-ep-curPw", "Current Password");
  validateRequired(errs, "pop-ep-newPw", "New Password");
  validateRequired(errs, "pop-ep-newPw2", "New Password (Repeat)");
  if (errs.length == 0) {
    if (value("pop-ep-newPw") != value("pop-ep-newPw2")) {
      errs.push(errMsg("pop-ep-newPw", "New password fields do not match."));
    }
  }
  return errs;
}
function updateMyUserCallback(errorMsg) {
  Pop.Working.close();
  if (errorMsg == null) {
    Pop.close();
    refreshPage(3);
  } else {
    Pop.Msg.showCritical(errorMsg, updateErrorCallback);
  }
}
function updateErrorCallback() {
  focus("pop-ep-curPw");
}
function updateMyUserGroupCallback() {
  Pop.Working.close();
  refreshPage();
}

</script>
      <?php include "inc/footer.php" ?>
    </form>
  </body>
</html>
<script type="text/javascript">
function refreshPage(msg) {
  Pop.Working.show();
  var url = "profile.php";
  if (msg) {
    url += "?m=" + msg;
  }
  window.location = url;
}
var TIMEOUTS = <?=jsonencode(CommonCombos::timeouts()) ?>;
/**
 * RecordEntryPop TimeoutPop
 */
TimeoutPop = {
  pop:function(rec) {
    TimeoutPop = this.create().pop(rec);
  },
  create:function() {
    var self = Html.RecordEntryPop.create('Edit Profile');
    return self.aug({
      buildForm:function(ef) {
        ef.li('Timeout').select('timeout', TIMEOUTS);
      },
      save:function(rec, onsuccess, onerror) {
        Ajax.get(Ajax.SVR_POP, 'updateTimeout', rec.timeout, function() {
          refreshPage();
        });
      }
    });
  }
}
</script>