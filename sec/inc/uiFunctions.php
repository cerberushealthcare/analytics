<?php
require_once 'php/data/Version.php';
require_once 'serverFunctions.php';
require_once 'JsonConstants.php';
/**
 * Standard <head> contents
 * @param string $title 'Reporting'
 * @param string $controller JS file 'ReportingPage'
 * @param string $css CSS file 'messages.css'
 */
function HEAD($title, $controller = null, $css = null) {
  global $login;
  $v = Version::getUrlSuffix();
  echo <<<END
<!-- Copyright (c)2011-12 by LCD Solutions, Inc. -->
<!-- http://www.clicktate.com -->
<!-- ****** crs 6/30/2016 <title>$title &bull; Clicktate</title>  crs 6/30/2016 ****** -->
<title>$title &bull; Clinical Information Network</title>  <!-- ****** crs 6/30/2016 ****** -->
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<meta http-equiv="Content-Language" content="en-us" />
<meta name="keywords" content="dictate, dictation, medical note, document generation, note generation" />
<meta name="description" content="Automated document generation." />
<link rel='stylesheet' type='text/css' href='css/xb/_clicktate.css?$v' />
<link rel="icon" href="img/icons/favicon.ico"> <!-- ****** crs 6/29/2016 ****** -->
END;
  if ($css) { 
    echo <<<END
    <link rel="stylesheet" type="text/css" href="css/$css?$v" />
END;
  }
  if ($login->isPapyrus()) {
    echo <<<END
    <link rel="stylesheet" type="text/css" href="css/papyrus.css?$v" />
END;
  }
  if (! get($login->ui, 'tablet')) { 
    echo <<<END
 		<link rel='stylesheet' type='text/css' href='css/xb/_hover.css?$v' />
END;
  }
    echo <<<END
<script type='text/javascript' src='js/_lcd_core.js?$v'></script>
<script type='text/javascript' src='js/_lcd_html.js?$v'></script>
<script type='text/javascript' src='js/pages/Ajax.js?$v'></script>
<script type='text/javascript' src='js/pages/Header.js?$v'></script>
<script type='text/javascript' src='js/pages/Includer.js?$v'></script>
<script type='text/javascript' src='js/pages/Lookup.js?$v'></script>
<script type='text/javascript' src='js/icd-pop.js?$v'></script>
<script type='text/javascript' src='js/pages/Page.js?$v'></script>
<script type='text/javascript' src='js/pages/Polling.js?$v'></script>
<script type='text/javascript' src='js/pages/Pop.js?$v'></script>
<script type='text/javascript' src='js/libs/AddressUi.js?$v'></script>
<script type='text/javascript' src='js/libs/DateUi.js?$v'></script>
<script type='text/javascript' src='js/libs/ClientUi.js?$v'></script>
<script type='text/javascript' src='js/libs/DocUi.js?$v'></script>
<script type='text/javascript' src='js/yui/yahoo-min.js?$v'></script>
<script type='text/javascript' src='js/yui/event-min.js?$v'></script>
<script type='text/javascript' src='js/yui/connection-min.js?$v'></script>
<script type='text/javascript' src='js/components/AnchorTab.js?$v'></script>
<script type='text/javascript' src='js/components/TableLoader.js?$v'></script>
<script type='text/javascript' src='js/components/TabBar.js?$v'></script>
<script type='text/javascript' src='js/components/TemplateUi.js?$v'></script>
<script type='text/javascript' src='js/components/TemplateForm.js?$v'></script>
<script type='text/javascript' src='js/components/CmdBar.js?$v'></script>
<script type='text/javascript' src='js/components/EntryForm.js?$v'></script>
<script type='text/javascript' src='js/components/DateInput.js?$v'></script>
<script type='text/javascript' src='js/components/ProfileLoader.js?$v'></script>
<script type='text/javascript' src='js/_ui/PatientSelector.js?$v'></script>
END;
  if ($controller) { 
    echo <<<END
<script type='text/javascript' src='js/pages/$controller.js?$v'></script>
END;
  }
  global $pageController;
  $pageController = $controller;
}
/**
 * HEAD_Function invoked by HEAD_UI('Function',..) e.g. HEAD_UI('Scanning')
 */
