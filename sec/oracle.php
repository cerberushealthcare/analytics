<?php
	
echo 'The path is <b>' . get_include_path() . '&&&' . PATH_SEPARATOR . '&&&' . $_SERVER['DOCUMENT_ROOT'] . '</b><br><br>';
require_once 'config/Environments.php';
require_once 'config/MyEnv.php';
//$con=oci_connect('userame','password','oracle_sid');
echo 'Connecting with username ' . MyEnv::$DB_USER . ' to 208.187.161.81...';
$conn = oci_connect(MyEnv::$DB_USER,'emrtest','208.187.161.81/pdborcl');
if($conn)
	echo "Connection succeeded";
else
{
	echo "Connection failed";
    //$err = oci_error();
	trigger_error(htmlentities($err['message'], ENT_QUOTES), E_USER_ERROR);	
}
?>
<table border=1 cellpadding=5><tr><td> Section Code</td><td>Section Name</td></tr>
<?php  
	$stid = oci_parse($conn, 'SELECT user_groups');
	oci_execute($stid);
	while (($row = oci_fetch_array($stid, OCI_BOTH)) != false) {
?>
  <tr>
	<td><?php echo $row[0] ?></td>
	<td><?php echo $row[1] ?></td>
  </tr>
<?php
}
oci_free_statement($stid);
oci_close($conn);
//http://windows.php.net/downloads/pecl/releases/oci8/2.0.8/php_oci8-2.0.8-5.5-ts-vc11-x64.zip*/
?>
</table>