<?php
$v = file_get_contents('version', FILE_USE_INCLUDE_PATH);
?>
<!DOCTYPE html>
<html>
<head>
  <meta name='viewport' content='user-scalable=no,width=device-width,initial-scale=1.0,maximum-scale=1.0'/>
  <meta name='apple-mobile-web-app-capable' content='yes'/>
  <meta name='apple-mobile-web-app-status-bar-style' content='black'/>
  <link rel='apple-touch-icon-precomposed' href='img/skywatch.png'/>
  <link rel='apple-touch-startup-image' href='img/skywatch-splash.png'/>
  <link rel='stylesheet' href='css/jquery.mobile-1.3.2.min.css'>
  <link rel='stylesheet' href='css/my-jquery-overrides.css?<?=$v?>'>
  <link rel='stylesheet' href='css/my.css?<?=$v?>'>
  <script type='text/javascript' src='js/jquery.js'></script>
  <script type='text/javascript' src='js/jquery.mobile-1.3.2.min.js'></script>
  <script type='text/javascript' src='js/lcd.js?<?=$v?>'></script>
  <script type='text/javascript' src='js/my-plugins.js?<?=$v?>'></script>
  <script type='text/javascript' src='js/data/Flights.js?<?=$v?>'></script>
  <script type='text/javascript' src='js/ui/Pages.js?<?=$v?>'></script>
  <title>Sky Watch</title>
</head>
<body>

</body>
<script>
$(document).ready(function() {
  var t = $.now();
  alert(t);
  var q = $.now();
  alert ((q - t) / 1000);
})
</script>
</html>