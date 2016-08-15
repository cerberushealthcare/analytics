<?php 
require_once "inc/noCache.php";
require_once "inc/requireLogin.php";
require_once "php/dao/LookupAdminDao.php";
require_once "php/dao/JsonDao.php";

$action = $_GET['act'];
$id = $_GET['id'];
$title = "";
$h = array();
switch ($action) {
  case 'testActionSearch':  
    $pref = JsonDao::getPref($id);
    $title = "'$pref' occurs in:";
    $tables = LookupAdminDao::searchTestActionRefs($pref);
    $h[] = table(1);
    foreach ($tables as $label => $rows) {
      $h[] = tr(1, "<td colspan=2><h3>$label:</h3></td>");
      foreach ($rows as $row) {
        $h[] = tr(1);
        $a = qanchor($row);
        $field = str_replace($pref, "<b>$pref</b>", $row['field']);
        $h[] = "<td>$a</td><td class=f>$field</td>";
        $h[] = tr();
      }
      $h[] = tr(1, "<td colspan=2>&nbsp;</td>");
    }
    $h[] = table();
    break;
  case 'qsyncSearch':
    $title = "'$id' used by:";
    $rows = LookupAdminDao::searchQSyncs($id);
    $h[] = table(1);
    foreach ($rows as $row) {
      $a = qanchor($row);
      $p = ptext($row);
      $h[] = tr(1, "<td>$p</td><td>$a</td>");
    }
    $h[] = table();
    break;
}
$html = implode("", $h);

function table($start = 0) {
  return ($start) ? "<table border=0 cellpadding=0 cellspacing=0>" : "</table>";
}
function tr($start = 0, $content = null) {
  static $x = false;
  $x = ! $x;
  $class = ($x) ? "class=off" : "";
  if ($content != null) {
    return "<tr $class>$content</tr>"; 
  }
  return ($start) ? "<tr $class>" : "</tr>";
}
function ptext($row) {
  $current = $row['current'];
  $eff = $row['date_effective'];
  $eff = ($eff == "0000-00-00 00:00:00") ? '' : "($eff)";
  $pref = $row['pref'];
  $pref = "$pref $eff";
  return ($current) ? "<span>$pref</span>" : "<span class=o>$pref</span>";
}
function qanchor($row) {
  $tid = $row['tid'];
  $sid = $row['sid'];
  $qid = $row['qid'];
  $qref = $row['qref'];
  return "<a href='adminQuestion.php?id=$qid&sid=$sid&tid=$tid'>$qref</a>";
}
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <!-- Copyright (c)2006 by LCD Solutions, Inc.  All rights reserved. -->
  <!-- http://www.clicktate.com -->
  <head>
    <title>Clicktate Admin</title>
    <style>
H3 {
display:inline;
}
BODY {
font-family:Calibri;
}
DIV#content B {
background-color:yellow;
font-weight:normal;
}
DIV#content A {
font-weight:bold;
}
TR {
}
TR.off {
background-color:#EEF6FC;
}
TD {
vertical-align:top;
padding:0 5px 4px 5px;
font-size:10pt;
}
TD.f {
font-family:Tahoma;
font-size:10pt;
}
TD SPAN {
font-weight:bold;
}
TD SPAN.o {
color:red;
}
    </style>
  </head>
  <body>
    <h2><?=$title ?></h2>
	  <div id="content"><?=$html ?></div>
  </body>
  <input type="button" onclick="history.go(-1)" value="< Back">
</html>
