<?php
$rf = isset($_GET['rf']);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">
<html>
  <head>
    <title>Papyrus Billing</title>
  </head>
  <frameset rows="35px,1*" frameborder='0' framespacing='0'>
    <frame src="cerberus-title.php" />
    <frame src="cerberus-body.php?<?=$_SERVER['QUERY_STRING'] ?>" />
  </frameset>
</html>