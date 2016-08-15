<?php 
require_once "inc/noCache.php";
require_once "inc/requireLogin.php";
require_once "php/delegates/JsonDelegate.php";

$field = $_GET["fld"];
if (isset($_GET['meth'])) {
  $method = $_GET['meth'];
  $button = 'Build ' . $method;
} else {
  $method = null;
  $button = 'Select';
}
$level = $_GET["l"];  // 2=par,3=question,4=option
$delim = isset($_GET["delim"]) ? $_GET["delim"] : "; ";
$templateId = $_GET["tid"];
$sectionId = $_GET["sid"];
$parId = $_GET["pid"];
$questionId = $_GET["qid"];
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <!-- Copyright (c)2006 by LCD Solutions, Inc.  All rights reserved. -->
  <!-- http://www.clicktate.com -->
  <head>
    <title>Clicktate Admin Builder</title>
    <link rel="stylesheet" type="text/css" href="css/popup.css" media="screen" />
    <script language="JavaScript1.2" src="js/old-ajax.js?2"></script>
    <script language="JavaScript1.2" src="js/yahoo-min.js"></script>
    <script language="JavaScript1.2" src="js/connection-min.js"></script>
    <script language="JavaScript1.2" src="js/admin-pop.js"></script>
    <SCRIPT language="JavaScript">
    if (document.images) {
      preload_image = new Image(16, 16); 
	   preload_image.src="img/folder.gif"; 
	 }
    </SCRIPT>
  </head>
  <body>
	 <table border=0 cellpadding=0 cellspacing=0 width=100%>
      <tr>
        <td>
	       <div id="breadcrumb">&nbsp;</div>
        </td>
        <td width=20 valign=top nowrap><span id="working"></span></td>
  		</tr>
    </table>
	 <div id="content">
    </div>
    <div id="action">
      <input id=copy type="button" value="<?=$button ?>" onclick="copyToParent()">
      <input type="button" value="Cancel" onclick="window.close()">
    </div>
  </body>
</html>
<script>
var allTemplates = false;
templateId = <?=$templateId ?>;
template = <?=JsonDelegate::jsonJTemplate($templateId) ?>;
<?php if ($questionId) { ?>
  section = template.sections["S<?=$sectionId ?>"];
  section.pars = <?=JsonDelegate::getPars($sectionId) ?>;
  par = section.pars[<?=$parId ?>];
  par.questions = <?=JsonDelegate::jsonJQuestions($parId) ?>;
  getQuestion(<?=$questionId ?>);
<?php } else if ($parId) { ?>
  section = template.sections["S<?=$sectionId ?>"];
  section.pars = <?=JsonDelegate::getPars($sectionId) ?>;
  getPar(<?=$parId ?>);
<?php } else if ($sectionId) { ?>
  getSection(<?=$sectionId ?>);
<?php } else { ?>
  showSections();
<?php } ?>

