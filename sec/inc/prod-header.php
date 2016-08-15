<? 
$page = currentPage();
$noAlert = ($page == "registerCard.php");
?>
      <div id="logo-head">
        <table border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td><a href="welcome.php"><img src="img/lhdLogoTop.bmp" /></a></td>
            <td class="logo-right" />
              <div style="float:left;margin-top:15px;">
              <? if (! $myLogin->isOnProd()) { ?>
                <?=LoginResult::testingLabel() ?>
              <? } ?>
              </div>
              <div class="loginfo">
                <? if (isset($myLogin)) { ?>
                  Logged in as <b><?=$myLogin->uid ?></b>
                  | <a href=".?logout=Y">Logout</a>
                <? } ?>
              </div>
            </td>
          </tr>
        </table>
        <table border=0 cellpadding=0 cellspacing=0 width="100%">
          <tr>
            <td class="logoBottom">
              <a href="welcome.php"><img src="img/lhdLogoBottom.bmp" /></a>
            </td>
            <td class="loginfo2">
              <div class="loginfo2">
                <? if (isset($myLogin)) { ?>
                  <a href="welcome.php">Home</a>
                  <? if ($myLogin->permissions->accessPatients > Permissions::ACCESS_NONE) { ?>
                    | <a href="patients.php">Patients</a>
                  <? } ?>
                  | <a href="documents.php">Documents</a>
                  <? if ($myLogin->permissions->accessSchedule > Permissions::ACCESS_NONE) { ?>
                    | <a href="schedule.php">Scheduling</a>
                  <? } ?>
                  <? if ($myLogin->permissions->accessProfile > Permissions::ACCESS_NONE) { ?>
                    | <a href="profile.php">My Profile</a>
                  <? } else { ?>
                    | <a href="profile.php?cp=1">Change Password</a>
                  <? } ?>
                  <? if ($myLogin->admin) { ?>
                    | <a href="serverAdm.php">Admin</a>
                  <? } ?>
                <? } ?>
              </div>
            </td>
          </tr>
        </table>
        <? if (1 == 2 && ! isset($myLogin->hideStickies["downnote"])) { ?>
          <div id="downnote" class="sticky">
            <span>
            <table border="0" cellpadding="0" cellspacing="0">
              <tr>
                <td>
                  <b>Note</b>: Clicktate will undergo scheduled maintenance at 9:30PM EST tonight.
                  <br/>
                  The website will be down for approximately 2-3 hours.
                </td>
                <td style="padding-left:10px; vertical-align:top; font-family:Verdana; font-weight:bold; font-size:8pt">
                  <a title="Close" href="javascript:closeSticky('downnote')">X</a>
                </td>
              </tr>
            </table>
            </span>
          </div>
        <? } ?>
        <? if (! isset($myLogin->hideStickies["browser"]) && ($myLogin->ie != "6" && $myLogin->ie != "7" && $myLogin->ie != "8")) { ?>
          <div id="browser" class="sticky">
            <span>
            <table border="0" cellpadding="0" cellspacing="0">
              <tr>
                <td>
                  <b>Warning</b>: Clicktate only supports Internet Explorer (6, 7, 8)
                  <br/>
                  Some features will not work properly on other browsers.
                </td>
                <td style="padding-left:10px; vertical-align:top; font-family:Verdana; font-weight:bold; font-size:8pt">
                  <a title="Close" href="javascript:closeSticky('browser')">X</a>
                </td>
              </tr>
            </table>
            </span>
          </div>
        <? } ?>
        <? if ($myLogin->userType == User::USER_TYPE_DOCTOR && ! $noAlert && $myLogin->onTrial && $myLogin->daysLeft < 25) { ?>       
	        <div id="countdown">
	          <span>
	            You have <?=daysLeft($myLogin) ?> remaining on your trial account. 
	            <a href="registerCard.php">Activate now</a>
	          </span>
	        </div>
        <? } else if (! $noAlert && $myLogin->isInactiveDoctor()) { ?>
          <div id="countdown">
            <span>
              <?=$myLogin->getInactiveReason() ?><br/>
              At present you have limited (read-only) access to your information.<br/>
              <a href="registerCard.php">Update billing info and restore full access ></a>
            </span>
          </div>
        <? } else if (! $noAlert && ! $myLogin->onTrial && $myLogin->daysLeft < 60) { ?>       
          <div id="countdown">
            <span>
              Your credit card on file expires in <?=daysLeft($myLogin) ?>. 
              <a href="registerCard.php">Update card</a>
            </span>
          </div>
          <div id="countdown" style="height:5px"></div>
        <? } else { ?>
        <? } ?>
      </div>        
<?
function daysLeft($myLogin) {
	$s = $myLogin->daysLeft;
	if ($s == 1) {
		$s .= " day";
	} else {
		$s .= " days";
	}
	return $s;
}
?>
<script>
function quickhelp(notAsEmail) {
  var n = (! notAsEmail) ? "&n=<?=$myLogin->name ?>" : ""; 
  window.frames["help"].location.href = "../email/quick-help.php?t=<?=$myLogin->subscription ?>" + n;
  zoomPop("pop-help");
}
function printThis() {
  window.frames["help"].focus();
  window.frames["help"].print();
}
function closeSticky(id) {
  hide(id);
  sendRequest(4, "action=hideSticky&id=" + id);
}
try {
  document.execCommand("BackgroundImageCache", false, true);
} catch(e) {
}
</script>