<?php
set_include_path('../');
require_once 'app/util.php';
//
$airport = geta($_GET, 'a', 'SDF');
$rnd = mt_rand(10000000, 99999999);
?>
<html>
  <head>
    <title>Flight Tracker</title>
    <script language="JavaScript1.2" src="js/jquery-1.7.1.min.js"></script>
    <style>
      BODY {
        font-family:Calibri,Arial;
        font-size:40pt;
        background-color:#09335b;
        color:white;
      }
      A {
        color:white;
      }
      LABEL {
        display:inline-block;
        width:200px;
        text-align:right;
        margin-right:10px;
      }
      INPUT {
        font-size:40pt;
        border:1px solid white;
        color:#09335b;
      }
      INPUT.tb {
        background-color:white;
        font-family:Arial;
        padding:0 4px;
      }
      INPUT#sub {
        font-family:Calibri,Arial;
        margin-top:20px;
        background-color:gray;
        color:white;
        border:2px solid white;
        padding:0 15px;
      }
      FORM#frm {
        margin-top:50px;
      }
    </style>
  </head>
  <body>
    <form id="frm" method="get" action="index.php">
      <label>Airport</label><input id="a" class="tb" name="a"/><br/>
      <label>Lat</label><input id="t" class="tb" name="t"/><br/>
      <label>Long</label><input id="g" class="tb" name="g"/><br/>
      <label></label><input id='sub' type="submit" value="Submit" />
      <input type="hidden" name="rnd" value="<?=$rnd?>" />
      &nbsp;&nbsp;<a href='javascript:reset()'>Reset</a>
    </form>
  </body>
<script>
Function.prototype.curry = function() {
  var fn = this;
  var args = Array.prototype.slice.call(arguments);
  return function() {
    return fn.apply(fn, args.concat(Array.prototype.slice.call(arguments)));
  }
}
reset();
function load(pos) {
  $('#a').val('<?=$airport?>');
  $('#t').val(pos.coords.latitude);
  $('#g').val(pos.coords.longitude);
  //window.location.href = 'index.php?a=<?=$airport?>&t=' + pos.coords.latitude + '&g=' + pos.coords.longitude;
}
function reset() {
  $('.tb').val('');
  setTimeout(function() {
    navigator.geolocation.getCurrentPosition(load);
  }, 500);
}
</script>
</html>