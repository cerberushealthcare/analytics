<?php
require_once 'server.php';
require_once 'php/c/patient-list/PatientList.php';
require_once 'php/data/rec/sql/Facesheets.php';
//
echo "<style>BODY {font-family:Segoe UI} H3 {margin:0} TABLE {border-collapse:collapse;margin-bottom:1em;} TH {border:1px solid #c0c0c0} TD {padding:5px;border:1px solid #c0c0c0;vertical-align:top}</style>";
try {
  LoginSession::verify_forServer()->requires($login->admin);
  $ugid = $_GET['ugid'];
  switch ($action) {
    //
    case 'find':      
      echo "
<h3>Lookup by ID</h3>
<form method='get' action='serverAdmMerge.php'><input type='hidden' name='ugid' value='$ugid'/><input type='hidden' name='action' value='ifetch'/>
<input type='text' name='id1'/> <input type='text' name='id2'/><input type='submit'/>
</form>
<h3 style='margin:0'>Lookup by last name</h3>
<form method='get' action='serverAdmMerge.php'><input type='hidden' name='ugid' value='$ugid'/><input type='hidden' name='action' value='nfetch'/>
<input type='text' name='id1'/> <input type='text' name='id2'/><input type='submit'/>
</form>";
      exit;
    case 'ifetch':
      $id1 = $_GET['id1'];
      $id2 = $_GET['id2'];
      $recs1 = $id1 ? PStub_Search::search($ugid, null, null, $id1) : null;
      $recs2 = $id1 ? PStub_Search::search($ugid, null, null, $id2) : null;
      $cid1 = empty($recs1) ? "" : $recs1[0]->clientId;
      $cid2 = empty($recs2) ? "" : $recs2[0]->clientId;
      echo "<table><tr><th>LEFT</th><th>RIGHT</th></tr><tr><td>";
      p_r($recs1);
      echo "</td><td>";
      p_r($recs2);
      echo "</td></tr></table>";
      echo "
<h3>Fetch by client ID</h3>
<form method='get' action='serverAdmMerge.php'><input type='hidden' name='ugid' value='$ugid'/><input type='hidden' name='action' value='check'/>
<input type='text' name='cid1' value='$cid1'/> <input type='text' name='cid2' value='$cid2'/><input type='submit'/>
</form>";
      exit;
    case 'check':
      $cid1 = $_GET['cid1'];
      $cid2 = $_GET['cid2'];
      $fs1 = Facesheet_Merge::fetch($cid1);
      $fs2 = Facesheet_Merge::fetch($cid2);
      echo "<form method='get' action='serverAdmMerge.php'><input type='hidden' name='ugid' value='$ugid'/><input type='hidden' name='action' value='merge'/><input type='hidden' name='cid1' value='$cid1'/><input type='hidden' name='cid2' value='$cid2'/>";
      echo "<table><tr><th>LEFT</th><th>RIGHT</th></tr><tr><td>";
      p_r($fs1);
      echo "</td><td>"; 
      p_r($fs2);
      echo "</td></tr><tr><th>Survivor<br/><input name='survivor' type='submit' value='Left'/></th><th>Survivor<br/><input name='survivor' type='submit' value='Right'/></th></tr></table>";
      exit;
    case 'merge':
      $ugid = $_GET['ugid'];
      $cid1 = $_GET['cid1'];
      $cid2 = $_GET['cid2'];
      if ($_GET['survivor'] == 'LEFT') {
        $source = $cid2;
        $target = $cid1;
      } else {
        $source = $cid1;
        $target = $cid2;
      }
      $rows = fetchArray("call merge($source, $target, 1)");
      $fs = Facesheet_Merge::fetch($target);
      echo "<h3>Survivor</h3>";
      p_r($fs);
      echo "<a href='serverAdmMerge.php?action=find&ugid=$ugid>Another lookup</a>";
  }
} catch (Exception $e) {
  AjaxResponse::exception($e);
}
