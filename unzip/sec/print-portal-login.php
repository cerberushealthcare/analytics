<?php 
require_once "inc/requireLogin.php";
require_once 'inc/uiFunctions.php';
require_once 'php/data/rec/sql/PortalUsers.php';
//
$cid = $_GET['id'];
$puser = PortalUsers::editFor($cid);
?>
<html>
<head>
<title>Login Card</title>
<style>
BODY {
font-family:Arial;
}
TH {
text-align:right;
}
TD {
letter-spacing:1px;
font-family:'Courier New';
}
</style>
</head>
<body>
<p>Here is your login information:</p>
<table border=1 cellpadding=5 cellspacing=0>
<tr>
<th>Web Address</th><td><?=MyEnv::$BASE_URL?>portal</td>
</tr><tr>
<th>Login ID</th><td><?=$puser->uid ?></td>
</tr><tr>
<th>Password</th><td><?=$puser->pwpt ?></td>
</tr>
</table>
</body>
</html>
<script>
window.print();
</script>