function HEAD_Dashboard() {
  HEAD_REC('Messaging');
  HEAD_REC('Dashboard');
  HEAD_UserSelector();
}
function HEAD_QuickConsole() {
  HEAD_Templates();
  HEAD_REC('TemplateMap');
}
function HEAD_Facesheet() {
  HEAD_OrderEntry();
  HEAD_ProcEntry();
  HEAD_Reporting();
  HEAD_DocHistory();
  HEAD_PortalUserEntry();
  HEAD_Scanning();
  HEAD_VisitSummaryPop();
  HEAD_UI_CSSJS('CcdDownloader');
  HEAD_REC('Immuns');
}
function HEAD_Reporting() {
  HEAD_Templates();
  HEAD_UserSelector();
  HEAD_REC('Reporting');
  HEAD_UI_CSSJS('ReportBuilder');
  HEAD_UI_JS('HmDueNowPop');
}
function HEAD_Graphing() {
  HEAD_UI_JS('Graphing');
  echo "\n<script type='text/javascript' src='js/mod-julian.js'></script>";
  echo "\n<script type='text/javascript' src='js/components/Canvas.js'></script>";
  echo "\n<script type='text/javascript' src='js/components/Canvas_SVG.js'></script>";
  echo "\n<link rel='stylesheet' type='text/css' href='css/Canvas.css' />";
}
function HEAD_Scanning() {
  HEAD_Entry();
  HEAD_UI_CSSJS('Scanning');
  HEAD_REC('Facesheet');
}
function HEAD_Messaging() {
  HEAD_PortalUserEntry();
  HEAD_Templates();
  HEAD_DocHistory();
  HEAD_UserSelector();
  HEAD_REC('Messaging');
  HEAD_REC('Dashboard');
}
function HEAD_VisitSummaryPop() {
  HEAD_REC('VisitSummary');
  HEAD_UI_JS('VisitSummaryPop');
}
function HEAD_ProcEntry() {
  HEAD_Entry();
  HEAD_REC('Procedures');
  HEAD_UI_JS('ProcEntry');
}
function HEAD_Profile() {
  HEAD_REC('Profile');
  HEAD_UI_JS('UserManagerPop');
  HEAD_PasswordEntry();
}
function HEAD_OrderEntry() {
  HEAD_Entry();
  HEAD_UI_CSSJS('OrderEntry');
  HEAD_UI_JS('TrackingTable');
  HEAD_REC('Facesheet');
}
function HEAD_Entry() { 
  HEAD_Templates();
  HEAD_UserSelector();  
  HEAD_IpcPicker();  
  HEAD_ClientSelector();
  HEAD_ProviderPicker();
  HEAD_OrderPicker();
}
function HEAD_DocPreview() {
  HEAD_Scanning();
  HEAD_ProcEntry();
  HEAD_UI_CSSJS('DocPreview');
  HEAD_UI_CSSJS('ProcResultHistory');
  HEAD_UI_CSSJS('LabMessagePop');
  HEAD_REC('HtmlPdfDoc');
  HEAD_REC('Labs');
  HEAD_REC('VisitSummary');
}
function HEAD_DocHistory() {
  HEAD_DocPreview();
  HEAD_UI_JS('DocHistory');
  HEAD_UI_JS('FacePops');
}
function HEAD_Templates() {
  HEAD_UI_CSSJS('QuestionPop');
  HEAD_REC('Templates');
  HEAD_IpcPicker();  
}
function HEAD_UserSelector() {
  HEAD_UI_CSSJS('UserSelector');
}
function HEAD_ClientSelector() {
  HEAD_UI_JS('ClientSelector');
}
function HEAD_OrderPicker() {
  HEAD_UI_JS('OrderPicker');
}
function HEAD_ProviderPicker() {
  HEAD_UI_JS('ProviderPicker');
}
function HEAD_PasswordEntry() {
  HEAD_UI_CSSJS('PasswordEntry');
}
function HEAD_PortalUserEntry() {
  HEAD_UI_JS('PortalUserEntry');
  HEAD_REC('PortalUsers');
}
function HEAD_IpcPicker() {
  HEAD_UI_CSSJS('IpcPicker');
  HEAD_REC('Ipc');
}
function HEAD_RichText() {
  echo '<script type="text/javascript" src="../tiny_mce/tiny_mce.js"></script>';
}
/*
 * @param (string,..) ('Reporting', 'Templates', 'Entry')
 */
