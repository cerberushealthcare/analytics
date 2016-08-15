<html>
<head>
<script type='text/javascript' src='js/_lcd_core.js?$v'></script>
<script type='text/javascript' src='js/_lcd_html.js?$v'></script>
</head>
<body>
<div id='fred' onclick='stopit()'>0</div>
<div id='barney'>0</div>
<div id='stop'></div>
<div id='xray'></div>
</body>
<script>
var f = _$('fred');
var b = _$('barney');
var s = _$('stop');
var x = _$('xray');

document.addEventListener('webkitvisibilitychange', onbl);

f.setText(new Date().getTime());
aloop(function(exit) {
  if (s.innerText == 'stop')
    exit();
  var fi = String.toInt(f.innerText);
  var bi = String.toInt(b.innerText);
  var t = new Date().getTime();
  f.setText(t);
  if (t - fi > bi)
    b.setText(t - fi);
})
function stopit() {
  _$('stop').setText('stop');
}
function onbl() {
  x.setText('yo' + new Date().getTime());
}
</script>
</html>