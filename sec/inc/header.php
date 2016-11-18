<?php
require_once "php/data/Version.php";
require_once "php/data/rec/sql/Messaging.php";
require_once "php/data/rec/sql/Messaging_DocStubReview.php";
require_once "php/data/rec/sql/HL7_Labs.php";
require_once "php/dao/UserDao.php";
//
global $login;
$baiju = $login->userGroupId == 3028;
$page = currentPage();
$noAlert = ($page == "registerCard.php" || $page == 'welcome-trial.php');
$pop = ($page == 'face.php' && isset($_GET['pop']));
$popstyle = ($pop) ? 'style="display:none"' : '';
$sessId = isset($_GET['sess']) ? $_GET['sess'] : null;
if ($page != 'cerberus-login.php' && $login->cerberus)
  $clogin = CerberusLogin::fetch();
?>
    <script language="JavaScript1.2" src="js/pages/Ajax.js?<?php echo Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/pages/Header.js?<?php echo Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/pages/Includer.js?<?php echo Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/pages/Lookup.js?<?php echo Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/pages/Page.js?<?php echo Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/pages/Polling.js?<?php echo Version::getUrlSuffix() ?>"></script>
    <script language="JavaScript1.2" src="js/ui.js?<?php echo Version::getUrlSuffix() ?>"></script>
    <div id="header">
      <div id="logo-head" <?php echo $popstyle?>>
        <div style='background:#D2E3E0;'>
