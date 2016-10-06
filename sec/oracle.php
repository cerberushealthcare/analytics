<?php
	
echo 'The path is <b>' . get_include_path() . '&&&' . PATH_SEPARATOR . '&&&' . $_SERVER['DOCUMENT_ROOT'] . '</b><br><br>';
require_once 'config/Environments.php';
require_once 'config/MyEnv.php';
//$con=oci_connect('userame','password','oracle_sid');
echo 'Connecting with username cin to 208.187.161.81...';
$conn = oci_connect('cin','Cin123','208.187.161.81/pdborcl');
if($conn)
	echo "Connection succeeded";
else
{
	echo "Connection failed";
    //$err = oci_error();
	trigger_error(htmlentities($err['message'], ENT_QUOTES), E_USER_ERROR);	
}
?>
<table border=1 cellpadding=5><tr><td>Upload ID</td><td>status</td></tr>
<?php  
	$sql = "select upload.upload_id, upload.user_group_id, upload.practice_id, upload.name, upload.blob_content, upload.status, 
			user_groups.upload_uid, user_groups.upload_pw, user_groups.user_group_id
			from upload
			left join user_groups
			on upload.user_group_id = user_groups.USER_GROUP_ID
			where upload.status = 'UPLOAD REQUESTED'
			order by upload.user_group_id";
	$stid = oci_parse($conn, $sql);
	oci_execute($stid);
	echo 'We have ' . oci_fetch_all($stid, $res) . ' rows!';
	$err = oci_error($stid);
	if (!empty($err)) {
		echo 'Error: ' . $err['message'] . '. Query: ' . $err['sqltext'] . '<br>';
		exit;
	}
	
	while (($row = oci_fetch_array($stid, OCI_BOTH)) != false) {
?>
  <tr>
	<td><?php echo $row['UPLOAD_ID'] ?></td>
	<td><?php echo $row['STATUS'] ?></td>
  </tr>
<?php
}
oci_free_statement($stid);
oci_close($conn);
//http://windows.php.net/downloads/pecl/releases/oci8/2.0.8/php_oci8-2.0.8-5.5-ts-vc11-x64.zip*/
?>
</table>