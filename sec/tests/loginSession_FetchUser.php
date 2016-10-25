<?php
	set_include_path('../');
	require_once 'php/data/rec/sql/UserLogins.php';
	
	$testPassed = false;
	$user = UserLogin::fetchByUidtest('mm');
	if (gettype($user) == 'object') {
		$testPassed = true;
	}
	echo 'user is a ' . gettype($user);
	echo '<pre>';
	print_r($user);
	echo '</pre>';
	
	include('postTestProcedures.php');
?>