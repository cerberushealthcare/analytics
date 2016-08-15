<?php
require_once 'php/data/LoginSession.php';
//
LoginSession::verify_forUser()->requires($login->admin);
?>
<html>
<head>
</head>
<body>
  <div style='margin-bottom:10px;padding:5px;background-color:black;color:white;font-size:11pt'>
    <pre>
<?php
if (isset($_POST['dc'])) {
  $t = MyCrypt_Auto::decrypt($_POST['dc']);
  echo $t;
}
?>
    </pre>
  </div>
  <div>
    <form id='#form' method='post' action='tester-dc.php'>
      <textarea onkeypress='return oncr()' id='#dc' name='dc' style='width:100%' rows='10'></textarea>
      <input id='#submit' style='display:block' type='submit' />
    </form>
  </div>
</body>
<script>
document.getElementById('#dc').focus();
function oncr() {
  if (event.keyCode == 13) {
    document.getElementById('#submit').focus();
    document.getElementById('#form').submit();
    return false;
  }
}
</script>
</html>
