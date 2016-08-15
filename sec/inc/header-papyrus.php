<? 
require_once "php/data/Version.php";
require_once "php/data/rec/sql/Messaging.php";
require_once "php/data/rec/sql/Messaging_DocStubReview.php";
require_once "php/data/rec/sql/HL7_Labs.php";
require_once "php/dao/UserDao.php";
//
global $login;
$page = currentPage();
$noAlert = ($page == "registerCard.php" || $page == 'welcome-trial.php');
$pop = ($page == 'face.php' && isset($_GET['pop']));
$popstyle = ($pop) ? 'style="display:none"' : '';
$sessId = isset($_GET['sess']) ? $_GET['sess'] : null;
?>
    <script language="JavaScript1.2" src="js/pages/Ajax.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/pages/Header.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/pages/Includer.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/pages/Lookup.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/pages/Page.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/pages/Polling.js?<?=Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/ui.js?<?=Version::getUrlSuffix() ?>"></script>
    <div id="header">
      <div id="logo-head" <?=$popstyle?>>
        <div style='background:#D2E3E0;'>
          <img src='img/papy-pyr.png' class='pyr' />
          <div id='paptext'>PAPYRUS MEDICAL</div>
          <table border="0" cellpadding="0" cellspacing="0">
            <tr>
              <td><a class='logo' href="welcome.php"></a></td>
              <td class="logo-right">
                <div class="loginfo tf-header">
                  <? if (isset($login)) { ?>
                    Logged in as <b><?=$login->uid ?></b>
                    <? if ($login->Role->Profile->any()) { ?>| <a href="profile2.php">My Profile</a><? } else { ?>| <a href="profile.php?cp=1">Change Password</a><? } ?>
                    | <a href="javascript:Header.icdLook()">ICD</a>
                    <? if ($login->admin) { ?>| <a href="serverAdm.php">Admin</a><? } ?>
                    <? if (1==2) { ?>| <a class='action tpdf' href="https://www.clicktate.com/ClicktateUserGuide.pdf">User Guide</a><? } ?>
                    | <a href="javascript:Header.logout()">Logout</a>
                  <? } ?>
                </div>
              </td>
            </tr>
          </table>
        </div>
        <div id='top'>
          <div class='edge'>
            <span id='nc-pharm' style='display:none'>
              <span style='font-family:Times New Roman'><b style='color:black'>R</b><b style='color:#800000'>x</b></span>
              <a id='a-ncp' href="javascript:Header.Erx.pharm_onclick()"></a>
            </span>
            <span id='nc-status' style='padding-left:3px;display:none;'>
              <span style='font-family:Times New Roman'><b style='color:black'>R</b><b style='color:#800000'>x</b></span>
              <a id='a-ncs' href="javascript:Header.Erx.status_onclick()"></a>
            </span>
            <span style='padding-left:3px<?if (! $login->Role->Artifact->labs) {?>;display:none<?}?>'>
              <a id="a-labs" class="labs red" href="labs.php" style='display:none'>Labs</a>
            </span>
            <span style='padding-left:3px<?if (! $login->Role->Artifact->markReview) {?>;display:none<?}?>'>
              <a id="a-review" class="todo" href="review.php">Review</a>
            </span>
            <span style='padding-left:2px;'>
              <img id="img-mail" style="position:absolute;z-index:100;display:none" src='img/new/mail-medium.png' />
              <a id="a-mail" href="messages.php?mine=1"></a>
            </span>
          </div>
          <div class='edge2'>
            <table id='logo-bot' border=0 cellpadding=0 cellspacing=0>
              <tr>
                <td>
                </td>
                <td id='page-nav' class="loginfo2">
                  <table border=0 cellpadding=0 cellspacing=0 width="100%">
                    <tr>
                      <td style="vertical-align:top">
                        <div class="loginfo2">
                          <? if (isset($login)) { ?>
                            <a href="welcome.php">Home</a>
                            <? if ($login->Role->Patient->any()) { ?>| <a href="patients.php" title="Patient Database">Patients</a><? } ?>
                            <? if ($login->Role->Artifact->noteRead) { ?>| <a href="documents.php"title="Document Manager">Documents</a><? } ?>
                            <? if ($login->Role->Patient->sched) { ?>| <a href="schedule.php" title="Scheduling">Sched</a><? } ?>
                            <? if ($login->Role->Patient->track) { ?>| <a href="tracking.php" title="Order Tracking Sheet">Track</a><? } ?>
                            <? if ($login->Role->Artifact->scan) { ?>| <a href="scanning.php" title="Scanning">Scan</a><? } ?>
                            <? if ($login->Role->Report->any()) { ?>| <a href="reporting.php" title="Reporting">Report</a><? } ?>
                            <? if ($login->Role->Cerberus->superbill) { ?>| <a href="superbills.php" title="Billing">Billing</a><? } ?>
                          <? } ?>
                        </div>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
            </table>
          </div>
        </div>
      </div>
    </div>
    <div id="stickies" <?=$popstyle?>>
      <? if (! $login->isPapyrus()) { ?>
        <? if ($page == 'welcome.php' && 0 == 1) { ?>
          <? STICKY('downnote') ?>
            <b>Note</b>: Clicktate will be down at 8:00PM EST (5PM PST) Friday night (11/16) for maintenance.<br/>
            The website will be down for approximately 30 minutes.
          <? _STICKY('downnote') ?>
        <? } ?>
        <? if (1 == 2) { ?>
          <? STICKY('downnote') ?>
            <b>Note</b>: Clicktate will undergo scheduled maintenance at 9:30PM EST tonight.
            <br/>
            The website will be down for approximately 2-3 hours.
          <? _STICKY('downnote') ?>
        <? } ?>
        <? if (! $noAlert && $login->User->isDoctor() && $login->User->isOnTrial() && $login->daysLeft < 25) { ?>
          <? STICKY('countdown') ?>
            Your trial account has <?=daysLeft($login->daysLeft) ?> remaining.
            <a href="registerCard.php">Activate now &gt;</a>
          <? _STICKY('countdown', false) ?>
        <? } else if (! $noAlert && $login->isInactive()) { ?>
          <? STICKY('countdown') ?>
            <?=$login->expireReason ?><br/> 
            At present you have limited (read-only) access to your information.<br/>
            <a href="registerCard.php">Update billing info and restore full access &gt;</a>
          <? _STICKY('countdown', false) ?>
        <? } else if (! $noAlert && $login->User->isPaying() && $login->daysLeft < 60) { ?>   
          <? STICKY('countdown') ?>
            <? if ($login->daysLeft < 0) { ?>
              Your credit card has expired. 
            <? } else { ?>
              Your credit card on file expires in <?=daysLeft($login->daysLeft) ?>.
            <? } ?>
            <a href="registerCard.php">Update card &gt;</a>
          <? _STICKY('countdown', false) ?>
        <? } ?>
        <? if ($login->LoginReqs && ! $login->User->isOnTrial()) { ?>
          <? foreach ($login->LoginReqs as $action => $reqs) { ?>
            <? foreach ($reqs as $req) { ?>
              <? $stid = 'req' . $req->userLoginReqId ?>
              <? STICKY($stid) ?>
                <?=daysLeft($req->_daysLeft, true) ?> left for
                <a style='color:red' href='javascript:' onclick='Pop.show("pop-not");return false'><?=$req->LoginReq->name ?> &gt;</a>
              <? _STICKY($stid) ?>
            <? } ?>
          <? } ?>
        <? } ?>
        <? STICKY('legacy-sticky', 'display:none') ?>
          This patient has <i>legacy medications/allergies</i> that may need to be transferred to our partner ePrescribing system.
          <br><a href='javascript:Facesheet.pNewCrop("medentry")'>Click here to do this now</a>
        <? _STICKY('legacy-sticky', false) ?>
      <? } ?>
    </div>
    <? if ($login->LoginReqs) { ?>
      <div id="pop-not" class="pop" onmousedown="event.cancelBubble = true" style='width:680px; background:white'>
        <div id="pop-not-cap" class="pop-cap">
          <div id="pop-not-cap-text">
            Notifications and Warnings
          </div>
          <a href="javascript:Pop.close()" class="pop-close"></a>
        </div>
        <div class="pop-content">
          <? foreach ($login->LoginReqs as $action => $reqs) { ?>
            <? foreach ($reqs as $req) { ?>
              <? renderBoxStart('wide min-pad push') ?>
                <h4 style='color:red;margin-bottom:-0.5em'><?=$action . ': ' . $req->LoginReq->name ?></h4>
                <div><?=$req->LoginReq->notifyText ?></div>
              <? renderBoxEnd() ?>
            <? } ?>
          <? } ?>
          <div class="pop-cmd">
            <a href="javascript:Pop.close()" class="cmd none">&nbsp;&nbsp;&nbsp;Exit&nbsp;&nbsp;&nbsp;</a>
          </div>
        </div>
      </div>
    <? } ?>
<?
function daysLeft($amt, $cap = false) {
	$s = round($amt);
	if ($s < 1) 
	  $s = ($cap) ? 'Zero days' : 'zero days';
	else if ($s == 1)
		$s .= ' day';
	else 
		$s .= ' days';
	return $s;
}
?>
<script>
var today = "<?=date("m/d/Y", strtotimeAdjusted(nowTimestamp())) ?>";
var now = <?=now() ?>;
var me = <?=$login->asJson() ?>;
me.isErx = function() {return me.User.UserGroup.usageLevel == 2};
Header.load(
  '<?=$page ?>', 
  <?=Messaging::getMyUnreadCt()?>, 
  <?=Messaging_DocStubReview::getUnreviewedCt()?>,
  <?=HL7_Labs::getInboxCt()?>);
<? if ($sessId) { ?>
Ajax.setSessionId('<?=$sessId ?>');
<? } ?>
</script>