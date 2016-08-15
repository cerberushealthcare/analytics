<?php
require_once "inc/noCache.php";
require_once "inc/requireLogin.php";
require_once "php/delegates/JsonDelegate.php";

$templateId = $_GET["tid"];
$sectionId = $_GET["sid"];
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <!-- Copyright (c)2006 by LCD Solutions, Inc.  All rights reserved. -->
  <!-- http://www.clicktate.com -->
  <head>
    <title>clicktate : Dialog</title>
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
	 <table border=0 cellpadding=0 cellspacing=0 width=100%>
      <tr>
        <td colspan=2 align="center">
          <span id="loading">
            <br><br><br><br><br><br>Copying questions...<br>
            <img src="img/loading.gif">
          </span>
        </td>
      </tr>
	 </table>
	 <div id="content">
    </div>
    <div id="action">
      <input id=copy type="button" value="Copy Selected" onclick="copyToParent()">
      <input type="button" value="Cancel" onclick="window.close()">
    </div>
  </body>
</html>
<script>
var allTemplates = true;
templateId = <?=$templateId ?>;
template = <?=JsonDelegate::jsonJTemplate($templateId) ?>;
<?php if (isset($sectionId)) { ?>
  getSection(<?=$sectionId ?>);
<?php } else { ?>
  showSections();
<?php } ?>
function showPars() {
  doBreadcrumb();
  var h ="<table border=0 cellpadding=0 cellspacing=0>";
  for (var i = 0; i < section.pars.length; i++) {
    var p = section.pars[i];
    var pid = p.id;
    var eff = (p.effective == "0000-00-00 00:00:00") ? "" : " (" + p.effective + ")";
    var desc = (p.current) ? p.desc + eff : "<span style='color:#a9a9a9'>" + p.desc + eff + "</span>";
    var sty = (p.current) ? "" : " style='color:#a9a9a9'";
    var href = "javascript:getPar(" + pid + ")";
//    h += "<tr style='padding-bottom:2px'>";
//    h += "<td><a href='" + href + "'><img src='img/folder.gif'></a></td><td width=2 nowrap></td>";
//    h += "<td nowrap><a href='" + href + "'>P:<b>" + p.uid + "</b></a></td><td width=15 nowrap></td><td nowrap>" + p.desc + "</td>";
//    h += "</tr>";
      h += "<tr style='padding-bottom:2px'>";
      h += "<td><a href='" + href + "'><img src='img/folder.gif'></a></td><td width=2 nowrap></td>";
      h += "<td nowrap><a href='" + href + "'" + sty + ">P:<b>" + p.uid + "</b></a></td><td width=15 nowrap></td><td nowrap>" + desc + "</td>";
      h += "</tr>";
  }
  document.getElementById("content").innerHTML = h;
}
function showQuestions() {
  doBreadcrumb();
  var h ="<table border=0 cellpadding=0 cellspacing=0>";
  for (var i = 0; i < par.questions.length; i++) {
    var q = par.questions[i];
    h += "<tr>";
    h += "<td><input name=chk value='" + q.id + "' type=checkbox></td><td width=2></td>";
    h += "<td nowrap>Q:<b>" +q.uid + "</b></td><td width=15 nowrap></td><td nowrap>" + q.desc + "</td>";
    h += "</tr>";
  }
  h += "</table>";
  document.getElementById("content").innerHTML = h;
  copy.style.display = "inline";
}
function copyToParent() {
  var chks = document.getElementsByName("chk");
  var sel = "";
  for (var i = 0; i < chks.length; i++) {
    if (chks[i].checked) {
      if (sel != "") sel += ",";
      sel += chks[i].value;
    }
  }
  breadcrumb.innerHTML = "";
  content.innerHTML = "";
  action.innerHTML = "";
  content.id = "";
  loading.style.display = "inline";
  window.opener.document.forms[0].copyValues.value = sel;
  window.opener.submitCopy();
}
</script>
