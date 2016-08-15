<html>
<head>
<!-- <meta http-equiv="refresh" content="60"> -->
<style>
BODY {
  font-family:Calibri,Arial;
}
TABLE {
  border-collapse:collapse;
  border-spacing:0;
}
TH {
  text-align:left;
  padding:2px 5px;
  font-size:20pt;
}
TD {
  border:1px solid #c0c0c0;
  padding:2px 5px;
  font-size:20pt;
}
TD.red {
  color:red;
}
TD.rj {
  text-align:right;
}
.hilite {
  background-color:yellow;
}
.gray {
  background-color:#e0e0e0;
}
.cyan {
  background-color:cyan;
}
</style>
</head>
<body>
<?php
set_include_path('../');
require_once 'server/server.php';
require_once 'app/c/polling/Polling.php';
//
switch ($action) {
  //
  case 'fetch':
    $recs = Polling::fetch();
    echo "<table>";
    echo "<thead><th>ID</th><th>Craft</th><th>Origin</th><th>Dest</th><th>Dir</th><th>Bear</th><th>Dist</th><th>EMA</th><th>Alt</th><th>Lat</th><th>Long</th><th>MPH</th></thead>";
    echo "<tbody>";
    foreach ($recs as $rec) {
      if (! $rec->_inbound && ! $rec->_outbound)
        $cls = 'gray';
      else 
        $cls = isset($rec->_hilite) ? 'hilite' : ($rec->_outbound ? 'cyan' : '');
      echo "<tr class='$cls'>";
      echo "<td>$rec->id</td>";
      echo "<td>$rec->craft</td>";
      echo "<td>$rec->origin</td>";
      echo "<td>$rec->dest</td>";
      echo "<td class='rk'>$rec->dir</td>";
      $cls = isset($rec->_interest) ? 'rj red hilite' : 'rj';
      echo "<td class='$cls'>$rec->_bearing</td>";
      echo "<td class='rj'>$rec->_dist</td>";
      // echo "<td>$rec->_ts</td>";
      echo "<td class='rj'>$rec->_ema</td>";
      echo "<td class='rj'>$rec->_alt</td>";
      echo "<td class='rj'>$rec->_lat</td>";
      echo "<td class='rj'>$rec->_long</td>";
      echo "<td class='rj'>$rec->_mph</td>";
      echo "</tr>";
    }
    echo "</tbody></table>";
    //AjaxResponse::from($map)->out();
    exit;
}
?>
</body>
</html>