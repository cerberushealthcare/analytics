<!DOCTYPE html>
<html manifest='manifest.php'>
<head>
  <meta name='viewport' content='user-scalable=no,width=device-width,initial-scale=1.0,maximum-scale=1.0'/>
  <meta name='apple-mobile-web-app-capable' content='yes'/>
  <meta name='apple-mobile-web-app-status-bar-style' content='black'/>
  <link rel='apple-touch-icon-precomposed' href='img/skywatch.png'/>
  <link rel='apple-touch-startup-image' href='img/skywatch-splash.png'/>
  <link rel='stylesheet' href='css/jquery.mobile-1.3.2.min.css'>
  <link rel='stylesheet' href='css/my-jquery-overrides.css'>
  <link rel='stylesheet' href='css/my.css'>
  <script type='text/javascript' src='js/jquery-1.10.2.min.js'></script>
  <script type='text/javascript' src='js/jquery.mobile-1.3.2.min.js'></script>
  <script type='text/javascript' src='js/lcd.js'></script>
  <script type='text/javascript' src='js/my-plugins.js'></script>
  <script type='text/javascript' src='js/data/Flights.js'></script>
  <script type='text/javascript' src='js/ui/Pages.js'></script>
  <title>Sky Watch</title>
</head>
<body>
  <div id='home' data-role='page'>
    <div class='head'>
      <table>
        <tr>
          <td class='l'>&nbsp;</td>
          <th class='m'>Sky Watch</th>
          <td class='r'>&nbsp;</td>
        </tr>
      </table>
    </div>  
    <div id='body-home' class='body'>
      <form id='location-form'>
        <div class='fields'>
          <label for='edit-airport'>Airport</label>
          <input type='text' id='edit-airport' class='required' />
          <label for='edit-lat'>Latitude</label>
          <input type='text' id='edit-lat' class='required' />
          <label for='edit-long'>Longitude</label>
          <input type='text' id='edit-long' class='required' />
        </div>
        <a id='edit-save' href='#' data-role='button' data-icon='ios7-fwd' data-iconpos='right' data-theme='b'>Set</a>
      </form>
    </div>
  </div>
  <div id='radar' data-role='page'>
    <div class='head'>
      <table>
        <tr>
          <td class='l'><a class='back' href='#'></a></td>
          <th class='m'>Sky Watch</th>
          <td class='r'>&nbsp;</td>
        </tr>
      </table>
    </div>  
    <div id='body-radar' class='body'>
      <div class='canvas'>
        <canvas></canvas>
      </div>
      <div class='lock'></div>
      <div class='config'></div>
    </div>
  </div>
</body>
<script>
$(document).ready(function() {
  Pages.start();
})
</script>
</html>