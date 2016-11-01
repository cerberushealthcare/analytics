<?php
ob_start('ob_gzhandler');
require_once "php/data/LoginSession.php";
require_once "php/data/rec/sql/Dashboard.php";
require_once 'php/c/template-entry/TemplateEntry.php';
require_once 'inc/uiFunctions.php';
//
LoginSession::verify_forUser();
if ($login->super) {
  header("Location: reporting.php");
  exit;
}
$addr = Address::formatCsz($login->User->UserGroup->Address);
$ct = Login::countRecentBadLogins_forUid('lcdadmin4');
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.0 Strict//EN'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
  <head>
    <?php HEAD('Dashboard', 'DashboardPage', 'dashboard.css') ?>
    <?php HEAD_UI('Dashboard') ?>
  </head>
  <body>
    <?php BODY() ?>
      <table class='h'>
        <tr>
          <th>
            <h1 id='h1'>Dashboard
              &bull; <?php echo $login->User->UserGroup->name ?>
            </h1>
          </th>
          <td style='padding-bottom:2px'>
            <span id='failed-span' style='display:none'>
              <span id='failed'></span>&nbsp;&bull;&nbsp;
            </span>
            <span id='last-login-span' style='display:none'>
              <a id='last-login' class='action list' href='javascript:' onclick='LoginHistPop.pop()'>Login History</a>
            </span>
          </td>
        </tr>
      </table>
      <table id='boxes' class='boxes'>
        <tr>
          <td class='w50'>
            <?php DPANEL('sched', 'schedule.php', 'img/welcome/scheduling.png') ?>
          </td>
          <td class='w50'>
            <?php DPANEL('patient', 'patients.php', 'img/welcome/1patients.png') ?>
          </td>
        </tr>
        <tr>
          <td>
            <?php DPANEL('message', 'messages.php', 'img/welcome/email-open.png') ?>
          </td>
          <td>
            <?php DPANEL('review', 'review.php', 'img/welcome/2documents.png') ?>
          </td>
        </tr>
      </table>
    <?php _BODY() ?>
  </body>
  <?php CONSTANTS('Messaging', 'Client', 'Doctors', 'Users', 'Templates') ?>
  <?php START() ?>
</html>
<?
function DPANEL($id, $url, $img) {
  BOX($id);
  echo <<<END
  <table class='dpanel w100'>
    <tr>
      <td rowspan="2" class="icon" id="td$id">
        <a href="$url"><img src='$img' /></a>
      </td>
      <td class='w100'>
        <div id='$id-head' class='dhead'>
        </div>
      </td>
    </tr>
    <tr>
      <td>
        <div id='$id-table'>
        </div>
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <div id='$id-foot' class='dfoot'>
        </div>
      </td>
    </tr>
  </table>
END;
  _BOX();
}