function HEAD_UI() {
  $names = func_get_args();
  foreach ($names as $name) 
    call_user_func("HEAD_$name");
}
function HEAD_UI_CSSJS($name) {
  $v = Version::getUrlSuffix();
  echo "\n<script type='text/javascript' src='js/_ui/$name.js?$v'></script>";
  echo "\n<link rel='stylesheet' type='text/css' href='js/_ui/$name.css?$v' />";
}
function HEAD_UI_JS($name) {
  $v = Version::getUrlSuffix();
  echo "\n<script type='text/javascript' src='js/_ui/$name.js?$v'></script>";
}
function HEAD_REC($name) {
  $v = Version::getUrlSuffix();
  echo "\n<script type='text/javascript' src='js/_rec/$name.js?$v'></script>";
}
function BODY() {
  echo <<<END
<div id='bodyContainer'>
  <div id='curtain'></div>
END;
  include 'inc/header.php';
  echo <<<END
  <div id='bodyContent' class='content'>
END;
}
function TITLE($title) {
  echo <<<END
    <table class='h'>
      <tr>
        <th><h1>$title</h1></th>
        <td id='headmenu'>
END;
}
function _TITLE() {
  echo <<<END
        </td>
      </tr>
    </table>
END;
}
function _BODY() {
  echo <<<END
  </div>
  <div id='bottom'><div></div></div>
</div>      
END;
  include 'inc/footer.php';
}
function CONSTANTS() {
  $names = func_get_args();
  echo "<script type='text/javascript'>";
  JsonConstants::add($names);
  echo "</script>";
}
function START() {
  global $pageController;
  $args = jsonencode($_GET);
  if ($args == '[]' || $args == '')
    $args = '{}';
  echo <<<END
<script type='text/javascript'>
async(function() {
  $pageController.start($args);
})
</script>  
END;
}
/**
 * @param string $id Sticky ID
 * @param(opt) string $style
 */
function STICKY($id, $style = ''){
  global $login;
  if ($login->isStickyHidden($id))
    $style = 'display:none';
  echo <<<END
  
<div id='$id' class="sticky" style='$style'>
  <span style=''>
    <table border='0' cellpadding='0' cellspacing='0'>
      <tr>
        <td>
END;
}
/**
 * @param string $id same as for renderSticky()
 * @param(opt) bool $remember true to keep pop from reappearing after close for this session 
 */
function _STICKY($id, $remember = true) {
  $remember = ($remember) ? '1' : '0';
  echo <<<END
        </td>
        <td class='x'>
          <a title='Close' href="javascript:Header.closeSticky('$id', $remember)">X</a>
        </td>
      </tr>
    </table>
  </span>
</div>
END;
}

