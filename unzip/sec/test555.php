<html>
<head>
<script type='text/javascript' src='js/_lcd_core.js'></script>
<script type='text/javascript' src='js/_lcd_html.js'></script>
</head>
<body onUnload='unload()'>
This is the popup.
<input type='button' value='test' onclick='test()' />

</body>
<script>
function unload() {
  window.opener.refreshme(1);
}
function test() {
  alert(window._callback);
}
</script>
</html>