<?
require_once "inc/uiFunctions.php";
require_once "php/data/LoginSession.php";
require_once "php/data/Version.php";
require_once "inc/captchaValue.php";
//
//import_request_variables("p", "p_");
$p_id = geta($_POST, 'id');
$p_pw = geta($_POST, 'pw');
$p_tablet = geta($_POST, 'tablet');
//import_request_variables("g", "g_");
$g_logout = geta($_GET, 'logout');
$g_timeout = geta($_GET, 'timeout');
$g_cp = geta($_GET, 'cp');
//
$login = null;
session_start();
if (isset($_SESSION['post'])) { // redirected from emr login
  $p_id = $_SESSION['post']['id'];
  $p_pw = $_SESSION['post']['pw'];
  $p_tablet = $_SESSION['post']['tablet'];
  unset($_SESSION['post']);
}
$captcha = false;
if (isset($g_logout))
  LoginSession::clear();
else if (isset($g_cp))
  $login = LoginSession::get();
if (! isset($p_id)) {
  $p_id = "";
  unset($_SESSION['captcha']);
} else if (isset($p_pw)) {
  $captcha = isset($_SESSION['captcha']);
  if (empty($p_id)) {
    $errors = array('User ID is required.');
  } else if (empty($p_pw)) {
    $errors = array('Password is required.');
  } else if ($captcha && (! isset($p_cap) || $p_cap != $_SESSION['captcha'])) {
    $errors = array('Text does not match.');
    $_SESSION["captcha"] = genImageValue();
    sleep(2);
  } else {
    try {
      $login = LoginSession::login($p_id, $p_pw);  //->setUi($p_tablet == '1');
      if ($login->User->needsNewBilling()) {
        $url = 'registerCard.php';
      } else {
        if ($login->cerberus)
          $url = 'cerberus-login.php';
        else
          $url = 'welcome.php';
      }
      header("Location: $url");
      exit;
    } catch (LoginEmrException $e) {
      $_SESSION['post2'] = $_POST;
      //header('Location: ../../prod-clicktate/sec');
      header('Location: ../../sec/index.php');
      exit;
    } catch (LoginInvalidException $e) {
      if ($e->locked)
        header("Location: forgot-login.php?locked=1");
      else
        $errors = array("ID or password is incorrect, please re-enter.");
      if ($e->attempts > 2) {
        $captcha = true;
        $_SESSION["captcha"] = genImageValue();
      }
    } catch (LoginDisallowedException $e) {
      $errors = array("This account is currently inactive.<BR>Please call 1-888-425-8258 for more information.");
    } catch (AppUnavailableException $e) {
      $errors = array("The system is currently unavailable.<BR>Please try your request later.<br>Call 1-888-425-8258 if you continue to have problems.");
    } catch (Exception $e) {
      logit_r($e);
      $errors = array("This ID cannot be logged into at this time.<BR>Please call 1-888-425-8258 if you continue to have problems.");
    }
  }
}
session_write_close();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <!-- Copyright (c)2012 by LCD Solutions, Inc.  All rights reserved. -->
  <!-- http://www.clicktate.com -->
  <head>
    <title>
      Clicktate - Login
    </title>
    <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <meta http-equiv="Content-Script-Type" content="text/javascript" />
    <meta http-equiv="Content-Language" content="en-us" />
    <meta name="keywords" content="dictate, dictation, medical note, document generation, note generation, medical office notes, medical transcription, emr, ehr, medical documentation, progress notes, medical progress notes, soap notes, medical soap notes, medical note generation, medical notes, medical dictation, medical transcription, family practice notes, internal medicine notes, pediatric notes, urgent care notes, urgent care documentation, internal medicine documentation, pediatric documentation, family practice documentation, small office emr, small office ehr" />
    <meta name="description" content="Automated document generation." />
    <link rel="stylesheet" type="text/css" href="css/home.css?2" />
    <link rel="stylesheet" type="text/css" href="css/xb/pop.css?2" />
    <link rel="stylesheet" type="text/css" href="js/_ui/PasswordEntry.css" />
    <script type='text/javascript' src='js/_lcd_core.js?2'></script>
    <script type='text/javascript' src='js/_lcd_html.js?2'></script>
    <script language="JavaScript1.2" src="js/_ui/PasswordEntry.js?1"></script>
    <script language="JavaScript1.2" src="js/pages/Page.js"></script>
    <script language="JavaScript1.2" src="js/pages/Ajax.js"></script>
    <script language="JavaScript1.2" src="js/pages/Pop.js"></script>
    <script language="JavaScript1.2" src="js/old-ajax.js"></script>
    <script language="JavaScript1.2" src="js/yahoo-min.js"></script>
    <script language="JavaScript1.2" src="js/connection-min.js"></script>
    <script type='text/javascript' src='js/components/CmdBar.js'></script>
  </head>
  <body style='background-color:#000000;' onload="initfocus()">
  <div id="bodyContainer">
    <div id="curtain" class="cdark"></div>
    <div id="head">
      <div class="content">
        <div id="nav">
          <table cellpadding="0" cellspacing="0">
            <tr>
              <td>
              </td>
              <td style="text-align:right">
              </td>
            </tr>
          </table>
        </div>
      </div>
    </div>
    <div id="body">
      <div class="content">
        <div class="center">
        <table cellpadding='0' cellspacing='0' width='100%'>
          <tr>
            <td id='td'>
              <div style="padding-right:50px;padding-top:20px">
                <h1 style='padding-left:100px;margin-top:60px'>
                  <?=getLoginLabel()?>
                </h1>
                <div class="login" style='padding-left:100px'>
                  <?php require_once "inc/errors.php" ?>
                <? if (isset($g_timeout)) { ?>
                <div style='padding-bottom:1em;font-family:Arial;font-weight:bold;color:red'>Your session has expired from inactivity.<br>Please login to continue.</div>
                <? } ?>
                </div>
                <table cellpadding='0' cellspacing='0'>
                  <tr>
                    <td class='wm' style='padding-right:0;'>
                      <? renderBoxStart() ?>
                        <div id="login">
                          <form id="frm" method="post" action="index.php" autocomplete="off">
                            <input name="tablet" id="tablet" type="hidden" value="" />
                            <input style="display:none" />
                            <input type="password" style="display:none" /> <!-- to elim cached autocompletes -->
                            <div class="l" style="margin-top:10px">
                              <label>User ID</label><br/>
                              <input type='text' id='uid' size='20' name='id' value="<?=$p_id ?>" autocomplete="off" />
                            </div>
                            <div class="l">
                              <label>Password</label><br/>
                              <input name="pw" id='pw' type="password" size="20" autocomplete="off" onkeydown="if ((event.which && event.which == 13) || (event.keyCode && event.keyCode == 13)) {sub();return false;} else return true;" />
                            </div>
                            <? if ($captcha) { ?>
                            <div class="l" style="margin-top:10px;padding-top:10px;border:1px solid #c0c0c0; background-color:white;">
                              <img src="inc/captchaGen.php?sid=captcha&<? echo time() ?>"><br/><br/>
                              <label>Enter the text above</label><br/>
                              <input name="cap" id='cap' type="text" size="20" onkeydown="if ((event.which && event.which == 13) || (event.keyCode && event.keyCode == 13)) {sub();return false;} else return true;" />
                            </div>
                            <? } ?>
                            <div id="trial" style="padding-bottom:10px">
                              <a id="alog" href="javascript:sub()" class="tour">Login ></a>
                            </div>
                          </form>
                        </div>
                      <? renderBoxEnd() ?>
                    </td>
                  </tr>
                </table>
                <div id="forgot">
                  <div style='padding-left:100px'>
                    <a href="forgot-login.php" class="gb">Forgot your login ID or password</a>?
                  </div>
                </div>
              </div>
            </td>
          </tr>
        </table>
        </div>
      </div>
    </div>
    <div id="foot">
      <div class="content">
        <div class="foot-text">
          v 5.0 &copy; 2015 LCD Solutions, Inc.<br/>
          All rights reserved.
        </div>
        <div>
          <a href="../privacy.php">Privacy Policy</a>
          <span>|</span>
          <a href="../terms.php">Terms of Service</a>
          <span>|</span>
          <a style="background:url(img/pdf.gif) no-repeat; padding-left:20px" href="http://www.clicktate.com/ClicktateBAA.pdf">Business Associate Agreement</a>
          <span>|</span>
          <a href="../contact-us.php">Contact Us</a>
        </div>
      </div>
    </div>
  </div>
  </body>