function showWarnings() {
  echo <<<END
	error_reporting(E_ALL); ini_set('display_errors', '1');
END;
}
function getIeVersion() {
  $u = geta($_SERVER, 'HTTP_USER_AGENT');
  if ($u) {
    $a = explode('MSIE ', $u);
    if (count($a) > 1) {
      $v = explode('.', $a[1]);
      return $v[0];
    }
  }
}
// Render <HEAD> tag
function renderHead($pageTitle) {
  $v = Version::getUrlSuffix();
  echo <<<END
<!-- Copyright (c)2011-12 by LCD Solutions, Inc. -->
<!-- http://www.clicktate.com -->
  <title>$pageTitle &bull; Clicktate</title>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
  <meta http-equiv="Content-Style-Type" content="text/css" />
  <meta http-equiv="Content-Script-Type" content="text/javascript" />
  <meta http-equiv="Content-Language" content="en-us" />
  <meta name="keywords" content="dictate, dictation, medical note, document generation, note generation" />
  <meta name="description" content="Automated document generation." />
  <script type='text/javascript' src='js/_lcd_core.js?$v'></script>
  <script type='text/javascript' src='js/_lcd_html.js?$v'></script>
END;
}
function BOX($id = "") {
  echo "<div class='box' id='$id'>";
  renderBoxStart('wide min-pad');
}
function _BOX() {
  renderBoxEnd();
  echo '</div>';
}
function renderBoxStart($class = "", $contentStyle = "", $contentId = "", $boxId = "") {
  $cstyle = ($contentStyle != "") ? "style=\"$contentStyle\"" : "";
  $cid = ($contentId != "") ? "id='$contentId'" : "";
  $bid = ($boxId != "") ? "id='$boxId'" : "";
  echo <<<END
<table $bid class="box $class" cellpadding="0" cellspacing="0">
  <tr class="box-tb">
    <td class="tl"></td>
    <td class="t"></td>
    <td class="tr"></td>
  </tr>
  <tr>
    <td class="l" nowrap></td>
    <td $cid class="content" $cstyle valign="top">
      <div class="box-content">
  
END;
}

function renderBoxEnd() {
  echo <<<END
      </div>
    </td>
    <td class="r" nowrap></td>
  </tr>
  <tr class="box-tb">
    <td class="bl"></td>
    <td class="b"></td>
    <td class="br"></td>
  </tr>
</table>

END;
}


// Build a combo from an associated array
function renderCombo($name, $arr, $selectedValue = "", $events = "", $disabled = false, $blankText = null) {
  $dis = ($disabled) ? "disabled=true" : ""; 
  echo "<select id=\"$name\" name=\"$name\" $events $dis>";
  if ($blankText !== null) {
    echo "<option value=\"\">$blankText</option>";
  }
  foreach ($arr as $value => $text) {
    echo "<option value=\"$value\"";
    if ($value == $selectedValue) {
      echo "selected";
    }
    echo ">$text</option>";
  }
  echo "</select>";
}

// Other inputs
function renderCalendar($id, $value = "", $readOnly = true) {
  echo "<input id='$id' type='text' size='10' value='$value' ";
  if ($readOnly) {
    echo "readonly='readonly' style='cursor:hand' onclick=\"Pop.Calendar.showFromTextbox('$id')\" ";
  }
  echo "/>&nbsp;";
  echo "<a href=\"javascript:Pop.Calendar.showFromTextbox('$id')\" class='cal'></a>";
}
function renderClock($id, $value = "", $hoursOnly = false) {
  echo "<input id='$id' type='text' readonly='readonly' size='7' value='$value' onclick=\"Pop.Clock.showFromTextbox('$id', ";
  echo toString($hoursOnly);
  echo ")\" />&nbsp;";
  echo "<a href=\"javascript:Pop.Clock.showFromTextbox('$id', ";
  echo toString($hoursOnly);
  echo ")\" class='clock'></a>";
}
function renderLabelCheck($id, $caption, $checked = false, $style = null, $onclick = null, $lblId = null) {  // delim strings in $onclick by unescaped apostrophes only, e.g. alert('hi') 
  $sty = ($style) ? "style='" . $style . "'" : "";
  $onc = ($onclick) ? "onclick=\"" . $onclick . "\"" : "";
  $ond = ";" . $onclick;
  $chk = ($checked) ? "checked" : "";
  $cls = ($checked) ? "lcheck-on" : "lcheck";
  $lid = ($lblId) ? "id='" . $lblId . "'" : "";
  echo <<<eos
<input id='$id' type='checkbox' $chk class='lcheck' onpropertychange='lcheckc(this)' $onc ondblclick="this.checked=!this.checked;$ond"><label unselectable='on' $lid class='$cls' onclick="lcheck(this)$ond" ondblclick="lcheck(this)$ond">$caption</label>
eos;
}
/*
 * Render group of checkboxes from record array
 * Designed to work with ui.js functions getCheckedValues() and setCheckedValues()
 * $parentId: ID of enclosing <span> 
 * $id: common ID of rendered checkboxes
 * $records: [[field=>value,field=>value,..],..]
 * $valueField: record field to use as checkbox value
 * $labelField: record field to use as checkbox label
 * $delim: delimiter between rendered checkboxes
 * Note: Designed to work with getCheckedValues(id, parentId) from ui.js
 */
