<?php
	
	set_include_path('../..');
	
	require('php/data/Database.class.php');
	require('php/data/AssocArray.class.php');
	
	$testPassed = false;
	$sql = "select upload.upload_id, upload.user_group_id, upload.name, upload.blob_content, upload.status, 
			user_groups.upload_uid, user_groups.upload_pw, user_groups.user_group_id
			from upload
			left join user_groups
			on upload.user_group_id = user_groups.USER_GROUP_ID
			where upload.status = 'UPLOAD REQUESTED'
			order by upload.user_group_id";
	$vars = array();
	
	try {
		$obj = new AssocArray($sql, $vars, false, false, false, 'cin', 'oracle');
		$result = $obj->getAssoc();
		
		if (gettype($result) == 'array') {
			$testPassed = true;
		}
	}
	catch (Exception $e) {
		echo $e->getMessage();
	}
	
	include('postTestProcedures.php');
?>