</html>
<script type='text/javascript'>
var tablet = 0;//Boolean.toInt(document.ontouchstart === null);
Html.Input.$('tablet').setValue(tablet);
<? if ($login && isset($g_cp)) { ?>
ChangePasswordPop_Expired.pop(<?=$login->userId?>, tablet);
<? } ?>
Cookies.expire('NC_STATUS');
function setpw() {
  hide("pop-cp-errors");
  if (validpw()) {
    var u = {pw:value("pop-cp-pw")};
    postRequest(4, "action=updateMyPw&obj=" + jsonUrl(u));
    Pop.Working.show();
  }
}
function updateMyUserCallback(errorMsg) {
  Pop.Working.close();
  if (errorMsg == null) {
    Pop.close();
    Pop.Working.show();
    window.location = "welcome.php";
  } else {
    Pop.Msg.showCritical(errorMsg, updateErrorCallback, true);
  }
}
function updateErrorCallback() {
  focus("pop-cp-pw");
}
function validpw() {
  var errs = [];
  validateRequired(errs, "pop-cp-pw", "New Password");
  validateRequired(errs, "pop-cp-pw2", "New Password (Repeat)");
  var pw = value("pop-cp-pw");
  if (errs.length == 0) {
    if (pw != value("pop-cp-pw2")) {
      errs.push(errMsg("pop-cp-pw", "New password fields do not match."));
    }
  }
  if (errs.length == 0) {
    if (pw.length < 6) {
      errs.push(errMsg("pop-cp-pw", "New password must be at least 6 characters long."));
    }
    if (pw.length < 6) {
      errs.push(errMsg("pop-cp-pw", "New password must be at least 6 characters long."));
    }
    if (pw.match(/[0-9]/) == null) {
      errs.push(errMsg("pop-cp-pw", "New password must contain at least 1 numeric character."));
    }
  }
  if (errs.length > 0) {
    showErrors("pop-cp-errors", errs);
    focus(errs[0].id);
    return false;
  }
  return true;
}
function working() {
  var a = document.getElementById('alog');
  a.className = 'tour working';
  a.innerText = "";
}
function sub() {
  working();
  setTimeout('sub2()',1);
}
function sub2() {
  document.getElementById('frm').submit();
}
function initfocus() {
  <? if (! isset($g_cp)) { ?>
    Html.InputText.$('uid').setFocus();
  <? } ?>
}
var me = null;
var td = _$('td');
var msg = _$('msg');
var h = td.getHeight();
//msg.setHeight(h - 10);
<? if (1 == 2 && empty($errors)) { ?>
var pad = _$('padd');
pad.setHeight(h);
pause(1, function() {
  loop(function(exit) {
    h = h - 5;
    pad.setHeight(h);
    if (h < 5)
      exit();
  })
})
<? } ?>
</script>
<?php
function getLoginLabel() {
  switch (substr($_SERVER['HTTP_HOST'], 0, 5)) {
    case 'local':
      return "<span style=''>Clicktate 4.0</span> ";
    case 'test.':
      return "<span style='color:orange'>Test Login</span> ";
    default:
      return 'Clicktate 4.0';
  }
  return ' ';
}