function renderLabelChecks($parentId, $parentClass = "", $id, $records, $valueField, $labelField, $delim = "<br>") {
  echo "<span id='$parentId' class='$parentClass'>";
  foreach ($records as &$rec) {
    $val = $rec[$valueField];
    $lbl = $rec[$labelField];
    echo <<<eos
<input id='$id' value='$val' type='checkbox' class='lcheck' onpropertychange='lcheckc(this)' ondblclick="this.checked=!this.checked"><label unselectable='on' class='lcheck' onclick="lcheck(this)" ondblclick="lcheck(this)">$lbl</label>$delim
eos;
  }
  echo "</span>";
}
function renderLabelRadio($id, $name, $caption, $checked = false, $style = null, $onclick = null, $lblId = null) {  // delim strings in $onclick by unescaped apostrophes only, e.g. alert('hi') 
  $sty = ($style) ? "style='" . $style . "'" : "";
  $onc = ($onclick) ? "onclick=\"" . $onclick . "\"" : "";
  $ond = ";" . $onclick;
  $chk = ($checked) ? "checked" : "";
  $cls = ($checked) ? "lcheck-on" : "lcheck";
  $lid = ($lblId) ? "id='" . $lblId . "'" : "";
  echo "<input id='$id' name='$name' type='radio' $chk class='lcheck' onpropertychange='lcheckc(this)' $onc ondblclick=\"$ond\"><label unselectable='on' $lid class='$cls' onclick=\"lrcheck(this)$ond\" ondblclick=\"lrcheck(this)$ond\">$caption</label>";
}
/*
 * Render group of radio buttons from record array
 * $parentId: ID of enclosing <span> 
 * $id: common ID of rendered checkboxes
 * $records: [[field=>value,field=>value,..],..]
 * $valueField: record field to use as checkbox value
 * $labelField: record field to use as checkbox label
 * $delim: delimiter between rendered checkboxes
 * Note: Designed to work with getCheckedValues(id, parentId) from ui.js
 */
function renderLabelRadios($parentId, $parentClass = "", $id, $records, $valueField, $labelField, $delim = "<br>") {
  echo "<span id='$parentId' class='$parentClass'>";
  foreach ($records as &$rec) {
    $val = $rec[$valueField];
    $lbl = $rec[$labelField];
    echo <<<eos
<input id='$id' name='$id' value='$val' type='radio' class='lcheck' onpropertychange='lcheckc(this)'><label unselectable='on' class='lcheck' onclick="lrcheck(this)" ondblclick="lrcheck(this)">$lbl</label>$delim
eos;
  }
  echo "</span>";
}

// Basic / permission check
// Will shortcircuit javascript if user is basic (non-EMR) or $ro=true (read-only); otherwise $jscall is rendered
// Example usage:
// function addSchedPop(userId, date, time) {
//   <? permCheck($myLogin->permissions->accessSchedule >= Permissions::ACCESS_UPDATE, "addSchedPop2(userId, date, time)") ? > 
// }
function permContinue($test, $jscall) {
  //if (premiumOnlyJs()) {
   // return;
  //}
  if ($test) {
    echo $jscall;
  }
}

