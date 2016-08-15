<?
$pw = isset($_GET["pw"]) ? $_GET["pw"] : "";
if ($pw != "yukon") {
  header("Location: index.php");
  exit;
}
?>
<html>
  <head>
    <style>
P {
  font-family:Calibri;
  font-size:10pt;
}
TABLE {
  border-collapse:collapse;
}
TH {
  border:1px solid #c0c0c0;
  font-family:Calibri;
  font-size:10pt;
  background-color:#008E80;
  color:white;
  padding:0 2px;
}
TD {
  border:1px solid #c0c0c0;
  vertical-align:top;
  font-family:Calibri;
  font-size:10pt;
  padding:0 2px;
  background-color:#EDF9F7;
}
TD.nw {
  width:80px;
}
TR.r1 TD {
  background-color:#D0F0EC;
}
B {
}
    </style>
  </head>
  <body>
    <table>
      <tr>
        <th>Time</th>
        <th>IP</th>
        <th>QS</th>
        <th>Referrer</th>
        <th>Browser</th>
        <th>Cookie</th>
      </tr>
<?php
$f = fopen("page-hits.csv", "r");
$toggle = false;
$l = substr(fgets($f), 1, -1);
$i = 0;
while (!feof($f)) {
  $i++;
  echo "<tr class=r$toggle>";
  $a = explode("\",\"", $l);
  $ip = $a[1];
  $a[1] = "<a href=\"javascript:show('" . $ip . "')\">" . $ip . "</a>";
  $bro = explode(";", $a[4]);
  if (count($bro) > 3) {
    $a[4] = $bro[0] . ";" . $bro[1] . ";" . $bro[2] . ";...)";
  }
  $ref = $a[3];
  if ($ref != "") {
    $text = str_replace("&", "& ", $ref);
    $text = str_replace("=", "= ", $text);
    $a[3] = "<a target='_blank' href='" . $ref . "'>" . $text . "</a>";
  }
  $a[5] = str_replace("=", "= ", $a[5]);
  echo "<td class=nw>" . implode("</td><td>", $a) . "</td>";  
  echo "</tr>";
  $toggle = ! $toggle;
  $l = substr(fgets($f), 1, -1);
}
@fclose($f);
?>
    </table>
    <p>
      Total records: <?=$i ?>
    </p>
  </body>
</html>
<script> 
function show(ip) {
  window.open("http://ws.arin.net/whois/?queryinput=" + ip, "ip", "height=400,width=700,resizable=1,toolbar=0,scrollbars=1,menubar=0");
}
</script>
