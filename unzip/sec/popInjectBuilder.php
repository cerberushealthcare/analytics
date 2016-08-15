<?php 
require_once "inc/noCache.php";
require_once "inc/requireLogin.php";
require_once "php/delegates/JsonDelegate.php";

$field = $_GET["fld"];
$templateId = $_GET["tid"];
$sectionId = isset($_GET["sid"]) ? $_GET["sid"] : null;
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <!-- Copyright (c)2006 by LCD Solutions, Inc.  All rights reserved. -->
  <!-- http://www.clicktate.com -->
  <head>
    <title>clicktate : Dialog</title>
    <link rel="stylesheet" type="text/css" href="css/popup.css" media="screen" />
    <script language="JavaScript1.2" src="js/ajax.js?2"></script>
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
      <input id=copy type="button" value="Build Action" onclick="copyToParent()">
      <input type="button" value="Cancel" onclick="window.close()">
    </div>
  </body>
</html>
<script>
var allTemplates = false;
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
  for (var pid in section.pars) {
    var p = section.pars[pid];
    h += "<tr>";
    h += "<td><input name=chk value='" + p.uid + "' type=checkbox></td><td width=2></td>";
    h += "<td nowrap>P:<b>" +p.uid + "</b></td><td width=15 nowrap></td><td nowrap>" + p.desc + "</td>";
    h += "</tr>";
  }
  document.getElementById("content").innerHTML = h;
  copy.style.display = "inline";
}
function copyToParent() {
  var fld = window.opener.document.forms[0].<?=$field ?>;
  var chks = document.getElementsByName("chk");
  var action = fld.value;
  if (action != "") {
    action += "; ";
  }
  action += "inject(";
  var first = true;
  for (var i = 0; i < chks.length; i++) {
    if (chks[i].checked) {
      if (! first) {
        action += "); inject(";
      }
      action += section.uid + "." + chks[i].value;
      first = false;
    }
  }
  action += ")";
  fld.value = action;
  window.opener.makeDirty();
  window.close();
}
</script>