// Javascript renderers
function premiumOnlyJs() {
  global $login;
  if ($myLogin->isBasic()) {
    echo "Pop.Msg.showCritical('This feature is available only to Clicktate Premium subscribers.');";
    return true;
  }
  return false;
}
function timeoutCallbackJs($workingOffFn = '') {
  echo <<<END
function timeoutCallback() {
  $workingOffFn
  Pop.Msg.showCritical("We're sorry, but your session has timed out from inactivity.", goHome);
}
function goHome(confirmed) {
  window.location.href = "index.php";
}

END;
}

// UI renderers
function selectedIf($test) {
  echo ($test) ? "selected" : "";
}
function checkedIf($test) {
  echo ($test) ? "checked" : "";
}
function yesIf($test) {
  echo ($test) ? "Yes" : "No";
}
function toString($test) {
  echo ($test) ? "1" : "0";
}
function echoIf($test, $trueText, $falseText = "") {
  echo ($test) ? $trueText : $falseText;
}
function disabledIf($test) {
  echo ($test) ? "disabled='disabled'" : "";
}

// String helpers
function splitJoin($text, $splitDelim, $joinDelim) {
  $words = explode($splitDelim, $text);
  for ($i = 0; $i < count($words); $i++) {
    $words[$i] = trim($words[$i]);  
  }
  return implode($joinDelim, $words);
}

// UI helpers
function plural($value, $noun) {
  if ($value == 1) {
    return "1 " . $noun;
  } else {
    return $value . " " . $noun . "s";
  }
}
function isBlank($s) {
  return (trim($s) == "");
}
function isEmpty($a) {
  return ($a == null || count($a) == 0);
}
function nullify($s) {
  return (isBlank($s)) ? null : $s;
}
function nc($o, $default) {  // null coalesce, in PHP 5.3 can replace with $o ?: $default  
  return ($o) ? $o : $default;
}
// Build errors array containing single error
// (To allow errors tile to display a non-validation error)
function singleError($field, $msg) {
  $errors = array();
  $errors[$field] = $msg;
  return $errors;
}

// Frontend validators
function isValidEmail($email) {  
  // First, we check that there's one @ symbol, and that the lengths are right
  if (!preg_match("/^[^@]{1,64}@[^@]{1,255}$/", $email)) {
    // Email invalid because wrong number of characters in one section, or wrong number of @ symbols.
    return false;
  }
  // Split it into sections to make life easier
  $email_array = explode("@", $email);
  $local_array = explode(".", $email_array[0]);
  for ($i = 0; $i < sizeof($local_array); $i++) {
     if (!preg_match("/^(([A-Za-z0-9!#$%&'*+\/=?^_`{|}~-][A-Za-z0-9!#$%&'*+\/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$/", $local_array[$i])) {
      return false;
    }
  }
    if (!preg_match("/^\[?[0-9\.]+\]?$/", $email_array[1])) { 
    // Check if domain is IP. If not, it should be valid domain name
    $domain_array = explode(".", $email_array[1]);
    if (sizeof($domain_array) < 2) {
        return false; // Not enough parts to domain
    }
    for ($i = 0; $i < sizeof($domain_array); $i++) {
      if (!preg_match("/^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$/", $domain_array[$i])) {
        return false;
      }
    }
  }
  return true;
} 
function isValidPassword($test) {
  if (strlen($test) < 6) {
     return false;
  }
  if (!preg_match("/[a-zA-Z]+/", $test)) {
     return false;
  }
  if (!preg_match("/[0-9]+/", $test)) {
     return false;
  }
  return true;
}

// Session/post helpers
function clearSessionVars() {
  unset($_SESSION["login"]);
  session_destroy();        
  session_unset();     
}
function getPostedChecks($name, $postArray) {  // sync up a posted check array with another posted array
  $a = (isset($_POST[$name])) ? $_POST[$name] : array();
  $ixs = array_keys($postArray);
  $b = array();
  foreach ($ixs as $ix) {
    $b[$ix] = array_key_exists($ix, $a);
  }
  return $b;  // returns boolean array
}

// Math functions
function rnd() {
  return "&" . mt_rand(0, 99999999);
}
function idiv($x, $y) {  // integer division
  return ($x - ($x % $y)) / $y;
}
