              <div id="font-check" style="position:absolute;visibility:hidden">
                <span id="fc-calib" style="font-family:Calibri,Arial;font-size:14pt">This is a test</span>
                <span id="fc-arial" style="font-family:Arial;font-size:14pt">This is a test</span>
              </div>
<?
echo $_SERVER['HTTP_USER_AGENT']; 
$v = getIeVersion();
echo '<br>version=' . $v;

function getIeVersion() {
  $u = $_SERVER['HTTP_USER_AGENT'];
  $a = explode('MSIE ', $u);
  if (count($a) > 1) {
    $v = explode('.', $a[1]);
    return $v[0];
  }
}
?>
<script>
function msieversion() {
  var ua = window.navigator.userAgent
  var msie = ua.indexOf("MSIE ");
  if (msie > 0) {
    return parseInt(ua.substring(msie + 5, ua.indexOf(".", msie)));
  } else { 
    return 0;
  }
}
//setValue("vista", bool($("fc-arial").scrollWidth != $("fc-calib").scrollWidth));
//setValue("ie", msieversion());
</script>
