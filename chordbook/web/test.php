<!DOCTYPE html>
<html>
<head>
  <meta charset='utf-8'>
  <meta name='viewport' content='user-scalable=no,width=device-width,initial-scale=1.0,maximum-scale=1.0'>
  <meta name='apple-mobile-web-app-capable' content='yes'>
  <meta name='apple-mobile-web-app-status-bar-style' content='black'>
  <link rel='apple-touch-icon-precomposed' href='icons/icon_60x60.png'>
  <link rel='apple-touch-startup-image' href='icons/splash_320x460.png'>
  <script type='text/javascript' src='js/jquery.js'></script>
  <title>Chordbook</title>
</head>
<body>
  <div id='test'>
  </div>
</body>
<script>
var ct = 0;
var $b = $('<div>Count: 0</div>').appendTo($('#test'));
var $a = $('<div style="border:1px solid red;font-size:2em">Increment Me!!!</div>')
.on('touchstart', function(e) {
  ct++;
  $b.text('Count: ' + ct);
})
.appendTo($('#test').append($('<br><br>')));
</script>
</html>