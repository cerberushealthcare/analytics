<html>
  <head>
    <title>Flight Tracker</title>
    <script language="JavaScript1.2" src="js/jquery-1.7.1.min.js"></script>
  </head>
  <body>
    <div id='output'></div>
  </body>
</html>
<script>
function showit(pos) {
  alert(pos.coords.latitude);
}
navigator.geolocation.getCurrentPosition(showit);
/*
$.ajax({
  url:'../server/srv-polling.php?action=fetch',
  dataType:'json',
  success:function(response) {
    alert(data);
  },
  error:function(http, status, error) {
    alert(status + ': ' + error);
  }
})
*/
</script>