function getQuestion(id) {
  question = par.questions[id];
  showOptions();
}
function showPars() {
  doBreadcrumb();
  var h ="<table border=0 cellpadding=0 cellspacing=0>";
  for (var i = 0; i < section.pars.length; i++) {
    var p = section.pars[i];
    var pid = p.parId || p.id;
    var eff = (p.effective == "0000-00-00 00:00:00") ? "" : " (" + p.effective + ")";
    var desc = (p.current) ? p.desc + eff : "<span style='color:#a9a9a9'>" + p.desc + eff + "</span>";
    var sty = (p.current) ? "" : " style='color:#a9a9a9'";
    <? if ($level == "2") { ?>
      h += "<tr>";
      h += "<td><input name=chk value='" + p.uid + "' type=checkbox></td><td width=2></td>";
      h += "<td nowrap>P:<b>" + p.uid + "</b></td><td width=15 nowrap></td><td nowrap>" + desc + "</td>";
      h += "</tr>";
    <? } else { ?>
      var href = "javascript:getQuestions(" + pid + ")";
      h += "<tr style='padding-bottom:2px'>";
      h += "<td><a href='" + href + "'><img src='img/folder.gif'></a></td><td width=2 nowrap></td>";
      h += "<td nowrap><a href='" + href + "'" + sty + ">P:<b>" + p.uid + "</b></a></td><td width=15 nowrap></td><td nowrap>" + desc + "</td>";
      h += "</tr>";
    <? } ?>
  }
  document.getElementById("content").innerHTML = h;
  <? if ($level == "2") { ?>
    copy.style.display = "inline";
  <? } ?>
}
function showQuestions() {
  doBreadcrumb();
  var h ="<table border=0 cellpadding=0 cellspacing=0>";
  for (var qid in par.questions) {
    var q = par.questions[qid];
    <? if ($level == "3") { ?>
      h += "<tr>";
      h += "<td><input name=chk value='" + <?if($method){?>q.uid<?}else{?>q.id<?}?> + "' type=checkbox></td><td width=2></td>";
      h += "<td nowrap>Q:<b>" + q.uid + "</b></td><td width=15 nowrap></td><td nowrap>" + q.desc + "</td>";
      h += "</tr>";
    <? } else { ?>
      var href = "javascript:getQuestion(" + qid + ")";
      h += "<tr style='padding-bottom:2px'>";
      h += "<td><a href='" + href + "'><img src='img/folder.gif'></a></td><td width=2 nowrap></td>";
      h += "<td nowrap><a href='" + href + "'>Q:<b>" + q.uid + "</b></a></td><td width=15 nowrap></td><td nowrap>" + q.desc + "</td>";
      h += "</tr>";
    <? } ?>
  }
  document.getElementById("content").innerHTML = h;
  <? if ($level == "3") { ?>
    copy.style.display = "inline";
  <? } ?>
}
function showOptions() {
  doBreadcrumb();
  var h ="<table border=0 cellpadding=0 cellspacing=0>";
  if (question.opts) {
    for (var i = 0; i < question.opts.length; i++) {
      var o = question.opts[i];
      h += "<tr>";
      h += "<td><input name=chk value='" + o.uid + "' type=checkbox></td><td width=2></td>";
      h += "<td nowrap><b>" +o.uid + "</b></td>";
      h += "</tr>";
    }
  }
  h += "</table>";
  document.getElementById("content").innerHTML = h;
  copy.style.display = "inline";
}
function copyToParent() {
  var fld;
  if (window.opener.document.forms.length > 0) {
    fld = window.opener.document.forms[0].<?=$field ?>;
  } else {
    fld = window.opener.document.getElementById('<?=$field ?>');
  }
  var chks = document.getElementsByName("chk");
  var action = fld.value;
  if (action != "") {
    action += "<?=$delim ?>";
  }
  <? if ($method) { ?>
    action += "<?=$method ?>(";
  <? } else { ?>
    action += "[";
  <? } ?>
  var first = true;
  for (var i = 0; i < chks.length; i++) {
    if (chks[i].checked) {
      if (! first) {
        <? if ($method) { ?>
          action += ")<?=$delim ?><?=$method ?>(";
        <? } else { ?>
          action += '<?=$delim?>';
        <? } ?>
      }
      <? if ($method) { ?>
        <? if ($level == 2) { ?>
          action += section.uid + "." + chks[i].value;
        <? } else if ($level == 3) { ?>
          action += section.uid + "." + par.uid + "." + chks[i].value;
        <? } else { ?>
          action += section.uid + "." + par.uid + "." + question.uid + "." + chks[i].value;
        <? } ?>
      <? } else { ?>
        action += chks[i].value;
      <? } ?>
      first = false;
    }
  }
  <? if ($method) { ?>
    action += ")";
  <? } else { ?>
    action += ']';
  <? } ?>
  fld.value = action;
  if (window.opener.makeDirty)
    window.opener.makeDirty();
  window.close();
}
</script>
