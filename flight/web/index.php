<?php
set_include_path('../');
require_once 'app/util.php';
require_once 'app/c/tracking/Tracking.php';
//
$airport = geta($_GET, 'a');
$lat = geta($_GET, 't');  //, '38.249175');  
$long = geta($_GET, 'g');  //, '-85.657111');
if ($airport == null) {
  header("Location: get-location.php");
  exit;
}
if ($lat == null)
  $latlong = $airport;
else
  $latlong = "$lat,$long"; 
$recs = Tracking::fetch($airport, $lat, $long);
?>
<html>
  <head>
    <title>Flight Tracker</title>
    <style>
      BODY {
        font-family:Calibri,Arial;
        background-color:#09335b;
        color:white;
      }
      A {
        color:white;
        text-decoration:none;
      }
      TD.foot {
        padding-top:10px;
        text-align:center;
        font-size:16pt;
        border-top:1px solid #909090;
      }
      TD.foot A {
        text-decoration:underline;
      }
      TABLE {
        border-collapse:collapse;
        border-spacing:0;
      }
      TH {
        text-align:left;
        padding:2px 5px;
        font-size:20pt;
        color:#909090;
        border-bottom:1px solid #909090;
      }
      TD {
        padding:3px 5px;
        font-size:20pt;
      }
      .red {
        color:#ff00ff;
      }
      .rj {
        text-align:right;
      }
      .hilite {
        background-color:black;
      }
      .gray {
        background-color:#e0e0e0;
      }
      .cyan {
        background-color:cyan;
      }
      .fgray {
        color:#909090;
      }
      .fgray A {
        color:#909090;
      }
      .blue, TD.blue A {
        color:#80FFFF;
      }
      .break {
        height:1px;
        border-top:1px solid #909090;
        border-bottom:1px solid #909090;
      }
      .it {
        font-style:italic;
      }
    </style>
  </head>
  <body>  
<?php
$broke = 0;
echo "<table>";
echo "<thead><th></th><th>Craft</th><th>O/>D</th><th class='rj'>AT</th><th class='rj'>XT</th><th class='rj'>EMA</th><th class='rj'>Dist</th><th>Bear</th><th class='rj'>Alt</th><th>Hd</th><th class='rj'>Spd</th>";
echo "<tbody>";
foreach ($recs as $rec) {
  if ($rec->_sort >= '2,000.00' && ! $broke) {
    echo "<tr><td colspan=11 class='break'></td></tr>";
    $broke = 1;
  }
  $cls = isset($rec->_hilite) ? 'hilite' : ''; // ($rec->_outbound ? 'cyan' : ($rec->_inbound ? '' : 'gray'));
  if (! $rec->_targeted)
    $cls .= ' fgray';
  echo "<tr class='$cls'>";
  $cls = $rec->_ft == 'A' ? 'blue' : '';
  echo "<td class='$cls'><a href='http://www.flightradar24.com/data/flights/$rec->code'>$rec->id</a></td>";
  $cls = $rec->_special ? 'red' : '';
  echo "<td class='$cls'>$rec->craft</td>";
  $cls = $rec->_inbound ? 'red' : '';
  echo "<td class='$cls'>$rec->_oa</td>";
  echo "<td class='rj'>$rec->_at</td>";
  $cls = $rec->_close ? 'rj red' : 'rj';
  echo "<td class='$cls'>$rec->_xt</td>";
  $cls = isset($rec->_approach) ? 'rj red' : 'rj';
  echo "<td class='$cls'>$rec->_ema</td>";
  echo "<td class='rj'>$rec->_dist</td>";
  echo "<td class='rj'>$rec->_mb</td>";
  echo "<td class='rj'>$rec->_alt</td>";
  echo "<td class=''>$rec->_dir</td>";
  echo "<td class='rj'>$rec->_mph</td>";
  //echo "<td class='rj'>$rec->projected</td>";
  //echo "<td class='rj'>$rec->reg</td>";
  //echo "<td class='rj'>$rec->code</td>";
  //echo "<td class='rj'>$rec->unk2</td>";
  echo "</tr>";
}
$rnd = mt_rand(10000000, 99999999);
?>
    <tr><td colspan=11 class='foot'>
      <?=date("g:iA")?> (<?=$latlong?>)
      &nbsp;<a href='get-location.php?<?=$rnd?>'>Reset</a>
      &nbsp;<a href='index.php?a=SDF&t=38.249175&g=-85.657111&rnd=<?=$rnd?>'>Home</a>
    </td></tr>
<?
echo "</tbody></table>";
?>
  </body>
</html>
