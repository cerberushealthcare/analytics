<html>
<head>
<script type='text/javascript' src='js/_lcd_core.js'></script>
<script type='text/javascript' src='js/_lcd_html.js'></script>
</head>
<body>
<input type='button' value='Pop up' onclick='pop()' />
<input type='button' value='Test refresh' onclick='refreshme()' />
<div id='ref'></div>
</body>
<script>
function pop() {
  var x = window.open('test555.php', 'x', 'height=200,width=500');
  x._callback = refreshme;
}
function refreshme(fromUnload) {
  var x = new Date().getTime();
  if (fromUnload) 
    x = x + ' IT WORKED!';
  _$('ref').setText(x);
}
</script>
</html>