<?php /*  ***** crs 6/29/2016
        <?php if ($baiju) { ?>
            <img src='img/baiju70.png' class='baiju' />
          <?php } else if ($login->super) { ?>
            <img src='img/papy-pyr.png' class='pyr' />
            <div id='paptext'>Papyrus Analytics<small><small><small> &trade;</small></small></small></div>
          <?php } else if ($login->cerberus) { ?>
            <img src='img/papy-pyr.png' class='pyr' />
            <div id='paptext'>Papyrus<small><small><small> &trade;</small></small></small></div>
          <?php } ?>
*/ ?>
            <img src='img/papy-pyr.png' class='pyr' />
            <div id='paptext'>Papyrus Clinical Information Network<small><small><small> &trade;</small></small></small></div>
          <table border="0" cellpadding="0" cellspacing="0">
            <tr>
              <!--   ***** crs 6/29/2016
              <td><a class='logo' href="welcome.php"><img src="img/lhdLogoTop2.png" <?php if ($login->cerberus || $login->super || $baiju) {?>style="visibility:hidden" <?php }?>/></a></td> // ***** crs 6/29/2016 -->
              <td><a class='logo' href="welcome.php"><img src="img/lhdLogoTop2.png" <?php if ( !$login->super  || $login->cerberus || $login->super || $baiju) {?>style="visibility:hidden" <?php }?>/></a></td>
              <td class="logo-right">
                <div class="loginfo tf-header">
                  <?php if (isset($login)) { ?>
                    Logged in as <b><?php echo $login->uid ?></b>
                    <?php if (! $login->super) { ?>
                      <?php if ($login->Role->Profile->any()) { ?>| <a href="profile2.php">My Profile</a><?php } else { ?>| <a href="profile.php?cp=1">Change Password</a><?php } ?>
                      <?php if ($login->admin) { ?>| <a href="serverAdm.php">Admin</a><?php } ?>
                      <?php if (1==2) { ?>| <a class='action tpdf' href="https://www.clicktate.com/ClicktateUserGuide.pdf">User Guide</a><?php } ?>
                    <?php } ?>
                    |
                    <?php if ($login->cerberus) { ?>
                      <a href="<?php echo $clogin->url?>102:<?php echo $clogin->sessionId?>::::APP_HOME_PAGE_CHOICE:PMS">Return to PMS</a>
                    <?php } else { ?>
                      <a href="javascript:Header.logout()">Logout</a>
                    <?php } ?>
                  <?php } ?>
                </div>
              </td>
            </tr>
          </table>
        </div>
        <div id='top'>
        <!-- ****** crs 6/29/2016
          <div class='edge'>
            <span id='nc-pharm' style='display:none'>
              <span style='font-family:Times New Roman'><b style='color:black'>R</b><b style='color:#800000'>x</b></span>
              <a id='a-ncp' href="javascript:Header.Erx.pharm_onclick()"></a>
            </span>
            <span id='nc-status' style='padding-left:3px;display:none;'>
              <span style='font-family:Times New Roman'><b style='color:black'>R</b><b style='color:#800000'>x</b></span>
              <a id='a-ncs' href="javascript:Header.Erx.status_onclick()"></a>
            </span>
            <span style='padding-left:3px<?if ($login->super || ! $login->Role->Artifact->labs) {?>;display:none<?}?>'>
              <a id="a-labs" class="labs red" href="labs.php" style='display:none'>Labs</a>
            </span>
            <span style='padding-left:3px<?if ($login->super || ! $login->Role->Artifact->markReview) {?>;display:none<?}?>'>
              <a id="a-review" class="todo" href="review.php">Review</a>
            </span>
            <span style='padding-left:2px;<?if ($login->super) {?>;display:none<?}?>'>
              <img id="img-mail" style="position:absolute;z-index:100;display:none" src='img/new/mail-medium.png' />
              <a id="a-mail" href="messages.php?mine=1"></a>
            </span>
          </div>
       ****** crs 6/29/2016 -->
          <div<?php if ($login->cerberus || $login->super || $baiju) echo "class='edge2'"; ?>>
            <table id='logo-bot' border=0 cellpadding=0 cellspacing=0>
              <tr>
                <?php // ***** crs 6/29/2016 if ($login->cerberus || $login->super || $baiju) { ?>
                <?php if (1==1 || $login->cerberus || $login->super || $baiju) { ?>
                  <td></td>
                <?php } else { ?>
                  <td class="logoBottom">
                    <a href="welcome.php"><img src="img/lhdLogoBottomL.png" /></a>
                  </td>
                <?php } ?>
                <td id='page-nav' class="loginfo2">
                  <table border=0 cellpadding=0 cellspacing=0 width="100%">
                    <tr>
                      <td style="vertical-align:top">
                        <div class="loginfo2">
                          <?php if (isset($login)) { ?>
                            <?php if ($login->super) { ?>
                              <a href="reporting.php" title="Reporting">Reporting</a>
                              | <a href="practices.php" title="Practice Database">Practices</a>
                            <?php } else { ?>
                       <!-- ****** crs 6/29/2016
                              <a href="welcome.php">Home</a>
                              <?php if ($login->Role->Patient->any()) { ?>| <a href="patients.php" title="Patient Database">Patients</a><?php } ?>
                       ****** crs 6/29/2016 -->
                              <?php if ($login->Role->Patient->any()) { ?> <a href="patients.php" title="Patient Database">Patients</a><?php } ?>
                       <!-- ****** crs 6/29/2016
                              <?php if ($login->Role->Artifact->noteRead) { ?>| <a href="documents.php"title="Document Manager">Documents</a><?php } ?>
                              <?php if (! $login->cerberus && $login->Role->Patient->sched) { ?>| <a href="schedule.php" title="Scheduling">Sched</a><?php } ?>
                              <?php if ($login->Role->Patient->track) { ?>| <a href="tracking.php" title="Order Tracking Sheet">Track</a><?php } ?>
                              <?php if ($login->Role->Artifact->scan) { ?>| <a href="scanning.php" title="Scanning">Scan</a><?php } ?>
                       ****** crs 6/29/2016 -->
                              <?php if ($login->Role->Report->any()) { ?>| <a href="reporting.php" title="Reporting">Report</a><?php } ?>
                              <?php if ($login->Role->Report->pqri) { ?>| <a href="reporting-pqri.php" title="Clinical Quality Measures">CQM</a><?php } ?>
                       <!-- ****** crs 6/29/2016
                              <?php if ($login->Role->Cerberus->superbill) { ?>| <a href="javascript:billing()" title="Billing">Billing</a><?php } ?>
                       ****** crs 6/29/2016 -->
                            <?php } ?>
                          <?php } ?>
                        </div>
                      </td>
                      <td style='text-align:right'>
                        <div class="loginfo2 rj">
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
    <div id="stickies" <?php echo $popstyle?>>
      <?php if (! $login->isPapyrus()) { ?>
        <?php if ($page == 'welcome.php' && 1 == 0) { ?>
          <?php STICKY('downnote') ?>
            <b>Note</b>: Clicktate will be down at 10:00PM EST tonight (7/19) for maintenance.<br/>
            The website will be down for approximately 30 minutes.
          <?php _STICKY('downnote') ?>
        <?php } ?>
        <?php if (1 == 2) { ?>
          <?php STICKY('downnote') ?>
            <b>Note</b>: Clicktate will undergo scheduled maintenance at 9:30PM EST tonight.
            <br/>
            The website will be down for approximately 2-3 hours.
          <?php _STICKY('downnote') ?>
        <?php } ?>
        <?php if (! $noAlert && $login->User->isDoctor() && $login->User->isOnTrial() && $login->daysLeft < 25) { ?>
          <?php STICKY('countdown') ?>
            Your trial account has <?php echo daysLeft($login->daysLeft) ?> remaining.
            <!-- <a href="registerCard.php">Activate now &gt;</a> -->
          <?php _STICKY('countdown', false) ?>
        <?php } else if (! $noAlert && $login->isInactive()) { ?>
          <?php STICKY('countdown') ?>
            <?php echo $login->expireReason ?><br/>
            At present you have limited (read-only) access to your information.<br/>
            <a href="registerCard.php">Update billing info and restore full access &gt;</a>
          <?php _STICKY('countdown', false) ?>
        <?php } else if (! $noAlert && $login->User->isPaying() && $login->daysLeft < 60) { ?>
          <?php STICKY('countdown') ?>
            <?php if ($login->daysLeft < 0) { ?>
              Your credit card has expired.
            <?php } else { ?>
              Your credit card on file expires in <?php echo daysLeft($login->daysLeft) ?>.
            <?php } ?>
            <a href="registerCard.php">Update card &gt;</a>
          <?php _STICKY('countdown', false) ?>
        <?php } ?>
        <?php if ($login->LoginReqs && ! $login->User->isOnTrial()) { ?>
          <?php $reqs = $login->getStickyLoginReqs(); ?>
          <?php foreach ($reqs as $req) { ?>
            <?php $stid = 'req' . $req->userLoginReqId ?>
            <?php STICKY($stid) ?>
              <?php echo daysLeft($req->_daysLeft, true) ?> left for
              <a style='color:red' href='javascript:' onclick='Pop.show("pop-not");return false'><?php echo $req->LoginReq->name ?> &gt;</a>
            <?php _STICKY($stid) ?>
          <?php } ?>
        <?php } ?>
        <?php STICKY('legacy-sticky', 'display:none') ?>
          This patient has <i>legacy medications/allergies</i> that may need to be transferred to our partner ePrescribing system.
          <br><a href='javascript:Facesheet.pNewCrop("medentry")'>Click here to do this now</a>
        <?php _STICKY('legacy-sticky', false) ?>
      <?php } ?>
    </div>
    <?php if ($login->LoginReqs) { ?>
      <div id="pop-not" class="pop" onmousedown="event.cancelBubble = true" style='width:680px; background:white'>
        <div id="pop-not-cap" class="pop-cap">
          <div id="pop-not-cap-text">
            Notifications and Warnings
          </div>
          <a href="javascript:Pop.close()" class="pop-close"></a>
        </div>
        <div class="pop-content">
          <?php foreach ($login->LoginReqs as $action => $reqs) { ?>
            <?php foreach ($reqs as $req) { ?>
              <?php renderBoxStart('wide min-pad push') ?>
                <h4 style='color:red;margin-bottom:-0.5em'><?php echo $action . ': ' . $req->LoginReq->name ?></h4>
                <div><?php echo $req->LoginReq->notifyText ?></div>
              <?php renderBoxEnd() ?>
            <?php } ?>
          <?php } ?>
          <div class="pop-cmd">
            <a href="javascript:Pop.close()" class="cmd none">&nbsp;&nbsp;&nbsp;Exit&nbsp;&nbsp;&nbsp;</a>
          </div>
        </div>
      </div>
    <?php } ?>
<?php 
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
var today = "<?php echo date("m/d/Y", strtotimeAdjusted(nowTimestamp())) ?>";
var now = <?php echo now() ?>;
var me = <?php echo $login->asJson() ?>;
me.isErx = function() {return me.User.UserGroup.usageLevel == 2};
Header.load(
  '<?php echo $page ?>',
  <?php echo Messaging::getMyUnreadCt()?>,
  <?php echo Messaging_DocStubReview::getUnreviewedCt()?>,
  <?php echo HL7_Labs::getInboxCt()?>);
<?php if ($sessId) { ?>
Ajax.setSessionId('<?php echo $sessId ?>');
<?php } ?>
<?php if ($login->Role->Cerberus->superbill) { ?>
function billing() {
  CerberusPop.pop_asUnsigned();
}
<?php } ?>
</script>