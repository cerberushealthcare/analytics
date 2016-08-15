<html>
<body>
<input id='t1' type='textbox' />
<input type='button' value='Copy' onclick='copyit()' />
<br/>
<input id='t2' type='textbox' />
</body>
<script>
function copyit() {
  var input = document.getElementById('t1');
  input.focus();
  input.select();
  document.execCommand('copy');
}
</script>
</html>

